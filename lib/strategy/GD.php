<?php
/**
 * Windwork
 * 
 * 一个开源的PHP轻量级高效Web开发框架
 * 
 * @copyright Copyright (c) 2008-2017 Windwork Team. (http://www.windwork.org)
 * @license   http://opensource.org/licenses/MIT
 */
namespace wf\captcha\strategy;

/**
 * 验证码(GD库实现)
 * 
 * 安全的验证码要：验证码文字旋转，使用不同字体，可加干扰码、可加干扰线、可使用中文、可使用背景图片
 * 可配置的属性都是一些简单直观的变量，我就不用弄一堆的setter/getter了
 * 
 * useage:
 * $capt = \wf\Factory::captcha();
 * $capt->setCfg($cfg);
 * $capt->entry();
 * 
 *  验证码对比校验
 *  if (!\wf\captcha\Code::check(@$_POST['secode'], $id)) {
 *      print 'error secode';
 *  }
 * 
 * @package     wf.captcha.strategy
 * @author      cmm <cmm@windwork.org>
 * @link        http://docs.windwork.org/manual/wf.captcha.html
 * @since       1.0.0
 */
class GD implements \wf\captcha\ICaptcha 
{
    private $cfg = [
        'bg'        => [255, 255, 255], // 验证码背景颜色
        'expire'    => 3000,   // 验证码过期时间（s）
        'useBgImg'  => false,  // 是否使用背景图片 
        'useCurve'  => false,  // 是否画混淆曲线
        'useNoise'  => true,   // 是否添加杂点    
        'gradient'  => 22,     // 文字倾斜度范围
        'fontSize'  => 16,     // 验证码字体大小(px)
        'height'    => 0,      // 验证码图片高，0为根据fontSize自动计算
        'width'     => 0,      // 验证码图片宽，0为根据fontSize自动计算
        'length'    => 4,      // 验证码位数
        'ttfs'      => ['1.ttf'],  // 验证码使用字体列表
        'bgs'       => ['1.jpg', '2.jpg', '3.jpg', '4.jpg', '5.jpg', '6.jpg', '7.jpg', '8.jpg'], // 验证码使用背景图片列表
    ];
    
    public function __construct(array $cfg = []) 
    {
        $this->cfg = array_replace_recursive($this->cfg, $cfg);
    }
    
    /**
     * 验证码中使用的字符，01IO容易混淆，建议不用
     *
     * @var string
     */
    private $codeSet = '3456789AbcDEFHKLMNPQRSTUVWXY';
    private $image   = null;     // 验证码图片实例
    private $color   = null;     // 验证码字体颜色

    /**
     * 生成验证码
     * {@inheritDoc}
     * @see \wf\captcha\ICaptcha::render()
     */
    public function render($id = 'sec') 
    {        
        // 图片宽(px)
        $this->cfg['width'] || $this->cfg['width'] = $this->cfg['length'] * $this->cfg['fontSize'] * 1.35; 
        
        // 图片高(px)
        $this->cfg['height'] || $this->cfg['height'] = $this->cfg['fontSize'] * 2;
        
        // 建立一幅 $this->cfg['width'] x $this->cfg['height'] 的图像
        $this->image = imagecreate($this->cfg['width'], $this->cfg['height']); 
        
        // 设置背景      
        imagecolorallocate($this->image, $this->cfg['bg'][0], $this->cfg['bg'][1], $this->cfg['bg'][2]); 
        
        // 验证码字体随机颜色
        $this->color = imagecolorallocate($this->image, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));
        
        // 验证码使用随机字体
        $ttf = dirname(dirname(__DIR__)) . '/assets/ttfs/' . $this->cfg['ttfs'][array_rand($this->cfg['ttfs'])];    
        
        // 绘制干扰信息
        if($this->cfg['useBgImg']) {
            $this->background(); // 添加背景图片
        } elseif ($this->cfg['useNoise']) {
            $this->writeNoise(); // 绘杂点
        }
        $this->cfg['useCurve'] && $this->writeCurve(); // 绘干扰线
        
