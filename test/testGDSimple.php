<?php
require_once '../lib/CaptchaInterface.php';
require_once '../lib/Exception.php';
require_once '../lib/Code.php';
require_once '../lib/adapter/GDSimple.php';

$capt = new \wf\captcha\adapter\GDSimple();
$capt->render('login');

// 验证码对比校验
//if (!\wf\captcha\Code::check(@$_POST['secode'], 'login')) {
//    print 'error secode';
//}

