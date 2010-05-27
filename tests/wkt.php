<?php

class WKT extends PHPUnit_Framework_TestCase {
    private $decoder = null;

    public function setup() {
        if (!$this->decoder) {
            $this->decoder = new gisconverter\WKT();
        }
    }

    /**
     * @expectedException gisconverter\InvalidText
     */
    public function testInvalidText1 () {
        $this->decoder->geomFromText('CRAP');
    }

    /**
     * @expectedException gisconverter\InvalidText
     */
    public function testInvalidText2 () {
        $this->decoder->geomFromText('CRAP ()');
    }

    public function testPoint() {
        $geom = $this->decoder->geomFromText('POINT(10 10)');
        $this->assertEquals($geom->toWKT(), 'POINT(10 10)');

        $geom = $this->decoder->geomFromText(' POINT  ( 10  10 ) ');
        $this->assertEquals($geom->toWKT(), 'POINT(10 10)');

        $geom = $this->decoder->geomFromText('POINT(0 0)');
        $this->assertEquals($geom->toWKT(), 'POINT(0 0)');
    }

    public function testMultiPoint() {
        $geom = $this->decoder->geomFromText('MULTIPOINT(3.5 5.6,4.8 10.5,10 10)');
        $this->assertEquals($geom->toWKT(), 'MULTIPOINT(3.5 5.6,4.8 10.5,10 10)');

        $geom = $this->decoder->geomFromText('MULTIPOINT()');
        $this->assertEquals($geom->toWKT(), 'MULTIPOINT()');
    }

    public function testLineString() {
        $geom = $this->decoder->geomFromText('LINESTRING(3.5 5.6,4.8 10.5,10 10)');
        $this->assertEquals($geom->toWKT(), 'LINESTRING(3.5 5.6,4.8 10.5,10 10)');
    }

    public function testMultiLineString() {
        $geom = $this->decoder->geomFromText('MULTILINESTRING((3.5 5.6,4.8 10.5,10 10))');
        $this->assertEquals($geom->toWKT(), 'MULTILINESTRING((3.5 5.6,4.8 10.5,10 10))');

        $geom = $this->decoder->geomFromText('MULTILINESTRING((3.5 5.6,4.8 10.5,10 10),(10 10,10 20,20 20,20 15))');
        $this->assertEquals($geom->toWKT(), 'MULTILINESTRING((3.5 5.6,4.8 10.5,10 10),(10 10,10 20,20 20,20 15))');
    }

    public function testLinearRing() {
        $geom = $this->decoder->geomFromText('LINEARRING(3.5 5.6,4.8 10.5,10 10,3.5 5.6)');
        $this->assertEquals($geom->toWKT(), 'LINEARRING(3.5 5.6,4.8 10.5,10 10,3.5 5.6)');
    }

    public function testPolygon() {
        $geom = $this->decoder->geomFromText('POLYGON((10 10,10 20,20 20,20 15,10 10))');
        $this->assertEquals($geom->toWKT(), 'POLYGON((10 10,10 20,20 20,20 15,10 10))');

        $geom = $this->decoder->geomFromText('POLYGON((0 0,10 0,10 10,0 10,0 0),(1 1,9 1,9 9,1 9,1 1))');
        $this->assertEquals($geom->toWKT(), 'POLYGON((0 0,10 0,10 10,0 10,0 0),(1 1,9 1,9 9,1 9,1 1))');
    }

    public function testMultiPolygon() {
        $geom = $this->decoder->geomFromText('MULTIPOLYGON(((10 10,10 20,20 20,20 15,10 10)))');
        $this->assertEquals($geom->toWKT(), 'MULTIPOLYGON(((10 10,10 20,20 20,20 15,10 10)))');

        $geom = $this->decoder->geomFromText('MULTIPOLYGON(((10 10,10 20,20 20,20 15,10 10)),((60 60,70 70,80 60,60 60)))');
        $this->assertEquals($geom->toWKT(), 'MULTIPOLYGON(((10 10,10 20,20 20,20 15,10 10)),((60 60,70 70,80 60,60 60)))');
    }

    public function testGeometryCollection() {
        $geom = $this->decoder->geomFromText('GEOMETRYCOLLECTION(POINT(10 10),POINT(30 30),LINESTRING(15 15,20 20))');
        $this->assertEquals($geom->toWKT(), 'GEOMETRYCOLLECTION(POINT(10 10),POINT(30 30),LINESTRING(15 15,20 20))');
    }

}

?>
