<?php
declare(strict_types=1);
require_once 'msg.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Load composer's autoloader
require 'vendor/autoload.php';

require dirname(__DIR__) . '/config/smtp.php';
//require '../config/config.php';

/**
 * get the web address of this host
 * @var string $host
 * the top picture address
 * @var string $picpath
 * the base server url
 * @var string $serverAdd
 * */
$host = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'];
$picpath = $host . '/Doc/Top.jpg';
$serverAdd = $host . getMsg('/en-US/');

/**
 * send an email
 * @param string $to
 * the receiver's address
 * @param string $subject
 * @param string $body
 * */
function postMail(string $to, string $subject = '', string $body = ''): void
{
    global $smtp_config;
    error_reporting(E_STRICT);
    $mail = new PHPMailer();
    $mail->CharSet = 'utf-8';
    $mail->isSMTP();
    $mail->SMTPAuth = true;
    $mail->Port = $smtp_config['port'];
    if ($smtp_config['ssl_enabled'])
        $mail->SMTPSecure = 'ssl';
    $mail->Host = $smtp_config['host'];
    $mail->Username = $smtp_config['username'];
    $mail->Password = $smtp_config['password'];
    $mail->setFrom($smtp_config['from_email'], getMsg('Educational Resources'));
    $mail->addReplyTo($smtp_config['from_email'], getMsg('Educational Resources'));
    $mail->Subject = $subject;
    $mail->AltBody = 'To view the content, please use a HTML compatible email viewer!';
    $mail->msgHTML($body);
    $mail->addAddress($to, '');
    if (!$mail->send()) {
        //echo 'Mailer Error: ' . $mail->ErrorInfo;
    }
}

/**
 * send an email with an activation link to an user
 * this function generates an automatic email template
 * @param string $to
 * the receiver's address
 * @param string $token
 * the activation code
 * */
function sendActEmail(string $to, string $token): void
{
    global $picpath, $serverAdd;
    $emailbody = '
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
* {font-family: Gotham, "Helvetica Neue", Helvetica, Arial, sans-serif;}
#title {cursor: pointer; text-decoration: none; font-weight: normal; padding-top: 80px;}
#title:hover {text-decoration: underline; font-weight: bold;}
#all {background-color: #FFFFE5; width: 800px; margin: auto auto; text-align: center;}
#top {background-color: #B4EFB5;}
#top_pic {background-image:url(' . $picpath . '); width: 800px; height: 150px; text-align: left; color: #FFFFFF; font-size: 26pt;}
#content {background-color: #D4FFDD; width: 800px; text-align: center; margin: auto auto; font-size: 22px;}
#bottom {background-color: #D4FFDD; width: 800px; text-align: center; margin: auto auto; font-size: 18px;}
</style>
</head>
<body style="background-color:#FFFFE5;">
<div id="all">
    <div id="top">
        <div id="top_pic">
            <p id="title" onclick="window.location.href=\'index.php\';">&nbsp;' . getMsg('Educational Resources') . '</p> </div>
    </div>
    <div id="content"> 
    	<div id="link">
            <br />
            ' . getMsg('Please click the following link or copy it to your browser to activate your account!') . '
            <br />
            <a href="' . $serverAdd . 'activate.php?code=' . $token . '">' . $serverAdd . 'activate.php?code=' . $token . '</a><br />
            <br />
        </div>
        <div id="bottom">
        ' . getMsg('This activation link will be expired in 24 hours, so please finish your registration as soon as possible. If you are not able to finish this registration immediately, you can login your account and resend the activation email.') . '
        </div>
</div>
</body>
</html>';
    postMail($to, getMsg('EduResources -- Account activation'), $emailbody);
}

/**
 * send an email with a password reset address to an user
 * this function generates an automatic email template
 * @param string $to
 * the receiver's address
 * @param string $token
 * the password reset token
 * */
