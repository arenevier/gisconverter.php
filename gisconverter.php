<?php

# Copyright (c) 2010 Arnaud Renevier, Inc, published under the modified BSD
# license.

namespace gisconverter;

abstract class CustomException extends \Exception {
    protected $message;
    public function __toString() {
        return get_class($this) . " {$this->message} in {$this->file}({$this->line})\n{$this->getTraceAsString()}";
    }
}

class Unimplemented extends CustomException {
    public function __construct($message) {
        $this->message = "unimplemented $message";
    }
}

class UnimplementedMethod extends Unimplemented {
    public function __construct($method, $class) {
        $this->message = "method {$this->class}::{$this->method}";
    }
}

class InvalidText extends CustomException {
    public function __construct($decoder_name, $text = "") {
        $this->message =  "invalid text for decoder " . $decoder_name . ($text ? (": " . $text) : "");
    }
}

class InvalidFeature extends CustomException {
    public function __construct($decoder_name, $text = "") {
        $this->message =  "invalid feature for decoder $decoder_name" . ($text ? ": $text" : "");
    }
}

abstract class OutOfRangeCoord extends CustomException {
    private $coord;
    public $type;

    public function __construct($coord) {
        $this->message = "invalid {$this->type}: $coord";
    }
}
class OutOfRangeLon extends outOfRangeCoord {
    public $type = "longitude";
}
class OutOfRangeLat extends outOfRangeCoord {
    public $type = "latitude";
}

class UnavailableResource extends CustomException {
    public function __construct($ressource) {
        $this->message = "unavailable ressource: $ressource";
    }
}

interface iDecoder {
    /*
     * @param string $text
     * @return Geometry
     */
    static public function geomFromText($text);
}

abstract class Decoder implements iDecoder {
    static public function geomFromText($text) {
        throw new UnimplementedMethod(__FUNCTION__, get_called_class());
    }
}

interface iGeometry {
    /*
     * @return string
     */
    public function toGeoJSON();

    /*
     * @return string
     */
    public function toKML();

    /*
     * @return string
     */
    public function toWKT();

    /*
     * @param mode: trkseg, rte or wpt
     * @return string
     */
    public function toGPX($mode = null);

    /*
     * @param Geometry $geom
     * @return boolean
     */
    public function equals(Geometry $geom);
}

abstract class Geometry implements iGeometry {
    const name = "";

    public function toGeoJSON() {
        throw new UnimplementedMethod(__FUNCTION__, get_called_class());
    }

    public function toKML() {
        throw new UnimplementedMethod(__FUNCTION__, get_called_class());
    }

    public function toGPX($mode = null) {
        throw new UnimplementedMethod(__FUNCTION__, get_called_class());
    }

    public function toWKT() {
        throw new UnimplementedMethod(__FUNCTION__, get_called_class());
    }

    public function equals(Geometry $geom) {
        throw new UnimplementedMethod(__FUNCTION__, get_called_class());
    }

    public function __toString() {
        return $this->toWKT();
    }
}

class WKT extends Decoder {
    static public function geomFromText($text) {
        $ltext = strtolower($text);
        $type_pattern = '/\s*(\w+)\s*\(\s*(.*)\s*\)\s*$/';
        if (!preg_match($type_pattern, $ltext, $matches)) {
            throw new InvalidText(__CLASS__, $text);
        }
        foreach (array("Point", "MultiPoint", "LineString", "MultiLinestring", "LinearRing",
                       "Polygon", "MultiPolygon", "GeometryCollection") as $wkt_type) {
            if (strtolower($wkt_type) == $matches[1]) {
                $type = $wkt_type;
                break;
            }
        }

        if (!isset($type)) {
            throw new InvalidText(__CLASS__, $text);
        }

        try {
            $components = call_user_func(array('self', 'parse' . $type), $matches[2]);
        } catch(InvalidText $e) {
            throw new InvalidText(__CLASS__, $text);
        } catch(\Exception $e) {
            throw $e;
        }

        $constructor = __NAMESPACE__ . '\\' . $type;
        return new $constructor($components);
    }

    static protected function parsePoint($str) {
        return preg_split('/\s+/', trim($str));
    }

    static protected function parseMultiPoint($str) {
        $str = trim($str);
        if (strlen ($str) == 0) {
            return array();
        }
        return self::parseLineString($str);
    }

    static protected function parseLineString($str) {
        $components = array();
        foreach (preg_split('/,/', trim($str)) as $compstr) {
            $components[] = new Point(self::parsePoint($compstr));
        }
        return $components;
    }

