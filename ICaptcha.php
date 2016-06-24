<?php
/**
 * Windwork
 * 
 * 一个开源的PHP轻量级高效Web开发框架
 * 
 * @copyright   Copyright (c) 2008-2015 Windwork Team. (http://www.windwork.org)
 * @license     http://opensource.org/licenses/MIT	MIT License
 */
namespace wf\captcha;

/**
 * 验证码接口
 * 
 * useage:
 * $capt = \wf\captcha\CaptchaFactory::create();
 * $capt->render();
 * 
 *  验证码对比校验
 *  if (!\wf\captcha\CaptchaFactory::create()->check(@$_POST['secode'])) {
 *  	print 'error secode';
 *  }
 *  
 * @package     wf.captcha
 * @author      erzh <cmpan@qq.com>
 * @link        http://www.windwork.org/manual/wf.captcha.html
 * @since       0.1.0
 */
interface ICaptcha {

	/**
	 * 验证验证码是否正确
	 *
	 * @param string $code 用户验证码
	 * @param string $id = 'sec' 下标
	 * @return bool 用户验证码是否正确
	 */
	public function check($code, $id = 'sec');

	/**
	 * 输出验证码并把验证码的值保存的session中
	 * 验证码保存到session的格式为：
	 *  $_SESSION[self::$seKey] = array('code' => '验证码值', 'time' => '验证码创建时间');
	 *  
	 * @param string $id = 'sec'
	 */
	public function render($id = 'sec');
}

