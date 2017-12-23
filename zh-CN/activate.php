<?php declare(strict_types=1); ?>
<!doctype html>
<html>
<head>
    <link rel="shortcut icon" href="../Doc/head.ico" type="image/x-icon">
    <meta charset="utf-8">
    <?php
    require 'phps/msg.php';
    ?>
    <title><?php echo getMsg('Activation'); ?></title>
    <link href="css/login.css" rel="stylesheet" type="text/css">
</head>

<body style="background-color:#FFFFE5; background-image:url(../Doc/form_background.jpg); background-size:100%; background-repeat:no-repeat;">
<div id="all">
    <div id="content">
        <div id="top">
            <div id="top_pic"><br/>
                <br/>
                <span id="title"
                      onclick="window.location.replace('../index.php');">&nbsp;<?php echo getMsg('Educational Resources'); ?></span>
            </div>
        </div>
        <div style="padding:30px;">
            <?php

            /**
             * process user's activation request
             * */
            if (empty($_GET['code'])) {
                header('Location: index.php?action=home');
                exit();
            }
            // check if the code is of correct md5 format
            $code = trim($_GET['code']);
            if (!preg_match('/^[a-fA-F0-9]{32,32}+$/', $code))
                exit(getMsg('Invalid activation link'));

            //check if code is correct and is not expired
            $nowtime = time();
            require_once 'phps/mysqli_conn.php';
            $row = executeSQL('SELECT id, token_exptime FROM userlist WHERE status=0 AND `token`=?', array('s', $code));
            if (isset($row[0])) {
                $row = $row[0];
                if ($nowtime > $row['token_exptime'])
                    $msg = getMsg('Your activation code has expired');
                else {
                    // activate user's account
                    $mysqli->query("UPDATE userlist SET status=1 WHERE id={$row['id']}");
                    $msg = getMsg('Your account has been successfully activated!');
                }
            } else
                $msg = getMsg('Invalid activation link or your account is already activated!');
            echo $msg;
            $mysqli->close();
            ?>
        </div>
    </div>
    <p style="font-size: 16px; padding: 5px;clear:both; background-color: #DDDDDD; color:#000000; text-align: center">
        © 2016-2017 教育资源 Educational Resources 周涵之 Hanzhi Zhou. All Rights Reserved.
    </p>
</div>
</body>
</html>
