<?php
declare(strict_types=1);
/**
 * The image resize function that I copied from internet with some changes
 * @link http://www.jb51.net/article/52380.htm
 * @param $imgsrc
 * the path of source image
 * @param $imgdst
 * the path of destination image
 * @param int $s
 * the maximum width and height allowed
 * */
function imageResize($imgsrc, $imgdst, int $s = 1000): void
{
    list($width, $height, $type) = getimagesize($imgsrc);
    if ($width > $s || $height > $s) {
        if ($width > $height) {
            $ratio = $width / $s;
            $new_width = $s;
            $new_height = (int)($height / $ratio);
        } else {
            $ratio = $height / $s;
            $new_height = $s;
            $new_width = (int)($width / $ratio);
        }
    } else {
        $new_width = $width;
        $new_height = $height;
    }
    switch ($type) {
        case 1:
            $giftype = check_gifcartoon($imgsrc);
            if ($giftype) {
                $image_wp = imagecreatetruecolor($new_width, $new_height);
                $image = imagecreatefromgif($imgsrc);
                imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                imagepng($image_wp, $imgdst);
                imagedestroy($image_wp);
            }
            break;
        case 2:
            $image_wp = imagecreatetruecolor($new_width, $new_height);
            $image = imagecreatefromjpeg($imgsrc);
            imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            imagepng($image_wp, $imgdst);
            imagedestroy($image_wp);
            break;
        case 3:
            $image_wp = imagecreatetruecolor($new_width, $new_height);
            //save the alpha channel
            $alpha = imagecolorallocatealpha($image_wp, 0, 0, 0, 127);
            imagefill($image_wp, 0, 0, $alpha);
            $image = imagecreatefrompng($imgsrc);
            imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            imagesavealpha($image_wp, true);
            imagepng($image_wp, $imgdst);
            imagedestroy($image_wp);
            break;
    }
}

function check_gifcartoon($image_file): bool
{
    $fp = fopen($image_file, 'rb');
    $image_head = fread($fp, 1024);
    fclose($fp);
    return preg_match("/" . chr(0x21) . chr(0xff) . chr(0x0b) . 'NETSCAPE2.0' . "/", $image_head) ? false : true;
}