<?php

class InvalidFeatures extends PHPUnit_Framework_TestCase {
    private $default_decoder = null;

    public function setup() {
        if (!$this->default_decoder) {
            $this->default_decoder = new gisconverter\WKT();
        }
    }

    /**
     * @expectedException gisconverter\InvalidFeature
     */
    public function testInvalidPoint () {
        $this->default_decoder->geomFromText('POINT (10)');
    }

    /**
     * @expectedException gisconverter\InvalidFeature
     */
    public function testInvalidMultiPoint1 () {
        $this->default_decoder->geomFromText('MULTIPOINT (10)');
    }

    /**
     * @expectedException gisconverter\InvalidFeature
     */
    public function testInvalidMultiPoint2 () {
        $this->default_decoder->geomFromText('MULTIPOINT (10 10,)');
    }

    /**
     * @expectedException gisconverter\InvalidFeature
     */
    public function testInvalidLineString () {
        $this->default_decoder->geomFromText("LINESTRING (10 10)");
    }

    /**
     * @expectedException gisconverter\InvalidFeature
     */
    public function testInvalidLinearRing1 () {
        $this->default_decoder->geomFromText("LINEARRING (10 10)");
    }

    /**
     * @expectedException gisconverter\InvalidFeature
     */
    public function testInvalidLinearRing2 () {
        $this->default_decoder->geomFromText("LINEARRING(3.5 5.6, 4.8 10.5, 10 10)");
    }

    /**
     * @expectedException gisconverter\InvalidFeature
     */
    public function testInvalidPolygon () {
        $this->default_decoder->geomFromText("POLYGON((0 0, 10 0, 10 10, 0 10, 0 0), (-1 1, 9 1, 9 9, 1 9, -1 1))");
    }

}
