<?php declare(strict_types=1);?>
<!doctype html>
<html>
<head>
    <link rel="shortcut icon" href="../Doc/head.ico" type="image/x-icon">
    <meta charset="utf-8">
    <?php
    session_start();

    // make sure that user is logged in
    if (empty($_SESSION['id'])) {
        header('Location: login.html');
        exit();
    }
    /**
     * Declaration of some constants
     * @const array USER_KEY_DIC
     * fields which can be used to sort users
     * @const array ORDER_DIC
     * fields which can be used to defined order of sorting
     * @const array EPISODE_KEY_DIC
     * fields which can be used to sort episodes
     * */
    define('USER_KEY_DIC', array('nickname', 'regtime', 'gender', 'status', 'silence', 'userlevel', 'email'));
    define('ORDER_DIC', array('asc', 'desc'));
    define('EPISODE_KEY_DIC', array('subject', 'publish_time', 'author', 'authorid', 'title'));

    require 'phps/mysqli_conn.php';
    require 'phps/msg.php';
    use voku\helper\AntiXSS;
    require 'phps/vendor/autoload.php';
    $antiXSS = new AntiXSS();

    /**
     * @var int $id
     * the id of the user
     * @var string $email
     * the email address of the user
     * @var bool $isAdmin
     * whether the user is an admin
     * @var array $action_arr
     * the possible actions which this user can perform
     * $_GET['action'] must be in one of these
     * */
    $id = intval($_SESSION['id']);
    $email = $_SESSION['email'];
    $row = $mysqli->query("SELECT * FROM userlist WHERE id=$id LIMIT 1")->fetch_assoc();
    $isAdmin = $row['userlevel'] > 5;
    $action_arr = array('personal', 'upload', 'changepass', 'activation', 'comments', 'subscription');

    if ($row['userlevel'] > 0)
        $action_arr = array_merge($action_arr, array('upepisode', 'episodemanage'));
    if ($isAdmin)
        $action_arr = array_merge($action_arr, array('usermanage', 'subjectmanage', 'coremanage'));
    if (empty($_GET['action']))
        $_GET['action'] = 'personal';
    if (!in_array($_GET['action'], $action_arr))
        $_GET['action'] = 'personal';
    ?>
    <title><?php echo getMsg('Dashboard'); ?></title>
    <link href="css/dashboard.css?ver=10" rel="stylesheet" type="text/css">
    <script>
        var userKeyDic = {
            "<?php echo getMsg('Email');?>": "email",
            "<?php echo getMsg('Nickname');?>": "nickname",
            "<?php echo getMsg('Regtime');?>": "regtime",
            "<?php echo getMsg('Gender');?>": "gender",
            "<?php echo getMsg('Status');?>": "status",
            "<?php echo getMsg('Silence');?>": "silence",
            "<?php echo getMsg('User type');?>": "userlevel"
        };
        var epiKeyDic = {
            "<?php echo getMsg('Title');?>": "title",
            "<?php echo getMsg('Subject');?>": "subject",
            "<?php echo getMsg('Publish time');?>": "publish_time",
            "<?php echo getMsg('Uploader');?>": "authorid",
            "<?php echo getMsg('Author');?>": "author"
        };
        var getAction = "<?php echo $_GET['action'];?>";
        function getDisplay() {
            changeMenuStyle("menu_" + getAction);
            var cnt = document.getElementById('content');
            cnt.style.height = (cnt.offsetHeight + 200) + 'px';
            <?php if ($row['userlevel'] > 0){ ?>
            if (getAction === "usermanage" || getAction === "episodemanage") {
                var getSortKey = "<?php if (!empty($_GET['sortKey'])) echo $_GET['sortKey'];?>";
                var currentDic = <?php if ($_GET['action'] === 'usermanage') echo 'userKeyDic'; elseif ($_GET['action'] === 'episodemanage') echo 'epiKeyDic';
                else echo '1'?>;
                if (getSortKey !== "") {
                    var row = document.getElementById("<?php if ($_GET['action'] === 'usermanage') echo 'usertab_title'; elseif ($_GET['action'] === 'episodemanage') echo 'epitab_title';?>");
                    var key = getSortKey.split("-")[0];
                    var direction = getSortKey.split("-")[1];
                    for (var i = 0; i < row.cells.length; i++)
                        if (currentDic[row.cells[i].innerHTML] === key) {
                            if (direction === "desc")
                                row.cells[i].innerHTML = "▼" + row.cells[i].innerHTML;
                            else
                                row.cells[i].innerHTML = "▲" + row.cells[i].innerHTML;
                        }
                }
            }
            <?php } ?>
        }
        window.onload = getDisplay;
        function changeSortKey(cell, keyDic) {
            var form = document.getElementById("action_hidden");
            var txt = cell.innerHTML;
            var sortKey;
            if (txt.charAt(0) === "▲")
                sortKey = keyDic[txt.substring(1)] + "-desc";
            else if (txt.charAt(0) === "▼")
                sortKey = keyDic[txt.substring(1)] + "-asc";
            else
                sortKey = keyDic[txt] + "-desc";
            form.sortKey.value = sortKey;
            form.submit();
        }
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
    </script>
    <script src="locale/for_js.js"></script>
    <script src="js/check_extension.min.js"></script>
    <script src="js/showhide.min.js"></script>
