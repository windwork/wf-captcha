<?php
$dir = dirname(__DIR__);
require_once $dir . 'ICaptcha.php';
require_once $dir . 'adapter/GD.php';
require_once $dir . 'CaptchaFactory.php';

$capt = \wf\captcha\CaptchaFactory::create();
$capt->render('xx');
