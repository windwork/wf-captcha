<?php
/**
 * Windwork
 * 
 * 一个用于快速开发高并发Web应用的轻量级PHP框架
 * 
 * @copyright Copyright (c) 2008-2017 Windwork Team. (http://www.windwork.org)
 * @license   http://opensource.org/licenses/MIT
 */
namespace wf\captcha;

/**
 * 验证码接口
 * 
 * useage:
 * $capt = wfCaptcha(); // 
 * $capt->render();
 * 
 *  验证码对比校验
 *  if (!\wf\captcha\Code::check(@$_POST['secode'])) {
 *      print 'error secode';
 *  }
 *  
 * @package     wf.captcha
 * @author      cm <cmpan@qq.com>
 * @link        http://docs.windwork.org/manual/wf.captcha.html
 * @since       0.1.0
 */
interface CaptchaInterface 
{
    /**
     * 生成验证码，输出为图片格式，并保存验证码字符到session
     *  
     * @param string $id = 'sec' 验证码类别，如登录）login；注册）regster
     */
    public function render($id = 'sec');
}

