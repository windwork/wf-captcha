<?php
/**
 * Windwork
 * 
 * 一个开源的PHP轻量级高效Web开发框架
 * 
 * @copyright   Copyright (c) 2008-2015 Windwork Team. (http://www.windwork.org)
 * @license     http://opensource.org/licenses/MIT	MIT License
 */
namespace wf\captcha\adapter;

/**
 * 验证码(GD库实现)
 * 
 * 安全的验证码要：验证码文字旋转，使用不同字体，可加干扰码、可加干扰线、可使用中文、可使用背景图片
 * 可配置的属性都是一些简单直观的变量，我就不用弄一堆的setter/getter了
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
 * @package     wf.captcha.adapter
 * @author      erzh <cmpan@qq.com>
 * @link        http://www.windwork.org/manual/wf.captcha.html
 * @since       0.1.0
 */
class GD implements \wf\captcha\ICaptcha {	
		
	/**
	 * 文字倾斜度范围
	 * 
	 * @var bool
	 */
	public $gradient  = 18;
	
	/**
	 * 验证码过期时间（s）
	 * @var int
	 */
	public static $expire    = 3000;
	
	/**
	 * 验证码位数
	 * @var int
	 */
	public $length = 4;        // 验证码位数
	
	/**
	 * 验证码的session的下标
	 * 
	 * @var string
	 */
	const SESS_KEY = 'wf.captcha.code';
	
	/**
	 * 背景颜色
	 * @var array
	 */
	public $bgColor = array(243, 251, 254);
	
	/**
	 * 
	 * @var array
	 */
	public static $lang = array(
		'tip' => '色验证码',
		'color' => array(
			'red'    => '红',
			'green'  => '绿',
			'blue'   => '蓝',
			'purple' => '紫',
			'black'  => '黑',
		)
	);
	
	/**
	 * 验证码中使用的字符，0O、1I、2Z容易混淆，建议不用
	 *
	 * @var string
	 */
	private $codeSet = '3456789ABCDEFHKLMNPQRSTUVWXY';
	
	/**
	 * 验证验证码是否正确
	 *
	 * @param string $code 用户验证码
	 * @param string $id 下标
	 * @return bool 用户验证码是否正确
	 */
	public function check($code, $id = 'sec') {		
		isset($_SESSION) || session_start();
		
		// 验证码不能为空
		if(empty($code) || empty($_SESSION[self::SESS_KEY])) {
			return false;
		}
		
		$secode =  @$_SESSION[self::SESS_KEY][$id];
		
		// session 过期检查
		if(time() - $secode['time'] > self::$expire) {
			return false;
		}

		if(strtoupper($code) == strtoupper($secode['code'])) {
			return true;
		}

		return false;
	}

	/**
	 * 
	 * {@inheritDoc}
	 * @see \wf\captcha\ICaptcha::render()
	 */
	public function render($id = 'sec') {
		$id || $id = 'sec';
		
		$width = 200; // 图片宽(px)
		$height = 80; // 图片高(px)
		$fontSize = 15;
		
		// 建立图片
		$image = imagecreate(200, 80); 
		
		// 设置背颜色  
		imagecolorallocate($image, $this->bgColor[0], $this->bgColor[1], $this->bgColor[2]); 
		
		// 验证码字体随机颜色
		$colorList = array(
			array('label' => static::$lang['color']['red'],    'color' => array(0xff, 0x00, 0x00)),
			array('label' => static::$lang['color']['green'],  'color' => array(0x00, 0xff, 0x66)),
			array('label' => static::$lang['color']['blue'],   'color' => array(0x00, 0x66, 0xff)),
			array('label' => static::$lang['color']['purple'], 'color' => array(0x99, 0x00, 0xff)),
			array('label' => static::$lang['color']['black'],  'color' => array(0x00, 0x00, 0x00)),
		);
		shuffle($colorList);

		// 验证码颜色
		$fontColorRand = array_pop($colorList);
		$fontColor = $fontColorRand['color'];
		$realFontColor = imagecolorallocate($image, $fontColor[0], $fontColor[1], $fontColor[2]);

		// 绘制一个验证码字符
		$labelColor = imagecolorallocate($image, 0x99, 0x99, 0x99);
		imagettftext($image, 20, 0, 40, 28, $labelColor, __DIR__ . '/res/label.otf', "{$fontColorRand['label']}");
		imagettftext($image, 14, 0, 70, 26, $labelColor, __DIR__ . '/res/label.otf', static::$lang['tip']);
		
		// 干扰验证码颜色
		$mix1 = array_pop($colorList);
		$mix1Color = $mix1['color'];
		$mixFontColor1 = imagecolorallocate($image, $mix1Color[0], $mix1Color[1], $mix1Color[2]);
		
		$mix2 = array_pop($colorList);
		$mix2Color = $mix2['color'];
		$mixFontColor2 = imagecolorallocate($image, $mix2Color[0], $mix2Color[1], $mix2Color[2]);
		
		// 验证码字体
		$ttf = __DIR__ . '/res/code.ttf';
		
		// 验证码位置
		$posPossibleArr = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11);
		shuffle($posPossibleArr);
		$posArr = array_slice($posPossibleArr, 0, $this->length);
				
		$codeNX = - mt_rand($fontSize*0.1, $fontSize*0.3); // 验证码第N个字符的左边距
		$mixCount = 0;
		$codeStr = '';
		
		for ($i = 0; $i < 12; $i++) {
			$codeNX += mt_rand($fontSize*0.95, $fontSize*1.1); // 字符横向位置
			$codeNY = mt_rand(36 + $fontSize*1.25, 36 + $fontSize*1.36); // 字符纵向位置
			$gradient = mt_rand(-$this->gradient, $this->gradient);	// 字符倾斜度
			$char = $this->codeSet[mt_rand(0, strlen($this->codeSet) - 1)]; // 随机字符
			
			// 字符颜色
			if (in_array($i, $posArr)) {
				$color = $realFontColor;
				$codeStr .= $char;
			} else {
				$color = $mixCount < 4 ? $mixFontColor1 : $mixFontColor2;
				$mixCount ++;
			}
			
			// 绘制一个验证码字符
			imagettftext($image, $fontSize, $gradient, $codeNX, $codeNY, $color, $ttf, $char);
		}
		
		// 保存验证码
		isset($_SESSION) || session_start();
		$_SESSION[self::SESS_KEY][$id]['code'] = $codeStr; // 把校验码保存到session
		$_SESSION[self::SESS_KEY][$id]['time'] = time();  // 验证码创建时间

		header('Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', false);		
		header('Pragma: no-cache');
		header("content-type: image/png");
	
		// 输出图像
		imagepng($image); 
		imagedestroy($image);
	}
}