    static protected function parseMultiLineString($str) {
        return self::_parseCollection($str, "LineString");
    }

    static protected function parseLinearRing($str) {
        return self::parseLineString($str);
    }

    static protected function parsePolygon($str) {
        return self::_parseCollection($str, "LinearRing");
    }

    static protected function parseMultiPolygon($str) {
        return self::_parseCollection($str, "Polygon");
    }

    static protected function parseGeometryCollection($str) {
        $components = array();
        foreach (preg_split('/,\s*(?=[A-Za-z])/', trim($str)) as $compstr) {
            $components[] = self::geomFromText($compstr);
        }
        return $components;
    }

    static protected function _parseCollection($str, $child_constructor) {
        $components = array();
        foreach (preg_split('/\)\s*,\s*\(/', trim($str)) as $compstr) {
            if (strlen($compstr) and $compstr[0] == '(') {
                $compstr = substr($compstr, 1);
            }
            if (strlen($compstr) and $compstr[strlen($compstr)-1] == ')') {
                $compstr = substr($compstr, 0, -1);
            }

            $childs = call_user_func(array('self', 'parse' . $child_constructor), $compstr);
            $constructor = __NAMESPACE__ . '\\' . $child_constructor;
            $components[] = new $constructor($childs);
        }
        return $components;
    }

}

class GeoJSON extends Decoder {

    static public function geomFromText($text) {
        $ltext = strtolower($text);
        $obj = json_decode($ltext);
        if (is_null ($obj)) {
            throw new InvalidText(__CLASS__, $text);
        }

        try {
            $geom = self::_geomFromJson($obj);
        } catch(InvalidText $e) {
            throw new InvalidText(__CLASS__, $text);
        } catch(\Exception $e) {
            throw $e;
        }

        return $geom;
    }

    static protected function _geomFromJson($json) {
        if (property_exists ($json, "geometry") and is_object($json->geometry)) {
            return self::_geomFromJson($json->geometry);
        }

        if (!property_exists ($json, "type") or !is_string($json->type)) {
            throw new InvalidText(__CLASS__);
        }

        foreach (array("Point", "MultiPoint", "LineString", "MultiLinestring", "LinearRing",
                       "Polygon", "MultiPolygon", "GeometryCollection") as $json_type) {
            if (strtolower($json_type) == $json->type) {
                $type = $json_type;
                break;
            }
        }

        if (!isset($type)) {
            throw new InvalidText(__CLASS__);
        }

        try {
            $components = call_user_func(array('self', 'parse'.$type), $json);
        } catch(InvalidText $e) {
            throw new InvalidText(__CLASS__);
        } catch(\Exception $e) {
            throw $e;
        }

        $constructor = __NAMESPACE__ . '\\' . $type;
        return new $constructor($components);
    }

   static protected function parsePoint($json) {
        if (!property_exists ($json, "coordinates") or !is_array($json->coordinates)) {
            throw new InvalidText(__CLASS__);
        }
        return $json->coordinates;
    }

    static protected function parseMultiPoint($json) {
        if (!property_exists ($json, "coordinates") or !is_array($json->coordinates)) {
            throw new InvalidText(__CLASS__);
        }
        return array_map(function($coords) {
            return new Point($coords);
        }, $json->coordinates);
    }

    static protected function parseLineString($json) {
        return self::parseMultiPoint($json);
    }

    static protected function parseMultiLineString($json) {
        $components = array();
        if (!property_exists ($json, "coordinates") or !is_array($json->coordinates)) {
            throw new InvalidText(__CLASS__);
        }
        foreach ($json->coordinates as $coordinates) {
            $linecomp = array();
            foreach ($coordinates as $coordinates) {
                $linecomp[] = new Point($coordinates);
            }
            $components[] = new LineString($linecomp);
        }
        return $components;
    }

    static protected function parseLinearRing($json) {
        return self::parseMultiPoint($json);
    }

    static protected function parsePolygon($json) {
        $components = array();
        if (!property_exists ($json, "coordinates") or !is_array($json->coordinates)) {
            throw new InvalidText(__CLASS__);
        }
        foreach ($json->coordinates as $coordinates) {
            $ringcomp = array();
            foreach ($coordinates as $coordinates) {
                $ringcomp[] = new Point($coordinates);
            }
            $components[] = new LinearRing($ringcomp);
        }
        return $components;
    }

