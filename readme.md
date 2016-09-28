验证码组件
=============
安全可靠的验证码，拥有强大的放机器破解能力，同时又不失人眼阅读体验。

```
//useage:
// 生成验证码
$capt = \wf\captcha\CaptchaFactory::create();
$secId = 'login';
$capt->render($secId);

// 验证码对比校验
if (!\wf\captcha\Code::check(@$_POST['secode']), 'login') {
     print 'error secode';
}
```

## 效果预览

- 普通效果
![效果图](res/example-1.png)

- 高级效果
![效果图](res/example-2.jpg)
