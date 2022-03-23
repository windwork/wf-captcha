验证码组件
=============
安全可靠的验证码，拥有很强的反机器识别能力，同时又不失人眼阅读体验。

吐槽一下，这个验证码我最新发布在 PHPChina 论坛，后来2009年9月我转发到了[javaeye](https://www.iteye.com/topic/469170)。现在有很多PHP图片验证码都是抄袭自这里，然后堂而皇之的改版权，比如 ThinkPHP 的 [think-captcha](https://github.com/top-think/think-captcha/blob/3.0/src/Captcha.php)。

当前大数据横行的年代，这种图片验证码已经过时，图片扭曲到很难看清，干扰信息加上一大堆，机器识别率都能到百分之八九十。
推荐使用短信、邮箱验证码 + 登录限流。
注册可以用交互式验证码 + 限流，如选中文（出两三个扭曲中文，让用户从扭曲旋转文字的九宫格中按顺序选出对应的字），滑动验证码、旋转验证码等。

## require
 - php 7.0+
 - GD2

## 安装
该组件已包含在Windwork框架中，如果你已安装Windwork框架则可以直接使用。

- 安装
```
composer require windwork/captcha
```

## 使用示例

```
<?php

use wf\captcha\Captcha;

require_once 'vendor/autoload.php';

$capt = new Captcha();
//$capt->setLevel(Captcha::LEVEL_HEIGHT);
$capt->create();

// set to session
//session_start();
//$_SESSION['phrase'] = $capt->getPhrase();

$capt->output(90);

//echo $capt->get();


```

