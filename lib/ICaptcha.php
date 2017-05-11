<?php
/**
 * Windwork
 * 
 * 一个开源的PHP轻量级高效Web开发框架
 * 
 * @copyright Copyright (c) 2008-2017 Windwork Team. (http://www.windwork.org)
 * @license   http://opensource.org/licenses/MIT
 */
namespace wf\captcha;

/**
 * 验证码接口
 * 
 * useage:
 * $capt = app()->getDi()->captcha();
 * $capt->render();
 * 
 *  验证码对比校验
 *  if (!app()->getDi()->captcha()->check(@$_POST['secode'])) {
 *      print 'error secode';
 *  }
 *  
 * @package     wf.captcha
 * @author      cm <cmpan@qq.com>
 * @link        http://docs.windwork.org/manual/wf.captcha.html
 * @since       0.1.0
 */
interface ICaptcha 
{
    /**
     * 输出验证码并把在服务器端保存验证码
     *  
     * @param string $id = 'sec' 验证码类别，如登录）login；注册）regster
     */
    public function render($id = 'sec');
}

