<?php declare(strict_types=1);?>
<html>
<meta charset="utf-8">
<body>
<?php
session_start();
if (!empty($_SESSION['id']))
    $id = intval($_SESSION['id']);
else
    exit();
require 'mysqli_conn.php';
require 'msg.php';

use voku\helper\AntiXSS;
require 'vendor/autoload.php';
$antiXSS = new AntiXSS();

// make sure that this user has the permission to perform actions on these subjects
$row = $mysqli->query("SELECT userlevel FROM userlist WHERE id=$id limit 1")->fetch_assoc();
$isAdmin = $row['userlevel'] > 5;

if (!$isAdmin || (empty($_POST['name']) && !isset($_POST['did'])))
    exit(goBack());

// deletion of this subject:
if (isset($_POST['did'])) {
    $did = intval($_POST['did']);
    $mysqli->query("DELETE FROM subjects WHERE id=$did");

    // delete the table of subscribers of this subject
    if ($mysqli->affected_rows) {
        $mysqli->query("ALTER TABLE subjects AUTO_INCREMENT=$did");
        $mysqli->query("DROP TABLE subscribe_{$did}");
        echo alertAndBack('Deleted!');
    } else
        echo alertAndBack('Failed to delete that subject!');
} /**
 * Insertion of a new subject
 * @var int id
 * the id of the new subject
 * @var string name
 * the name used in links and databases, ASCII characters only
 * @var string altname
 * the display name of this subject, UTF-8 characters
 * */
elseif (isset($_POST['nid'])) {
    $sql = $mysqli->prepare('INSERT INTO subjects (id, name, altname) VALUES (?, ?, ?)');
    $nid = intval($_POST['nid']);
    $name = $antiXSS->xss_clean(trim($_POST['name']));
    $altname = $antiXSS->xss_clean(trim($_POST['altname']));
    $sql->bind_param('iss', $nid, $name, $altname);
    $sql->execute();
    if ($sql->insert_id) {
        $mysqli->query("CREATE TABLE subscribe_{$sql->insert_id} (`id` int(10) NOT NULL PRIMARY KEY) ENGINE=MyISAM DEFAULT CHARSET=utf8");
        echo goBack();
    } else
        echo alertAndBack('Failed to add new subject!');
    $sql->close();
} /**
 * alternation of a subject
 * @var mysqli_stmt $sql
 * the update statement
 * @var int $id
 * the id of the subject which the user wants to edit
 * @var string $old
 * old name of this subject
 * @var string $name
 * new name of this subject
 * @var string $altname
 * new alternative name of this subject
 * */
elseif (isset($_POST['id'])) {

    // first update the record of this subject
    $sql = $mysqli->prepare('UPDATE subjects SET name=?, altname=? WHERE id=?');
    $id = intval($_POST['id']);
    $old = $mysqli->query("SELECT name FROM subjects WHERE id=$id")->fetch_assoc()['name'];
    $name = $antiXSS->xss_clean(trim($_POST['name']));
    $altname = $antiXSS->xss_clean(trim($_POST['altname']));
    $sql->bind_param('ssi', $name, $altname, $id);
    $sql->execute();

    // then update the records associated with this record
    if ($sql->affected_rows) {
        executeSQL('UPDATE episodes SET subject=? WHERE subject=?', array('ss', $old, $name));
        echo goBack();
    } else
        echo alertAndBack('Not updated!');
    $sql->close();
}
$mysqli->close();
?>
</body>
</html>