<?php
declare(strict_types=1);
session_start();
require 'class.ValidateCode.php';
$_vc = new ValidateCode();
$_vc->doimg();
$_SESSION['vcode'] = $_vc->getCode();