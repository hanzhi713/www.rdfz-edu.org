<?php declare(strict_types=1);?>
<!DOCTYPE html>
<html>
<meta charset="utf-8">
<body>
<?php
session_start();

if (!empty($_SESSION['id']))
    $id = intval($_SESSION['id']);
else {
    header('Location: ../index.php?action=home');
    exit();
}

if (empty($_POST['id'])) {
    header('Location: ../index.php?action=home');
    exit();
}
require 'mysqli_conn.php';
require 'msg.php';
use voku\helper\AntiXSS;
require 'vendor/autoload.php';
$antiXSS = new AntiXSS();

/**
 * Collect the information about the episode which the user is going to edit
 * @var int $epi_id
 * @var array $epi
 * */
$epi_id = intval($_POST['id']);
$epi = $mysqli->query("SELECT * FROM episodes WHERE id=$epi_id");
if (!($epi = $epi->fetch_assoc())) {
    header('Location: ../index.php?action=home');
    exit();
}

$antiXSS = new AntiXSS();

/**
 * check if the user has the permission
 * user has the permission if:
 * he/she is the author, OR
 * he/she is the administrator
 * @var bool $isAdmin
 * */
$row = $mysqli->query("SELECT userlevel FROM userlist WHERE id=$id LIMIT 1")->fetch_assoc();
$isAdmin = $row['userlevel'] > 5 || ($id == $epi['authorid']);

if (!$isAdmin) {
    header('Location: ../index.php?action=home');
    exit();
}
/**
 * the path of videos and posters, relative to index.php
 * @var string $path
 * @var string $real
 * real: the path of posters and videos relative to this file, in addition to $path
 * */
$path = '../Episodes/';
$real = '../';

if (!empty($_POST['del_video'])) {
    $video_path = $mysqli->query("SELECT video_path FROM episodes WHERE id=$epi_id");
    $video_path = $video_path->fetch_assoc();
    if (!empty($video_path['video_path']))
        if (file_exists($real . $video_path['video_path']))
            unlink($real . $video_path['video_path']);
    $mysqli->query("UPDATE episodes SET video_path='' WHERE id=$epi_id");
    echo alertAndBack('Deleted!');

} /**
 * delete the material
 *
 * */
elseif (!empty($_POST['del_material'])) {
    $material_path = $mysqli->query("SELECT material_path FROM episodes WHERE id={$epi_id}")->fetch_assoc();

    // delete the file if it exists
    if (!empty($material_path['material_path']))
        if (file_exists($real . $material_path['material_path']))
            unlink($real . $material_path['material_path']);

    // clear the database record
    $mysqli->query("UPDATE episodes SET material_path='', material_title='', material_description='' WHERE id=$epi_id");
    echo alertAndBack('Deleted!');

} /**
 * update the summary
 * @var string $title
 * the (new) title
 * @var string $subject
 * the (new) subject
 * users are allowed to change the subject
 * @var string $author
 * the (new) authors' name
 * @var string $summary
 * the (new) summary
 * */

elseif (!empty($_POST['sub_summary'])) {
    $title = $antiXSS->xss_clean(trim($_POST['title']));
    $subject = $antiXSS->xss_clean(trim($_POST['subject']));
    $author = $antiXSS->xss_clean(trim($_POST['author']));
    $summary = $antiXSS->xss_clean(trim($_POST['summary']));

    // check if the new title is used by a previous episode
    $check_if_same = executeSQL('SELECT id FROM episodes WHERE title=?', array('s', $title));
    if (!empty($check_if_same[0]))
        if ($check_if_same[0]['id'] != $epi_id)
            exit(alertAndBack('The title you entered is the same as one previous post. Please change to a new one'));

    // update database record
    $sql = 'UPDATE episodes SET title=?, subject=?, author=?, summary=? WHERE id=?';
    $sql = $mysqli->prepare($sql);
    $sql->bind_param('ssssi', $title, $subject, $author, $summary, $epi_id);
    $sql->execute();
    if ($sql->affected_rows)
        echo alertAndBack('Saved!');
    else
        echo alertAndBack('Content unchanged!');
    $sql->close();

} /**
 * save the new poster
 * */
elseif (!empty($_POST['sub_poster'])) {
    $poster_path = $path . "{$epi_id}.png";

    // check if the poster is already uploaded and it is of appropriate file type and size
    if (is_uploaded_file($_FILES['photo']['tmp_name'])) {
        $type = $_FILES['photo']['type'];
        if ((($type === 'image/gif') || ($type === 'image/jpeg') || ($type === 'image/png')) && ($_FILES['photo']['size'] < 4000000)) {
        } else
            exit(alertAndBack('Failed to upload poster: Incorrect file type or file is too large!'));
        move_uploaded_file($_FILES['photo']['tmp_name'], '../' . $poster_path);
        require_once('imageResize.php');

        // resize the image
        // maximum 800x800 for the 'summary' section
        // maximum 200x200 for the 'epilist' section
        imageResize('../' . $poster_path, '../' . $poster_path, 800);
        imageResize('../' . $poster_path, '../' . $poster_path . '.mini', 200);

        //update database record
        $mysqli->query("UPDATE episodes set poster_path='$poster_path' WHERE id=$epi_id");
        echo alertAndBack('Poster updated!');
    } else
        echo alertAndBack('Failed to update poster!');

} /**
 * save the video
 * @var string $external_src
 * HTML code of external video source
 * */