    static protected function parseMultiPolygon($json) {
        $components = array();
        if (!property_exists ($json, "coordinates") or !is_array($json->coordinates)) {
            throw new InvalidText(__CLASS__);
        }
        foreach ($json->coordinates as $coordinates) {
            $polycomp = array();
            foreach ($coordinates as $coordinates) {
                $ringcomp = array();
                foreach ($coordinates as $coordinates) {
                    $ringcomp[] = new Point($coordinates);
                }
                $polycomp[] = new LinearRing($ringcomp);
            }
            $components[] = new Polygon($polycomp);
        }
        return $components;
    }

    static protected function parseGeometryCollection($json) {
        if (!property_exists ($json, "geometries") or !is_array($json->geometries)) {
            throw new InvalidText(__CLASS__);
        }
        $components = array();
        foreach ($json->geometries as $geometry) {
            $components[] = self::_geomFromJson($geometry);
        }

        return $components;
    }

}

abstract class XML extends Decoder {
    static public function geomFromText($text) {
        if (!function_exists("simplexml_load_string") || !function_exists("libxml_use_internal_errors")) {
            throw new UnavailableResource("simpleXML");
        }
        $ltext = strtolower($text);
        libxml_use_internal_errors(true);
        $xmlobj = simplexml_load_string($ltext);
        if ($xmlobj === false) {
            throw new InvalidText(__CLASS__, $text);
        }

        try {
            $geom = static::_geomFromXML($xmlobj);
        } catch(InvalidText $e) {
            throw new InvalidText(__CLASS__, $text);
        } catch(\Exception $e) {
            throw $e;
        }

        return $geom;
    }

    static protected function childElements($xml, $nodename = "") {
        $nodename = strtolower($nodename);
        $res = array();
        foreach ($xml->children() as $child) {
            if ($nodename) {
                if (strtolower($child->getName()) == $nodename) {
                    array_push($res, $child);
                }
            } else {
                array_push($res, $child);
            }
        }
        return $res;
    }

    protected static function _geomFromXML($xml) {}
}

class KML extends XML {
    static protected function parsePoint($xml) {
        $coordinates = self::_extractCoordinates($xml);
        $coords = preg_split('/,/', (string)$coordinates[0]);
        return array_map("trim", $coords);
    }

    static protected function parseLineString($xml) {
        $coordinates = self::_extractCoordinates($xml);
        foreach (preg_split('/\s+/', trim((string)$coordinates[0])) as $compstr) {
            $coords = preg_split('/,/', $compstr);
            $components[] = new Point($coords);
        }
        return $components;
    }

    static protected function parseLinearRing($xml) {
        return self::parseLineString($xml);
    }

    static protected function parsePolygon($xml) {
        $ring = array();
        foreach (self::childElements($xml, 'outerboundaryis') as $elem) {
            $ring = array_merge($ring, self::childElements($elem, 'linearring'));
        }

        if (count($ring) != 1) {
            throw new InvalidText(__CLASS__);
        }

        $components = array(new LinearRing(self::parseLinearRing($ring[0])));
        foreach (self::childElements($xml, 'innerboundaryis') as $elem) {
            foreach (self::childElements($elem, 'linearring') as $ring) {
                $components[] = new LinearRing(self::parseLinearRing($ring[0]));
            }
        }
        return $components;
    }

    static protected function parseMultiGeometry($xml) {
        $components = array();
        foreach ($xml->children() as $child) {
            $components[] = self::_geomFromXML($child);
        }
        return $components;
    }

    static protected function _extractCoordinates($xml) {
        $coordinates = self::childElements($xml, 'coordinates');
        if (count($coordinates) != 1) {
            throw new InvalidText(__CLASS__);
        }
        return $coordinates;
    }

