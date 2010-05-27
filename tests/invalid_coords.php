<?php

class InvalidCoords extends PHPUnit_Framework_TestCase {
    private $default_decoder = null;

    public function setup() {
        if (!$this->default_decoder) {
            $this->default_decoder = new gisconverter\WKT();
        }
    }

    public function testLonLimit () {
        $geom = $this->default_decoder->geomFromText("POINT (180 10)");
        $geom = $this->default_decoder->geomFromText("POINT (-180 10)");
    }

    public function testLatLimit () {
        $geom = $this->default_decoder->geomFromText("POINT (10 90)");
        $geom = $this->default_decoder->geomFromText("POINT (10 -90)");
    }

    /**
     * @expectedException gisconverter\OutOfRangeLon
     */
    public function testOorLon1 () {
        $this->default_decoder->geomFromText("POINT (181 10)");
    }

    /**
     * @expectedException gisconverter\OutOfRangeLon
     */
    public function testOorLon2 () {
        $this->default_decoder->geomFromText("POINT (-181 10)");
    }

    /**
     * @expectedException gisconverter\OutOfRangeLon
     */
    public function testOorLon3 () {
        $this->default_decoder->geomFromText("POINT (crap 10)");
    }

    /**
     * @expectedException gisconverter\OutOfRangeLat
     */
    public function testOorLat1 () {
        $this->default_decoder->geomFromText("POINT (10 91)");
    }

    /**
     * @expectedException gisconverter\OutOfRangeLat
     */
    public function testOorLat2 () {
        $this->default_decoder->geomFromText("POINT (10 -91)");
    }

    /**
     * @expectedException gisconverter\OutOfRangeLat
     */
    public function testOorLat3 () {
        $this->default_decoder->geomFromText("POINT (10 crap)");
    }

}

?>
