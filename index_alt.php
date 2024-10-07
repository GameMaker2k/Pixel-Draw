<?php

function txt_draw_image($text, $imgtype = "png", $outputimage = true, $resize = 1, $resizetype = "resize", $outfile = null)
{
    // Ensure valid resize value
    if (!is_numeric($resize) || $resize < 1) {
        $resize = 1;
    }

    // Validate resizetype
    if ($resizetype != "resample" && $resizetype != "resize") {
        $resizetype = "resize";
    }

    // Clean up text input
    $text = preg_replace("/^#(.*?)\n/", "", $text);
    $text = preg_replace("/^\/\/(.*?)\n/", "", $text);

    // Handle resizing multiplier from the text input
    if (preg_match("/^([-]?[0-9]*[\.]?[0-9])x/", $text)) {
        preg_match("/^([-]?[0-9]*[\.]?[0-9])x/", $text, $resize_match);
        $resize = $resize_match[1];
        $text = preg_replace("/^([-]?[0-9]*[\.]?[0-9])x\n/", "", $text);
    }

    $text = trim($text);
    $text_y = explode("\n", $text);
    $num_y = count($text_y);
    $num_x = count(explode(" ", $text_y[0]));

    // Set image headers based on image type
    if ($outputimage) {
        switch ($imgtype) {
            case "gif":
                header("Content-Type: image/gif");
                break;
            case "jpg":
            case "jpeg":
                header("Content-Type: image/jpeg");
                break;
            default:
                header("Content-Type: image/png");
                break;
        }
    }

    // Create base image
    $txt_img = imagecreatetruecolor($num_x, $num_y);
    imagefilledrectangle($txt_img, 0, 0, $num_x, $num_y, 0xFFFFFF); // White background
    imageinterlace($txt_img, true);  // Enable interlacing

    // Fill image with pixel colors based on the input
    foreach ($text_y as $count_y => $line) {
        $text_x = explode(" ", $line);
        foreach ($text_x as $count_x => $pixel) {
            $pixel = trim($pixel) ?: "FFFFFF";  // Default color if missing
            $Transparency = 0;

            // Handle transparency
            if (preg_match("/^([0-9A-Fa-f]+):(0[0-9]{2}|1[0-1][0-9]|12[0-7]|[0-9]{2})$/", $pixel, $getTransparent)) {
                $pixel = $getTransparent[1];
                $Transparency = (int)$getTransparent[2];
            }

            // Parse pixel colors and draw
            $color = hexdec($pixel);
            imagesetpixel($txt_img, $count_x, $count_y, imagecolorallocatealpha($txt_img, ($color >> 16) & 0xFF, ($color >> 8) & 0xFF, $color & 0xFF, $Transparency));
        }
    }

    // Handle resizing
    if ($resize > 1) {
        $new_txt_img = imagecreatetruecolor($num_x * $resize, $num_y * $resize);
        imagefilledrectangle($new_txt_img, 0, 0, $num_x * $resize, $num_y * $resize, 0xFFFFFF);  // White background

        if ($resizetype == "resize") {
            imagecopyresized($new_txt_img, $txt_img, 0, 0, 0, 0, $num_x * $resize, $num_y * $resize, $num_x, $num_y);
        } else {
            imagecopyresampled($new_txt_img, $txt_img, 0, 0, 0, 0, $num_x * $resize, $num_y * $resize, $num_x, $num_y);
        }
        imagedestroy($txt_img);  // Free old image
        $txt_img = $new_txt_img;
    }

    // Output or save the image based on type
    switch ($imgtype) {
        case "gif":
            if ($outputimage) {
                imagegif($txt_img);
            }
            if ($outfile) {
                imagegif($txt_img, $outfile);
            }
            break;
        case "jpg":
        case "jpeg":
            if ($outputimage) {
                imagejpeg($txt_img);
            }
            if ($outfile) {
                imagejpeg($txt_img, $outfile);
            }
            break;
        default:
            if ($outputimage) {
                imagepng($txt_img);
            }
            if ($outfile) {
                imagepng($txt_img, $outfile);
            }
            break;
    }

    imagedestroy($txt_img);  // Free image memory
    return true;
}

// Example code to handle input file and call the function
if (!isset($_GET['pic'])) {
    $_GET['pic'] = null;
}

if (!preg_match("/([A-Za-z0-9\.\-_]+)\.pxd/is", $_GET['pic'])) {
    $_GET['pic'] = null;
}

if (empty($_GET['pic'])) {
    foreach (glob("*.pxd") as $filename) {
        $_GET['pic'] = $filename;
        break;
    }
}

if (!isset($_GET['imgtype']) || !in_array($_GET['imgtype'], ['png', 'gif', 'jpg', 'jpeg'])) {
    $_GET['imgtype'] = 'png';
}

// Call function to generate image
txt_draw_image(file_get_contents($_GET['pic']), $_GET['imgtype'], true);
