<?php declare(strict_types=1);?>
<!DOCTYPE html>
<html>
<meta charset="utf-8">
<body style="text-align:center; color:#E50003; font-size: 18px; vertical-align: top;">
<?php
session_start();

// process user's logout request
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'logout') {
        session_unset();
        session_destroy();
        header("Location: /");
        exit();
    }
}

require 'msg.php';

/**
 * process user's login request
 * REQUIRED FIELDS:
 * @var string $vcode
 * validation code must appear in both HTTP POST data and session
 * @var string $email
 * user's email address
 * @var string $password
 * unencrypted password
 *
 * */
if (empty($_POST['submit']) || empty($_POST['vcode']) || empty($_SESSION['vcode']))
    exit(getMsg('Illegal Access!'));

if (strtolower($_POST['vcode']) != $_SESSION['vcode'])
    exit(getMsg('Incorrect validation code!'));

$email = trim($_POST['email']);
if (!preg_match('/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/', $email))
    exit(getMsg('Invalid email address!'));

$password = md5($_POST['password']);
require_once 'mysqli_conn.php';
$result = executeSQL('SELECT email, id, regtime, password FROM userlist WHERE email=? LIMIT 1', array('s', $email));
if (!empty($result[0])) {
    $result = $result[0];
    if (saltpass($password, $email, $result['regtime']) === $result['password']) {
        $_SESSION['email'] = $email;
        $_SESSION['id'] = intval($result['id']);
        echo '<script>window.parent.location.replace("', empty($_POST['ref']) ? '../index.php?action=home' : $_POST['ref'], '");</script>';
    } else
        echo getMsg('Incorrect password!');
} else
    echo getMsg('Incorrect email address!');
unset($_SESSION['vcode']);
$mysqli->close();
?>
</body>
</html>