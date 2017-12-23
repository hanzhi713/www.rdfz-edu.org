<?php declare(strict_types=1); ?>
    <!doctype html>
    <html>
    <head>
        <link rel="shortcut icon" href="../Doc/head.ico" type="image/x-icon">
        <meta charset="utf-8">
        <?php
        require 'phps/msg.php';
        ?>
        <title><?php echo getMsg('Episode Details'); ?></title>
        <link href="css/view-episode.css?ver=2" rel="stylesheet" type="text/css">
        <?php
        session_start();
        if (empty($_SESSION['id'])) {
            header('Location: index.php?action=home');
            exit();
        }

        if (empty($_GET['id'])) {
            header('Location: index.php?action=home');
            exit();
        }

        require_once('phps/mysqli_conn.php');

        $epi_id = intval($_GET['id']);
        $d = $mysqli->query("SELECT * FROM episodes WHERE id=$epi_id");
        if (!($episode = $d->fetch_assoc())) {
            header('Location: index.php?action=home');
            exit('<script>alert("Invalid episode id!")</script>');
        }

        $id = intval($_SESSION['id']);
        $data = $mysqli->query("SELECT userlevel FROM userlist WHERE id=$id LIMIT 1");
        $row = $data->fetch_assoc();
        $isAdmin = $row['userlevel'] > 5 || (intval($episode['authorid']) === $id);

        if (!$isAdmin) {
            header('Location: index.php?action=home');
            exit();
        }
        $action_arr = array('poster', 'summary', 'upvideo', 'upmaterial', 'publish', 'delete');
        if (empty($_GET['action'])) $_GET['action'] = 'summary';
        elseif (!in_array($_GET['action'], $action_arr)) $_GET['action'] = 'summary';

        ?>
        <script>
            var getAction = "<?php echo $_GET['action'];?>";
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange =
                function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        var responseText = xhr.responseText;
                        proL.innerHTML = parseFloat(responseText).toFixed(2) + '%';
                        proB.style.width = proL.innerHTML;
                    }
                };
            function getProgress() {
                xhr.open("GET", "phps/progress.php?<?php echo ini_get("session.upload_progress.name");?>=mt", true);
                xhr.send(null);
            }
            function getDisplay() {
                changeMenuStyle("menu_" + getAction);
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
        <script src="js/showhide.min.js"></script>
        <script src="js/check_extension.min.js"></script>
        <div id="top">
            <div id="top_bar">
                <table width="1000px">
                    <tr>
                        <td style="vertical-align:central;">
                            <span style="font-size:32px">&nbsp;<?php echo getMsg('Episode Details'); ?></span>
                        </td>
                        <td style="text-align:right;">
                            <table style="margin-left:auto;height: 50px">
                                <tr>
                                    <td style="text-align:right; font-size:28px;"><?php echo $episode['title']; ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </head>
    <body style="background-color:#FFFFE5;">
    <br/>
    <div id="content">
        <div id="left_menu">
            <div id="menu_summary" class="menu" onClick="menuclick(this);"><?php echo getMsg('Summary'); ?></div>
            <div id="menu_poster" class="menu" onClick="menuclick(this);"><?php echo getMsg('Poster'); ?></div>
            <div id="menu_upvideo" class="menu" onClick="menuclick(this);"><?php echo getMsg('Video'); ?></div>
            <div id="menu_upmaterial" class="menu" onClick="menuclick(this);"><?php echo getMsg('Material'); ?></div>
            <div id="menu_publish" class="menu" onClick="menuclick(this);"><?php echo getMsg('Publication'); ?></div>
            <div id="menu_delete" class="menu" onClick="menuclick(this);"><?php echo getMsg('Delete episode'); ?></div>
        </div>
        <div id="main">
            <?php if ($_GET['action'] === 'summary') { ?>
                <div id="summary" class="context">
                    <script src="js/check_summary.min.js"></script>
                    <form action="phps/modifyEpisode.php" method="post" onSubmit="return checkSummary(this);">
                        <input type="hidden" name="id" value="<?php echo $epi_id; ?>"/>
                        <br/>
                        <strong style="font-size:24px;"><?php echo getMsg('Episode Information'); ?></strong><br/>
                        <br/>
                        <table class="infotable">
                            <tr>
                                <td><label for="subject"><?php echo getMsg('Subject'); ?></label></td>
                                <td><select name="subject" id="subject">
                                        <?php
                                        $subjs = $mysqli->query('SELECT name, altname FROM subjects ORDER BY id');
                                        while ($s = $subjs->fetch_assoc()) {
                                            if ($episode['subject'] === $s['name'])
                                                echo '<option value="' . $s['name'] . '" selected>' . $s['altname'] . '</option>';
                                            else
                                                echo '<option value="' . $s['name'] . '">' . $s['altname'] . '</option>';
                                        }
                                        ?>
                                    </select></td>
                            </tr>
                            <tr>
                                <td><label for="t"><?php echo getMsg('Title'); ?></label></td>
                                <td><input type="text" name="title" id="t" value="<?php echo $episode['title']; ?>"></td>
                            </tr>
                            <tr>
                                <td><label for="author"><?php echo getMsg('Author'); ?></label></td>
                                <td><input type="text" name="author" id="author" value="<?php echo $episode['author']; ?>">
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo getMsg('Publish time'); ?></td>
                                <td><?php echo date('m/d/Y H:i:s', (int)$episode['publish_time']); ?></td>
                            </tr>
                            <tr>
                                <td><label for="summary"><?php echo getMsg('Summary'); ?></label></td>
                                <td><textarea rows="8" cols="40" style="font-size:17px;" id="summary"
                                              name="summary"><?php echo $episode['summary']; ?></textarea></td>
                            </tr>
                        </table>
                        <br/>
                        <input type="submit" name="sub_summary" value=" <?php echo getMsg('Save'); ?> " class="submitbutton"/>
                    </form>
                </div>
            <?php } elseif ($_GET['action'] === 'poster') { ?>
                <div id="poster" class="context">
                    <script src="js/check_poster.min.js"></script>
                    <form action="phps/modifyEpisode.php" method="post" enctype="multipart/form-data"
                          onSubmit="return checkPoster(this);">
                        <input type="hidden" name="id" value="<?php echo $epi_id; ?>" hidden=""/>
                        <img src="<?php echo $episode['poster_path']; ?>" width="700px"/>
                        <input type="file" name="photo" style="font-size:18px;"/>
                        <input type="submit" name="sub_poster" value=" <?php echo getMsg('Save'); ?> "
                               class="submitbutton"/>
                    </form>
                </div>
            <?php } elseif ($_GET['action'] === 'upvideo') { ?>
                <div id="upvideo" class="context">
                    <form id="uploadform" action="phps/modifyEpisode.php" method="post" enctype="multipart/form-data"
                          onSubmit="return isValidVideo(this);">
                        <input type="hidden" name="id" value="<?php echo $epi_id; ?>"/>
                        <div id="ext_sh" class="SH" onClick="ShowHide('ext_src', this)">◢&nbsp;<?php echo getMsg('External Video Source'); ?></div>
                        <?php
                        if (!empty($episode['external_src']))
                            echo '<div id="ext_src">', $episode['external_src'], '</div>';
                        ?>
                        <br/><label for="external_src"><?php echo getMsg('External Sources (HTML code)'); ?></label><br/>
                        <textarea name="external" rows="5" cols="80" id="external_src"
                                  style="font-size:16px;"><?php if (!empty($episode['external_src'])) echo $episode['external_src'] ?></textarea><br/><br/>
                        <div id="video_sh" class="SH" onClick="ShowHide('int_video', this)">◢&nbsp;<?php echo getMsg('Internal Video Source'); ?></div>
                        <?php
                        if (!empty($episode['video_path']))
                            echo '<div id="int_video">', '<video src="' . $episode['video_path'] . '" controls style="z-index:-1;" width="740px"></video></div>';
                        ?>
                        <input type="hidden" name="<?php echo ini_get("session.upload_progress.name"); ?>" value="mt"/>
                        <input type="file" name="video" style="font-size:18px;"/><br/><br/>
                        <input type="submit" name="sub_video" value=" <?php echo getMsg('Save'); ?> " class="submitbutton"/>&nbsp;
                        <input type="button" name="del_video" value="<?php echo getMsg('Delete Video'); ?>"
                               class="submitbutton" onClick="vConfirm();"/>
                    </form>
                    <script>
                        function isValidVideo(form) {
                            /*
                             if (form.video.value!=""){
                             if (!checkExt(form.video.value, ["mp4"])){
                             alert("video must be mp4 only!");
                             return false;
                             }
                             }*/
                            if (form.video.value !== "") {
                                proD.style.display = 'block';
                                setInterval(function () {
                                    getProgress();
                                }, 1111);
                            }
                            return true;
                        }
                        function vConfirm() {
                            var form = document.getElementById('uploadform');
                            if (confirm("<?php echo getMsg('Are you sure you want to delete the video? This action is irreversible!');?>")) {
                                var text = document.createElement('input');
                                text.type = 'text';
                                text.name = 'del_video';
                                text.value = 'holder';
                                text.hidden = true;
                                form.appendChild(text);
                                form.submit();
                            }
                        }
                    </script>
                </div>
            <?php } elseif ($_GET['action'] === 'upmaterial') { ?>
                <div id="upmaterial" class="context"><br/>
                    <strong style="font-size:24px;"><?php echo getMsg('Supplementary Material'); ?></strong><br/>
                    <form id="materialform" action="phps/modifyEpisode.php" method="post" enctype="multipart/form-data"
                          onSubmit="return isValidMaterial(this);">
                        <input type="hidden" name="id" value="<?php echo $epi_id; ?>"/>
                        <table class="infotable">
                            <tr>
                                <td><label for="material_title"><?php echo getMsg('Title'); ?></label></td>
                                <td><input type="text" name="material_title" id="material_title"
                                           value="<?php echo $episode['material_title']; ?>"></td>
                            </tr>
                            <tr>
                                <td><label for="description"><?php echo getMsg('Description'); ?></label></td>
                                <td><textarea rows="8" cols="40" style="font-size:17px;"
                                              name="material_description"
                                              id="description"><?php echo $episode['material_description']; ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo getMsg('Material'); ?></td>
                                <td><a href="<?php echo $episode['material_path']; ?>"
                                       target="_blank"><?php echo getMsg('Download'); ?></a></td>
                            </tr>
                        </table>
                        <input type="hidden" name="<?php echo ini_get("session.upload_progress.name"); ?>" value="mt"/>
                        <input type="file" name="material" style="font-size:18px;">
                        <br/><br/>
                        <input type="submit" name="sub_material" value=" <?php echo getMsg('Save'); ?> "
                               class="submitbutton"/>
                        <input type="button" name="del_material" value="<?php echo getMsg('Delete Material'); ?>"
                               class="submitbutton"
                               onClick="mConfirm();"/>
                    </form>
                    <script>
                        function isValidMaterial(form) {
                            if (form.material_title.value === "") {
                                alert("Title cannot be empty!");
                                form.material_title.focus();
                                return false;
                            }
                            if (form.material.value === "") {
                                alert("Error! No file is uploaded!");
                                return false;
                            }
                            proD.style.display = 'block';
                            setInterval(function () {
                                getProgress();
                            }, 1100);
                            return true;
                        }
                        function mConfirm() {
                            var form = document.getElementById('materialform');
                            if (confirm("Are you sure you want to delete the material? This action is irreversible")) {
                                var text = document.createElement('input');
                                text.type = 'text';
                                text.name = 'del_material';
                                text.value = 'holder';
                                text.hidden = true;
                                form.appendChild(text);
                                form.submit();
                            }
                        }
                    </script>
                </div>
            <?php } elseif ($_GET['action'] === 'delete') { ?>
                <div id="delete" class="context"><br/>
                    <form method="post" action="phps/modifyEpisode.php">
                        <input type="hidden" name="id" value="<?php echo $epi_id; ?>"/>
                        <input type="submit" name="delete" value=" <?php echo getMsg('Delete'); ?> " class="submitbutton"/>
                        <br/><br/><?php echo getMsg('Warning: This action is irreversible!'); ?>
                    </form>
                </div>
            <?php } elseif ($_GET['action'] === 'publish') { ?>
                <div id="publish" class="context"><br/>
                    <form method="post" action="phps/modifyEpisode.php">
                        <input type="hidden" name="id" value="<?php echo $epi_id; ?>"/>
                        <?php
                        if ($episode['status'])
                            echo '<input type="submit" name="publish" value="', getMsg('Cancel publication'), '" class="submitbutton"/>';
                        else
                            echo '<input type="submit" name="publish" value="', getMsg('Publish'), '" class="submitbutton"/>', '<br /><br />',
                            '<input class="checkbox" type="checkbox" name="sendemail" value="233" checked/>', getMsg('Send email to subscribers');
                        ?>
                        <br/><br/><?php echo getMsg('Current episode will be available at index page after publication'); ?>
                    </form>
                </div>
            <?php } ?>
            <form id="action_hidden" hidden="" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="get">
                <input type="hidden" name="action" value="<?php echo $_GET['action']; ?>"/>
                <input type="hidden" name="id" value="<?php echo $epi_id; ?>"/>
            </form>
            <div id="progressDiv" class="progress" style="margin-bottom:10px;display:none;">
                <div id="progressbar" class="bar" style="width:0%;"></div>
                <div id="progresslabel" class="label">0%</div>
            </div>
        </div>
        <p style="font-size: 16px; padding: 5px;clear:both; background-color: #DDDDDD; color:#000000; text-align: center">
            © 2016-2017 教育资源 Educational Resources 周涵之 Hanzhi Zhou. All Rights Reserved.
        </p>
    </div>
    </body>
    <script type="text/javascript">
        getDisplay();
        var proD = document.getElementById('progressDiv');
        var proB = document.getElementById('progressbar');
        var proL = document.getElementById('progresslabel');
        function adjustDisplays() {
            var cnt = document.getElementById('content');
            cnt.style.height = (cnt.offsetHeight + 50) + 'px';
        }
        window.onload = adjustDisplays;
    </script>
    </html>
<?php $mysqli->close(); ?>