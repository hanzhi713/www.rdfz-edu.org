<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html>
<meta charset="utf-8">
<body>
<?php
session_start();
require 'msg.php';
use voku\helper\AntiXSS;

require 'vendor/autoload.php';
$antiXSS = new AntiXSS();
/**
 * process user's comment request
 * REQUIRED FIELDS:
 * @var int $userid
 * user must log in and have a session id
 * @var int $episode_id
 * the id of the episode which the user wants to comment on
 * @var string $comment_body
 * the body of the comment
 * */
if (!empty($_SESSION['id']) && !empty($_POST['episodeid'])) {
    require_once 'mysqli_conn.php';

    // make sure that user is not silenced
    $userid = intval($_SESSION['id']);
    $user = $mysqli->query("SELECT silence FROM userlist WHERE id={$userid}")->fetch_assoc();
    if ($user['silence'] != 0)
        exit(alertAndBack('Your are silenced by administrators and cannot post comment or reply!'));

    // check if the episode which the user commented on exists
    $episode_id = intval(trim($_POST['episodeid']));
    $epi_check = $mysqli->query("SELECT id FROM episodes where id={$episode_id}")->fetch_assoc();
    if (empty($epi_check['id']))
        exit(alertAndBack('The episode does not exist!'));

    $comment_time = time();
    $comment_body = $antiXSS->xss_clean($_POST['commentbody']);
    executeSQL('INSERT INTO comments (episode_id, userid, comment_time, comment_body) VALUES (?, ?, ?, ?)', array('iiis', $episode_id, $userid, $comment_time, $comment_body));
    echo alertAndBack('Comment Posted!');
} else
    exit(alertAndBack('Illegal Access!'));
$mysqli->close();
?>
</body>
</html>
