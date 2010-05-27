
<?php

class Equal extends PHPUnit_Framework_TestCase {
    private $default_decoder = null;

    public function setup() {
        if (!$this->default_decoder) {
            $this->default_decoder = new gisconverter\WKT();
        }
    }

    public function testPoint () {
        $geom1 = $this->default_decoder->geomFromText("POINT (10 10)");
        $geom2 = $this->default_decoder->geomFromText("POINT (10 10)");
        $geom3 = $this->default_decoder->geomFromText("POINT (20 20)");
        $this->assertTrue($geom1->equals($geom2));
        $this->assertFalse($geom1->equals($geom3));
    }

    public function testMultiPoint () {
        $geom1 = $this->default_decoder->geomFromText("MULTIPOINT (10 10, 20 20)");
        $geom2 = $this->default_decoder->geomFromText("MULTIPOINT (10 10, 20 20)");
        $geom3 = $this->default_decoder->geomFromText("MULTIPOINT (10 10, 20 20, 30 30)");
        $geom4 = $this->default_decoder->geomFromText("MULTIPOINT (11 10, 20 20)");
        $this->assertTrue($geom1->equals($geom2));
        $this->assertFalse($geom1->equals($geom3));
        $this->assertFalse($geom1->equals($geom4));
    }

    public function testLineString () {
        $geom1 = $this->default_decoder->geomFromText("LINESTRING (10 10, 20 20)");
        $geom2 = $this->default_decoder->geomFromText("LINESTRING (10 10, 20 20)");
        $geom3 = $this->default_decoder->geomFromText("LINESTRING (10 10, 20 20, 30 30)");
        $geom4 = $this->default_decoder->geomFromText("LINESTRING (11 10, 20 20)");
        $this->assertTrue($geom1->equals($geom2));
        $this->assertFalse($geom1->equals($geom3));
        $this->assertFalse($geom1->equals($geom4));
    }

    public function testDifferentClasses () {
        $point = $this->default_decoder->geomFromText("POINT (10 10)");
        $multipoint = $this->default_decoder->geomFromText("MULTIPOINT (10 10, 20 20)");
        $this->assertFalse($point->equals($multipoint));
    }

}

?>
