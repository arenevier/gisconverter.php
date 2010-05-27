<?php

class Conversions extends PHPUnit_Framework_TestCase {
    private $default_decoder = null;

    public function setup() {
        if (!$this->default_decoder) {
            $this->default_decoder = new gisconverter\WKT();
        }
    }

    public function testPoint() {
        $geom = $this->default_decoder->geomFromText('POINT(10 10)');
        $this->assertEquals($geom->toGeoJSON(), '{"type":"Point","coordinates":[10,10]}');
        $this->assertEquals($geom->toKML(), '<Point><coordinates>10,10</coordinates></Point>');

        $geom = $this->default_decoder->geomFromText('POINT(0 0)');
        $this->assertEquals($geom->toGeoJSON(), '{"type":"Point","coordinates":[0,0]}');
        $this->assertEquals($geom->toKML(), '<Point><coordinates>0,0</coordinates></Point>');
    }

    public function testMultiPoint() {
        $geom = $this->default_decoder->geomFromText('MULTIPOINT(3.5 5.6,4.8 10.5,10 10)');
        $this->assertEquals($geom->toGeoJSON(), '{"type":"MultiPoint","coordinates":[[3.5,5.6],[4.8,10.5],[10,10]]}');
        $this->assertEquals($geom->toKML(), '<MultiGeometry><Point><coordinates>3.5,5.6</coordinates></Point><Point><coordinates>4.8,10.5</coordinates></Point><Point><coordinates>10,10</coordinates></Point></MultiGeometry>');

        $geom = $this->default_decoder->geomFromText('MULTIPOINT()');
        $this->assertEquals($geom->toGeoJSON(), '{"type":"MultiPoint","coordinates":[]}');
        $this->assertEquals($geom->toKML(), '<MultiGeometry></MultiGeometry>');
    }

    public function testLineString() {
        $geom = $this->default_decoder->geomFromText('LINESTRING(3.5 5.6,4.8 10.5,10 10)');
        $this->assertEquals($geom->toGeoJSON(), '{"type":"LineString","coordinates":[[3.5,5.6],[4.8,10.5],[10,10]]}');
        $this->assertEquals($geom->toKML(), '<LineString><coordinates>3.5,5.6 4.8,10.5 10,10</coordinates></LineString>');
    }

    public function testMultiLineString() {
        $geom = $this->default_decoder->geomFromText('MULTILINESTRING((3.5 5.6,4.8 10.5,10 10))');
        $this->assertEquals($geom->toGeoJSON(), '{"type":"MultiLineString","coordinates":[[[3.5,5.6],[4.8,10.5],[10,10]]]}');
        $this->assertEquals($geom->toKML(), '<MultiGeometry><LineString><coordinates>3.5,5.6 4.8,10.5 10,10</coordinates></LineString></MultiGeometry>');

        $geom = $this->default_decoder->geomFromText('MULTILINESTRING((3.5 5.6,4.8 10.5,10 10),(10 10,10 20,20 20,20 15))');
        $this->assertEquals($geom->toGeoJSON(), '{"type":"MultiLineString","coordinates":[[[3.5,5.6],[4.8,10.5],[10,10]],[[10,10],[10,20],[20,20],[20,15]]]}');
        $this->assertEquals($geom->toKML(), '<MultiGeometry><LineString><coordinates>3.5,5.6 4.8,10.5 10,10</coordinates></LineString><LineString><coordinates>10,10 10,20 20,20 20,15</coordinates></LineString></MultiGeometry>');
    }

    public function testLinearRing() {
        $geom = $this->default_decoder->geomFromText('LINEARRING(3.5 5.6,4.8 10.5,10 10,3.5 5.6)');
        $this->assertEquals($geom->toGeoJSON(), '{"type":"LinearRing","coordinates":[[3.5,5.6],[4.8,10.5],[10,10],[3.5,5.6]]}');
        $this->assertEquals($geom->toKML(), '<LinearRing><coordinates>3.5,5.6 4.8,10.5 10,10 3.5,5.6</coordinates></LinearRing>');
    }

    public function testPolygon() {
        $geom = $this->default_decoder->geomFromText('POLYGON((10 10,10 20,20 20,20 15,10 10))');
        $this->assertEquals($geom->toGeoJSON(), '{"type":"Polygon","coordinates":[[[10,10],[10,20],[20,20],[20,15],[10,10]]]}');
        $this->assertEquals($geom->toKML(), '<Polygon><outerBoundaryIs><LinearRing><coordinates>10,10 10,20 20,20 20,15 10,10</coordinates></LinearRing></outerBoundaryIs></Polygon>');

        $geom = $this->default_decoder->geomFromText('POLYGON((0 0,10 0,10 10,0 10,0 0),(1 1,9 1,9 9,1 9,1 1))');
        $this->assertEquals($geom->toGeoJSON(), '{"type":"Polygon","coordinates":[[[0,0],[10,0],[10,10],[0,10],[0,0]],[[1,1],[9,1],[9,9],[1,9],[1,1]]]}');
        $this->assertEquals($geom->toKML(), '<Polygon><outerBoundaryIs><LinearRing><coordinates>0,0 10,0 10,10 0,10 0,0</coordinates></LinearRing></outerBoundaryIs><innerBoundaryIs><LinearRing><coordinates>1,1 9,1 9,9 1,9 1,1</coordinates></LinearRing></innerBoundaryIs></Polygon>');
    }

    public function testMultiPolygon() {
        $geom = $this->default_decoder->geomFromText('MULTIPOLYGON(((10 10,10 20,20 20,20 15,10 10)))');
        $this->assertEquals($geom->toGeoJSON(), '{"type":"MultiPolygon","coordinates":[[[[10,10],[10,20],[20,20],[20,15],[10,10]]]]}');
        $this->assertEquals($geom->toKML(), '<MultiGeometry><Polygon><outerBoundaryIs><LinearRing><coordinates>10,10 10,20 20,20 20,15 10,10</coordinates></LinearRing></outerBoundaryIs></Polygon></MultiGeometry>');

        $geom = $this->default_decoder->geomFromText('MULTIPOLYGON(((10 10,10 20,20 20,20 15,10 10)),((60 60,70 70,80 60,60 60)))');
        $this->assertEquals($geom->toGeoJSON(), '{"type":"MultiPolygon","coordinates":[[[[10,10],[10,20],[20,20],[20,15],[10,10]]],[[[60,60],[70,70],[80,60],[60,60]]]]}');
        $this->assertEquals($geom->toKML(), '<MultiGeometry><Polygon><outerBoundaryIs><LinearRing><coordinates>10,10 10,20 20,20 20,15 10,10</coordinates></LinearRing></outerBoundaryIs></Polygon><Polygon><outerBoundaryIs><LinearRing><coordinates>60,60 70,70 80,60 60,60</coordinates></LinearRing></outerBoundaryIs></Polygon></MultiGeometry>');
    }

    public function testGeometryCollection() {
        $geom = $this->default_decoder->geomFromText('GEOMETRYCOLLECTION(POINT(10 10),POINT(30 30),LINESTRING(15 15,20 20))');
        $this->assertEquals($geom->toGeoJSON(), '{"type":"GeometryCollection","geometries":[{"type":"Point","coordinates":[10,10]},{"type":"Point","coordinates":[30,30]},{"type":"LineString","coordinates":[[15,15],[20,20]]}]}');
        $this->assertEquals($geom->toKML(), '<MultiGeometry><Point><coordinates>10,10</coordinates></Point><Point><coordinates>30,30</coordinates></Point><LineString><coordinates>15,15 20,20</coordinates></LineString></MultiGeometry>');
    }

}

?>