    static protected function _geomFromXML($xml) {
        $nodename = strtolower($xml->getName());
        if ($nodename == "kml" or $nodename == "placemark") {
            $childs = $xml->children();
            $components = array();
            foreach (self::childElements($xml) as $child) {
                try {
                    $geom = self::_geomFromXML($child);
                    $components[] = $geom;
                } catch(InvalidText $e) {
                }
            }
            $ncomp = count($components);
            if ($ncomp == 0) {
                throw new InvalidText(__CLASS__);
            } else if ($ncomp == 1) {
                return $components[0];
            } else {
                return new GeometryCollection($components);
            }
        }

        foreach (array("Point", "LineString", "LinearRing", "Polygon", "MultiGeometry") as $kml_type) {
            if (strtolower($kml_type) == $nodename) {
                $type = $kml_type;
                break;
            }
        }

        if (!isset($type)) {
            throw new InvalidText(__CLASS__);
        }

        try {
            $components = call_user_func(array('self', 'parse'.$type), $xml);
        } catch(InvalidText $e) {
            throw new InvalidText(__CLASS__);
        } catch(\Exception $e) {
            throw $e;
        }

        if ($type == "MultiGeometry") {
            if (count($components)) {
                $possibletype = $components[0]::name;
                $sametype = true;
                foreach (array_slice($components, 1) as $component) {
                    if ($component::name != $possibletype) {
                        $sametype = false;
                        break;
                    }
                }
                if ($sametype) {
                    switch ($possibletype) {
                        case "Point":
                            return new MultiPoint($components);
                        break;
                        case "LineString":
                            return new MultiLineString($components);
                        break;
                        case "Polygon":
                            return new MultiPolygon($components);
                        break;
                        default:
                        break;
                    }
                }
            }
            return new GeometryCollection($components);
        }

        $constructor = __NAMESPACE__ . '\\' . $type;
        return new $constructor($components);
    }
}

class GPX extends XML {
    static protected function _extractCoordinates($xml) {
        $attributes = $xml->attributes();
        $lon = (string) $attributes['lon'];
        $lat = (string) $attributes['lat'];
        if (!$lon or !$lat) {
            throw new InvalidText(__CLASS__);
        }
        return array($lon, $lat);
    }

    static protected function parseTrkseg($xml) {
        $res = array();
        foreach ($xml->children() as $elem) {
            if (strtolower($elem->getName()) == "trkpt") {
                $res[] = new Point(self::_extractCoordinates($elem));
            }
        }
        return $res;
    }

    static protected function parseRte($xml) {
        $res = array();
        foreach ($xml->children() as $elem) {
            if (strtolower($elem->getName()) == "rtept") {
                $res[] = new Point(self::_extractCoordinates($elem));
            }
        }
        return $res;
    }

    static protected function parseWpt($xml) {
        return self::_extractCoordinates($xml);
    }

    static protected function _geomFromXML($xml) {
        foreach (array("Trkseg", "Rte", "Wpt") as $kml_type) {
            if (strtolower($kml_type) == $xml->getName()) {
                $type = $kml_type;
                break;
            }
        }

        if (!isset($type)) {
            throw new InvalidText(__CLASS__);
        }

        try {
            $components = call_user_func(array('self', 'parse'.$type), $xml);
        } catch(InvalidText $e) {
            throw new InvalidText(__CLASS__);
        } catch(\Exception $e) {
            throw $e;
        }

        if ($type == "Trkseg" or $type == "Rte") {
            $constructor = __NAMESPACE__ . '\\' . 'LineString';
        } else if ($type == "Wpt") {
            $constructor = __NAMESPACE__ . '\\' . 'Point';
        }
        return new $constructor($components);
    }
}

class Point extends Geometry {
    const name = "Point";

    private $lon;
    private $lat;

    public function __construct($coords) {
        if (count ($coords) < 2) {
            throw new InvalidFeature(__CLASS__, "Point must have two coordinates");
        }
        $lon = $coords[0];
        $lat = $coords[1];
        if (!$this->checkLon($lon)) {
            throw new OutOfRangeLon($lon);
        }
        if (!$this->checkLat($lat)) {
            throw new OutOfRangeLat($lat);
        }
        $this->lon = (float)$lon;
        $this->lat = (float)$lat;
    }

    public function __get($property) {
        if ($property == "lon") {
            return $this->lon;
        } else if ($property == "lat") {
            return $this->lat;
        } else {
            throw new \Exception ("Undefined property");
        }
    }

    public function toWKT() {
        return strtoupper(static::name) . "({$this->lon} {$this->lat})";
    }

    public function toKML() {
        return "<" . static::name . "><coordinates>{$this->lon},{$this->lat}</coordinates></" . static::name . ">";
    }

    public function toGPX($mode = null) {
        if (!$mode) {
            $mode = "wpt";
        }
        if ($mode != "wpt") {
            throw new UnimplementedMethod(__FUNCTION__, get_called_class());
        }
        return "<wpt lon=\"{$this->lon}\" lat=\"{$this->lat}\"></wpt>";
    }

    public function toGeoJSON() {
        $value = (object)array ('type' => static::name, 'coordinates' => array($this->lon, $this->lat));
        return json_encode($value);
    }

