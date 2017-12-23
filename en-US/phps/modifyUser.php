<?php declare(strict_types=1);?>
<!DOCTYPE html>
<html>
<meta charset="utf-8">
<body>
<?php
session_start();
if (empty($_SESSION['id']))
    exit(alertAndBack('Illegal Access!'));

require 'mysqli_conn.php';
require 'msg.php';
use voku\helper\AntiXSS;
require 'vendor/autoload.php';
$antiXSS = new AntiXSS();

$id = intval($_SESSION['id']);
$row = $mysqli->query("SELECT userlevel FROM userlist WHERE id=$id LIMIT 1")->fetch_assoc();
$isAdmin = $row['userlevel'] > 5;

/**
 * update the personal information of a given user, using its id
 * update user level and silence state if admin
 * @param int $id
 * @param bool $isAdmin
 * */
function updatePersonalInfo(int $id, bool $isAdmin): void
{
    global $antiXSS;
    $nickname = $antiXSS->xss_clean(trim($_POST['nickname']));
    // check if the length of nickname is appropriate
    // and nickname is not taken by another user
    if (strlen($nickname) > 50)
        exit(alertAndBack('Nickname is too long!'));
    $u = executeSQL('SELECT id FROM userlist WHERE nickname=?', array('s', $nickname));
    if (isset($u[0]))
        if ($u[0]['id'] != $id)
            exit(alertAndBack('This nickname is used by another user!'));
    $gender = $_POST['gender'];
    if (!is_numeric($gender))
        exit(alertAndBack('Illegal Access!'));
    $ps = $antiXSS->xss_clean(trim($_POST['ps']));
    if ($isAdmin) {
        if (!empty($_POST['id']))
            $id = intval($_POST['id']);
        $userlevel = intval($_POST['usertype']);
        $silenced = empty($_POST['silence']) ? 0 : 1;
        if (executeSQL('UPDATE userlist SET gender=?, nickname=?, ps=?, silence=?, userlevel=? WHERE id=?', array('issiii', $gender, $nickname, $ps, $silenced, $userlevel, $id)))
            echo goBack();
        else
            echo alertAndBack('Failed to update personal information!');
    } else
        if (executeSQL('UPDATE userlist SET gender=?, nickname=?, ps=? WHERE id=?', array('issi', $gender, $nickname, $ps, $id)))
            echo goBack();
        else
            echo alertAndBack('Failed to update personal information!');
}

/**
 * save user's headshot
 * @param int $id
 * */
function uploadHeadshot(int $id): void
{
    global $mysqli;
    if (empty($_FILES['photo']))
        echo goBack();
    $type = $_FILES['photo']['type'];
    if ((($type === 'image/gif') || ($type === 'image/jpeg') || ($type === 'image/png')) && ($_FILES['photo']['size'] < 5000000)) {

        if ($_FILES['photo']['error'] > 0)
            exit('<script>alert("Error: ' . $_FILES['photo']['error'] . '");window.location.replace(document.referrer);</script>');
        $path = '../Headshots/';
        $save_path = $path . $id . '.png';

        if (move_uploaded_file($_FILES['photo']['tmp_name'], '../' . $save_path)) {
            require_once('imageResize.php');
            imageResize('../' . $save_path, '../' . $save_path);
            imageResize('../' . $save_path, '../' . $save_path . '.mini', 60);
            if ($mysqli->query("UPDATE userlist SET headshot='{$save_path}' WHERE id=$id"))
                echo goBack();
        } else
            echo alertAndBack('An exception occurred when trying to save your headshot!');
    } else
        echo alertAndBack('Invalid file type or file size exceeds 5MB!');
}

/**
 * delete the user
 * @param int $id
 * user id
 * @param bool $isAdmin
 * the following three parameters are used only if $isAdmin is true
 * @param bool $sendEmail
 * whether to send an email to user to inform him/her about account deletion
 * @param bool $giveReason
 * whether to append reason in the email
 * @param string $reason
 * the reason, used only if $giveReason is true
 * */
function deleteUser(int $id, bool $isAdmin = false, bool $sendEmail = false, bool $giveReason = false, string $reason = ''): void
{
    global $mysqli;
    $user = $mysqli->query("SELECT headshot, email FROM userlist WHERE id=$id");
    $real = '../';
    $path = $user->fetch_assoc();

    // delete user's headshot
    if ($path['headshot'] != '../Headshots/default.jpg') {
        if (file_exists($real . $path['headshot']))
            unlink($real . $path['headshot']);
        if (file_exists($real . $path['headshot'] . '.mini'))
            unlink($real . $path['headshot'] . '.mini');
    }
    require_once 'sendEmail.php';
    $mysqli->query("DELETE FROM userlist WHERE id=$id");
    if ($isAdmin) {
        if ($mysqli->affected_rows)
            echo alertAndRedirect('Account deleted!', '../dashboard.php?action=usermanage');
        if ($sendEmail)
            if ($giveReason)
                postMail($path['email'], getMsg('Educational Resources'), getMsg('Your account has been deleted by an administrator because of the following reason(s):') . '<br /><br />' . $reason);
            else
                postMail($path['email'], getMsg('Educational Resources'), getMsg('Your account has been deleted by an administrator.'));
    } else {
        if ($mysqli->affected_rows)
            echo alertAndRedirect('Account deleted!', '../index.php?action=home');
        postMail($path['email'], getMsg('Educational Resources'), getMsg('You have successfully deleted your account!'));
    }
}

// if the request is from dashboard.php
if (empty($_POST['id'])) {
    $id = intval($_SESSION['id']);
    if (!empty($_POST['personalinfo']))
        updatePersonalInfo($id, $isAdmin);
    elseif (!empty($_POST['upload']))
        uploadHeadshot($id);
    elseif (!empty($_POST['changepassword'])) {
        $password = $mysqli->query("SELECT password, email, regtime FROM userlist WHERE id='$id';")->fetch_assoc();

        $oldpass = saltpass(md5($_POST['oldpass']), $password['email'], $password['regtime']);
        $newpass = saltpass(md5($_POST['password']), $password['email'], $password['regtime']);

        if ($oldpass === $password['password'])
            if ($mysqli->query("UPDATE userlist SET password='$newpass' WHERE id=$id;"))
                echo alertAndBack('Password updated!');
            else
                echo alertAndBack('Failed to update your password!');
        else
            echo alertAndBack('Incorrect password!');
    } elseif (!empty($_POST['delete']))
        deleteUser($id);
    else
        echo alertAndBack('Unknown Action!');
} // if the request is from user-profile.php
else {
    if (!$isAdmin)
        exit(alertAndBack('No permission!'));

    $id = intval($_POST['id']);
    if (!empty($_POST['personalinfo']))
        updatePersonalInfo($id, true);
    elseif (!empty($_POST['upload']))
        uploadHeadshot($id);
    elseif (!empty($_POST['delete']))
        deleteUser($id, true, isset($_POST['sendemail']), isset($_POST['givereason']), htmlspecialchars($_POST['reason']));
    else
        echo alertAndBack('Unknown Action!');
}
$mysqli->close();
?>
</body>
</html>
