<?php declare(strict_types=1); ?>
<!doctype html>
<html>
<head>
    <link rel="shortcut icon" href="../Doc/head.ico" type="image/x-icon">
    <meta charset="utf-8">
    <?php
    require 'phps/msg.php';
    ?>
    <title><?php echo getMsg('User Profile'); ?></title>
    <link href="css/user-profile.css?ver=5" rel="stylesheet" type="text/css">
    <?php
    session_start();
    if (empty($_GET['id'])) {
        header('Location: index.php?action=home');
        exit();
    }

    require 'phps/mysqli_conn.php';
    $id = intval($_GET['id']);
    $row = $mysqli->query("SELECT * FROM userlist WHERE id=$id")->fetch_assoc();
    if (empty($row['id']))
        exit(alertAndRedirect('User does not exist!', 'index.php?action=home'));

    if (isset($_SESSION['id'])) {
        $sessid = intval($_SESSION['id']);
        $data = $mysqli->query("SELECT * FROM userlist WHERE id=$sessid");
        $currentUser = $data->fetch_assoc();
        $isAdmin = $currentUser['userlevel'] > 5;
    } else
        $isAdmin = false;

    $action_arr = array('personal', 'upload', 'comments', 'delete');
    if (empty($_GET['action']))
        $_GET['action'] = 'personal';
    elseif (!in_array($_GET['action'], $action_arr))
        $_GET['action'] = 'personal';
    ?>
    <script>
        var getAction = "<?php echo $_GET['action'];?>";
    </script>
    <script src="locale/for_js.js"></script>
    <script src="js/check_extension.min.js"></script>
</head>
<div id="top">
    <div id="top_bar">
        <table width="1000px">
            <tr>
                <td><span style="font-size:26px">&nbsp;<?php echo getMsg('User Profile'); ?></span></td>
                <td style="text-align:right;">
                    <table style="margin-left:auto; height:50px">
                        <tr>
                            <td><img src="<?php echo $row['headshot']; ?>.mini" width="50px" height="50px"></td>
                            <td style="line-height:25px; font-size:20px;"><span><?php echo $row['email']; ?><br/>
                                    <?php echo $row['nickname']; ?></span></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</div>
