<?php

class KML extends PHPUnit_Framework_TestCase {
    private $decoder = null;

    public function setup() {
        if (!$this->decoder) {
            $this->decoder = new gisconverter\KML();
        }
    }

    /**
     * @expectedException gisconverter\InvalidText
     */
    public function testInvalidText1 () {
        $this->decoder->geomFromText('<Crap></Crap>');
    }

    /**
     * @expectedException gisconverter\InvalidText
     */
    public function testInvalidText2 () {
        $this->decoder->geomFromText('<Point><coordinates>10, 10<coordinates></Point>');
    }

    public function testPoint() {
        $geom = $this->decoder->geomFromText('<Point><coordinates>10,10</coordinates></Point>');
        $this->assertEquals($geom->toKML(), '<Point><coordinates>10,10</coordinates></Point>');

        $geom = $this->decoder->geomFromText('  <Point>  <coordinates>10,  10 </coordinates></Point> ');
        $this->assertEquals($geom->toKML(), '<Point><coordinates>10,10</coordinates></Point>');

        $geom = $this->decoder->geomFromText('<Point><coordinates>0,0</coordinates></Point>');
        $this->assertEquals($geom->toKML(), '<Point><coordinates>0,0</coordinates></Point>');

        $geom = $this->decoder->geomFromText('<Point><coordinates>10,10</coordinates><crap>some stuff</crap></Point>');
        $this->assertEquals($geom->toKML(), '<Point><coordinates>10,10</coordinates></Point>');
    }

    /**
     * @expectedException gisconverter\InvalidText
     */
    public function testInvalidPoint1 () {
        $this->decoder->geomFromText('<Point>10, 10</Point>');
    }

    /**
     * @expectedException gisconverter\InvalidText
     */
    public function testInvalidPoint2 () {
        $this->decoder->geomFromText('<Point><coordinates>10, 10</coordinates><coordinates>10, 10</coordinates></Point>');
    }

    public function testLineString() {
        $geom = $this->decoder->geomFromText('<LineString><coordinates>3.5,5.6 4.8,10.5 10,10</coordinates></LineString>');
        $this->assertEquals($geom->toKML(), '<LineString><coordinates>3.5,5.6 4.8,10.5 10,10</coordinates></LineString>');
    }

    public function testLinearRing() {
        $geom = $this->decoder->geomFromText('<LinearRing><coordinates>3.5,5.6 4.8,10.5 10,10 3.5,5.6</coordinates></LinearRing>');
        $this->assertEquals($geom->toKML(), '<LinearRing><coordinates>3.5,5.6 4.8,10.5 10,10 3.5,5.6</coordinates></LinearRing>');
    }

    public function testPolygon() {
        $geom = $this->decoder->geomFromText('<Polygon><outerBoundaryIs><LinearRing><coordinates>10,10 10,20 20,20 20,15 10,10</coordinates></LinearRing></outerBoundaryIs></Polygon>');
        $this->assertEquals($geom->toKML(), '<Polygon><outerBoundaryIs><LinearRing><coordinates>10,10 10,20 20,20 20,15 10,10</coordinates></LinearRing></outerBoundaryIs></Polygon>');

        $geom = $this->decoder->geomFromText('<Polygon><outerBoundaryIs><LinearRing><coordinates>0,0 10,0 10,10 0,10 0,0</coordinates></LinearRing></outerBoundaryIs><innerBoundaryIs><LinearRing><coordinates>1,1 9,1 9,9 1,9 1,1</coordinates></LinearRing></innerBoundaryIs></Polygon>');
        $this->assertEquals($geom->toKML(), '<Polygon><outerBoundaryIs><LinearRing><coordinates>0,0 10,0 10,10 0,10 0,0</coordinates></LinearRing></outerBoundaryIs><innerBoundaryIs><LinearRing><coordinates>1,1 9,1 9,9 1,9 1,1</coordinates></LinearRing></innerBoundaryIs></Polygon>');
    }

    /**
     * @expectedException gisconverter\InvalidText
     */
    public function testInvalidPolygon() {
        $geom = $this->decoder->geomFromText('<Polygon><innerBoundaryIs><LinearRing><coordinates>1,1 9,1 9,9 1,9 1,1</coordinates></LinearRing></innerBoundaryIs></Polygon>');
    }

    public function testMultiPoint() {
        $geom = $this->decoder->geomFromText('<MultiGeometry><Point><coordinates>3.5,5.6</coordinates></Point><Point><coordinates>4.8,10.5</coordinates></Point><Point><coordinates>10,10</coordinates></Point></MultiGeometry>');
        $this->assertEquals($geom->toKML(), '<MultiGeometry><Point><coordinates>3.5,5.6</coordinates></Point><Point><coordinates>4.8,10.5</coordinates></Point><Point><coordinates>10,10</coordinates></Point></MultiGeometry>');

    }

    public function testEmptyMultiGeometry() {
        $geom = $this->decoder->geomFromText('<MultiGeometry></MultiGeometry>');
        $this->assertEquals($geom->toKML(), '<MultiGeometry></MultiGeometry>');
    }

