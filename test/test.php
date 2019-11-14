<?php
require_once '../lib/CaptchaInterface.php';
require_once '../lib/Captcha.php';

$capt = new \wf\Captcha\Captcha([
    'useBgImg'    => false,  // 是否使用背景图片
    'useNoise'    => false,  // 是否添加干扰字符
    'curve'       => 1,      // 画混淆曲线数量
    'distort'     => 0,      // 扭曲级别（0-9），0为不扭曲，建议为验证码字体大小/8
    'length'      => 4,      // 验证码位数
    'fontSize'    => 36,     // 验证码字体大小(px)
]);

// set to session
session_start();
$_SESSION['phrase'] = $capt->getPhrase();

$capt->output(90);

//echo $capt->get();
