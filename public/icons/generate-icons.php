<?php

if (!extension_loaded('gd')) {
    fwrite(STDERR, "GD extension not available\n");
    exit(1);
}

$src = dirname(__DIR__) . '/mit.png';
$dir = __DIR__;

if (!is_dir($dir)) {
    mkdir($dir, 0775, true);
}

$data = @file_get_contents($src);
$img = $data ? @imagecreatefromstring($data) : false;
if (!$img) {
    fwrite(STDERR, "Failed to load source icon\n");
    exit(1);
}

foreach ([192, 512] as $size) {
    $out = imagecreatetruecolor($size, $size);
    imagealphablending($out, false);
    imagesavealpha($out, true);
    $transparent = imagecolorallocatealpha($out, 0, 0, 0, 127);
    imagefilledrectangle($out, 0, 0, $size, $size, $transparent);
    imagealphablending($out, true);

    $w = imagesx($img);
    $h = imagesy($img);
    $side = min($w, $h);
    $sx = (int) (($w - $side) / 2);
    $sy = (int) (($h - $side) / 2);
    imagecopyresampled($out, $img, 0, 0, $sx, $sy, $size, $size, $side, $side);

    $path = $dir . "/icon-{$size}.png";
    imagepng($out, $path);
    imagedestroy($out);
    echo "OK {$size} => {$path}\n";
}

imagedestroy($img);
