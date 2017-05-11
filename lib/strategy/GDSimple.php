<?php

namespace wf\captcha\strategy;

/**
 * 验证码(GD库实现)
 * 
 * 安全的验证码要：验证码文字旋转，使用不同字体，可加干扰码、可加干扰线、可使用中文、可使用背景图片
 * 可配置的属性都是一些简单直观的变量，我就不用弄一堆的setter/getter了
 * 
 * useage:
 * $capt = new \component\captcha\Captcha($cfg);
 * $capt->entry();
 * 
 *  验证码对比校验
 *  if (!\component\captcha\Captcha::check(@$_POST['secode'], $id)) {
 *      print 'error secode';
 *  }
 * 
 * @package     component.captcha
 * @author      cm <cmpan@qq.com>
 */
class GDSimple implements \wf\captcha\ICaptcha 
{    
    private $cfg = [
        'useBgImg'  => 0,   // 是否使用背景图片
        'bgColor'   => [255, 255, 255], // 验证码背景颜色，不使用背景图片时有效
        'color'     => [0x45, 0x45, 0x45], // 验证码背景颜色
        'gradient'  => 32,  // 文字倾斜度范围
        'fontSize'  => 20,  // 验证码字体大小(px)
        'length'    => 4,   // 验证码位数
        'height'    => 0,   // 验证码图片高，0为根据fontSize自动计算
        'width'     => 0,   // 验证码图片宽，0为根据fontSize自动计算
        // 验证码中使用的字符，01IO容易混淆，建议不用
        'codeSet'   => '356789ABCDEFGHKLMNPQRSTUVWXY',
        'expire'    => 3000,
    ];

    public function __construct(array $cfg = []) 
    {
        $this->cfg = array_replace_recursive($this->cfg, $cfg);
    }
    
    /**
     * 验证码图片实例
     * @var \component\captcha\Captcha
     */
    private $image   = null;
    
    /**
     * 验证码字体颜色
     * @var array
     */
    private $color   = null;

    /**
     * 生成验证码
     */
    public function render($id = 'sec') 
    {        
        // 图片宽(px)
        $this->cfg['width'] || $this->cfg['width'] = $this->cfg['length'] * $this->cfg['fontSize']*0.9; 
        
        // 图片高(px)
        $this->cfg['height'] || $this->cfg['height'] = $this->cfg['fontSize'] * 1.36;
        
        if ($this->cfg['useBgImg']) {
            // 使用图片背景
            
            // 建立一幅 $this->cfg['width'] x $this->cfg['height'] 的图像
            $this->image = imagecreatetruecolor($this->cfg['width'], $this->cfg['height']); 
            
            $this->setBgImg();
        } else {
            // 使用纯色背景            
            // 建立一幅 $this->cfg['width'] x $this->cfg['height'] 的图像
            $this->image = imagecreate($this->cfg['width'], $this->cfg['height']);
            
            imagecolorallocate($this->image, $this->cfg['bgColor'][0], $this->cfg['bgColor'][1], $this->cfg['bgColor'][2]); 
        }
                
        // 验证码文字颜色
        $this->color = imagecolorallocate($this->image, $this->cfg['color'][0], $this->cfg['color'][1], $this->cfg['color'][2]);
        
        // 验证码使用随机字体
        $ttf = dirname(dirname(__DIR__)) . '/assets/gd_simple/code.ttf';    
        
        // 绘验证码
        $code = []; // 验证码
        $codeNX = - $this->cfg['fontSize']*0.3; // 验证码第N个字符的左边距    
        $this->cfg['gradient'] < 5 && $this->cfg['gradient'] = 5;
        for ($i = 0; $i<$this->cfg['length']; $i++) {
            $code[$i] = $this->cfg['codeSet'][mt_rand(0, strlen($this->cfg['codeSet'])-1)];
            if ($i > 0 && in_array($code[$i-1], ['M', 'W'])) {
                // 最宽的 MW
                $codeNX += $this->cfg['fontSize']*0.8;
            } elseif ($i > 0 && in_array($code[$i-1], ['B', 'G', 'H', 'N'])) {
                // 较宽易混淆的字符
                $codeNX += $this->cfg['fontSize']*0.7;
            } elseif ($i > 0 && (is_numeric($code[$i-1]) || in_array($code[$i-1], [6, 7, 9, 'A', 'C', 'F', 'L', 'P', 'T', 'V']))) {
                // 不易混淆的字符 679ACFLPTV
                $codeNX += $this->cfg['fontSize']*0.5;
            } else {
                $codeNX += $this->cfg['fontSize']*0.6;
            }
            
            // 倾斜度
            $gradient = mt_rand(-$this->cfg['gradient'], $this->cfg['gradient']);
            
            // 写一个验证码字符
            imagettftext($this->image, $this->cfg['fontSize'], $gradient, $codeNX, $this->cfg['fontSize']*1.1, $this->color/*imagecolorallocate($this->image, mt_rand(1,130), mt_rand(1,130), mt_rand(1,130))*/, $ttf, $code[$i]);
        }
        
        // 保存验证码
        \wf\captcha\Code::save(join('', $code), time() + $this->cfg['expire'], $id);

        header('Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);        
        header('Pragma: no-cache');
        header("content-type: image/png");
    
        // 输出验证码图像
        imagepng($this->image); 
        imagedestroy($this->image);
    }
    
    /**
     * 设置验证码背景图片
     */
    private function setBgImg() 
    {
        // 随机选择背景图片
        $imgId = mt_rand(1, 8);
        $bgImgPath = dirname(dirname(__DIR__)) . "/assets/gd_simple/bgs/{$imgId}.jpg";        
        list($width, $height) = @getimagesize($bgImgPath);
        
        // 复制背景图片设置
        $bgImage = @imagecreatefromjpeg($bgImgPath);
        $srcX = mt_rand(0, floor($width - $this->cfg['width']));
        $srcY = mt_rand(0, floor($height - $this->cfg['height']));
        
        @imagecopy($this->image, $bgImage, 0, 0, $srcX, $srcY, $this->cfg['width'], $this->cfg['height']);
        @imagedestroy($bgImage);
    }
}



