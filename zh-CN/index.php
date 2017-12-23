<?php declare(strict_types=1); ?>
<!doctype html>
<html>
<head>
    <link rel="shortcut icon" href="../Doc/head.ico" type="image/x-icon">
    <meta charset="utf-8">
    <?php
    require 'phps/msg.php';
    ?>
    <title><?php echo getMsg('Educational Resources'); ?></title>
    <link href="css/index.css?ver=15" rel="stylesheet" type="text/css">
    <?php
    session_start();
    /**
     * Declaration of some constants
     * @const array SECTIONS
     * sections of this web page within 'subjects'
     * @const array ORDERS
     * fields which can be used to defined order of sorting
     * @const array ACTIONS
     * @const array SORT_KEYS
     * list of key fields which can be used to sort episodes
     * */
    define('SECTIONS', array('summary', 'episode', 'material'));
    define('ACTIONS', array('home', 'subjects', 'about'));
    define('SORT_KEYS', array('publish_time', 'title'));
    define('ORDERS', array('asc', 'desc'));

    require 'phps/mysqli_conn.php';
    $episode_sql = $mysqli->prepare('SELECT title FROM episodes WHERE subject=? AND status=1 ORDER BY publish_time DESC LIMIT 1');

    use voku\helper\AntiXSS;
    require 'phps/vendor/autoload.php';
    $antiXSS = new AntiXSS();

    /**
     * get the title of the latest episode of a subject
     * @param string $subj_name
     * the name of this subject
     * @return string
     * returns the title
     * */
    function getLatestTitle(string $subj_name): string
    {
        global $episode_sql;
        $episode_sql->bind_param('s', $subj_name);
        $episode_sql->execute();
        $episode_sql->bind_result($title);
        $episode_sql->fetch();
        if (!empty($title))
            return $title;
        else
            return getMsg('Not available');
    }

    //get the names and alternative names of all subjects
    $subjs = $mysqli->query('SELECT name, altname FROM subjects ORDER BY id');

    /**
     * @var array $subj_names
     * The array of subject names
     * @var array $subj_altnames
     * The array of subject alternative names
     * @var int $num_sub
     * The number of subjects
     * */
    $subj_names = array();
    $subj_altnames = array();
    while ($s = $subjs->fetch_assoc()) {
        $subj_names[] = $s['name'];
        $subj_altnames[] = $s['altname'];
    }
    $num_sub = count($subj_names);

    //make sure that the http get parameters are all valid.
    if (!empty($_SESSION['id'])) {
        $id = intval($_SESSION['id']);
        $row = $mysqli->query("SELECT * FROM userlist WHERE id=$id LIMIT 1")->fetch_assoc();
    }

    if (empty($_GET['action'])) {
        $_GET['action'] = 'home';
    } else {
        if ($_GET['action'] === 'subjects') {
            if (empty($_GET['subject'])) {
                $_GET['subject'] = isset($subj_names[0]) ? $subj_names[0] : '';
                $_GET['section'] = 'epilist';
            } else {
                if (empty($_GET['section']))
                    $_GET['section'] = 'epilist';
                else
                    if (!in_array($_GET['section'], SECTIONS))
                        $_GET['section'] = 'epilist';
                if (in_array($_GET['subject'], $subj_names)) {
                    if (!$_GET['section'] === 'epilist') {
                        if (empty($_GET['episode']))
                            $_GET['episode'] = getLatestTitle($_GET['subject']);
                        else
                            $_GET['episode'] = $antiXSS->xss_clean($_GET['episode']);
                    }
                } else {
                    $_GET['subject'] = isset($subj_names[0]) ? $subj_names[0] : '';
                    $_GET['section'] = 'epilist';
                }
            }
        } else
            if (!in_array($_GET['action'], ACTIONS))
                $_GET['action'] = 'home';
    }
    ?>
    <script>
        function toHome() {
            get_submit({"action": "home"});
        }
        function toAbout() {
            get_submit({"action": "about"});
        }
        function toSubjects(subject) {
            get_submit({
                "action": "subjects",
                "subject": subject,
                "section": "epilist",
                "sortby": "publish_time",
                "order": "desc"
            });
        }
        function get_submit(vals) {
            var form = document.selectVideo;
            if (vals["action"] !== "subjects") {
                try {
                    while (true)
                        form.removeChild(form.lastChild);
                }
                catch (e) {
                }
            }
            for (var x in vals) {
                var id = "get" + x;
                if (document.getElementById(id) === null) {
                    text = document.createElement("textarea");
                    text.name = x;
                    text.id = id;
                    text.style.display = "none";
                    text.value = vals[x];
                    form.appendChild(text);
                }
                else {
                    var text = document.getElementById(id);
                    text.value = vals[x];
                }
            }
            form.submit();
        }
        function show(name) {
            var tabs = document.getElementsByClassName("tab");
            for (var i = 0; i < tabs.length; i++) {
                tabs[i].style.color = "";
                tabs[i].style.backgroundColor = "";
                tabs[i].style.border = "inset";
            }
            var tab_div = document.getElementById(name + "_tab");
            tab_div.style.color = "#FFFFFF";
            tab_div.style.backgroundColor = "#007706";
            tab_div.style.border = "outset";
        }
    </script>
    <script src="js/showhide.min.js"></script>
