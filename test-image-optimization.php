<?php
// Test script for image optimization // Test skripta za optimizaciju slike

// Function to optimize image // Funkcija za optimizaciju slike
function optimizeImage($source_path, $max_size = 800) {
    $img_type = exif_imagetype($source_path);
    $allowed_types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp', IMAGETYPE_GIF => 'gif'];

    if (!array_key_exists($img_type, $allowed_types)) {
        return false; // Unsupported format // Nepodržan format
    }

    switch ($img_type) {
        case IMAGETYPE_JPEG:
            $src_img = imagecreatefromjpeg($source_path);
            break;
        case IMAGETYPE_PNG:
            $src_img = imagecreatefrompng($source_path);
            break;
        case IMAGETYPE_WEBP:
            $src_img = imagecreatefromwebp($source_path);
            break;
        case IMAGETYPE_GIF:
            $src_img = imagecreatefromgif($source_path);
            break;
    }

    if (!$src_img) {
        return false; // Failed to load image // Niso uspeo da učitam sliku
    }

    $src_width = imagesx($src_img);
    $src_height = imagesy($src_img);

    // Calculate new size (max 800x800, keep ratio) // Izračunaj novu veličinu (max 800x800, očuvaj odnos)
    if ($src_width > $max_size || $src_height > $max_size) {
        $scale = min($max_size / $src_width, $max_size / $src_height);
        $new_width = (int)($src_width * $scale);
        $new_height = (int)($src_height * $scale);
    } else {
        $new_width = $src_width;
        $new_height = $src_height;
    }

    // Create new image // Kreiraj novu sliku
    $dst_img = imagecreatetruecolor($new_width, $new_height);
    // Preserve transparency for PNG // Očuvaj transparenciju za PNG
    if ($img_type == IMAGETYPE_PNG) {
        imagealphablending($dst_img, false);
        imagesavealpha($dst_img, true);
    }
    // Resize image // Promeni veličinu slike
    imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $new_width, $new_height, $src_width, $src_height);

    // Save optimized image // Sačuvaj optimizovanu sliku
    $success = false;
    switch ($img_type) {
        case IMAGETYPE_JPEG:
            $success = imagejpeg($dst_img, $source_path, 70); // Lower quality 70 for JPEG to test compression // Niži kvalitet 70 za JPEG da testiram kompresiju
            break;
        case IMAGETYPE_PNG:
            $success = imagepng($dst_img, $source_path, 9); // Compression 9 for PNG // Kompresija 9 za PNG
            break;
        case IMAGETYPE_WEBP:
            $success = imagewebp($dst_img, $source_path, 85); // Quality 85 for WEBP // Kvalitet 85 za WEBP
            break;
        case IMAGETYPE_GIF:
            $success = imagegif($dst_img, $source_path); // GIF with no quality param // GIF bez parametra kvaliteta
            break;
    }

    // Free memory // Oslobodi memoriju
    imagedestroy($src_img);
    imagedestroy($dst_img);

    return $success;
}

// Test with an existing image // Test sa postojećom slikom
$test_image = 'uploads/pets/105602265.jpg'; // Replace with an actual image path // Zameni sa stvarnom putanjom slike

// Reset file to original state before second test // Ponovo postavi fajl na originalno stanje prije drugog testa
copy('uploads/pets/105602265.jpg.bak', 'uploads/pets/105602265.jpg') or die("Cannot restore backup");

// Create a backup for second test // Kreiraj bekap za drugi test
copy('uploads/pets/105602265.jpg', 'uploads/pets/105602265.jpg.bak2');

if (file_exists($test_image)) {
    $original_size = filesize($test_image);
    $original_dimensions = getimagesize($test_image);

    echo "Original size: " . $original_size . " bytes<br>";
    echo "Original dimensions: " . $original_dimensions[0] . "x" . $original_dimensions[1] . "<br>";

    if (optimizeImage($test_image)) {
        $optimized_size = filesize($test_image);
        $optimized_dimensions = getimagesize($test_image);

        echo "Optimized size: " . $optimized_size . " bytes<br>";
        echo "Optimized dimensions: " . $optimized_dimensions[0] . "x" . $optimized_dimensions[1] . "<br>";

        if ($optimized_dimensions[0] == $original_dimensions[0] && $optimized_dimensions[1] == $original_dimensions[1]) {
            echo "No resizing needed (already within 800x800)<br>";
        } else {
            echo "Resized: " . $original_dimensions[0] . "x" . $original_dimensions[1] . " -> " . $optimized_dimensions[0] . "x" . $optimized_dimensions[1] . "<br>";
        }

        echo "Space saved: " . ($original_size - $optimized_size) . " bytes (" . round((($original_size - $optimized_size) / $original_size) * 100, 2) . "%)<br>";
    } else {
        echo "Optimization failed.";
    }
} else {
    echo "Test image not found.";
}
?>