elseif (!empty($_POST['sub_video'])) {
    if (is_uploaded_file($_FILES['video']['tmp_name'])) {
        // save the video file
        $video_path = $path . "{$epi_id}-{$epi['title']}.mp4";
        move_uploaded_file($_FILES['video']['tmp_name'], '../' . $video_path);

        // update the database
        $mysqli->query("UPDATE episodes set video_path='$video_path' WHERE id=$epi_id");
        echo alertAndBack('Video updated!');
    } else
        echo goBack();

    // save the HTML code (the external source)
    if (isset($_POST['external'])) {
        $external_src = $_POST['external'];
        if (stripos($external_src, '<script>'))
            exit(alertAndBack('Do not inject script!'));
        $sql = $mysqli->prepare('UPDATE episodes SET external_src=? WHERE id=?');
        $sql->bind_param('si', $external_src, $epi_id);
        $sql->execute();
        if ($sql->affected_rows)
            echo(alertAndBack('External source updated!'));
        $sql->close();
    }

} /**
 * save the material
 * @var string $material_title
 * @var string $material_description
 * @var string $material_path
 * */
elseif (!empty($_POST['sub_material'])) {

    // if a material is already uploaded, delete it
    if ($epi['material_path'] != '')
        if (file_exists($real . $epi['material_path']))
            unlink($real . $epi['material_path']);

    // save the (new) material
    if (is_uploaded_file($_FILES['material']['tmp_name'])) {
        $material_path = "../Episodes/{$epi_id}-{$_FILES['material']['name']}";
        move_uploaded_file($_FILES['material']['tmp_name'], '../' . $material_path);
    } else
        exit(alertAndBack('Failed to upload material!'));

    // update database
    $material_title = $antiXSS->xss_clean(trim($_POST['material_title']));
    $material_description = $antiXSS->xss_clean(trim($_POST['material_description']));
    $sql = 'UPDATE episodes SET material_title=?, material_description=?, material_path=? WHERE id=?';
    $sql = $mysqli->prepare($sql);
    $sql->bind_param('sssi', $material_title, $material_description, $material_path, $epi_id);
    $sql->execute();
    if ($sql->affected_rows)
        echo alertAndBack('Material updated!');
    else
        echo alertAndBack('Not updated!');
    $sql->close();

} /**
 * publish episode
 * if this is non-empty, send email to subscribers
 * */
elseif (!empty($_POST['publish'])) {

    // if it's not published, publish it
    if (intval($epi['status']) === 0) {
        $mysqli->query("UPDATE episodes set status=1 WHERE id=$epi_id");
        if (!empty($_POST['sendemail'])) {
            require_once 'sendEmail.php';

            // iteratively fetch subscribers' email address and send emails
            $subj = $mysqli->query("SELECT * FROM subjects WHERE name='{$epi['subject']}'")->fetch_assoc();
            $subscribers = $mysqli->query("SELECT id FROM subscribe_{$subj['id']}");
            while ($s = $subscribers->fetch_assoc()) {
                $email = $mysqli->query("SELECT email FROM userlist WHERE id={$s['id']}")->fetch_assoc();
                subscriptionEmail($email['email'], $subj['altname'], $subj['name'], $epi['title'], $epi_id);
            }
        }
        echo alertAndBack('Published!');
    } // if it is already published, cancel the publication
    else {
        $mysqli->query("UPDATE episodes set status=0 WHERE id=$epi_id");
        if ($mysqli->affected_rows)
            echo alertAndBack('Publication canceled!');
    }
} /**
 * delete the episode and its related files
 * */
elseif (!empty($_POST['delete'])) {
    $episode = $mysqli->query("SELECT video_path, poster_path, material_path FROM episodes WHERE id=$epi_id");
    $paths = $episode->fetch_assoc();

    // delete all files linked to this episode, if they exist
    foreach ($paths as $path)
        if ($path != '')
            if (file_exists($real . $path))
                unlink($real . $path);
    if (file_exists($real . $paths['poster_path'] . '.mini'))
        unlink($real . $paths['poster_path'] . '.mini');

    // delete database records
    $mysqli->query("DELETE FROM episodes WHERE id=$epi_id");
    if ($mysqli->affected_rows) {
        $mysqli->query("ALTER TABLE episodes AUTO_INCREMENT=$epi_id");

        // delete all related comments and replies
        $comments = $mysqli->query("SELECT id FROM comments WHERE episode_id=$epi_id");
        while ($comment = $comments->fetch_assoc()) {
            $mysqli->query("DELETE FROM replies WHERE comment_id={$comment['id']}");
            $mysqli->query("DELETE FROM comments WHERE id={$comment['id']}");
        }
        echo alertAndRedirect('Deleted!', '../dashboard.php?action=episodemanage');
    } else {
        alertAndBack('Unknown Error!');
    }
}
$mysqli->close();
?>
</body>
</html>
