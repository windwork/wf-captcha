验证码组件
=============
安全可靠的验证码，拥有强大的放机器破解能力，同时又不失人眼阅读体验。

```
//useage:
// 生成验证码
$capt = \wf\captcha\CaptchaFactory::create();
$capt->render();

// 验证码对比校验
if (!\wf\captcha\CaptchaFactory::create()->check(@$_POST['secode'])) {
     print 'error secode';
}
```

## 效果预览
![效果图](example.jpg)
