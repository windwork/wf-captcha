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
 * 验证码存储及检查
 */
class Code {
	const SESS_KEY = '@captcha_sk';
	
	/**
	 * 验证验证码是否正确
	 *
	 * @param string $code 用户验证码
	 * @param string $id 下标
	 * @return bool 用户验证码是否正确
	 */
	public static function check($code, $id = 'sec') {
		$id || $id = 'sec';		
		isset($_SESSION) || session_start();
	
		// 验证码不能为空
		if(empty($code) || empty($_SESSION[self::SESS_KEY][$id])) {
			return false;
		}
	
		$secode =  $_SESSION[self::SESS_KEY][$id];
		
		// 用过立即清掉，不允许重复使用，防暴力破解
		unset($_SESSION[self::SESS_KEY][$id]);
	
		// session 过期检查
		if(time() > $secode['expire']) {
			return false;
		}
	
		if(strtoupper($code) == strtoupper($secode['code'])) {
			return true;
		}
	
		return false;
	}
	
	/**
	 * 保存最新验证码信息
	 * @param string $code 验证码字符串
	 * @param int $expire  验证码过期时间戳
	 * @param string $id = ''
	 */
	public static function save($code, $expire, $id = 'sec') {
		$id || $id = 'sec';
		isset($_SESSION) || session_start();

		$_SESSION[self::SESS_KEY][$id]['code'] = $code; // 把校验码保存到session
		$_SESSION[self::SESS_KEY][$id]['expire'] = $expire;  // 验证码创建时间
	}
}