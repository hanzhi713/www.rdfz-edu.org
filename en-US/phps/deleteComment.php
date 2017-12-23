<?php declare(strict_types=1);?>
<!DOCTYPE html>
<html>
<meta charset="UTF-8">
<body>
<?php
session_start();
require 'msg.php';
if (isset($_SESSION['id']) && isset($_GET['cid'])) {
    require 'mysqli_conn.php';
    $id = intval($_SESSION['id']);
    $cid = intval($_GET['cid']);

    // check if the comment exist
    $c = $mysqli->query("SELECT userid FROM comments WHERE id=$cid")->fetch_assoc();
    if (!isset($c['userid']))
        exit(alertAndBack('This comment does not exist!'));

    /*
     * check if the user has the permission to delete this comment
     * user has the permission if:
     * he/she is the one who posted it, OR
     * he/she is an administrator
     * */
    $user = $mysqli->query("SELECT userlevel FROM userlist WHERE id=$id LIMIT 1")->fetch_assoc();
    if ($user['userlevel'] > 5 || $id == $c['userid']) {
        $mysqli->query("DELETE FROM comments WHERE id=$cid");

        // if deletion is successful, also delete replies associated with this comment
        if ($mysqli->affected_rows) {
            $mysqli->query("DELETE FROM replies WHERE comment_id=$cid");
            echo alertAndBack('Comment deleted!');
        } else
            echo alertAndBack('Failed to delete the comment!');
    } else
        echo alertAndBack('Access denied! No permission!');
} else
    exit(alertAndBack('Illegal Access!'));
$mysqli->close();
?>
</body>
</html>
