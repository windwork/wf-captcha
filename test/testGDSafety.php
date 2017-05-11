<?php
require_once '../lib/ICaptcha.php';
require_once '../lib/Exception.php';
require_once '../lib/Code.php';
require_once '../lib/strategy/GDSafety.php';

$capt = new \wf\captcha\strategy\GDSafety();
$capt->render('login');

// 验证码对比校验
//if (!\wf\captcha\Code::check(@$_POST['secode'], 'login')) {
//    print 'error secode';
//}

