<?php
require_once '../lib/CaptchaInterface.php';
require_once '../lib/Exception.php';
require_once '../lib/Code.php';
require_once '../lib/adapter/GD.php';

$capt = new \wf\captcha\adapter\GD();
$capt->render('login');

// 验证码对比校验
//if (!\wf\captcha\Code::check(@$_POST['secode'], 'login')) {
//    print 'error secode';
//}

