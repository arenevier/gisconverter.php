#!/usr/bin/php
<?php

// don't forget, you need php at least 5.3 for gisconverter.php
if (version_compare(PHP_VERSION, '5.3') < 0) {
    die ('Sorry, you need php at least 5.3 for gisconverter.php');
}

require ('gisconverter.php'); // first, include gisconverter.php library

/*
 * helper functions (see below)
 */
function wkt_to_geojson ($text) {
    $decoder = new gisconverter\WKT();
    return $decoder->geomFromText($text)->toGeoJSON();
}
function wkt_to_kml ($text) {
    $decoder = new gisconverter\WKT();
    return $decoder->geomFromText($text)->toKML();
}
function geojson_to_wkt ($text) {
    $decoder = new gisconverter\GeoJSON();
    return $decoder->geomFromText($text)->toWKT();
}
function geojson_to_kml ($text) {
    $decoder = new gisconverter\GeoJSON();
    return $decoder->geomFromText($text)->toKML();
}
function kml_to_wkt ($text) {
    $decoder = new gisconverter\KML();
    return $decoder->geomFromText($text)->toWKT();
}
function kml_to_geojson ($text) {
    $decoder = new gisconverter\KML();
    return $decoder->geomFromText($text)->toGeoJSON();
}

$decoder = new gisconverter\WKT(); # create a WKT decoder in gisconverter namespace
$geometry = $decoder->geomFromText('MULTIPOLYGON(((10 10,10 20,20 20,20 15,10 10)))'); # create a geometry from a given string input

print $geometry->toGeoJSON();      # output geometry in GeoJSON format
print "\n\n";

print $geometry->toKML();       # output geometry in KML format
print "\n\n";

#ok, you get the idea. Now, let's use helper functions

print geojson_to_kml('{"type":"LinearRing","coordinates":[[3.5,5.6],[4.8,10.5],[10,10],[3.5,5.6]]}');
print "\n\n";

print geojson_to_wkt('{"type":"LinearRing","coordinates":[[3.5,5.6],[4.8,10.5],[10,10],[3.5,5.6]]}');
print "\n\n";

print kml_to_wkt('<Polygon><outerBoundaryIs><LinearRing><coordinates>10,10 10,20 20,20 20,15 10,10</coordinates></LinearRing></outerBoundaryIs></Polygon>');
print "\n\n";

print kml_to_geojson('<Polygon><outerBoundaryIs><LinearRing><coordinates>10,10 10,20 20,20 20,15 10,10</coordinates></LinearRing></outerBoundaryIs></Polygon>');
print "\n\n";

?>
