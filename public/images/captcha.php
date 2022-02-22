<?php
session_start();
$variableOfCaptcha = random_int(1000000, 9999999);
$_SESSION['variable_of_captcha'] = $variableOfCaptcha;
$variableOfCaptcha = (string) $variableOfCaptcha;

$font = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'georgia.ttf';
$img = imageCreateTrueColor(160, 25);
$grey = imageColorAllocate($img, 192, 192, 192);
$black = imageColorAllocate($img, 90, 90, 90);
imageFill($img, 0, 0, $grey);

$length = strlen($variableOfCaptcha);

for ($j = 0; $j < $length; $j++){
    $angle = random_int(-45, 45);
    $size = random_int(12, 22);
    static $x = 10;
    imageTtfText($img, $size, $angle, $x, 18, $black, $font, $variableOfCaptcha[$j]);
    $x += 20;
}

header("Content-type: image/jpeg");
imageJpeg($img, null, 100);