</head>
<div id="top">
    <div id="top_bar">
        <table width="1000px">
            <tr>
                <td><span id="title"
                          onclick="window.location.href='index.php';">&nbsp;<?php echo getMsg('Educational Resources'); ?></span>
                </td>
                <td style="text-align:right;">
                    <table style="margin-left:auto; height: 50px">
                        <tr>
                            <td><img src="<?php echo $row['headshot']; ?>.mini" width="50px" height="50px"></td>
                            <td style="line-height:25px; font-size:20px;"><span><?php echo $email ?><br/>
                                <a href="phps/login.php?action=logout"><?php echo getMsg('Logout'); ?></a></span></td>
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
        <div class="SH" onClick="ShowHide('profile', this)">◢ <?php echo getMsg('Profile'); ?></div>
        <div id="profile">
            <div id="menu_personal" class="menu"
                 onClick="menuclick(this);"><?php echo getMsg('Personal information'); ?></div>
            <div id="menu_upload" class="menu" onClick="menuclick(this);"><?php echo getMsg('Upload headshot'); ?></div>
        </div>
        <div class="SH" onClick="ShowHide('security', this)">◢ <?php echo getMsg('Security'); ?></div>
        <div id="security">
            <div id="menu_changepass" class="menu"
                 onClick="menuclick(this);"><?php echo getMsg('Change password'); ?></div>
            <div id="menu_activation" class="menu"
                 onClick="menuclick(this);"><?php echo getMsg('Account activation'); ?></div>
        </div>
        <div class="SH" onClick="ShowHide('action', this)">◢ <?php echo getMsg('Action'); ?></div>
        <div id="action">
            <div id="menu_comments" class="menu"
                 onClick="menuclick(this);"><?php echo getMsg('View my comments'); ?></div>
            <div id="menu_subscription" class="menu"
                 onClick="menuclick(this);"><?php echo getMsg('My subscriptions'); ?></div>
            <?php if ($row['userlevel'] > 0) { ?>
                <div id="menu_upepisode" class="menu"
                     onClick="menuclick(this);"><?php echo getMsg('Publication'); ?></div>
            <?php } ?>
        </div>
        <?php if ($row['userlevel'] > 0) { ?>
            <div class="SH" onClick="ShowHide('management', this)">◢ <?php echo getMsg('Management'); ?></div>
            <div id="management">
                <div id="menu_episodemanage" class="menu"
                     onClick="menuclick(this);"><?php echo getMsg('Episodes'); ?></div>
                <?php if ($row['userlevel'] > 5) { ?>
                    <div id="menu_usermanage" class="menu"
                         onClick="menuclick(this);"><?php echo getMsg('Users'); ?></div>
                    <div id="menu_subjectmanage" class="menu"
                         onClick="menuclick(this);"><?php echo getMsg('Subjects'); ?></div>
                    <!--                    <div id="menu_coremanage" class="menu"-->
                    <!--                         onClick="menuclick(this);">--><?php //echo getMsg('Core Team'); ?><!--</div>-->
                <?php } ?>
            </div>
        <?php } ?>
    </div>
    <div id="main">
        <?php if ($_GET['action'] === 'personal') { ?>
            <div id="personal" class="context"><br/>
                <form method="post" action="phps/modifyUser.php" name="personalInfo" onSubmit="return isValid(this);">
                    <table class="infotable">
                        <tr>
                            <td><?php echo getMsg('Email'); ?>:&nbsp;</td>
                            <td><?php echo $email; ?></td>
                        </tr>
                        <tr>
                            <td><?php echo getMsg('Registration time'); ?>:&nbsp;</td>
                            <td><?php echo date('Y/m/d H:i:s', (int)$row['regtime']); ?></td>
                        </tr>
                        <tr>
                            <td><label for="nickname"><?php echo getMsg('Nickname'); ?>:&nbsp;</label></td>
                            <td><input id="nickname" type="text" name="nickname"
                                       value="<?php echo $row['nickname']; ?>"></td>
                        </tr>
                        <tr>
                            <td><?php echo getMsg('Gender'); ?>:&nbsp;</td>
                            <td><input id="female" class="checkbox" type="radio" name="gender" value="1">
                                <label for="female"><?php echo getMsg('Female'); ?></label>
                                <input id="male" class="checkbox" type="radio" name="gender" value="2">
                                <label for="male"><?php echo getMsg('Male'); ?></label>
                                <input id="unset" class="checkbox" type="radio" name="gender" value="0">
                                <label for="unset"><?php echo getMsg('Unset'); ?></label>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="description"><?php echo getMsg('Description'); ?>:&nbsp;</label></td>
                            <td><textarea id="description" name="ps" rows="6" cols="30"
                                          style="font-size: 16px;"><?php echo $row['ps'] ?></textarea></td>
                        <tr>
                            <td><?php echo getMsg('User type'); ?>:&nbsp;</td>
                            <td><input id="user" class="checkbox" type="radio" name="usertype"
                                       value="0" <?php if (!$isAdmin) echo 'disabled'; ?>>
                                <label for="user"><?php echo getMsg('User'); ?></label>
                                <input id="editor" class="checkbox" type="radio" name="usertype"
                                       value="5" <?php if (!$isAdmin) echo 'disabled'; ?>>
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
                    <input type="submit" name="personalinfo" value=" <?php echo getMsg('Save'); ?> "
                           class="submitbutton"/>
                </form>
                <script src="js/check_nickname.min.js"></script>
                <script type="text/javascript">
                    var radios = document.personalInfo.gender;
                    for (var i = 0; i < radios.length; i++) {
                        if (+radios[i].value ===<?php echo $row['gender']?>) radios[i].checked = true;
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
            <div id="upload" class="context"><br/><?php echo getMsg('Current headshot'); ?><br/><br/>
                <script src="js/validate_headshot.min.js"></script>
                <img src="<?php echo $row['headshot']; ?>" width="350px">
                <form action="phps/modifyUser.php" method="post" enctype="multipart/form-data" lang="en"
                      style="text-align:center;" onSubmit="return validateHeadshot(this.photo);">
                    <br/>
                    <input type="file" name="photo" lang="en" style="font-size:18px;"/>
                    <input type="submit" name="upload" value=" <?php echo getMsg('Upload'); ?> " class="submitbutton"/>
                </form>
            </div>
        <?php } elseif ($_GET['action'] === 'changepass') { ?>
            <div id="changepass" class="context">
                <script src="js/check_password_consistency.min.js"></script>
                <form action="phps/modifyUser.php" method="post" onSubmit="return checkPass(this);">
                    <table class="infotable">
                        <tr>
                            <td><label for="oldpass"><?php echo getMsg('Old password'); ?>:&nbsp;</label></td>
                            <td><input type="password" name="oldpass" id="oldpass"></td>
                        </tr>
                        <tr>
                            <td><label for="newpass"><?php echo getMsg('New password'); ?>:&nbsp;</label></td>
                            <td><input type="password" name="password" id="newpass"></td>
                        </tr>
                        <tr>
                            <td><label for="conpass"><?php echo getMsg('Confirm password'); ?>:&nbsp;</label></td>
                            <td><input type="password" name="conpass" id="conpass"></td>
                        </tr>
                    </table>
                    <input type="submit" name="changepassword" value=" <?php echo getMsg('Confirm'); ?> "
                           class="submitbutton"/>
                </form>
            </div>
        <?php } elseif ($_GET['action'] === 'activation') { ?>
            <div id="activation" class="context"><br/><br/>
                <?php
                if (intval($row['status']) === 0)
                    echo
                    '<form method="post" action="phps/sendAct.php">
						<input type="submit" name="submit" value="', getMsg('Send activation email'), '" style="font-size:18px; padding:5px;">
						<br />
						<br />', getMsg('Note: Please refresh this page after activation'), '
					</form>';
                else
                    echo getMsg('Your account has already been activated!');
                ?>
            </div>
        <?php } elseif ($_GET['action'] === 'comments') { ?>
            <div id="comments" class="context">
                <br/><?php echo getMsg('Click title to redirect to the episodes that you commented on'); ?><br/>
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
                            $tab .= '
						<tr class="comment" id="' . count($replies) . '" onClick="showReply(this, this.parentNode);">
							<td>' . date('m/d/Y H:i:s', $comment['comment_time']) . "</td>
							<td><a href=\"$link\" class=\"episode_link\" target=\"_blank\">{$episode['title']}</a></td>
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
								</td></tr>";
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
        <?php } elseif ($_GET['action'] === 'subscription') { ?>
            <div id="subscription" class="context">
                <script>
                    function Subscribe(subject) {
                        window.location.replace('phps/subscribe.php?subject=' + subject);
                    }
                </script>
                <br/>
                <table class="substable">
                    <tr style="font-weight: bold; font-size: 22px;">
                        <td><?php echo getMsg('Subjects'); ?></td>
                        <td><?php echo getMsg('Subscription'); ?></td>
                    </tr>
                    <?php
                    $subjs = $mysqli->query('SELECT * FROM subjects ORDER BY id');
                    while ($s = $subjs->fetch_assoc()) {
                        echo "<tr><td>{$s['altname']}</td><td>";
                        $a = $mysqli->query("SELECT id FROM subscribe_{$s['id']} WHERE id=$id")->fetch_assoc();
                        // if user subscribed to subject....
                        if (!empty($a['id']))
                            echo "<button type=\"submit\" onClick=\"Subscribe('{$s['name']}')\" style=\"font-size: 18px;\">", getMsg('Cancel subscription'), '</button>';
                        else
                            echo "<button type=\"submit\" onClick=\"Subscribe('{$s['name']}')\" style=\"font-size: 18px;\">", getMsg('Subscribe'), '</button>';
                        echo '</td></tr>';
                    }
                    ?>
                </table>
            </div>
        <?php } elseif ($_GET['action'] === 'upepisode' && $row['userlevel'] > 0) { ?>
            <div id="upepisode" class="context">
                <script src="js/check_summary.min.js"></script>
                <script src="js/check_poster.min.js"></script>
                <script>
                    function isValidInfo(form) {
                        if (!checkSummary(form))
                            return false;
                        return checkPoster(form);
                    }
                </script>
                <form id="uploadform" action="phps/uploadEpisode.php" method="post" enctype="multipart/form-data"
                      target="upResult" onSubmit="return isValidInfo(this);">
                    <br/>
                    <strong style="font-size:24px;"><?php echo getMsg('Episode Information'); ?></strong><br/>
                    <br/>
                    <table class="infotable">
                        <tr>
                            <td><label for="subjects"><?php echo getMsg('Subject'); ?>:&nbsp;</label></td>
                            <td><select name="subject" id="subjects">
                                    <?php
                                    $subjs = $mysqli->query('SELECT name, altname FROM subjects ORDER BY id');
                                    while ($s = $subjs->fetch_assoc())
                                        echo "<option value=\"{$s['name']}\">{$s['altname']}</option>";
                                    ?>
                                </select></td>
                        </tr>
                        <tr>
                            <td><label for="epititle"><?php echo getMsg('Title'); ?>:&nbsp;</label></td>
                            <td><input type="text" name="title" id="epititle"></td>
                        </tr>
                        <tr>
                            <td><label for="author"><?php echo getMsg('Author'); ?>:&nbsp;</label></td>
                            <td><input type="text" name="author" id="author"></td>
                        </tr>
                        <tr>
                            <td><label for="summary"><?php echo getMsg('Summary'); ?>:&nbsp;</label></td>
                            <td><textarea rows="8" cols="40" style="font-size:16px;" name="summary"
                                          id="summary"></textarea></td>
                        </tr>
                        <tr>
                            <td><?php echo getMsg('Poster'); ?>:</td>
                            <td><input type="file" name="photo"/></td>
                        </tr>
                    </table>
                    <input type="submit" name="submit" value=" <?php echo getMsg('Confirm'); ?> " class="submitbutton"/>
                </form>
                <iframe id="upResult" name="upResult"
                        style="display:none; width:0px; height:0px; border: none;"></iframe>
            </div>
        <?php } elseif ($_GET['action'] === 'usermanage' && $isAdmin) { ?>
            <div id="usermanage" class="context"><br/><?php echo getMsg('Click email address to edit user profile'); ?>
                <br/>
                <br/>
                <table id="user_tab">
                    <tr id="usertab_title">
                        <td onClick="changeSortKey(this, userKeyDic);"
                            class="user_tab_title"><?php echo getMsg('Email'); ?></td>
                        <td onClick="changeSortKey(this, userKeyDic);"
                            class="user_tab_title"><?php echo getMsg('Nickname'); ?></td>
                        <td onClick="changeSortKey(this, userKeyDic);"
                            class="user_tab_title"><?php echo getMsg('Regtime'); ?></td>
                        <td onClick="changeSortKey(this, userKeyDic);" width="60px"
                            class="user_tab_title"><?php echo getMsg('Gender'); ?></td>
                        <td onClick="changeSortKey(this, userKeyDic);" width="60px"
                            class="user_tab_title"><?php echo getMsg('Silence'); ?></td>
                        <td onClick="changeSortKey(this, userKeyDic);" width="50px"
                            class="user_tab_title"><?php echo getMsg('Status'); ?></td>
                        <td onClick="changeSortKey(this, userKeyDic);" width="50px"
                            class="user_tab_title"><?php echo getMsg('User type'); ?></td>
                    </tr>
                    <?php
                    if (empty($_GET['sortKey']))
                        $key = array('regtime', 'desc');
                    else {
                        try {
                            $raw_key = explode('-', $_GET['sortKey']);
                            $key = array();
                            if (in_array($raw_key[0], USER_KEY_DIC))
                                array_push($key, $raw_key[0]);
                            else
                                array_push($key, 'regtime');
                            if (in_array($raw_key[1], ORDER_DIC))
                                array_push($key, $raw_key[1]);
                            else
                                array_push($key, 'desc');
                        } catch (Exception $e) {
                            $key = array('regtime', 'desc');
                        }
                    }
                    $sql = "SELECT id, email, nickname, regtime, gender, silence, status, userlevel FROM userlist ORDER BY {$key[0]} {$key[1]}";
                    $result = $mysqli->query($sql);
                    $tab_str = '';
                    while ($user = $result->fetch_assoc()) {
                        $tab_str .=
                            "<tr>
							<td><a href=\"user-profile.php?id={$user['id']}\" target=\"_blank\">{$user['email']}</a></td>
							<td>{$user['nickname']}</td>
							<td>" . date("Y/m/d H:i:s", (int)$user['regtime']) . "</td>
							<td width=\"60px\">{$user['gender']}</td>
							<td width=\"60px\">{$user['silence']}</td>
							<td width=\"50px\">{$user['status']}</td>
							<td width=\"50px\">{$user['userlevel']}</td>
						</tr>";
                    }
                    echo $tab_str;
                    ?>
                </table>
            </div>
        <?php } elseif ($_GET['action'] === 'episodemanage' && $row['userlevel'] > 0) { ?>
            <div id="episodemanage" class="context" style="font-size:21px;">
                <br/><?php echo getMsg('Click the title link to edit this post'); ?><br/>
                <label for="changeSubject"><?php echo getMsg('Subject'); ?>:</label>
                <select id="changeSubject" onChange="changeSubj(this);" style="font-size:22px">
                    <option value="subject"><?php echo getMsg('All'); ?></option>
                    <?php
                    $subjs = $mysqli->query('SELECT name, altname FROM subjects ORDER BY id');
                    if (!empty($_GET['subject']))
                        if (preg_match('/^[a-zA-Z_]+$/', $_GET['subject']))
                            $subj = $_GET['subject'];
                        else
                            $subj = 'subject';
                    else
                        $subj = 'subject';
                    while ($s = $subjs->fetch_assoc()) {
                        if ($subj === $s['name'])
                            echo "<option value=\"{$s['name']}\" selected>{$s['altname']}</option>";
                        else
                            echo "<option value=\"{$s['name']}\">{$s['altname']}</option>";
                    }
                    ?>
                </select>
                <script>
                    function changeSubj(sBox) {
                        var form = document.getElementById("action_hidden");
                        form.subject.value = sBox.options[sBox.selectedIndex].value;
                        form.submit();
                    }
                </script>
                <?php
                //                if ($isAdmin) echo 'Uploader:<input type="text" name="author_id" style="font-size:22px;"/>';
                ?>
                <table id="epi_tab">
                    <tr id="epitab_title">
                        <td onClick="changeSortKey(this, epiKeyDic);"
                            class="user_tab_title"><?php echo getMsg('Subject'); ?></td>
                        <td onClick="changeSortKey(this, epiKeyDic);"
                            class="user_tab_title"><?php echo getMsg('Title'); ?></td>
                        <td onClick="changeSortKey(this, epiKeyDic);"
                            class="user_tab_title"><?php echo getMsg('Uploader'); ?></td>
                        <td onClick="changeSortKey(this, epiKeyDic);"
                            class="user_tab_title"><?php echo getMsg('Author'); ?></td>
                        <td onClick="changeSortKey(this, epiKeyDic);"
                            class="user_tab_title"><?php echo getMsg('Publish time'); ?></td>
                    </tr>
                    <?php
                    if (empty($_GET['sortKey']))
                        $key = array('publish_time', 'desc');
                    else {
                        try {
                            $raw_key = explode('-', $_GET['sortKey']);
                            $key = array();
                            if (in_array($raw_key[0], EPISODE_KEY_DIC))
                                array_push($key, $raw_key[0]);
                            else
                                array_push($key, 'publish_time');
                            if (in_array($raw_key[1], ORDER_DIC))
                                array_push($key, $raw_key[1]);
                            else
                                array_push($key, 'desc');
                        } catch (Exception $e) {
                            $key = array('publish_time', 'desc');
                        }
                    }
                    $sql = 'SELECT * FROM episodes';
                    $order = " ORDER BY {$key[0]} {$key[1]}";
                    if ($subj != 'subject') {
                        if ($row['userlevel'] <= 5)
                            $where = " WHERE subject='$subj' AND authorid=$id";
                        else
                            $where = " WHERE subject='$subj'";
                    } else {
                        if ($row['userlevel'] <= 5)
                            $where = " WHERE authorid=$id";
                        else
                            $where = '';
                    }
                    $sql = $sql . $where . $order;
                    $result = $mysqli->query($sql);
                    $tab_str = '';
                    $getSubAltname = $mysqli->prepare('SELECT altname FROM subjects WHERE name=?');
                    while ($epi = $result->fetch_assoc()) {
                        $getSubAltname->bind_param('s', $epi['subject']);
                        $getSubAltname->execute();
                        $getSubAltname->bind_result($subj_altname);
                        $getSubAltname->fetch();
                        $tab_str .=
                            '<tr>
				<td>' . $subj_altname . '</td>
				<td><a href="view-episode.php?id=' . $epi['id'] . '" target="_blank">' . $epi['title'] . '</a></td>
				<td>' . $epi['authorid'] . '</td>
				<td>' . $epi['author'] . '</td>
				<td>' . date("m/d/Y H:i:s", (int)$epi['publish_time']) . '</td>
			</tr>';
                    }
                    echo $tab_str;
                    $getSubAltname->close();
                    ?>
                </table>
            </div>
        <?php } elseif ($_GET['action'] === 'subjectmanage' && $isAdmin) { ?>
            <div id="subjectmanage" class="context">
                <form id="subjectform" name="subjectform" method="post" action="phps/editSubject.php">
                    <table id="subject_tab">
                        <tr style="font-size: 24px; font-weight: bold">
                            <td><?php echo getMsg('ID'); ?></td>
                            <td><?php echo getMsg('Name'); ?></td>
                            <td><?php echo getMsg('Alternative name'); ?></td>
                        </tr>
                        <?php
                        $subjects = $mysqli->query('SELECT * FROM subjects');
                        while ($subject = $subjects->fetch_array()) {
                            echo '<tr onClick="editThis(this, this.parentNode)">
                                    <td>' . $subject['id'] . '</td>
                                    <td>' . $subject['name'] . '</td>
                                    <td>' . $subject['altname'] . '</td>
                                </tr>';
                        }
                        ?>
                    </table>
                    <input type="button" id="editsub" value="<?php echo getMsg('Edit'); ?>" class="submitbutton"
                           onClick="Edit(document.getElementById('subject_tab'), this)"/>&nbsp;
                    <input type="button" id="newsub" value="<?php echo getMsg('New'); ?>" class="submitbutton"
                           onClick="newSub(document.getElementById('subject_tab'), this)"/>&nbsp;
                    <input type="button" id="deletesub" value="<?php echo getMsg('Delete'); ?>" class="submitbutton"
                           onClick="deleteSub(document.getElementById('subject_tab'));" disabled/>&nbsp;
                    <input type="button" value="<?php echo getMsg('Cancel'); ?>" class="submitbutton"
                           onClick="window.location.reload(true);"/>&nbsp;
                    <input type="submit" name="subbbmit" value="<?php echo getMsg('Save'); ?>" class="submitbutton"/>
                </form>
                <script>
                    function editThis(r, table) {
                        for (var i = 0; i < table.rows.length; i++) {
                            table.rows[i].style.color = "";
                            table.rows[i].style.backgroundColor = "";
                        }
                        r.style.backgroundColor = "#007706";
                        r.style.color = "#FFFFFF";
                        document.getElementById('deletesub').disabled = false;
                    }
                    function deleteSub(table) {
                        if (confirm('<?php echo getMsg('Are you sure you want to delete this subject?'); ?>')) {
                            var form = document.subjectform;
                            for (var i = 0; i < table.rows.length; i++) {
                                if (table.rows[i].style.color !== "") {
                                    var id = document.createElement("input");
                                    id.type = "text";
                                    id.name = "did";
                                    id.value = "" + table.rows[i].cells[0].innerHTML;
                                    id.style.display = "none";
                                    form.appendChild(id);
                                    break;
                                }
                            }
                            form.submit();
                        }
                    }
                    function newSub(table, button) {
                        var row = table.insertRow();
                        var cell1 = row.insertCell();
                        cell1.innerHTML = table.rows.length - 1;
                        var cell2 = row.insertCell();
                        cell2.innerHTML = "<input type=\"hidden\" name=\"nid\" value=\"" + (table.rows.length - 1) + "\"><input style=\"font-size: 21px; width:150px;\" type=\"text\" name=\"name\" value=\"\" />";
                        var cell3 = row.insertCell();
                        cell3.innerHTML = "<input style=\"font-size: 21px; width:150px;\" type=\"text\" name=\"altname\" value=\"\" />";
                        button.disabled = true;
                        editBut = document.getElementById('editsub');
                        editBut.disabled = true;
                        deleteBut = document.getElementById('deletesub');
                        deleteBut.disabled = true;
                    }
                    function Edit(table, button) {
                        for (var i = 0; i < table.rows.length; i++) {
                            if (table.rows[i].style.color !== "") {
                                table.rows[i].cells[1].innerHTML = "<input type=\"hidden\" name=\"id\" value=\"" + table.rows[i].cells[0].innerHTML + "\"><input style=\"font-size: 21px; width:150px;\" type=\"text\" name=\"name\" value=\"" + table.rows[i].cells[1].innerHTML + "\" />";
                                table.rows[i].cells[2].innerHTML = "<input style=\"font-size: 21px; width:150px;\" type=\"text\" name=\"altname\" value=\"" + table.rows[i].cells[2].innerHTML + "\" />";
                                button.disabled = true;
                                newSub = document.getElementById('newsub');
                                newSub.disabled = true;
                                return;
                            }
                        }
                    }
                </script>
            </div>
        <?php } elseif ($_GET['action'] === 'coremanage' && $isAdmin) { ?>
            <!--            <div id="coremanage" class="context">-->
            <!---->
            <!--            </div>-->
        <?php } ?>
        <form id="action_hidden" hidden action="<?php echo $antiXSS->xss_clean($_SERVER['PHP_SELF']); ?>" method="get">
            <input type="hidden" name="action" value="<?php echo $_GET['action']; ?>"/>
            <?php
            if ($_GET['action'] === 'usermanage') {
                if (empty($_GET['sortKey']))
                    echo '<input type="hidden" name="sortKey" value="regtime-desc">';
                else
                    echo "<input type=\"hidden\" name=\"sortKey\" value=\"{$_GET['sortKey']}\">";
            } elseif ($_GET['action'] === 'episodemanage') {
                if (empty($_GET['subject']))
                    echo '<input type="hidden" name="subject" value="subject">';
                else
                    echo "<input type=\"hidden\" name=\"subject\" value=\"{$_GET['subject']}\">";
                if (empty($_GET['sortKey']))
                    echo '<input type="hidden" name="sortKey" value="publish_time-desc">';
                else
                    echo "<input type=\"hidden\" name=\"sortKey\" value=\"{$_GET['sortKey']}\">";
            }
            ?>
        </form>
    </div>
    <p style="font-size: 16px; padding: 5px;clear:both; background-color: #DDDDDD; color:#000000; text-align: center">
        © 2016-2017 教育资源 Educational Resources 周涵之 Hanzhi Zhou. All Rights Reserved.
    </p>
</div>
</body>
<?php $mysqli->close(); ?>
</html>