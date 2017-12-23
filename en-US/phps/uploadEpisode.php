<?php declare(strict_types=1);?>
<!DOCTYPE html>
<html>
<meta charset="UTF-8">
<body>
<?php
session_start();
if (!empty($_SESSION['id']))
    $id = intval($_SESSION['id']);
else
    exit();

require 'msg.php';
require 'mysqli_conn.php';
use voku\helper\AntiXSS;
require 'vendor/autoload.php';
$antiXSS = new AntiXSS();

/**
 * process user's upload request
 * REQUIRED NON-EMPTY FIELDS:
 * @var string $title
 * @var string $subject
 * @var string $author
 *
 * AUTO-GENERATED FIELDS:
 * @var int $authorid
 * @var int $publish_time
 * */

// check if user has sufficient permission
// user should be an editor or administrator
$row = $mysqli->query("SELECT userlevel FROM userlist WHERE id=$id LIMIT 1")->fetch_assoc();
$isAdmin = $row['userlevel'] > 0;

if (!$isAdmin || empty($_POST['submit']))
    exit();

$title = $antiXSS->xss_clean($_POST['title']);
$subject = $antiXSS->xss_clean($_POST['subject']);
$author = $antiXSS->xss_clean($_POST['author']);
$authorid = $id;
$publish_time = time();
$summary = $antiXSS->xss_clean($_POST['summary']);

// check if this subject exists
$subjects = $mysqli->query('SELECT name FROM subjects');
$flag = false;
while ($s = $subjects->fetch_assoc()) {
    if ($s['name'] === $subject) {
        $flag = true;
        break;
    }
}
if (!$flag) {
    $mysqli->close();
    exit(alert('Invalid subject!'));
}

// check if this title is used by a previous episode
$check_if_same = executeSQL('SELECT id FROM episodes WHERE title=?', array('s', $title));
if (isset($check_if_same[0]))
    exit(alert('The title you entered is the same as one previous post. Please change to a new one'));

$sql = 'INSERT INTO `episodes` (`author`, `authorid`, `title`, `subject`, `publish_time`, `summary`) VALUES (?, ?, ?, ?, ?, ?)';
$sql = $mysqli->prepare($sql);
$sql->bind_param('sissis', $author, $authorid, $title, $subject, $publish_time, $summary);
$sql->execute();
if ($epi_id = $sql->insert_id)
    echo alert('New episode is published!');

// save the poster, if uploaded
$path = '../Episodes/';
$poster_path = $path . $epi_id . '.jpg';

if (is_uploaded_file($_FILES['photo']['tmp_name'])) {
    $type = $_FILES['photo']['type'];
    if ((($type === 'image/gif') || ($type === 'image/jpeg') || ($type === 'image/png')) && ($_FILES['photo']['size'] < 4000000)) {
    } else
        exit(alert('Failed to upload poster: Incorrect file type or file is too large'));
    move_uploaded_file($_FILES['photo']['tmp_name'], '../' . $poster_path);
    include_once('imageResize.php');
    imageResize('../' . $poster_path, '../' . $poster_path, 800);
    imageResize('../' . $poster_path, '../' . $poster_path . '.mini', 200);
    $mysqli->query("UPDATE episodes SET poster_path='{$poster_path}' WHERE id={$epi_id}");
}
$mysqli->close();
?>
</body>
</html>
