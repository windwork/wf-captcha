<?php
/**
 * Windwork
 * 
 * 一个开源的PHP轻量级高效Web开发框架
 * 
 * @copyright   Copyright (c) 2008-2016 Windwork Team. (http://www.windwork.org)
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
	 * 设置配置信息
	 * 
	 * @param array $cfg
	 * @return \wf\captcha\ICaptcha
	 */
	public function setCfg(array $cfg);
	
	/**
	 * 输出验证码并把在服务器端保存验证码
	 *  
	 * @param string $id = 'sec' 验证码类别，如登录）login；注册）regster
	 */
	public function render($id = 'sec');
}

