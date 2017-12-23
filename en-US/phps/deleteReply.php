<?php declare(strict_types=1);?>
<!DOCTYPE html>
<html>
<meta charset="utf-8">
<body>
<?php
session_start();
require 'msg.php';
if (isset($_SESSION['id']) && isset($_GET['rid'])) {
    require_once('mysqli_conn.php');
    $id = intval($_SESSION['id']);
    $rid = intval($_GET['rid']);

    // check if the reply exists
    $r = $mysqli->query("SELECT userid FROM replies WHERE id=$rid")->fetch_assoc();
    if (!isset($r['userid']))
        exit(alertAndBack('This reply does not exist!'));

    /*
     * check if the user has the permission to delete this reply
     * user has the permission if:
     * he/she is the one who posted it, OR
     * he/she is an administrator
     * */
    $user = $mysqli->query("SELECT userlevel FROM userlist WHERE id=$id")->fetch_assoc();
    if ($user['userlevel'] > 5 || $id == $r['userid']) {
        $mysqli->query("DELETE FROM replies WHERE id=$rid");
        if ($mysqli->affected_rows)
            echo alertAndBack('Reply deleted!');
        else
            echo alertAndBack('Failed to delete that reply!');
    } else
        echo alertAndBack('Access denied! No permission!');
} else
    exit(alertAndBack('Illegal Access!'));
$mysqli->close();
?>
</body>
</html>
