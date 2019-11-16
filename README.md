验证码组件
=============
安全可靠的验证码，拥有很强的反机器识别能力，同时又不失人眼阅读体验。

## require
 - php 5.5+
 - GD2

## 安装
该组件已包含在Windwork框架中，如果你已安装Windwork框架则可以直接使用。

- 安装方式一：通过composer安装（推荐）  
```
composer require windwork/captcha
```

## 使用示例

```
<?php

use wf\captcha\Captcha;

require_once 'vendor/autoload.php';

$capt = new Captcha();
$capt->setLevel(Captcha::LEVEL_NORMAL);
$capt->create();

// set to session
session_start();
$_SESSION['phrase'] = $capt->getPhrase();

$capt->output(90);

//echo $capt->get();


```

