<?php
require_once '../lib/CaptchaInterface.php';
require_once '../lib/Captcha.php';

use wf\captcha\Captcha;

$capt = new Captcha();
$capt->setLevel(Captcha::LEVEL_NORMAL);
$capt->create();

// set to session
session_start();
$_SESSION['phrase'] = $capt->getPhrase();

$capt->output(90);

//echo $capt->get();
