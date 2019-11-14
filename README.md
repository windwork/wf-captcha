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

require_once 'vendor/autoload.php';

$capt = new \wf\captcha\captcha([
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

```

