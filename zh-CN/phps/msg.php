<?php
declare(strict_types=1);
require dirname(__DIR__) . '/locale/for_php.php';
/**
 * show a message box, then go to the last page(javascript)
 * @param string $msg
 * the message that appears in the message box
 * @return string
 * */
function alertAndBack(string $msg): string
{
    return '<script>alert("' . getMsg($msg) . '");window.location.replace(document.referrer);</script>';
}

/**
 * show a message box (javascript)
 * @param string $msg
 * the message that appears in the message box
 * @return string
 * */
function alert(string $msg): string
{
    return '<script>alert("' . getMsg($msg) . '");</script>';
}

/**
 * go to the last page(javascript)
 * @return string
 * */
function goBack(): string
{
    return '<script>window.location.replace(document.referrer);</script>';
}

/**
 * show a message box, then redirect to a new address
 * @param string $msg
 * the message that appears in the message box
 * @param string $add
 * the address which the web page will redirect to next
 * @return string
 * */
function alertAndRedirect(string $msg, string $add): string
{
    return '<script>alert("' . getMsg($msg) . '");window.location.replace("' . $add . '");</script>';
}

/**
 * translate the message using the dictionary
 * @param string $msg
 * @return string
 * */
function getMsg(string $msg): string
{
    global $dictionary;
    return isset($dictionary[$msg]) ? $dictionary[$msg] : $msg;
}