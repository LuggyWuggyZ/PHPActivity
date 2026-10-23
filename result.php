<?php
require 'data.inc.php';

function badRequest($message) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    exit($message);
}

$filename = $_GET['file'] ?? '';
// Only allow the filenames in the supplied painting data.
if (!is_string($filename) || !in_array($filename, array_column($paintings, 'filename'), true)) {
    badRequest('Please select a valid painting.');
}

function integerParameter($name, $default, $min, $max) {
    if (!isset($_GET[$name])) return $default;
    $value = filter_var($_GET[$name], FILTER_VALIDATE_INT);
    if ($value === false || $value < $min || $value > $max) {
        badRequest("Invalid $name value.");
    }
    return $value;
}

$width = integerParameter('width', null, 1, 1000);
$size1 = integerParameter('size1', 24, 12, 64);
$size2 = integerParameter('size2', 48, 12, 48);
$text1 = $_GET['text1'] ?? '';
$text2 = $_GET['text2'] ?? '';
foreach ([$text1, $text2] as $text) {
    if (!is_string($text) || strlen($text) > 500) {
        badRequest('Text must be a string of at most 500 bytes.');
    }
}

if (!extension_loaded('gd') || !function_exists('imagettftext')) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Enable the PHP GD extension with FreeType support to render images.');
}

$img = imagecreatefromjpeg('images/' . $filename . '.jpg');
$font = 'font/Lato-Medium.ttf';
$color = imagecolorallocate($img, 255, 255, 255);

// Position captions inside the source image before resizing.
if ($text1 !== '') {
    imagettftext($img, $size1, 0, 10, $size1 + 20, $color, $font, $text1);
}
if ($text2 !== '') {
    imagettftext($img, $size2, 0, 10, imagesy($img) - 20, $color, $font, $text2);
}

// A supplied width produces a square, as required by the assignment.
if ($width !== null) {
    $scaled = imagescale($img, $width, $width);
    imagedestroy($img);
    $img = $scaled;
}
header('Content-Type: image/jpeg');
imagejpeg($img);
imagedestroy($img);
