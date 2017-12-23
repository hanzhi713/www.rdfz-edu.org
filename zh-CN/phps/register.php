<?php declare(strict_types=1);?>
<!DOCTYPE html>
<html>
<meta charset="UTF-8">
<body style="text-align:center; color:#E50003; font-size: 18px; vertical-align: top;">
<?php
session_start();
require 'msg.php';

/**
 * process user's registration request
 * REQUIRED FIELDS FROM HTTP POST:
 * @var string $email
 * user's email address
 * @var string $vcode
 * validation code
 * @var string $password
 * user's password
 * */
if (empty($_POST['email']) || empty($_POST['vcode']) || empty($_SESSION['vcode'])) {
    header('Location: ../register.html');
    exit();
}
$email = trim($_POST['email']);
if (!preg_match('/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/', $email))
    exit(getMsg('Invalid email address!'));

if (strtolower($_POST['vcode']) !== $_SESSION['vcode'])
    exit(getMsg('Incorrect validation code!'));
unset($_SESSION['vcode']);
$password = $_POST['password'];

require 'mysqli_conn.php';

// check if the email address is used by another user
$check_query = $mysqli->query("SELECT id FROM userlist WHERE email='{$email}' LIMIT 1");
if ($check_query->fetch_assoc())
    exit('<script>alert("' . $email . getMsg(' is already registered by another user!') . '");</script>');
if (strlen($password) < 5 || strlen($password) > 20)
    exit(getMsg('Password too long or too short!'));

/**
 * prepare the fields
 * @var int $regtime
 * user's registration time
 * @var string $password
 * @var string $token
 * the token for account activation
 * will be sent to user's email
 * @var int $token_exptime
 * the expiration time of token
 * */
$regtime = time();
$password = saltpass(md5($password), $email, $regtime);
$token = md5($email . $password . $regtime . microtime());
$token_exptime = time() + 86400;

$sql = "INSERT INTO userlist (`email`,`password`,`token`,`token_exptime`,`regtime`,`nickname`) VALUES ('{$email}','{$password}','{$token}','{$token_exptime}','{$regtime}', '{$email}')";
$mysqli->query($sql);

if ($mysqli->insert_id) {
    require 'sendEmail.php';
    sendActEmail($email, $token);
    echo alert('Successfully registered! Please check your email and confirm the registration'), '<script>parent.window.location.replace( "../index.php")</script>';
}
$mysqli->close();
?>
</body>
</html>