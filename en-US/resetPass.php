<?php declare(strict_types=1);?>
<!doctype html>
<html>
<head>
    <link rel="shortcut icon" href="../Doc/head.ico" type="image/x-icon">
    <meta charset="utf-8">
    <?php
    require 'phps/msg.php';
    ?>
    <title><?php echo getMsg('Reset password'); ?></title>
    <link href="css/login.css" rel="stylesheet" type="text/css">
</head>

<body style="background-color:#FFFFE5; background-image:url(../Doc/form_background.jpg); background-size:100%;">
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
            session_start();
            /**
             * process user's reset password request
             *
             * */
            require 'phps/mysqli_conn.php';
            use voku\helper\AntiXSS;
            require 'phps/vendor/autoload.php';
            $antiXSS = new AntiXSS();

            /**
             * if the request is from forgotPass.html
             * then send the reset password link to user's email
             * @var string $email
             * @var string $vcode
             * the validation code, must be the same with that which is stored in session
             * */
            if (!empty($_POST['email'])) {
                if (empty($_POST['vcode']) || empty($_SESSION['vcode']))
                    exit(alertAndBack('Illegal Access!'));
                if ($_POST['vcode'] !== $_SESSION['vcode'])
                    exit(alertAndBack('Incorrect validation code!'));

                // check if the email is of correct format
                $email = trim($_POST['email']);
                unset($_SESSION['vcode']);
                if (!preg_match('/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/', $email))
                    exit(alertAndBack('Invalid email address!'));

                // check if the user with this email address exist, and whether his/her account is activated
                $user = $mysqli->query("SELECT id, password, regtime, status FROM userlist WHERE email='$email'");
                if ($user = $user->fetch_assoc()) {
                    if (intval($user['status']) === 0)
                        exit(alertAndBack('You cannot reset password for an deactivated account!'));
                    else {
                        // generate token, send link to user's email
                        $token = md5($user['id'] . $user['password'] . $user['regtime'] . time());
                        $token_exptime = time() + 43200;
                        if ($mysqli->query("UPDATE userlist SET token='{$token}', token_exptime='{$token_exptime}' WHERE email='{$email}'")) {
                            require 'phps/sendEmail.php';
                            sendResetEmail($email, $token);
                            exit(alertAndRedirect('An email for password reset is sent, please check you email for details.', 'index.php'));
                        } else
                            exit(alertAndBack('Unknown Error!'));
                    }
                } else
                    exit(alertAndBack('Incorrect email address!'));
            }
            /**
             * if user click the reset password link, which means the token (or code) is set
             * generate the form for resetting the password
             * @var string $code
             * */
            else {
                if (!empty($_GET['code'])) {

                    // check if the code is correct and not expired
                    $code = trim($_GET['code']);
                    if (!preg_match('/^[a-fA-F0-9]{32,32}+$/', $code))
                        exit(getMsg('Invalid reset link'));
                    $row = $mysqli->query("SELECT token_exptime FROM userlist WHERE token='{$code}'")->fetch_assoc();
                    if ($row) {
                        if (time() > $row['token_exptime'])
                            $msg = getMsg('Link expired!');
                        else

                            // the reset password form, which will be submitted to this php file
                            $msg =
            "<form action=\"" . $antiXSS->xss_clean($_SERVER['PHP_SELF']) . "\" method=\"post\" onSubmit=\"return isValid(this);\">
				<input type=\"password\" style=\"display:none;\" value=\"{$code}\" name=\"token\">
                <table id=\"login_tab\">
                    <tr>
                        <td>" . getMsg('New password') . ":&nbsp;</td>
                        <td><input type=\"password\" name=\"password\"></td>
                    </tr>
                    <tr>
                        <td>" . getMsg('Confirm password') . ":&nbsp;</td>
                        <td><input type=\"password\" name=\"conpass\"></td>
                    </tr>
                </table>
				<input type=\"submit\" name=\"submit\" value=\" " . getMsg('Reset') . " \" style=\"font-size:20px; padding:5px;\">
            </form>";
                    } else
                        $msg = getMsg('Invalid reset link');
                    echo $msg;
                } else {

                    /**
                     * check if all posted fields are correct
                     * */
                    if (!empty($_POST['token']) && !empty($_POST['password'])) {
                        $token = $_POST['token'];
                        if (!preg_match('/^[a-fA-F0-9]{32,32}+$/', $token))
                            exit(alertAndBack('Illegal Access!'));
                        $user = $mysqli->query("SELECT email, regtime, id FROM userlist WHERE token='{$token}'");
                        if ($user = $user->fetch_assoc()) {
                            $newpass = saltpass(md5($_POST['password']), $user['email'], $user['regtime']);
                            $mysqli->query("UPDATE userlist SET password='{$newpass}' WHERE id={$user['id']}");
                            exit(alertAndRedirect('Password updated!', 'index.php'));
                        } else
                            exit(alertAndBack('Illegal Access!'));
                    } else
                        exit(alertAndBack('Illegal Access!'));
                }
            }
            ?>
            <script src="locale/for_js.js"></script>
            <script src="js/check_password_consistency.min.js"></script>
            <script>
                function isValid(form) {
                    return checkPass(form);
                }
            </script>
        </div>
    </div>
    <p style="font-size: 16px; padding: 5px;clear:both; background-color: #DDDDDD; color:#000000; text-align: center">
        © 2016-2017 教育资源 Educational Resources 周涵之 Hanzhi Zhou. All Rights Reserved.
    </p>
</div>
</body>
</html>
