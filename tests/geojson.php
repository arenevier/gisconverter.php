<?php

class GeoJSON extends PHPUnit_Framework_TestCase {
    private $decoder = null;

    public function setup() {
        if (!$this->decoder) {
            $this->decoder = new gisconverter\GeoJSON();
        }
    }

    /**
     * @expectedException gisconverter\InvalidText
     */
    public function testInvalidText1 () {
        $this->decoder->geomFromText('{"type":"Crap","coordinates":[10,10]}');
    }

    /**
     * @expectedException gisconverter\InvalidText
     */
    public function testInvalidText2 () {
        $this->decoder->geomFromText('{"crap":"Point","coordinates":[10,10]}');
    }

    /**
     * @expectedException gisconverter\InvalidText
     */
    public function testInvalidText3 () {
        $this->decoder->geomFromText('{not well formed}');
    }

    public function testPoint() {
        $geom = $this->decoder->geomFromText('{"type":"Point","coordinates":[10,10]}');
        $this->assertEquals($geom->toGeoJSON(), '{"type":"Point","coordinates":[10,10]}');

        $geom = $this->decoder->geomFromText('{ "type" :"Point", "coordinates":  [10 ,10] } ');
        $this->assertEquals($geom->toGeoJSON(), '{"type":"Point","coordinates":[10,10]}');

        $geom = $this->decoder->geomFromText('{"type":"Point","coordinates":[0,0]}');
        $this->assertEquals($geom->toGeoJSON(), '{"type":"Point","coordinates":[0,0]}');
    }

    /**
     * @expectedException gisconverter\InvalidText
     */
    public function testInvalidPoint () {
        $this->decoder->geomFromText('{"type": "Point"}');
    }

    public function testMultiPoint() {
        $geom = $this->decoder->geomFromText('{"type":"MultiPoint","coordinates":[[3.5,5.6],[4.8,10.5],[10,10]]}');
        $this->assertEquals($geom->toGeoJSON(), '{"type":"MultiPoint","coordinates":[[3.5,5.6],[4.8,10.5],[10,10]]}');

        $geom = $this->decoder->geomFromText('{"type":"MultiPoint","coordinates":[]}');
        $this->assertEquals($geom->toGeoJSON(), '{"type":"MultiPoint","coordinates":[]}');
    }

    public function testLineString() {
        $geom = $this->decoder->geomFromText('{"type":"LineString","coordinates":[[3.5,5.6],[4.8,10.5],[10,10]]}');
        $this->assertEquals($geom->toGeoJSON(), '{"type":"LineString","coordinates":[[3.5,5.6],[4.8,10.5],[10,10]]}');
    }

    public function testMultiLineString() {
        $geom = $this->decoder->geomFromText('{"type":"MultiLineString","coordinates":[[[3.5,5.6],[4.8,10.5],[10,10]]]}');
        $this->assertEquals($geom->toGeoJSON(), '{"type":"MultiLineString","coordinates":[[[3.5,5.6],[4.8,10.5],[10,10]]]}');

        $geom = $this->decoder->geomFromText('{"type":"MultiLineString","coordinates":[[[3.5,5.6],[4.8,10.5],[10,10]],[[10,10],[10,20],[20,20],[20,15]]]}');
        $this->assertEquals($geom->toGeoJSON(), '{"type":"MultiLineString","coordinates":[[[3.5,5.6],[4.8,10.5],[10,10]],[[10,10],[10,20],[20,20],[20,15]]]}');
    }

    public function testLinearRing() {
        $geom = $this->decoder->geomFromText('{"type":"LinearRing","coordinates":[[3.5,5.6],[4.8,10.5],[10,10],[3.5,5.6]]}');
        $this->assertEquals($geom->toGeoJSON(), '{"type":"LinearRing","coordinates":[[3.5,5.6],[4.8,10.5],[10,10],[3.5,5.6]]}');
    }

    public function testPolygon() {
        $geom = $this->decoder->geomFromText('{"type":"Polygon","coordinates":[[[10,10],[10,20],[20,20],[20,15],[10,10]]]}');
        $this->assertEquals($geom->toGeoJSON(), '{"type":"Polygon","coordinates":[[[10,10],[10,20],[20,20],[20,15],[10,10]]]}');

        $geom = $this->decoder->geomFromText('{"type":"Polygon","coordinates":[[[0,0],[10,0],[10,10],[0,10],[0,0]],[[1,1],[9,1],[9,9],[1,9],[1,1]]]}');
        $this->assertEquals($geom->toGeoJSON(), '{"type":"Polygon","coordinates":[[[0,0],[10,0],[10,10],[0,10],[0,0]],[[1,1],[9,1],[9,9],[1,9],[1,1]]]}');
    }

    public function testMultiPolygon() {
        $geom = $this->decoder->geomFromText('{"type":"MultiPolygon","coordinates":[[[[10,10],[10,20],[20,20],[20,15],[10,10]]]]}');
        $this->assertEquals($geom->toGeoJSON(), '{"type":"MultiPolygon","coordinates":[[[[10,10],[10,20],[20,20],[20,15],[10,10]]]]}');

        $geom = $this->decoder->geomFromText('{"type":"MultiPolygon","coordinates":[[[[10,10],[10,20],[20,20],[20,15],[10,10]]],[[[60,60],[70,70],[80,60],[60,60]]]]}');
        $this->assertEquals($geom->toGeoJSON(), '{"type":"MultiPolygon","coordinates":[[[[10,10],[10,20],[20,20],[20,15],[10,10]]],[[[60,60],[70,70],[80,60],[60,60]]]]}');
    }

    public function testGeometryCollection() {
        $geom = $this->decoder->geomFromText('{"type":"GeometryCollection","geometries":[{"type":"Point","coordinates":[10,10]},{"type":"Point","coordinates":[30,30]},{"type":"LineString","coordinates":[[15,15],[20,20]]}]}');
        $this->assertEquals($geom->toGeoJSON(), '{"type":"GeometryCollection","geometries":[{"type":"Point","coordinates":[10,10]},{"type":"Point","coordinates":[30,30]},{"type":"LineString","coordinates":[[15,15],[20,20]]}]}');
    }

}