    public function equals(Geometry $geom) {
        if (get_class ($geom) != get_class($this)) {
            return false;
        }
        return $geom->lat == $this->lat && $geom->lon == $this->lon;
    }

    private function checkLon($lon) {
        if (!is_numeric($lon)) {
            return false;
        }
        if ($lon < -180 || $lon > 180) {
            return false;
        }
        return true;
    }
    private function checkLat($lat) {
        if (!is_numeric($lat)) {
            return false;
        }
        if ($lat < -90 || $lat > 90) {
            return false;
        }
        return true;
    }
}

abstract class Collection extends Geometry {
    protected $components;

    public function __get($property) {
        if ($property == "components") {
            return $this->components;
        } else {
            throw new \Exception ("Undefined property");
        }
    }

    public function toWKT() {
        $recursiveWKT = function ($geom) use (&$recursiveWKT) {
            if ($geom instanceof Point) {
                return "{$geom->lon} {$geom->lat}";
            } else {
                return "(" . implode (',', array_map($recursiveWKT, $geom->components)). ")";
            }
       };
        return strtoupper(static::name) . call_user_func($recursiveWKT, $this);
    }

    public function toGeoJSON() {
        $recurviseJSON = function ($geom) use (&$recurviseJSON) {
            if ($geom instanceof Point) {
                return array($geom->lon, $geom->lat);
            } else {
                return array_map($recurviseJSON, $geom->components);
            }
        };
        $value = (object)array ('type' => static::name, 'coordinates' => call_user_func($recurviseJSON, $this));
        return json_encode($value);
    }

    public function toKML() {
        return '<MultiGeometry>' . implode("", array_map(function($comp) { return $comp->toKML(); }, $this->components)) . '</MultiGeometry>';
    }

}

class MultiPoint extends Collection {
    const name = "MultiPoint";

    public function __construct($components) {
        foreach ($components as $comp) {
            if (!($comp instanceof Point)) {
                throw new InvalidFeature(__CLASS__, static::name . " can only contain Point elements");
            }
        }
        $this->components = $components;
    }

    public function equals(Geometry $geom) {
        if (get_class ($geom) != get_class($this)) {
            return false;
        }
        if (count($this->components) != count($geom->components)) {
            return false;
        }
        foreach (range(0, count($this->components) - 1) as $count) {
            if (!$this->components[$count]->equals($geom->components[$count])) {
                return false;
            }
        }
        return true;
    }

}

class LineString extends MultiPoint {
    const name = "LineString";
    public function __construct($components) {
        if (count ($components) < 2) {
            throw new InvalidFeature(__CLASS__, "LineString must have at least 2 points");
        }
        parent::__construct($components);
    }

    public function toKML() {
        return "<" . static::name . "><coordinates>" . implode(" ", array_map(function($comp) {
                    return "{$comp->lon},{$comp->lat}";
                }, $this->components)). "</coordinates></" . static::name . ">";
    }

    public function toGPX($mode = null) {
        if (!$mode) {
            $mode = "trkseg";
        }
        if ($mode != "trkseg" and $mode != "rte") {
            throw new UnimplementedMethod(__FUNCTION__, get_called_class());
        }
        if ($mode == "trkseg") {
            return '<trkseg>' . implode ("", array_map(function ($comp) {
                return "<trkpt lon=\"{$comp->lon}\" lat=\"{$comp->lat}\"></trkpt>";
            }, $this->components)). "</trkseg>";
        } else {
            return '<rte>' . implode ("", array_map(function ($comp) {
                return "<rtept lon=\"{$comp->lon}\" lat=\"{$comp->lat}\"></rtept>";
            }, $this->components)). "</rte>";
        }
    }

}

class MultiLineString extends Collection {
    const name = "MultiLineString";

    public function __construct($components) {
        foreach ($components as $comp) {
            if (!($comp instanceof LineString)) {
                throw new InvalidFeature(__CLASS__, "MultiLineString can only contain LineString elements");
            }
        }
        $this->components = $components;
    }

}

class LinearRing extends LineString {
    const name = "LinearRing";
    public function __construct($components) {
        $first = $components[0];
        $last = end($components);
        if (!$first->equals($last)) {
            throw new InvalidFeature(__CLASS__, "LinearRing must be closed");
        }
        parent::__construct($components);
    }
    public function contains(Geometry $geom) {
        if ($geom instanceof Collection) {
            foreach ($geom->components as $point) {
                if (!$this->contains($point)) {
                    return false;
                }
            }
            return true;
        } else if ($geom instanceof Point) {
            return $this->containsPoint($geom);
        } else {
            throw new Unimplemented(get_class($this) . "::" . __FUNCTION__ . " for " . get_class($geom) . " geometry");
        }
    }