</head>
<body style="background-color:#FFFFE5;">
<div id="all">
    <?php echo getMsg('NOTICE'); ?>
    <div id="content">
        <div id="top">
            <div id="top_pic"><br/>
                <span id="title"
                      onclick="window.location.href='index.php';">&nbsp;<?php echo getMsg('Educational Resources'); ?></span>
            </div>
            <div id="menu">
                <ul>
                    <li onClick="toHome();"><strong><?php echo getMsg('Home'); ?></strong></li>
                    <li><strong><?php echo getMsg('Subjects'); ?> ▾</strong>
                        <ul>
                            <?php
                            for ($i = 0; $i < $num_sub; $i++)
                                echo "<li onClick=\"toSubjects('{$subj_names[$i]}');\">{$subj_altnames[$i]}</li>";
                            ?>
                        </ul>
                    </li>
                    <li onClick="toAbout();"><strong><?php echo getMsg('About'); ?></strong></li>
                </ul>
            </div>
        </div>
        <?php if ($_GET['action'] === 'home') { ?>
            <div id="home" class="menuCorr">
                <div class="content" style="font-size:20px; text-align: left"><?php echo getMsg('GOALS'); ?></div>
            </div>
        <?php } elseif ($_GET['action'] === 'about') { ?>
            <div id="about" class="menuCorr"><br/>
                <span style="font-size:20pt; font-weight:bold;"><?php echo getMsg('About us'); ?></span><br/>
                <br/>
                <div class="SH" onClick="ShowHide('ThisSite', this);">◢ <?php echo getMsg('About this website'); ?></div>
                <div id="ThisSite"
                     style="font-size:22px; text-align:center; line-height:35px;"><?php echo getMsg('ABOUT_THIS_SITE') ?></div>
                <div class="SH" onClick="ShowHide('CoreTeam', this);">◢ <?php echo getMsg('Core Team Members'); ?></div>
                <div id="CoreTeam">
                    <div class="content">
                        <table class="text_about">
                            <tr>
                                <td width="300px" style="border:none;"></td>
                                <td style="border:none;"></td>
                                <td width="300px" style="border:none;"></td>
                            </tr>
                            <?php
                            $members = $mysqli->query('SELECT * FROM coreteam ORDER BY name DESC');
                            $i = 0;
                            while ($member = $members->fetch_assoc()) {
                                if ($i % 2 === 0)
                                    echo
                                    "<tr>
										<td><img src=\"{$member['pic_path']}\" width=\"300px\"></td>
										<td colspan=\"2\">{$member['description']}</td>
									</tr>";
                                else
                                    echo
                                    "<tr>
										<td colspan=\"2\">{$member['description']}</td>
										<td><img src=\"{$member['pic_path']}\" width=\"300px\"></td>
									</tr>";
                                $i++;
                            }
                            ?>
                        </table>
                    </div>
                </div>
            </div>
        <?php } elseif ($_GET['action'] === 'subjects') {
            $user_sql = $mysqli->prepare('SELECT id, headshot, email, nickname FROM userlist WHERE id=?');
            ?>
            <div id="subjects" class="menuCorr"><br/>
                <?php
                $subject = $_GET['subject'];
                for ($i = 0; $i < $num_sub; $i++) {
                    if ($subj_names[$i] === $subject) {
                        echo "<div id=\"{$subj_names[$i]}\" class=\"subject\">{$subj_altnames[$i]}</div>";
                        break;
                    }
                }
                ?>
                <br/>
                <div id="common">
                    <?php
                    if ($_GET['section'] === 'epilist') {
                        $sortkey = empty($_GET['sortby']) ? 'publish_time' :
                            (in_array($_GET['sortby'], SORT_KEYS) ? $_GET['sortby'] : 'publish_time');
                        $order = empty($_GET['order']) ? 'desc' :
                            (in_array($_GET['order'], ORDERS) ? $_GET['order'] : 'desc');
                        $sql = "SELECT * FROM episodes WHERE subject=? AND status=1 ORDER BY {$sortkey} {$order}";
                        $episodes = executeSQL($sql, array('s', $_GET['subject']));
                        ?>
                        <div class="SH" onClick="ShowHide('epilist', this);">◢ <?php echo getMsg('All episodes'); ?></div>
                        <p class="sortfield">
                            <label for="sortby"><?php echo getMsg('Sort by') ?>:</label>
                            <select name="sortby" id="sortby" onChange="changeSort()">
                                <option value="publish_time" <?php if ($sortkey === 'publish_time') echo 'selected'; ?>>
                                    <?php echo getMsg('Publish time'); ?>
                                </option>
                                <option value="title" <?php if ($sortkey === 'title') echo 'selected'; ?>>
                                    <?php echo getMsg('Title'); ?>
                                </option>
                            </select>
                            <label for="order"><?php echo getMsg('Order') ?>:</label>
                            <select name="order" id="order" onChange="changeSort()">
                                <option value="asc" <?php if ($order === 'asc') echo 'selected'; ?>><?php echo getMsg('Ascending'); ?></option>
                                <option value="desc" <?php if ($order === 'desc') echo 'selected'; ?>><?php echo getMsg('Descending'); ?></option>
                            </select>
                            <?php
                            // check if user is subscribed to the episode
                            if (isset($id)) {
                                $subj_id = $mysqli->query("SELECT id FROM subjects WHERE name='{$_GET['subject']}'")->fetch_assoc()['id'];
                                $user_subs = $mysqli->query("SELECT id FROM subscribe_{$subj_id} WHERE id=$id")->fetch_assoc();
                                if (isset($user_subs['id']))
                                    echo '<button type="submit" onClick="Subscribe()" style="font-size: 18px;">', getMsg('Cancel subscription'), '</button>';
                                else
                                    echo '<button type="submit" onClick="Subscribe()" style="font-size: 20px;">', getMsg('Subscribe'), '</button>';
                            }
                            ?>
                        </p>
                        <div id="epilist">
                            <table id="episodelist">
                                <?php
                                // echo the episode list
                                foreach ($episodes as $episode) {
                                    $user = getUserInfo($user_sql, $episode['authorid']);
                                    echo
                                    "<tr id=\"{$episode['title']}\" onClick=\"changeEpi(this)\">
                                        <td width=\"200px\" height=\"150px\" style=\"vertical-align:central;\">
                                            <img src=\"{$episode['poster_path']}.mini\" width=\"200px\"/>
                                        </td>
                                        <td style=\"vertical-align:central;\">
                                            <p class=\"episode_p_title\">{$episode['title']}</p>
                                            <p class=\"episode_p_author\">{$episode['author']}</p>
                                            <p class=\"episode_p_uploader\">{$user['email']}</p>
                                            <p class=\"episode_p_pt\">", date('Y/m/d H:i:s', $episode['publish_time']), "</p>
                                        </td>
                                    </tr>";
                                }
                                ?>
                            </table>
                        </div>
                        <script>
                            function changeEpi(tbrow) {
                                var form = document.selectVideo;
                                if (document.getElementById('getepisode') !== null)
                                    form.episode.value = tbrow.id;
                                else {
                                    var epis = document.createElement('input');
                                    epis.type = 'hidden';
                                    epis.name = 'episode';
                                    epis.value = tbrow.id;
                                    form.appendChild(epis);
                                }
                                try {
                                    form.removeChild(form.sortby);
                                    form.removeChild(form.order);
                                }
                                catch (e) {
                                }
                                form.section.value = 'summary';
                                form.submit();
                            }
                            function changeSort() {
                                var form = document.selectVideo;
                                var sortBy = document.getElementById('sortby').value;
                                var order = document.getElementById('order').value;
                                form.sortby.value = sortBy;
                                form.order.value = order;
                                form.submit();
                            }
                            function Subscribe() {
                                window.location.replace('phps/subscribe.php?subject=<?php echo $_GET['subject']; ?>');
                            }
                        </script>
                    <?php
                    }else{
                    $sql = 'SELECT * FROM episodes WHERE subject=? AND title=? AND status=1 LIMIT 1';
                    $current = executeSQL($sql, array('ss', $_GET['subject'], !empty($_GET['episode']) ? $_GET['episode'] : ''));
                    if (empty($current[0]))
                        header("Location: index.php?action=subjects&subject={$subject}&section=epilist&sortby=publish_time&order=desc");
                    else
                        $current = $current[0];
                    ?>
                        <script>
                            function tabclick(tab_div) {
                                var section = tab_div.id.split("_")[0];
                                get_submit({
                                    "action": "subjects",
                                    "subject": "<?php echo $_GET['subject'];?>",
                                    "episode": "<?php echo $_GET['episode'];?>",
                                    "section": section
                                });
                            }
                            function toList() {
                                var form = document.selectVideo;
                                var epiinput = document.getElementById('getepisode');
                                if (epiinput !== null)
                                    form.removeChild(epiinput);
                                get_submit({
                                    "action": "subjects",
                                    "subject": "<?php echo $_GET['subject'];?>", "section": "epilist",
                                    "sortby": "publish_time",
                                    "order": "desc"
                                });
                            }
                        </script>
                        <div id="tabs_container">
                            <div id="summary_tab" class="tab"
                                 onClick="tabclick(this);"><?php echo getMsg('Summary'); ?></div>
                            <?php
                            if (!empty($current['external_src']) || !empty($current['video_path']))
                                echo '<div id="episode_tab" class="tab" onClick="tabclick(this);">', getMsg('Video'), '</div>';
                            else
                                echo '<div id="episode_tab" class="tab_disabled">', getMsg('Video'), '</div>';
                            if (!empty($current['material_path']))
                                echo '<div id="material_tab" class="tab" onClick="tabclick(this);">', getMsg('Material'), '</div>';
                            else
                                echo '<div id="material_tab" class="tab_disabled">', getMsg('Material'), '</div>';
                            ?>
                            <div id="material_tab" class="tab" onClick="toList();"><?php echo getMsg('Back'); ?></div>
                            <div style="clear:both;"></div>
                        </div>
                        <div id="holder">
                            <?php if ($_GET['section'] === 'summary') { ?>
                                <div id="summary" class="tabCorr">
                                    <?php if (!empty($current['poster_path'])) echo '<img width="600px" src="' . $current['poster_path'] . '">'; ?>
                                    <table class="infotable">
                                        <tr>
                                            <td><?php echo getMsg('Title'); ?>:&nbsp;</td>
                                            <td><?php echo empty($current['title']) ? '' : $current['title']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo getMsg('Author'); ?>:&nbsp;</td>
                                            <td><?php echo empty($current['author']) ? '' : $current['author']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo getMsg('Uploader'); ?>:&nbsp;</td>
                                            <td><?php
                                                if (!empty($current['authorid'])) {
                                                    $temp = $mysqli->query("SELECT email FROM userlist WHERE id={$current['authorid']}")->fetch_assoc();
                                                    if (isset($temp['email']))
                                                        echo "<a href=\"user-profile.php?id={$current['authorid']}\" target=\"_blank\">{$temp['email']}</a>";
                                                }
                                                ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo getMsg('Publish time'); ?>:&nbsp;</td>
                                            <td><?php echo empty($current['publish_time']) ? 'Unknown' : date('Y/m/d H:i:s', $current['publish_time']); ?></td>
                                        </tr>
                                    </table>
                                    <p style="font-size:24px; margin-bottom:10px; font-weight:bold;"><?php echo getMsg('Summary'); ?></p>
                                    <?php echo empty($current['summary']) ? getMsg('Not available') : $current['summary']; ?>
                                    <br/><br/>
                                </div>
                            <?php } elseif ($_GET['section'] === 'episode') {
                                if (empty($current['video_path']) && empty($current['external_src']))
                                    echo "<script>window.location.replace('index.php?action=subjects&subject={$_GET['subject']}&section=summary&episode={$_GET['episode']}');</script>";
                                ?>
                                <div id="episode" class="tabCorr">
                                    <?php
                                    //output the video frame, external and internal
                                    if (!empty($current['external_src']))
                                        echo '<div id="ext_sh" class="SH" onClick="ShowHide(\'ext_src\', this)">◢&nbsp;', getMsg('External Video Source'), '</div>', '<div id="ext_src">', $current['external_src'], '</div>';
                                    if (!empty($current['video_path']))
                                        if (empty($current['external_src']))
                                            echo '<div id="video_sh" class="SH" onClick="ShowHide(\'int_video\', this)">◢&nbsp;', getMsg('Internal Video Source'), '</div>',
                                            "<div id=\"int_video\"><video src=\"{$current['video_path']}\" controls style=\"z-index:-1;\" width=\"800px\"></video></div>";
                                        else
                                            echo '<div id="video_sh" class="SH" onClick="ShowHide(\'int_video\', this)">▶&nbsp;', getMsg('Internal Video Source'), '</div>',
                                            "<div id=\"int_video\" style=\"display: none;\"><video src=\"{$current['video_path']}\" controls style=\"z-index:-1;\" width=\"800px\"></video></div>";
                                    ?>
                                </div>
                            <?php } elseif ($_GET['section'] === 'material') {
                                if (empty($current['material_path']))
                                    echo "<script>window.location.replace('index.php?action=subjects&subject={$_GET['subject']}&section=summary&episode={$_GET['episode']}');</script>";
                                ?>
                                <div id="material" class="tabCorr">
                                    <table class="infotable">
                                        <tr>
                                            <td><?php echo getMsg('Download link'); ?>:&nbsp;</td>
                                            <td>
                                                <a href="<?php echo $current['material_path']; ?>">
                                                    <?php echo $current['material_title']; ?>
                                                </a></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo getMsg('Description'); ?>:&nbsp;</td>
                                            <td><?php echo $current['material_description']; ?></td>
                                        </tr>
                                    </table>
                                </div>
                            <?php } ?>
                            <div class="SH" onClick="ShowHide('comments', this);">◢ <?php echo getMsg('Comments'); ?></div>
                            <div id="comments">
                                <div class="postcomment">
                                    <?php
                                    if (!empty($current['id'])) {
                                        echo '<br />';
                                        if (!empty($_SESSION['id'])) {
                                            // output the comment box if the user is registered and not silenced
                                            if (intval($row['status']) === 0)
                                                echo '<span class="hint">', getMsg('You should activate your account prior to posting comments!'), '</span><br /><br />';
                                            elseif (intval($row['silence']) === 1)
                                                echo '<span class="hint">', getMsg('You are silenced by administrator and cannot post comments!'), '</span><br /><br />';
                                            else
                                                echo '<form method="post" action="phps/comment.php" style="text-align:right;width:800px;" onSubmit="return isValidComment(this);">
                                                            <textarea style="width:795px; margin:auto auto;" rows="4" class="usercomment" name="commentbody"></textarea>
                                                            <input type="text" name="episodeid" hidden value="' . $current['id'] . '">
                                                            <input type="submit" name="submit" value=" ', getMsg('submit'), ' " style="font-size:20px;padding:5px;">
                                                        </form>';
                                        } else
                                            echo '<span class="hint">', getMsg('Dear guest, please login if you want to add comments'), '</span><br /><br />';
                                    }
                                    ?>
                                </div>
                                <?php
                                /**
                                 * convert time difference to words
                                 * @param int $timediff
                                 * the time difference between current time and the time when the comment/reply was posted
                                 * @return string
                                 * */
                                function getTimeStr($timediff)
                                {
                                    if ($timediff < 60)
                                        return getMsg('less than 1 min');
                                    else if ($timediff < 3600)
                                        return (int)($timediff / 60) . getMsg(' minutes ago');
                                    else if ($timediff < 86400)
                                        return (int)($timediff / 3600) . getMsg(' hours ago');
                                    else
                                        return (int)($timediff / 86400) . getMsg(' days ago');
                                }

                                //iteratively read comments and their replies
                                if (!empty($current['id'])) {

                                    //get comments using episode's id, ordering them in a way such that the latest one comes first
                                    $comments = executeSQL('SELECT * FROM comments WHERE episode_id=? ORDER BY comment_time DESC', array('i', $current['id']));
                                    if (!empty($comments[0])) {
                                        $comment_num = count($comments);
                                        echo '<div style="text-align: left"><strong style="font-size: 20px">',
                                        getMsg('All comments'), "&nbsp;({$comment_num} ", getMsg('in total'), "):</strong></div>";

                                        // generate the table of comments
                                        $comment_tab = '<table id="comment_tab">';

                                        // prepared statement is used to promote the efficiency
                                        $reply_sql = $mysqli->prepare('SELECT * FROM replies WHERE comment_id=?');
                                        for ($i = 0; $i < $comment_num; $i++) {
                                            $comment = $comments[$i];
                                            $user = getUserInfo($user_sql, $comment['userid']);
                                            $comment_time = getTimeStr(time() - $comment['comment_time']);
                                            $uNickname = displayName($user);

                                            $comment_tab .= "<tr><td width=\"80px\"><img src=\"{$user['headshot']}.mini\" class=\"comment_headshot\"></td>
										<td class=\"secondTD\"><a class=\"username\" href=\"user-profile.php?id={$user['id']}\" target=\"_blank\">$uNickname</a><br />
											<span class=\"usercomment\">{$comment['comment_body']}</span><br />
											<span class=\"commenttime\">{$comment_time}</span>&nbsp;&nbsp;";

                                            // append the reply button if user is logged in and is not silenced
                                            if (!empty($id) && empty($row['silence'])) {
                                                $comment_tab .= '<a href="javascript:dynamicBox(\'' . ($i + 1) . '\',\'' . $comment['id'] . '\');">' . getMsg('Reply') . '</a>';

                                                // append the link for deletion if user is admin or the user is the one who posted the comment
                                                if ($row['userlevel'] > 5 || intval($comment['userid']) === $id)
                                                    $comment_tab .= "&nbsp;&nbsp;<a href=\"phps/deleteComment.php?cid={$comment['id']}\">" . getMsg('Delete') . '</a>';
                                            }
                                            $comment_tab .= '<br /><br />';

                                            //get replies of this comment using its id
                                            $reply_sql->bind_param('i', $comment['id']);
                                            $reply_sql->execute();
                                            $replies = getResults($reply_sql);
                                            foreach ($replies as $reply) {
                                                $reply_time = getTimeStr(time() - $reply['reply_time']);
                                                $user = getUserInfo($user_sql, $reply['userid']);
                                                $uNickname = displayName($user);

                                                $comment_tab .=
                                                    "<table class=\"reply_tab\">
                                                <tr><td width=\"60px\"><img src=\"{$user['headshot']}.mini\" class=\"reply_headshot\"></td>
                                                    <td><a class=\"un_reply\" href=\"user-profile.php?id={$user['id']}\" target=\"_blank\">$uNickname</a><br />
                                                        <span class=\"uc_reply\">{$reply['reply_body']}</span><br />
                                                        <span class=\"replytime\">{$reply_time}</span>";

                                                // append the link for deletion if user is admin or the user is the one who posted the reply
                                                if (!empty($_SESSION['id']))
                                                    if ($row['userlevel'] > 5 || intval($reply['userid']) === $id)
                                                        $comment_tab .= "&nbsp;&nbsp;<a href=\"phps/deleteReply.php?rid={$reply['id']}\">" . getMsg('Delete') . '</a>';
                                                $comment_tab .= '</td></tr></table>';
                                            }
                                            $comment_tab .= '</td></tr>';
                                        }
                                        $reply_sql->close();
                                        echo $comment_tab, '</table>';
                                    } else
                                        echo '<br /><span class="hint">', getMsg('No comments are posted, add one now!'), '</span><br /><br />';
                                }
                                ?>
                                <script>
                                    function dynamicBox(row, cid) {
                                        var ifexist = document.getElementById("dynreplytr");
                                        if (ifexist !== null) {
                                            ifexist.parentNode.removeChild(ifexist);
                                        }
                                        var table = document.getElementById("comment_tab");
                                        var tbRow = table.insertRow(row);
                                        tbRow.id = "dynreplytr";
                                        var cell1 = tbRow.insertCell();
                                        var cell2 = tbRow.insertCell();
                                        cell2.innerHTML = "<div class=\"dyndiv\"><form method=\"post\" action=\"phps/reply.php\" style=\"text-align:right;width:720px;\" onSubmit=\"return isValidReply(this);\" id=\"dynform\"><textarea style=\"width:715px;font-size:16px;\" rows=\"3\" class=\"usercomment\" name=\"replybody\"></textarea><input type=\"text\" name=\"commentid\" style=\"display:none\" value=\"" + cid + "\"/><br /><input type=\"submit\" name=\"submit\" value=\" <?php echo getMsg('submit');?> \" style=\"font-size:20px;padding:5px;\"></form></div>";
                                        var form = document.getElementById("dynform");
                                        form.replybody.focus();
                                    }
                                    function isValidComment(form) {
                                        return (form.commentbody.value.length > 0);
                                    }
                                    function isValidReply(form) {
                                        return (form.replybody.value.length > 0);
                                    }
                                </script>
                            </div>
                        </div>
                        <?php $user_sql->close();
                    } ?>
                </div>
            </div>
        <?php }
        if ($_GET['action'] === 'home') { ?>
            <div id="bg_bottom">
                <div class="text_bottom"><br/>
                    <strong style="font-size:24px;"><?php echo getMsg('Our WeChat Official Account'); ?></strong><br/>
                    <br/>
                    <img src="../Doc/QR.jpg" style="width:300px;height:300px;"><br/>
                    <br/>
                    <a style="font-size:22px; font-weight:bold;"
                       href="https://mp.weixin.qq.com/mp/homepage?__biz=MzIxMTA2NDMyMg==&hid=2&sn=451be636925322ff831b9e5c2ba80483#wechat_redirect"
                       target="_blank"><?php echo getMsg('Past WeChat Push'); ?></a>
                    <br/><br/>
                </div>
            </div>
        <?php } ?>
    </div>
    <div id="right_margin">
        <div class="SH" onClick="ShowHide('languages', this);">◢ 语言/Languages</div>
        <div id="languages">
            <div onClick="window.location.href = '../zh-CN/index.php?action=<?php echo $_GET['action'],
            empty($_GET['subject']) ? '' : "&subject={$_GET['subject']}",
            empty($_GET['episode']) ? '' : "&episode={$_GET['episode']}",
            empty($_GET['section']) ? '' : "&section={$_GET['section']}";
            ?>';" <?php echo getMsg('zh_CN_STYLE') ?>>简体中文
            </div>
            <div onClick="window.location.href = '../en-US/index.php?action=<?php echo $_GET['action'],
            empty($_GET['subject']) ? '' : "&subject={$_GET['subject']}",
            empty($_GET['episode']) ? '' : "&episode={$_GET['episode']}",
            empty($_GET['section']) ? '' : "&section={$_GET['section']}";
            ?>';" <?php echo getMsg('en_US_STYLE') ?>>English
            </div>
        </div>
        <div class="SH" onClick="ShowHide('logreg', this)">◢&nbsp;
            <?php
            if (!empty($_SESSION['id']))
                echo getMsg('About me');
            else
                echo getMsg('Login/Register');
            ?>
        </div>
        <div id="logreg">
            <?php
            // if the user is logged in, echo the link to dashboard
            if (!empty($_SESSION['id'])) {
                echo
                '<table style="margin:auto auto;height:80px;">
						<tr>
							<td><img src="', $row['headshot'], '.mini" width="50px" height="50px" onClick="window.open(\'dashboard.php?action=personal\')" style="cursor: pointer;"></td>
							<td style="line-height:25px; font-size:20px;"><span><a href="dashboard.php?action=personal" target="_blank">', $row['email'], '</a>
								<br />
								<a href="phps/login.php?action=logout">', getMsg('Logout'), '</a>
								</span>
							</td>
						</tr>
					</table>';
            } // if not, echo the link to login/register page
            else
                echo '<div onClick="window.location.href=\'login.html\'">', getMsg('Login'), '</div>
            <div onClick="window.location.href=\'register.html\'">', getMsg('Register'), '</div>';
            ?>
        </div>
        <div class="SH" onClick="ShowHide('latest', this)">◢&nbsp;<?php echo getMsg('Latest episodes'); ?></div>
        <div id="latest">
            <?php
            /**
             * generate link to an episode, given its title and the subject
             * @param string $epi_title
             * @param string $subj
             * @return string
             * the link relative to index.php
             * */
            function genLink(string $epi_title, string $subj): string
            {
                if ($epi_title !== getMsg('Not available'))
                    return "index.php?action=subjects&subject={$subj}&section=summary&episode={$epi_title}";
                else
                    return '#';
            }

            unset($sub_latests);
            for ($i = 0; $i < $num_sub; $i++) {
                $t = getLatestTitle($subj_names[$i]);
                echo "<div class=\"right_title\">{$subj_altnames[$i]}</div>",
                '<p onClick="window.location.href=\'', genLink($t, $subj_names[$i]), '\'">', $t, '</p>';
            }
            ?>
        </div>
    </div>
    <p style="font-size: 16px; padding: 5px;clear:both; background-color: #DDDDDD; color:#000000; text-align: center">
        © 2016-2017 教育资源 Educational Resources 周涵之 Hanzhi Zhou. All Rights Reserved.
    </p>
