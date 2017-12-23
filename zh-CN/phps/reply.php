<?php declare(strict_types=1);?>
<!DOCTYPE html>
<html>
<meta charset="UTF-8">
<body>
<?php
session_start();
require 'msg.php';
use voku\helper\AntiXSS;
require 'vendor/autoload.php';
$antiXSS = new AntiXSS();
/**
 * process user's reply request
 * REQUIRED FIELDS:
 * @var int $userid
 * user must log in and have a session id
 * @var int $comment_id
 * the id of the comment which the user wants to reply to
 * @var string $reply_body
 * the body of the reply
 * */
if (!empty($_SESSION['id']) && !empty($_POST['commentid'])) {
    require_once 'mysqli_conn.php';
    // make sure that user is not silenced
    $userid = intval($_SESSION['id']);
    $user = $mysqli->query("SELECT silence FROM userlist WHERE id={$userid}")->fetch_assoc();
    if ($user['silence'] != 0)
        exit(alertAndBack('Your are silenced by administrators and cannot post comment or reply!'));
    $comment_id = intval(trim($_POST['commentid']));
    $reply_time = time();
    $reply_body = $antiXSS->xss_clean($_POST['replybody']);
    executeSQL('INSERT INTO replies (comment_id, userid, reply_time, reply_body) VALUES (?, ?, ?, ?)',
        array('iiis', $comment_id, $userid, $reply_time, $reply_body));
    echo alertAndBack('Replied!');
} else
    alertAndBack('Illegal Access!');
$mysqli->close();
?></body>
</html>
