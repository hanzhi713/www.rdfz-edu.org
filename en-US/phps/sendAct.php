<?php declare(strict_types=1);?>
<!DOCTYPE html>
<html>
<meta charset="UTF-8">
<body>
<?php
require 'msg.php';

/**
 * generate the token using user's other fields
 * */
if (empty($_POST['submit']))
    exit(alertAndBack('Illegal Access!'));
session_start();
if (empty($_SESSION['id'])) {
    header('Location: ../login.html');
    exit();
}
require 'sendEmail.php';
require 'mysqli_conn.php';

// update user's profile
// insert the token into the database
$id = intval($_SESSION['id']);
$row = $mysqli->query("SELECT email, password, regtime, status FROM userlist WHERE id=$id;")->fetch_assoc();
if (intval($row['status']) === 0) {
    $token = md5($row['email'] . $row['password'] . $row['regtime'] . microtime());
    $token_exptime = time() + 86400;
    $mysqli->query("UPDATE userlist SET token='{$token}', token_exptime='{$token_exptime}' WHERE id=$id;");
    sendActEmail($row['email'], $token);
    echo alertAndBack('Email is sent!');
} else
    echo alertAndBack('Illegal Access!');
$mysqli->close();
?>
</body>
</html>