function sendResetEmail(string $to, string $token): void
{
    global $picpath, $serverAdd;
    $emailsubject = getMsg('EduResources -- Reset Password');
    $emailbody = '
<!doctype html>
<html><head>
<meta charset="utf-8">
<style>
* {font-family: Gotham, "Helvetica Neue", Helvetica, Arial, sans-serif;}
#title {cursor: pointer; text-decoration: none; font-weight: normal; padding-top: 80px;}
#title:hover {text-decoration: underline; font-weight: bold;}
#all {background-color: #FFFFE5; width: 800px; margin: auto auto; text-align: center;}
#top {background-color: #B4EFB5;}
#top_pic {background-image:url(' . $picpath . '); width: 800px; height: 150px; text-align: left; color: #FFFFFF; font-size: 26pt;}
#content {background-color: #D4FFDD; width: 800px; text-align: center; margin: auto auto; font-size: 22px;}
#bottom {background-color: #D4FFDD; width: 800px; text-align: center; margin: auto auto; font-size: 18px;}
</style>
</head>
<body style="background-color:#FFFFE5;">
<div id="all">
    <div id="top">
        <div id="top_pic"><p id="title">&nbsp;' . getMsg('Educational Resources') . '</p></div>
    </div>
    <div id="content"> 
    	<div id="link">
            <br />' . getMsg('Please click the following link or copy it to your browser to reset your password!') . '<br /><br />
            <a href="' . $serverAdd . 'resetPass.php?code=' . $token . '">' . $serverAdd . 'resetPass.php?code=' . $token . '</a><br /><br />
        </div>
        <div id="bottom">' . getMsg('Note: This link will be expired in 12 hours') . '</div>
    </div>
</div>
</body>
</html>';
    postMail($to, $emailsubject, $emailbody);
}

/**
 * send an email inform user about a new publication
 * this function generates an automatic email template
 * @param string $to
 * the subscriber's address
 * @param string $subjectalt
 * the alternative name (display name) of this subject
 * @param string $subject
 * the name of this subject
 * @param string $title
 * the title of the new publication
 * @param int $id
 * the id of the new publication
 * the above five parameters are used to generate the link of the new publication.
 * */
function subscriptionEmail(string $to, string $subjectalt, string $subject, string $title, int $id): void
{
    global $host, $picpath;
    $emailsubject = getMsg('EduResources -- New publication!');
    $link = $host . getMsg('/en-US/') . "index.php?action=subjects&subject={$subject}&section=summary&episode={$title}";
    $emailbody = '
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
* {font-family: Gotham, "Helvetica Neue", Helvetica, Arial, sans-serif;}
#title {cursor: pointer; text-decoration: none; font-weight: normal; padding-top: 80px;}
#title:hover {text-decoration: underline; font-weight: bold;}
#all {background-color: #FFFFE5; width: 800px; margin: auto auto; text-align: center;}
#top {background-color: #B4EFB5;}
#top_pic {background-image:url(' . $picpath . '); width: 800px; height: 150px; text-align: left; color: #FFFFFF; font-size: 26pt;}
#content {background-color: #D4FFDD; width: 800px; text-align: center; margin: auto auto; font-size: 22px;}
</style>
</head>
<body style="background-color:#FFFFE5;">
<div id="all">
    <div id="top">
        <div id="top_pic">
            <p id="title">&nbsp;' . getMsg('Educational Resources') . '</p> </div>
    </div>
    <div id="content"> 
    	<div id="link">
            <br />
            ' . getMsg('A new ') . $subjectalt . getMsg(' episode is published!') . '<br />
            ' . getMsg('Title: ') . $title . '<br />
            <img width="600px" src="' . $host . "/Episodes/{$id}.png" . '">
            <br />
            <div style="width: 600px; margin: auto auto;">
                <span style="font-size: 18px;">' . getMsg('Link: ') . '</span>
                <a style="font-size: 16px" href="' . $link . '">' . $link . '</a><br />
            </div>
            <br />
        </div>
    </div>
</div>
</body>
</html>';
    postMail($to, $emailsubject, $emailbody);
}