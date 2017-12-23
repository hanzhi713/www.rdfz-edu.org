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
 * process user's subscription request
 * @var int $id
 * user must log in and has a session id
 * @var string $subscribe_subj
 * the name of subject
 * */
if (empty($_SESSION['id']))
    exit(goBack());
$id = intval($_SESSION['id']);
if (empty($_GET['subject']))
    exit(goBack());
else
    $subscribe_subj = $antiXSS->xss_clean(trim($_GET['subject']));
require_once 'mysqli_conn.php';

// check if this subject exists
$subjs = $mysqli->query('SELECT name, id FROM subjects');
$subscribe_id = -1;
while ($s = $subjs->fetch_assoc()) {
    if ($subscribe_subj === $s['name']) {
        $subscribe_id = $s['id'];
        break;
    }
}

// if this subject exists
if ($subscribe_id > -1) {
    $user_info = $mysqli->query("SELECT id FROM subscribe_{$subscribe_id} WHERE id=$id")->fetch_assoc();

    // add record of subscription
    if (empty($user_info['id']))
        $mysqli->query("INSERT INTO subscribe_{$subscribe_id} (id) VALUES ($id)");

    // if user already subscribed, cancel the subscription
    else
        $mysqli->query("DELETE FROM subscribe_{$subscribe_id} WHERE id=$id");
}
echo goBack();
$mysqli->close();
?>
</body>
</html>
