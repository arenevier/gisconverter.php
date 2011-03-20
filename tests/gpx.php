<?php

class GPX extends PHPUnit_Framework_TestCase {
    private $decoder = null;

    public function setup() {
        if (!$this->decoder) {
            $this->decoder = new gisconverter\GPX();
        }
    }

    /**
     * @expectedException gisconverter\InvalidText
     */
    public function testInvalidText1 () {
        $this->decoder->geomFromText('<Crap></Crap>');
    }

    public function testTracks() {
        $geom = $this->decoder->geomFromText('<trkseg><trkpt lon="5.6" lat="3.5"></trkpt><trkpt lon="10.5" lat="4.8"></trkpt><trkpt lon="10" lat="10"></trkpt></trkseg>');
        $this->assertEquals($geom->toGPX('trkseg'), '<trkseg><trkpt lon="5.6" lat="3.5"></trkpt><trkpt lon="10.5" lat="4.8"></trkpt><trkpt lon="10" lat="10"></trkpt></trkseg>');

        $geom = $this->decoder->geomFromText('<trkseg><trkpt lat="3.5" lon="5.6"></trkpt><trkpt lon="10.5"    lat="4.8"/> <trkpt lon="10" lat="10"><ele>1206.2</ele><speed>4.23</speed></trkpt></trkseg>');
        $this->assertEquals($geom->toGPX('trkseg'), '<trkseg><trkpt lon="5.6" lat="3.5"></trkpt><trkpt lon="10.5" lat="4.8"></trkpt><trkpt lon="10" lat="10"></trkpt></trkseg>');
    }

    /**
     * @expectedException gisconverter\InvalidText
     */
    public function testInvalidTracks () {
        $this->decoder->geomFromText('<trkseg><trkpt lat="3.5"></trkpt><trkpt lon="10.5" lat="4.8"></trkpt><trkpt lon="10" lat="10"></trkpt></trkseg>');
    }

    public function testRoutes() {
        $geom = $this->decoder->geomFromText('<rte><rtept lon="5.6" lat="3.5"></rtept><rtept lon="10.5" lat="4.8"></rtept><rtept lon="10" lat="10"></rtept></rte>');
        $this->assertEquals($geom->toGPX('rte'), '<rte><rtept lon="5.6" lat="3.5"></rtept><rtept lon="10.5" lat="4.8"></rtept><rtept lon="10" lat="10"></rtept></rte>');
    }

    public function testWaypoints() {
        $geom = $this->decoder->geomFromText('<wpt lon="10" lat="10"></wpt>');
        $this->assertEquals($geom->toGPX('wpt'), '<wpt lon="10" lat="10"></wpt>');
    }

    public function testFullDoc() {
        $geom = $this->decoder->geomFromText('<gpx version="1.0" xmlns="http://www.topografix.com/GPX/1/0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.topografix.com/GPX/1/0 http://www.topografix.com/GPX/1/0/gpx.xsd"><trk><author>user</author><trkseg><trkpt lon="5.6" lat="3.5"></trkpt><trkpt lon="10.5" lat="4.8"></trkpt><trkpt lon="10" lat="10"></trkpt></trkseg></trk></gpx>');
        $this->assertEquals($geom->toGPX('trkseg'), '<trkseg><trkpt lon="5.6" lat="3.5"></trkpt><trkpt lon="10.5" lat="4.8"></trkpt><trkpt lon="10" lat="10"></trkpt></trkseg>');
    }
}
