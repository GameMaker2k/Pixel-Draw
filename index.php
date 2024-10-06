<?php

function txt_draw_image($text, $imgtype = "png", $outputimage = true, $resize = 1, $resizetype = "resize", $outfile = null)
{
    if (!isset($resize) || !is_numeric($resize) || $resize < 1) {
        $resize = 1;
    }
    if ($resizetype != "resample" && $resizetype != "resize") {
        $resizetype = "resize";
    }
    $text = preg_replace("/^#(.*?)\n/", "", $text);
    $text = preg_replace("/^\/\/(.*?)\n/", "", $text);
    if (preg_match("/^([-]?[0-9]*[\.]?[0-9])x/", $text)) {
        preg_match("/^([-]?[0-9]*[\.]?[0-9])x/", $text, $resize_match);
        $resize = $resize_match[1];
        //if($resize<1) { $resize = 1; }
        $text = preg_replace("/^([-]?[0-9]*[\.]?[0-9])x\n/", "", $text);
    }
    $text = trim($text);
    $text_y = explode("\n", $text);
    $num_y = count($text_y);
    if ($num_y < 1) {
        $text_y[0] = $text_y;
    }
    $num_x = count(explode(" ", $text_y[0]));
    if ($imgtype == "png") {
        if ($outputimage == true) {
            header("Content-Type: image/png");
        }
    }
    if ($imgtype == "gif") {
        if ($outputimage == true) {
            header("Content-Type: image/gif");
        }
    }
    if ($imgtype == "jpg" || $imgtype == "jpeg") {
        if ($outputimage == true) {
            header("Content-Type: image/jpeg");
        }
    }
    $txt_img = imagecreatetruecolor($num_x, $num_y);
    imagefilledrectangle($txt_img, 0, 0, $num_x, $num_y, 0xFFFFFF);
    imageinterlace($txt_img, true);
    $count_y = 0;
    while ($count_y < $num_y) {
        $text_x = explode(" ", $text_y[$count_y]);
        $count_x = 0;
        while ($count_x < $num_x) {
            $pixeldone = false;
            if (!isset($text_x[$count_x])) {
                $text_x[$count_x] = "FFFFFF";
            }
            $text_x[$count_x] = trim($text_x[$count_x]);
            $Transparency = 0;
            if (preg_match("/^([0-9A-Fa-f]+):(0[0-9][0-9]|1[0-1][0-9]|12[0-7]|[0-9][0-9])$/", $text_x[$count_x])) {
                preg_match("/^([0-9A-Fa-f]+):(0[0-9][0-9]|1[0-1][0-9]|12[0-7]|[0-9][0-9])$/", $text_x[$count_x], $getTransparent);
                $text_x[$count_x] = preg_replace("/^([0-9A-Fa-f]+):(0[0-9][0-9]|1[0-1][0-9]|12[0-7]|[0-9][0-9])$/", "\\1", $text_x[$count_x]);
                $Transparency = $getTransparent[2];
            }
            if (preg_match("/^([0-9A-Fa-f]{2})$/", $text_x[$count_x]) && $pixeldone === false) {
                preg_match("/^([0-9A-Fa-f]{2})$/", $text_x[$count_x], $color_matches);
                $c8bitX = 32;
                $c8bitXnum = 0;
                $c8bitY = 8;
                $c8bitYnum = 0;
                $c8bitBlue = 0;
                $c8bitRed = 0;
                $c8bitGreen = 0;
                $c8bitNum = 0;
                if (hexdec($color_matches[1]) >= 32 && hexdec($color_matches[1]) < 64) {
                    $c8bitBlue = 0;
                    $c8bitRed = 0;
                    $c8bitYnum = 1;
                }
                if (hexdec($color_matches[1]) >= 64 && hexdec($color_matches[1]) < 96) {
                    $c8bitBlue = 0;
                    $c8bitRed = 0;
                    $c8bitYnum = 2;
                }
                if (hexdec($color_matches[1]) >= 96 && hexdec($color_matches[1]) < 128) {
                    $c8bitBlue = 0;
                    $c8bitRed = 0;
                    $c8bitYnum = 3;
                }
                if (hexdec($color_matches[1]) >= 128 && hexdec($color_matches[1]) < 160) {
                    $c8bitBlue = 0;
                    $c8bitRed = 0;
                    $c8bitYnum = 4;
                }
                if (hexdec($color_matches[1]) >= 160 && hexdec($color_matches[1]) < 192) {
                    $c8bitBlue = 0;
                    $c8bitRed = 0;
                    $c8bitYnum = 5;
                }
                if (hexdec($color_matches[1]) >= 192 && hexdec($color_matches[1]) < 224) {
                    $c8bitBlue = 0;
                    $c8bitRed = 0;
                    $c8bitYnum = 6;
                }
                if (hexdec($color_matches[1]) >= 224) {
                    $c8bitBlue = 0;
                    $c8bitRed = 0;
                    $c8bitYnum = 7;
                }
                if (hexdec($color_matches[1]) >= 32) {
                    $c8bitGreen = 36 * $c8bitYnum;
                    $c8bitNum = 32 * $c8bitYnum;
                }
                while ($c8bitYnum < $c8bitY) {
                    $c8bitXnum = 0;
                    while ($c8bitXnum < $c8bitX) {
                        if (hexdec($color_matches[1]) == 255) {
                            imagesetpixel($txt_img, $count_x, $count_y, imagecolorallocatealpha($txt_img, 255, 255, 255, $Transparency));
                            $pixeldone = true;
                            break;
                        }
                        if (hexdec($color_matches[1]) == $c8bitNum) {
                            imagesetpixel($txt_img, $count_x, $count_y, imagecolorallocatealpha($txt_img, $c8bitRed, $c8bitGreen, $c8bitBlue, $Transparency));
                            $pixeldone = true;
                            break;
                        }
                        // Blue = 85|255 Red = 36|252 Green = 36|252
                        $c8bitBlue += 85;
                        if ($c8bitBlue >= 340) {
                            $c8bitRed += 36;
                            $c8bitBlue = 0;
                        }
                        if ($c8bitRed >= 288) {
                            $c8bitGreen += 36;
                            $c8bitRed = 0;
                        }
                        ++$c8bitXnum;
                        ++$c8bitNum;
                    }
                    ++$c8bitYnum;
                }
            }
            if (preg_match("/^([0-9A-Fa-f])$/", $text_x[$count_x]) && $pixeldone === false) {
                preg_match("/^([0-9A-Fa-f])$/", $text_x[$count_x], $color_matches);
                if ($color_matches[1] == "0") {
                    $text_x[$count_x] = "000000000";
                }
                if ($color_matches[1] == "1") {
                    $text_x[$count_x] = "104104104";
                }
                if ($color_matches[1] == "2") {
                    $text_x[$count_x] = "000018144";
                }
                if ($color_matches[1] == "3") {
                    $text_x[$count_x] = "000039251";
                }
                if ($color_matches[1] == "4") {
                    $text_x[$count_x] = "000143021";
                }
                if ($color_matches[1] == "5") {
                    $text_x[$count_x] = "000249044";
                }
                if ($color_matches[1] == "6") {
                    $text_x[$count_x] = "000144146";
                }
                if ($color_matches[1] == "7") {
                    $text_x[$count_x] = "000252254";
                }
                if ($color_matches[1] == "8") {
                    $text_x[$count_x] = "155023008";
                }
                if ($color_matches[1] == "9") {
                    $text_x[$count_x] = "255048022";
                }
                if ($color_matches[1] == "A") {
                    $text_x[$count_x] = "154032145";
                }
                if ($color_matches[1] == "B") {
                    $text_x[$count_x] = "255063252";
                }
                if ($color_matches[1] == "C") {
                    $text_x[$count_x] = "148145025";
                }
                if ($color_matches[1] == "D") {
                    $text_x[$count_x] = "255253051";
                }
                if ($color_matches[1] == "E") {
                    $text_x[$count_x] = "184184184";
                }
                if ($color_matches[1] == "F") {
                    $text_x[$count_x] = "255255255";
                }
            }
            if (preg_match("/(000[0-9]|00[1-9][0-9]|01[0-9][0-9]|02[0-9][0-9]|03[0-6][0-9]|037[0-7])(000[0-9]|00[1-9][0-9]|01[0-9][0-9]|02[0-9][0-9]|03[0-6][0-9]|037[0-7])(000[0-9]|00[1-9][0-9]|01[0-9][0-9]|02[0-9][0-9]|03[0-6][0-9]|037[0-7])/", $text_x[$count_x]) && $pixeldone === false) {
                preg_match("/(000[0-9]|00[1-9][0-9]|01[0-9][0-9]|02[0-9][0-9]|03[0-6][0-9]|037[0-7])(000[0-9]|00[1-9][0-9]|01[0-9][0-9]|02[0-9][0-9]|03[0-6][0-9]|037[0-7])(000[0-9]|00[1-9][0-9]|01[0-9][0-9]|02[0-9][0-9]|03[0-6][0-9]|037[0-7])/", $text_x[$count_x], $color_matches);
                imagesetpixel($txt_img, $count_x, $count_y, imagecolorallocatealpha($txt_img, octdec($color_matches[1]), octdec($color_matches[2]), octdec($color_matches[3]), $Transparency));
                $pixeldone = true;
            }
            if (preg_match("/(00[0-9]|0[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(00[0-9]|0[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(00[0-9]|0[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])/", $text_x[$count_x]) && $pixeldone === false) {
                preg_match("/(00[0-9]|0[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(00[0-9]|0[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(00[0-9]|0[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])/", $text_x[$count_x], $color_matches);
                imagesetpixel($txt_img, $count_x, $count_y, imagecolorallocatealpha($txt_img, $color_matches[1], $color_matches[2], $color_matches[3], $Transparency));
                $pixeldone = true;
            }
            if (preg_match("/([0-9A-Fa-f]{2})([0-9A-Fa-f]{2})([0-9A-Fa-f]{2})/", $text_x[$count_x]) && $pixeldone === false) {
                preg_match("/([0-9A-Fa-f]{2})([0-9A-Fa-f]{2})([0-9A-Fa-f]{2})/", $text_x[$count_x], $color_matches);
                imagesetpixel($txt_img, $count_x, $count_y, imagecolorallocatealpha($txt_img, hexdec($color_matches[1]), hexdec($color_matches[2]), hexdec($color_matches[3]), $Transparency));
                $pixeldone = true;
            }
            ++$count_x;
        } ++$count_y;
    }
    if ($resize > 1) {
        $new_txt_img = imagecreatetruecolor($num_x * $resize, $num_y * $resize);
        imagefilledrectangle($new_txt_img, 0, 0, $num_x * $resize, $num_y * $resize, 0xFFFFFF);
        imageinterlace($new_txt_img, true);
        if ($resizetype == "resize") {
            imagecopyresized($new_txt_img, $txt_img, 0, 0, 0, 0, $num_x * $resize, $num_y * $resize, $num_x, $num_y);
        }
        if ($resizetype == "resample") {
            imagecopyresampled($new_txt_img, $txt_img, 0, 0, 0, 0, $num_x * $resize, $num_y * $resize, $num_x, $num_y);
        }
        imagedestroy($txt_img);
        $txt_img = $new_txt_img;
    }
    if ($imgtype == "png") {
        if ($outputimage == true) {
            imagepng($txt_img);
        }
        if ($outfile != null) {
            imagepng($txt_img, $outfile);
        }
    }
    if ($imgtype == "gif") {
        if ($outputimage == true) {
            imagegif($txt_img);
        }
        if ($outfile != null) {
            imagegif($txt_img, $outfile);
        }
    }
    if ($imgtype == "jpg" || $imgtype == "jpeg") {
        if ($outputimage == true) {
            imagejpeg($txt_img);
        }
        if ($outfile != null) {
            imagejpeg($txt_img, $outfile);
        }
    }
    return true;
}
if (!isset($_GET['pic'])) {
    $_GET['pic'] = null;
}
if (!preg_match("/([A-Za-z0-9\.\-_]+)\.pxd/is", $_GET['pic'])) {
    $_GET['pic'] = null;
}
if (!isset($_GET['pic']) || $_GET['pic'] === null || $_GET['pic'] == "") {
    foreach (glob("*.pxd") as $filename) {
        $_GET['pic'] = $filename;
    }
}
if (!isset($_GET['imgtype'])) {
    $_GET['imgtype'] = "png";
}
if ($_GET['imgtype'] != "png" && $_GET['imgtype'] != "gif" && $_GET['imgtype'] != "jpg" &&
$_GET['imgtype'] != "jpeg") {
    $_GET['imgtype'] = "png";
}
txt_draw_image(file_get_contents($_GET['pic']), $_GET['imgtype'], true);
