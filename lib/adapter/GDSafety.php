<?php
/**
 * Windwork
 * 
 * 一个开源的PHP轻量级高效Web开发框架
 * 
 * @copyright   Copyright (c) 2008-2016 Windwork Team. (http://www.windwork.org)
 * @license     http://opensource.org/licenses/MIT	MIT License
 */
namespace wf\captcha\adapter;

/**
 * 验证码(GD库实现)
 * 
 * 加入混淆字符串，从中选择一个颜色的验证码。
 * 
 * useage:
 * $capt = \wf\captcha\CaptchaFactory::create();
 * $capt->render();
 * 
 * @package     wf.captcha.adapter
 * @author      erzh <cmpan@qq.com>
 * @link        http://www.windwork.org/manual/wf.captcha.html
 * @since       0.1.0
 */
class GDSafety implements \wf\captcha\ICaptcha {	
	private $cfg = [
		'expire'    => 3000,   // 验证码过期时间（s）
		'gradient'  => 20,     // 文字倾斜度范围
		'length'    => 4,      // 验证码位数
		'bgColor'   => [243, 251, 254],
		'lang'      =>  [
			'tip' => '色是验证码',
			'color' => [
				'red'    => '红',
				'green'  => '绿',
				'blue'   => '蓝',
				'purple' => '紫',
				'black'  => '黑',
			]
		],
	];

	/**
	 * 
	 * {@inheritDoc}
	 * @see \wf\captcha\ICaptcha::setCfg()
	 */
	public function setCfg(array $cfg) {
		$this->cfg = array_merge($this->cfg, $cfg);
		return $this;
	}
	
	/**
	 * 验证码中使用的字符，0O、1I、2Z容易混淆，建议不用
	 *
	 * @var string
	 */
	private $codeSet = '3456789ABCDEFHKLMNPQRSTUVWXY';

	/**
	 * 
	 * {@inheritDoc}
	 * @see \wf\captcha\ICaptcha::render()
	 */
	public function render($id = 'sec') {		
		$width = 200; // 图片宽(px)
		$height = 80; // 图片高(px)
		$fontSize = 15;
		
		// 建立图片
		$image = imagecreate(200, 80); 
		
		// 设置背颜色  
		imagecolorallocate($image, $this->cfg['bgColor'][0], $this->cfg['bgColor'][1], $this->cfg['bgColor'][2]); 
		
		// 验证码字体随机颜色
		$colorList = array(
			array('label' => $this->cfg['lang']['color']['red'],    'color' => array(0xff, 0x00, 0x00)),
			array('label' => $this->cfg['lang']['color']['green'],  'color' => array(0x00, 0xff, 0x66)),
			array('label' => $this->cfg['lang']['color']['blue'],   'color' => array(0x00, 0x66, 0xff)),
			array('label' => $this->cfg['lang']['color']['purple'], 'color' => array(0x99, 0x00, 0xff)),
			array('label' => $this->cfg['lang']['color']['black'],  'color' => array(0x00, 0x00, 0x00)),
		);
		shuffle($colorList);

		// 验证码颜色
		$fontColorRand = array_pop($colorList);
		$fontColor = $fontColorRand['color'];
		$realFontColor = imagecolorallocate($image, $fontColor[0], $fontColor[1], $fontColor[2]);

		// 绘制一个验证码字符
		$labelColor = imagecolorallocate($image, 0x99, 0x99, 0x99);
		imagettftext($image, 20, 0, 40, 28, $labelColor, dirname(__DIR__) . '/assets/label.otf', "{$fontColorRand['label']}");
		imagettftext($image, 14, 0, 70, 26, $labelColor, dirname(__DIR__) . '/assets/label.otf', $this->cfg['lang']['tip']);
		
		// 干扰验证码颜色
		$mix1 = array_pop($colorList);
		$mix1Color = $mix1['color'];
		$mixFontColor1 = imagecolorallocate($image, $mix1Color[0], $mix1Color[1], $mix1Color[2]);
		
		$mix2 = array_pop($colorList);
		$mix2Color = $mix2['color'];
		$mixFontColor2 = imagecolorallocate($image, $mix2Color[0], $mix2Color[1], $mix2Color[2]);
		
		// 验证码字体
		$ttf = dirname(__DIR__) . '/assets/code.ttf';
		
		// 验证码位置
		$posPossibleArr = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11);
		shuffle($posPossibleArr);
		$posArr = array_slice($posPossibleArr, 0, $this->cfg['length']);
				
		$codeNX = - mt_rand($fontSize*0.1, $fontSize*0.3); // 验证码第N个字符的左边距
		$mixCount = 0;
		$codeStr = '';
		
		for ($i = 0; $i < 12; $i++) {
			$codeNX += mt_rand($fontSize*0.95, $fontSize*1.1); // 字符横向位置
			$codeNY = mt_rand(36 + $fontSize*1.25, 36 + $fontSize*1.36); // 字符纵向位置
			$gradient = mt_rand(-$this->cfg['gradient'], $this->cfg['gradient']);	// 字符倾斜度
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
		\wf\captcha\Code::save($codeStr, time() + $this->cfg['expire'], $id);

		header('Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', false);		
		header('Pragma: no-cache');
		header("content-type: image/png");
	
		// 输出验证码图像
		imagepng($image); 
		imagedestroy($image);
	}
}