        // 绘验证码
        $code = []; // 验证码
        $codeNX = - mt_rand($this->cfg['fontSize']*0.3, $this->cfg['fontSize']*0.6); // 验证码第N个字符的左边距        
        for ($i = 0; $i<$this->cfg['length']; $i++) {
            $code[$i] = $this->codeSet[mt_rand(0, strlen($this->codeSet)-1)];
            $codeNX += mt_rand($this->cfg['fontSize']*0.95, $this->cfg['fontSize']*1.1);
            $gradient = mt_rand(-$this->cfg['gradient'], $this->cfg['gradient']);    
            
            // 写一个验证码字符
            imagettftext($this->image, $this->cfg['fontSize'], $gradient, $codeNX, mt_rand($this->cfg['fontSize']*1.25, $this->cfg['fontSize']*1.36), $this->color/*imagecolorallocate($this->image, mt_rand(1,130), mt_rand(1,130), mt_rand(1,130))*/, $ttf, $code[$i]);
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
     * 画一条由两条连在一起构成的随机正弦函数曲线作干扰线(你可以改成更帅的曲线函数) 
     *      
     *      高中的数学公式咋都忘了涅，写出来
     *        正弦型函数解析式：y=Asin(ωx+φ)+b
     *      各常数值对函数图像的影响：
     *        A：决定峰值（即纵向拉伸压缩的倍数）
     *        b：表示波形在Y轴的位置关系或纵向移动距离（上加下减）
     *        φ：决定波形与X轴位置关系或横向移动距离（左加右减）
     *        ω：决定周期（最小正周期T=2π/∣ω∣）
     *
     */
    protected function writeCurve() 
    {        
        $px = $py = 0;
        
        // 曲线前部分
        $A = mt_rand(1, $this->cfg['height']/2);                  // 振幅
        $b = mt_rand(-$this->cfg['height']/4, $this->cfg['height']/4);   // Y轴方向偏移量
        $f = mt_rand(-$this->cfg['height']/4, $this->cfg['height']/4);   // X轴方向偏移量
        $T = mt_rand($this->cfg['height'], $this->cfg['width']*2);      // 周期
        $w = (2* M_PI)/$T;
                        
        $px1 = 0;  // 曲线横坐标起始位置
        $px2 = mt_rand($this->cfg['width']/2, $this->cfg['width'] * 0.8);  // 曲线横坐标结束位置

        for ($px = $px1; $px <= $px2; $px ++) {
            if ($w == 0) {
                break;
            }
            
            $py = $A * sin($w*$px + $f)+ $b + $this->cfg['height']/2;  // y = Asin(ωx+φ) + b
            $i = (int) ($this->cfg['fontSize']/6);
            while ($i > 0) {    
                imagesetpixel($this->image, $px, $py + $i, $this->color);  // 这里(while)循环画像素点比imagettftext和imagestring用字体大小一次画出（不用这while循环）性能要好很多                    
                $i--;
            }
        }
        
        // 曲线后部分
        $A = mt_rand(1, $this->cfg['height']/2);                  // 振幅        
        $f = mt_rand(-$this->cfg['height']/4, $this->cfg['height']/4);   // X轴方向偏移量
        $T = mt_rand($this->cfg['height'], $this->cfg['width']*2);      // 周期
        $w = (2* M_PI)/$T;        
        $b = $py - $A * sin($w*$px + $f) - $this->cfg['height']/2;
        $px1 = $px2;
        $px2 = $this->cfg['width'];

        for ($px = $px1; $px <= $px2; $px ++) {
            if ($w == 0) {
                break;
            }
            
            $py = $A * sin($w*$px + $f)+ $b + $this->cfg['height']/2;  // y = Asin(ωx+φ) + b
            $i = (int) ($this->cfg['fontSize']/6);
            while ($i > 0) {            
                imagesetpixel($this->image, $px, $py + $i, $this->color);
                $i--;
            }
        }
    }
    
    /**
     * 画杂点
     * 往图片上写不同颜色的字母或数字
     */
    protected function writeNoise() 
    {
        for($i = 0; $i < $this->cfg['fontSize']/4; $i++){
            //杂点颜色
            $noiseColor = imagecolorallocate($this->image, mt_rand(150, 255), mt_rand(150, 255), mt_rand(150, 255));
            
            for ($j = 0; $j < 4; $j++) {
                // 绘杂点
                imagestring($this->image, 5, mt_rand(-10, $this->cfg['width']), mt_rand(-10, $this->cfg['height']), $this->codeSet[mt_rand(0, 25)], $noiseColor);
            }            
        }
    }
    
    /**
     * 绘制背景图片
     */
    private function background() 
    {
        // 随机选择背景图片
        $bgImgPath = dirname(dirname(__DIR__)) . '/assets/bgs/' . $this->cfg['bgs'][array_rand($this->cfg['bgs'])];

        list($width, $height) = @getimagesize($bgImgPath);
        
        // Resample
        $bgImage = @imagecreatefromjpeg($bgImgPath);
        @imagecopyresampled($this->image, $bgImage, 0, 0, 0, 0, $this->cfg['width'], $this->cfg['height'], $width, $height);
        @imagedestroy($bgImage);
    }
}



