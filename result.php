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
// An empty height keeps the assignment's square default.
$height = (isset($_GET['height']) && $_GET['height'] !== '')
    ? integerParameter('height', null, 1, 1000)
    : null;
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
$strokeColor = imagecolorallocate($img, 0, 0, 0);

function captionBounds($font, $size, $text) {
    $box = imagettfbbox($size, 0, $font, $text);
    return [
        'left' => min($box[0], $box[2], $box[4], $box[6]),
        'right' => max($box[0], $box[2], $box[4], $box[6]),
        'top' => min($box[1], $box[3], $box[5], $box[7]),
        'bottom' => max($box[1], $box[3], $box[5], $box[7])
    ];
}

function captionWidth($font, $size, $text) {
    $bounds = captionBounds($font, $size, $text);
    return $bounds['right'] - $bounds['left'];
}

function wrapCaption($text, $font, $size, $maxWidth) {
    $lines = [];
    foreach (preg_split('/\R/u', $text) as $paragraph) {
        $line = '';
        foreach (preg_split('/\s+/u', trim($paragraph), -1, PREG_SPLIT_NO_EMPTY) as $word) {
            $candidate = $line === '' ? $word : $line . ' ' . $word;
            if (captionWidth($font, $size, $candidate) <= $maxWidth) {
                $line = $candidate;
                continue;
            }
            if ($line !== '') {
                $lines[] = $line;
                $line = '';
            }
            // Split even a single long word so it cannot cross the side margins.
            foreach (preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY) as $character) {
                if ($line !== '' && captionWidth($font, $size, $line . $character) > $maxWidth) {
                    $lines[] = $line;
                    $line = '';
                }
                $line .= $character;
            }
        }
        $lines[] = $line;
    }
    return $lines;
}

function drawCaption($img, $size, $position, $color, $strokeColor, $font, $text) {
    $text = function_exists('mb_strtoupper') ? mb_strtoupper($text, 'UTF-8') : strtoupper($text);
    $strokeWidth = max(1, (int) round($size / 12));
    $margin = 20;
    $maxWidth = imagesx($img) - 2 * ($margin + $strokeWidth);
    $lines = wrapCaption($text, $font, $size, $maxWidth);
    $metrics = captionBounds($font, $size, 'Ag');
    $lineHeight = $metrics['bottom'] - $metrics['top'] + (int) ceil($size * 0.3) + 2 * $strokeWidth;
    $first = captionBounds($font, $size, $lines[0]);
    $last = captionBounds($font, $size, $lines[count($lines) - 1]);
    $y = $position === 'top'
        ? $margin + $strokeWidth - $first['top']
        : imagesy($img) - $margin - $strokeWidth - $last['bottom'] - (count($lines) - 1) * $lineHeight;

    foreach ($lines as $line) {
        $bounds = captionBounds($font, $size, $line);
        $x = (int) round((imagesx($img) - ($bounds['right'] - $bounds['left'])) / 2 - $bounds['left']);
        // Draw a black outline behind each centered white line.
        for ($dx = -$strokeWidth; $dx <= $strokeWidth; $dx++) {
            for ($dy = -$strokeWidth; $dy <= $strokeWidth; $dy++) {
                if ($dx * $dx + $dy * $dy <= $strokeWidth * $strokeWidth) {
                    imagettftext($img, $size, 0, $x + $dx, $y + $dy, $strokeColor, $font, $line);
                }
            }
        }
        imagettftext($img, $size, 0, $x, $y, $color, $font, $line);
        $y += $lineHeight;
    }
}

// Wrap and center captions inside the source image before resizing.
if (trim($text1) !== '') {
    drawCaption($img, $size1, 'top', $color, $strokeColor, $font, $text1);
}
if (trim($text2) !== '') {
    drawCaption($img, $size2, 'bottom', $color, $strokeColor, $font, $text2);
}

// Width and optional height come from GET; width alone produces a square.
if ($width !== null || $height !== null) {
    $outputWidth = $width ?? imagesx($img);
    $outputHeight = $height ?? $outputWidth;
    $scaled = imagescale($img, $outputWidth, $outputHeight);
    imagedestroy($img);
    $img = $scaled;
}
header('Content-Type: image/jpeg');
imagejpeg($img);
imagedestroy($img);


