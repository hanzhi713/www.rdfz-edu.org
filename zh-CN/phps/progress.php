<?php
declare(strict_types=1);
/**
 * output the percentage of bytes uploaded
 * percentage = bytes_processed / content_length * 100
 * */
session_start();
$i = ini_get('session.upload_progress.name');
$key = ini_get("session.upload_progress.prefix") . $_GET[$i];
if (!empty($_SESSION[$key])) {
    $current = $_SESSION[$key]["bytes_processed"];
    $total = $_SESSION[$key]["content_length"];
    if ($current < $total)
        echo strval($current / $total * 100);
    else
        echo '100';
} else
    echo '100';