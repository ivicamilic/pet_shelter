<?php
$folder = __DIR__ . '/uploads/pets';
$maxSize = 800;

foreach (glob("$folder/*.{jpg,jpeg}", GLOB_BRACE) as $file) {
    $img = imagecreatefromjpeg($file);
    $width = imagesx($img);
    $height = imagesy($img);

    if ($width > $maxSize || $height > $maxSize) {
        $ratio = min($maxSize / $width, $maxSize / $height);
        $newWidth = intval($width * $ratio);
        $newHeight = intval($height * $ratio);

        $newImg = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($newImg, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagejpeg($newImg, $file, 85); // 85% kvalitet
        imagedestroy($newImg);
    }
    imagedestroy($img);
}
echo "Optimizacija završena!";
?>