</div>
<form method="get" name="selectVideo" action="<?php echo $antiXSS->xss_clean($_SERVER['PHP_SELF']); ?>" hidden>
    <?php
    // form for switching between sections
    echo "<input type=\"hidden\" name=\"action\" id=\"getaction\" value=\"{$_GET['action']}\">";
    if ($_GET['action'] === "subjects") {
        echo "<input type=\"hidden\" name=\"subject\" id=\"getsubject\" value=\"{$_GET['subject']}\">";
        echo "<input type=\"hidden\" name=\"section\" id=\"getsection\" value=\"{$_GET['section']}\">";
        if ($_GET['section'] === 'epilist')
            echo "<input type=\"hidden\" name=\"sortby\" id=\"sortby\" value=\"{$_GET['sortby']}\">",
            "<input type=\"hidden\" name=\"order\" id=\"order\" value=\"{$_GET['order']}\">";
        if (!empty($_GET['episode']))
            echo "<input type=\"hidden\" name=\"episode\" id=\"getepisode\" value=\"{$_GET['episode']}\">";
    }
    ?>
</form>
</body>
<script>
    function menuFix() {
        var elements = document.getElementById("menu").getElementsByTagName("li");
        for (var i = 0; i < elements.length; i++) {
            elements[i].onmouseover = function () {
                this.className += (this.className.length > 0 ? " " : "") + "listshow";
            };
            elements[i].onmouseout = function () {
                this.className = this.className.replace("listshow", "");
            };
        }
    }
    function adjustDisplays() {
        var right_mg = document.getElementById('right_margin');
        var content_d = document.getElementById('content');
        if (content_d.offsetHeight > right_mg.offsetHeight)
            right_mg.style.height = content_d.offsetHeight + 'px';
        else
            content_d.style.height = right_mg.offsetHeight + 'px';
    }
    menuFix();
    <?php
    if ($_GET['action'] === 'subjects')
        if ($_GET['section'] != 'epilist')
            echo "show('{$_GET['section']}');";
    ?>
    window.onload = adjustDisplays;
</script>
</html>
<?php
$episode_sql->close();
$mysqli->close();
?>