    protected function containsPoint(Point $point) {
    /*
     *PHP implementation of OpenLayers.Geometry.LinearRing.ContainsPoint algorithm
     */
        $px = round($point->lon, 14);
        $py = round($point->lat, 14);

        $crosses = 0;
        foreach (range(0, count($this->components) - 2) as $i) {
            $start = $this->components[$i];
            $x1 = round($start->lon, 14);
            $y1 = round($start->lat, 14);
            $end = $this->components[$i + 1];
            $x2 = round($end->lon, 14);
            $y2 = round($end->lat, 14);

            if($y1 == $y2) {
                // horizontal edge
                if($py == $y1) {
                    // point on horizontal line
                    if($x1 <= $x2 && ($px >= $x1 && $px <= $x2) || // right or vert
                       $x1 >= $x2 && ($px <= $x1 && $px >= $x2)) { // left or vert
                        // point on edge
                        $crosses = -1;
                        break;
                    }
                }
                // ignore other horizontal edges
                continue;
            }

            $cx = round(((($x1 - $x2) * $py) + (($x2 * $y1) - ($x1 * $y2))) / ($y1 - $y2), 14);

            if($cx == $px) {
                // point on line
                if($y1 < $y2 && ($py >= $y1 && $py <= $y2) || // upward
                   $y1 > $y2 && ($py <= $y1 && $py >= $y2)) { // downward
                    // point on edge
                    $crosses = -1;
                    break;
                }
            }
            if($cx <= $px) {
                // no crossing to the right
                continue;
            }
            if($x1 != $x2 && ($cx < min($x1, $x2) || $cx > max($x1, $x2))) {
                // no crossing
                continue;
            }
            if($y1 < $y2 && ($py >= $y1 && $py < $y2) || // upward
               $y1 > $y2 && ($py < $y1 && $py >= $y2)) { // downward
                $crosses++;
            }
        }
        $contained = ($crosses == -1) ?
        // on edge
        1 :
        // even (out) or odd (in)
        !!($crosses & 1);

        return $contained;
    }

}

class Polygon extends Collection {
    const name = "Polygon";
    public function __construct($components) {
        $outer = $components[0];
        foreach (array_slice($components, 1) as $inner) {
            if (!$outer->contains($inner)) {
                throw new InvalidFeature(__CLASS__, "Polygon inner rings must be enclosed in outer ring");
            }
        }
        foreach ($components as $comp) {
            if (!($comp instanceof LinearRing)) {
                throw new InvalidFeature(__CLASS__, "Polygon can only contain LinearRing elements");
            }
        }
        $this->components = $components;
    }

    public function toKML() {
        $str = '<outerBoundaryIs>' . $this->components[0]->toKML() . '</outerBoundaryIs>';
        $str .= implode("", array_map(function($comp) {
            return '<innerBoundaryIs>' . $comp->toKML() . '</innerBoundaryIs>';
        }, array_slice($this->components, 1)));
        return '<' . self::name . '>' . $str . '</' . self::name . '>';
    }

}

class MultiPolygon extends Collection {
    const name = "MultiPolygon";

    public function __construct($components) {
        foreach ($components as $comp) {
            if (!($comp instanceof Polygon)) {
                throw new InvalidFeature(__CLASS__, "MultiPolygon can only contain Polygon elements");
            }
        }
        $this->components = $components;
    }

}

class GeometryCollection extends Collection {
    const name = "GeometryCollection";

    public function __construct($components) {
        foreach ($components as $comp) {
            if (!($comp instanceof Geometry)) {
                throw new InvalidFeature(__CLASS__, "GeometryCollection can only contain Geometry elements");
            }
        }
        $this->components = $components;
    }

    public function toWKT() {
        return strtoupper(static::name) . "(" . implode(',', array_map(function ($comp) {
            return $comp->toWKT();
        }, $this->components)) . ')';
    }

    public function toGeoJSON() {
        $value = (object)array ('type' => static::name, 'geometries' =>
            array_map(function ($comp) {
                // XXX: quite ugly
                return json_decode($comp->toGeoJSON());
            }, $this->components)
        );
        return json_encode($value);
    }
}

?>