    public function testMultiLineString() {
        $geom = $this->decoder->geomFromText('<MultiGeometry><LineString><coordinates>3.5,5.6 4.8,10.5 10,10</coordinates></LineString></MultiGeometry>');
        $this->assertEquals($geom->toKML(), '<MultiGeometry><LineString><coordinates>3.5,5.6 4.8,10.5 10,10</coordinates></LineString></MultiGeometry>');

        $geom = $this->decoder->geomFromText('<MultiGeometry><LineString><coordinates>3.5,5.6 4.8,10.5 10,10</coordinates></LineString><LineString><coordinates>10,10 10,20 20,20 20,15</coordinates></LineString></MultiGeometry>');
        $this->assertEquals($geom->toKML(), '<MultiGeometry><LineString><coordinates>3.5,5.6 4.8,10.5 10,10</coordinates></LineString><LineString><coordinates>10,10 10,20 20,20 20,15</coordinates></LineString></MultiGeometry>');
    }

    public function testMultiPolygon() {
        $geom = $this->decoder->geomFromText('<MultiGeometry><Polygon><outerBoundaryIs><LinearRing><coordinates>10,10 10,20 20,20 20,15 10,10</coordinates></LinearRing></outerBoundaryIs></Polygon></MultiGeometry>');
        $this->assertEquals($geom->toKML(), '<MultiGeometry><Polygon><outerBoundaryIs><LinearRing><coordinates>10,10 10,20 20,20 20,15 10,10</coordinates></LinearRing></outerBoundaryIs></Polygon></MultiGeometry>');

        $geom = $this->decoder->geomFromText('<MultiGeometry><Polygon><outerBoundaryIs><LinearRing><coordinates>10,10 10,20 20,20 20,15 10,10</coordinates></LinearRing></outerBoundaryIs></Polygon><Polygon><outerBoundaryIs><LinearRing><coordinates>60,60 70,70 80,60 60,60</coordinates></LinearRing></outerBoundaryIs></Polygon></MultiGeometry>');
        $this->assertEquals($geom->toKML(), '<MultiGeometry><Polygon><outerBoundaryIs><LinearRing><coordinates>10,10 10,20 20,20 20,15 10,10</coordinates></LinearRing></outerBoundaryIs></Polygon><Polygon><outerBoundaryIs><LinearRing><coordinates>60,60 70,70 80,60 60,60</coordinates></LinearRing></outerBoundaryIs></Polygon></MultiGeometry>');
    }

    public function testGeometryCollection() {
        $geom = $this->decoder->geomFromText('<MultiGeometry><Point><coordinates>10,10</coordinates></Point><Point><coordinates>30,30</coordinates></Point><LineString><coordinates>15,15 20,20</coordinates></LineString></MultiGeometry>');
        $this->assertEquals($geom->toKML(), '<MultiGeometry><Point><coordinates>10,10</coordinates></Point><Point><coordinates>30,30</coordinates></Point><LineString><coordinates>15,15 20,20</coordinates></LineString></MultiGeometry>');
    }

    /**
     * @expectedException gisconverter\Unimplemented
     */
    public function invalidConversion1() {
        $decoder = new gisconverter\WKT();
        $geom = $decoder->geomFromText('POINT(10 10)');
        $geom->toGPX('rte');
    }

    /**
     * @expectedException gisconverter\Unimplemented
     */
    public function invalidConversion2() {
        $decoder = new gisconverter\WKT();
        $geom = $decoder->geomFromText('MULTIPOINT(3.5 5.6,4.8 10.5,10 10)');
        $geom->toGPX();
    }

    /**
     * @expectedException gisconverter\Unimplemented
     */
    public function invalidConversion3() {
        $decoder = new gisconverter\WKT();
        $geom = $decoder->geomFromText('LINESTRING(3.5 5.6,4.8 10.5,10 10)');
        $geom->toGPX('wpt');
    }

    public function testFullDoc() {
        $geom = $this->decoder->geomFromText('<?xml version="1.0" encoding="UTF-8"?><kml xmlns="http://www.opengis.net/kml/2.2"><Placemark><Point><coordinates>10,10</coordinates></Point></Placemark></kml>'); // <? <-- vim syntax goes crazy
        $this->assertEquals($geom->toKML(), '<Point><coordinates>10,10</coordinates></Point>');
        $geom = $this->decoder->geomFromText('<?xml version="1.0" encoding="UTF-8"?><kml xmlns="http://www.opengis.net/kml/2.2"><Point><coordinates>10,10</coordinates></Point></kml>'); // <? <-- vim syntax goes crazy
        $this->assertEquals($geom->toKML(), '<Point><coordinates>10,10</coordinates></Point>');
        $geom = $this->decoder->geomFromText('<?xml version="1.0" encoding="UTF-8"?><kml xmlns="http://www.opengis.net/kml/2.2"><Document><Placemark><Point><coordinates>10,10</coordinates></Point></Placemark></Document></kml>'); // <? <-- vim syntax goes crazy
        $this->assertEquals($geom->toKML(), '<Point><coordinates>10,10</coordinates></Point>');
    }

}

?>