<body style="background-color:#FFFFE5;">
<br/>
<div id="content">
    <div id="left_menu">
        <div id="menu_personal" class="menu"
             onClick="menuclick(this);"><?php echo getMsg('Personal information'); ?></div>
        <div id="menu_upload" class="menu" onClick="menuclick(this);"><?php echo getMsg('Headshot'); ?></div>
        <div id="menu_comments" class="menu" onClick="menuclick(this);"><?php echo getMsg('Comments'); ?></div>
        <?php if ($isAdmin) echo '<div id="menu_delete" class="menu" onClick="menuclick(this);">', getMsg('Delete User'), '</div>' ?>
    </div>
    <div id="main">
        <?php if ($_GET['action'] === 'personal') { ?>
            <div id="personal" class="context"><br/>
                <form method="post" action="phps/modifyUser.php" name="personalInfo" onSubmit="return isValid(this);">
                    <table class="infotable">
                        <tr>
                            <td><?php echo getMsg('Email'); ?>:&nbsp;</td>
                            <td><?php echo $row['email']; ?></td>
                        </tr>
                        <tr>
                            <td><?php echo getMsg('Registration time'); ?>:&nbsp;</td>
                            <td><?php echo date('Y/m/d H:i:s', (int)$row['regtime']); ?></td>
                        </tr>
                        <tr>
                            <td><label for="nickname"><?php echo getMsg('Nickname'); ?>:&nbsp;</label></td>
                            <td><input id="nickname" type="text" name="nickname" value="<?php echo $row['nickname']; ?>"></td>
                        </tr>
                        <tr>
                            <td><?php echo getMsg('Gender'); ?>:&nbsp;</td>
                            <td><input id="female" class="checkbox" type="radio" name="gender" value="1" <?php if (!$isAdmin) echo 'disabled'; ?>>
                                <label for="female"><?php echo getMsg('Female'); ?></label>
                                <input id="male" class="checkbox" type="radio" name="gender" value="2" <?php if (!$isAdmin) echo 'disabled'; ?>>
                                <label for="male"><?php echo getMsg('Male'); ?></label>
                                <input id="unset" class="checkbox" type="radio" name="gender" value="0" <?php if (!$isAdmin) echo 'disabled'; ?>>
                                <label for="unset"><?php echo getMsg('Unset'); ?></label>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="description"><?php echo getMsg('Description'); ?>:&nbsp;</label></td>
                            <td><textarea id="description" name="ps" rows="6" cols="30" style="font-size: 16px;"><?php echo $row['ps'] ?></textarea>
                            </td>
                        <tr>
                            <td><?php echo getMsg('User type'); ?>:&nbsp;</td>
                            <td><input id="user" class="checkbox" type="radio" name="usertype" value="0" <?php if (!$isAdmin) echo 'disabled'; ?>>
                                <label for="user"><?php echo getMsg('User'); ?></label>
                                <input id="editor" class="checkbox" type="radio" name="usertype" value="5" <?php if (!$isAdmin) echo 'disabled'; ?>>
                                <label for="editor"><?php echo getMsg('Editor'); ?></label>
                                <input id="administrator" class="checkbox" type="radio" name="usertype"
                                       value="10" <?php if (!$isAdmin) echo 'disabled'; ?>>
                                <label for="administrator"><?php echo getMsg('Administrator'); ?></label>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="silence"><?php echo getMsg('Silence'); ?>:&nbsp;</label></td>
                            <td><input id="silence" class="checkbox" type="checkbox"
                                       name="silence[]" <?php if (intval($row['silence']) === 1) echo 'checked'; ?> <?php if (!$isAdmin) echo 'disabled'; ?>>
                            </td>
                        </tr>
                    </table>
                    <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                    <input type="submit" name="personalinfo" value=" <?php echo getMsg('Save'); ?> "
                           class="submitbutton" <?php if (!$isAdmin) echo 'hidden=""'; ?>/>
                </form>
                <script src="js/check_nickname.min.js"></script>
                <script type="text/javascript">
                    var radios = document.personalInfo.gender;
                    for (var i = 0; i < radios.length; i++) {
                        if (+radios[i].value === <?php echo $row['gender']?>) radios[i].checked = true;
                    }
                    radios = document.personalInfo.usertype;
                    for (i = 0; i < radios.length; i++) {
                        if (+radios[i].value >= <?php echo $row['userlevel']?>) {
                            radios[i].checked = true;
                            break;
                        }
                    }
                    function isValid(form) {
                        return checkName(form);
                    }
                </script>
            </div>
        <?php } elseif ($_GET['action'] === 'upload') { ?>
            <div id="upload" class="context"><br/><?php echo getMsg('Headshot'); ?><br/>
                <br/>
                <img src="<?php echo $row['headshot']; ?>" width="350px">
                <?php if ($isAdmin) { ?>
                    <form action="phps/modifyUser.php" method="post" enctype="multipart/form-data"
                          style="text-align:center;" onSubmit="validateHeadshot(this.file);">
                        <br/>
                        <input type="file" name="photo" lang="en" style="font-size:18px;"/>
                        <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                        <input type="submit" name="upload" value=" <?php echo getMsg('Upload'); ?> "
                               class="submitbutton"/>
                    </form>
                    <script src="js/validate_headshot.min.js"></script>
                <?php } ?>
            </div>
        <?php } elseif ($_GET['action'] === 'comments') { ?>
            <div id="comments" class="context"><br/>
                <?php echo getMsg('Click title to redirect to the episodes that you commented on'); ?><br/>
                <?php echo getMsg('Click a comment to view its replies'); ?>
                <table id="comment_tab">
                    <tr id="tab_title">
                        <td width="110px"><?php echo getMsg('Time'); ?></td>
                        <td width="210px"><?php echo getMsg('Episode'); ?></td>
                        <td><?php echo getMsg('Comment body'); ?></td>
                    </tr>
                    <?php
                    $comments = executeSQL('SELECT * FROM comments WHERE userid=? ORDER BY comment_time DESC', array('i', $id));
                    $episode_sql = $mysqli->prepare('SELECT id, title, subject FROM episodes WHERE id=?');
                    $user_sql = $mysqli->prepare('SELECT id, headshot, email, nickname FROM userlist WHERE id=?');
                    $reply_sql = $mysqli->prepare('SELECT * FROM replies WHERE comment_id=?');
                    $tab = '';
                    if (!empty($comments[0])) {
                        foreach ($comments as $comment) {
                            $episode_sql->bind_param('i', $comment['episode_id']);
                            $episode_sql->execute();
                            $episode = getResults($episode_sql);
                            $episode = $episode[0];
                            $link = "index.php?action=subjects&subject={$episode['subject']}&episode={$episode['title']}&section=summary";

                            $reply_sql->bind_param('i', $comment['id']);
                            $reply_sql->execute();
                            $replies = getResults($reply_sql);
                            if (empty($replies[0])) $replies = array();
                            $tab .= '
						<tr class="comment" id="' . count($replies) . '" onClick="showReply(this, this.parentNode);">
							<td>' . date('m/d/Y H:i:s', $comment['comment_time']) . "</td>
							<td><a href=\"{$link}\" class=\"episode_link\" target=\"_blank\">{$episode['title']}</a></td>
							<td>{$comment['comment_body']}</td>
						</tr>";
                            foreach ($replies as $reply) {
                                $reply_time = date('m/d/Y H:i:s', $reply['reply_time']);
                                $Ruser = getUserInfo($user_sql, $reply['userid']);
                                $uNickname = displayName($Ruser);

                                $tab .=
                                    "<tr style=\"display:none\">
                                        <td style=\"border:none\"></td>
                                        <td style=\"border:none\" colspan=\"2\">
                                            <table class=\"reply_tab\">
                                                <tr>
                                                     <td width=\"60px\" style=\"border:none\"><img src=\"{$Ruser['headshot']}.mini\" class=\"reply_headshot\"></td>
                                                     <td style=\"border:none\"><a class=\"un_reply\" href=\"user-profile.php?id={$Ruser['id']}\"  target = \"_blank\" >{$uNickname}</a><br/>
                                                     <span class=\"uc_reply\" >{$reply['reply_body']}</span ><br />
                                                     <span class=\"replytime\" >{$reply_time}</span>&nbsp;&nbsp;
                                                     </td>
                                                </tr>
                                            </table>
								        </td>
								    </tr>";
                            }
                        }
                    }
                    $user_sql->close();
                    $episode_sql->close();
                    $reply_sql->close();
                    echo $tab;
                    ?>
                </table>
                <script src="js/show_reply.min.js"></script>
            </div>
        <?php } elseif ($_GET['action'] === 'delete' && $isAdmin) { ?>
            <div id="delete" class="context"><br/>
                <form method="post" action="phps/modifyUser.php">
                    <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                    <table style="margin: auto auto; text-align: left; font-size: 20px;">
                        <tr style="height: 40px;">
                            <td style="width: 40px; text-align: center;">
                                <input class="checkbox" type="checkbox" id="sendemail" checked name="sendemail[]"
                                       value="none" onChange="displayReason(this);"/>
                            </td>
                            <td><label for="sendemail"><?php echo getMsg('Inform user via email'); ?></label></td>
                        </tr>
                        <tr id="reasoncheck" style="height: 40px;">
                            <td style="width: 40px; text-align: center;">
                                <input class="checkbox" type="checkbox" id="givereason" checked name="givereason[]"
                                       value="none" onChange="displayText(this);"/>
                            </td>
                            <td><label for="givereason"><?php echo getMsg('Give reason(s)'); ?></label></td>
                        </tr>
                        <tr id="reasons" style="text-align: center;">
                            <td colspan="2"><textarea name="reason" cols="28" rows="8"></textarea></td>
                        </tr>
                    </table>
                    <input type="submit" name="delete" value=" <?php echo getMsg('Delete'); ?> " class="submitbutton"/>
                    <br/><br/><?php echo getMsg('Warning: This action is irreversible!'); ?>
                </form>
                <script>
                    function displayReason(box) {
                        var reasoncheck = document.getElementById('reasoncheck');
                        if (box.checked)
                            reasoncheck.style.display = '';
                        else
                            reasoncheck.style.display = 'none';
                        displayText(box);
                    }
                    function displayText(box) {
                        var reasons = document.getElementById('reasons');
                        if (box.checked)
                            reasons.style.display = '';
                        else
                            reasons.style.display = 'none';
                    }
                </script>
            </div>
        <?php } ?>
        <form id="action_hidden" style="display:none" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>"
              method="get">
            <input type="hidden" name="id" value="<?php if (!empty($id)) echo $id; ?>"/>
            <input type="hidden" name="action" value="<?php echo $_GET['action']; ?>"/>
        </form>
        <script>
            function changeMenuStyle(menuName) {
                var menu_div = document.getElementById(menuName);
                var menus = document.getElementsByClassName("menu");
                for (var i = 0; i < menus.length; i++) {
                    menus[i].style.color = "";
                    menus[i].style.backgroundColor = "";
                }
                menu_div.style.color = "#FFFFFF";
                menu_div.style.backgroundColor = "#007706";
            }
            function menuclick(menu) {
                var form = document.getElementById("action_hidden");
                form.action.value = menu.id.split("_")[1];
                form.submit();
            }
            changeMenuStyle("menu_" + getAction);
        </script>
    </div>
    <p style="font-size: 16px; padding: 5px;clear:both; background-color: #DDDDDD; color:#000000; text-align: center">
        © 2016-2017 教育资源 Educational Resources 周涵之 Hanzhi Zhou. All Rights Reserved.
    </p>
</div>
</body>
<script>
    function adjustDisplays() {
        var cnt = document.getElementById('content');
        cnt.style.height = (cnt.offsetHeight + 200) + 'px';
    }
    window.onload = adjustDisplays;
</script>
<?php $mysqli->close(); ?>
</html>