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
 * 验证码(GD库实现)
 *
 * 安全的验证码要：验证码文字旋转，使用不同字体，可加干扰码、可加干扰线、可使用中文、可使用背景图片
 * 可配置的属性都是一些简单直观的变量，我就不用弄一堆的setter/getter了
 *
 * usage:
 * $capt = \wf\captcha\adapter\GD($cfg);
 * $capt->entry();
 *
 *  验证码对比校验
 *  if (!\wf\captcha\Code::check(@$_POST['secode'], $id)) {
 *      print 'error secode';
 *  }
 *
 * @package     wf.captcha.adapter
 * @author      cmm <cmm@windwork.org>
 * @link        http://docs.windwork.org/manual/wf.captcha.html
 * @since       1.0.0
 */
class Captcha implements CaptchaInterface
{
    private $cfg = [
        'useBgImg'    => false,  // 是否使用背景图片
        'useNoise'    => false,  // 是否添加干扰字符
        'curve'       => 1,      // 画混淆曲线数量
        'distort'     => 0,      // 扭曲级别（0-9），0为不扭曲，建议为验证码字体大小/6
        'length'      => 4,      // 验证码位数
        'fontSize'    => 36,     // 验证码字体大小(px)
    ];

    /**
     * 验证码图片宽度（px，根据验证码字数自动计算）
     * @var int
     */
    protected $width;


    /**
     * 验证码图片高度（px，根据验证码字数自动计算）
     * @var int
     */
    protected $height;

    /**
     * 验证码中使用的字符，01IO容易混淆，建议不用
     *
     * @var string
     */
    private $phraseSet = '356789ABCDEFGHKLMNPQRSTUVWXY';

    /**
     * 验证码图片实例
     * @var function imagecreate
     */
    private $image = null;

    /**
     * 验证码字体颜色
     * @var function imagecolorallocate
     */
    private $color = null;

    private $phrase = '';

    /**
     * Captcha constructor.
     * @param array $cfg = <pre> [
     *   'useBgImg'    => false,  // 是否使用背景图片
     *   'useNoise'    => false,  // 是否添加干扰字符
     *   'curve'       => 1,      // 画混淆曲线数量
     *   'distort'     => 0,      // 扭曲级别（0-9），0为不扭曲，建议为验证码字体大小/6
     *   'length'      => 4,      // 验证码位数
     *   'fontSize'    => 36,     // 验证码字体大小(px)
     * ]; </pre>
     */
    public function __construct(array $cfg = [])
    {
        $this->cfg = array_replace_recursive($this->cfg, $cfg);

        // 图片宽(px)
        $this->width = $this->cfg['length'] * $this->cfg['fontSize']*0.9;

        // 图片高(px)
        $this->height = $this->cfg['fontSize'] * 1.36;

        $this->build();
    }

    public function getPhrase()
    {
        return $this->phrase;
    }

    public function output($quality = 90)
    {
        header('Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header("content-type: image/jpeg");

        // 输出验证码图像
        imagejpeg($this->image, null, $quality);
        imagedestroy($this->image);
    }

    public function get($quality = 90)
    {
        ob_start();

        imagejpeg($this->image, null, $quality);
        imagedestroy($this->image);

        return ob_get_clean();
    }

    protected function build($id = 'sec')
    {

        if ($this->cfg['useBgImg']) {
            // 使用图片背景
            // 建立一幅 $this->width x $this->height 的图像
            $this->image = imagecreatetruecolor($this->width, $this->height);

            $this->bgImg();
        } else {
            // 使用纯色背景
            // 建立一幅 $this->width x $this->height 的图像
            $this->image = imagecreate($this->width, $this->height);

            imagecolorallocate($this->image, mt_rand(120, 255), mt_rand(120, 255), mt_rand(120, 255));

            if ($this->cfg['useNoise']) {
                $this->noise(); // 绘杂点
            }
        }

        // 验证码字体随机颜色
        $this->color = imagecolorallocate($this->image, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));


        for ($i = 0; $i < $this->cfg['curve']; $i++) {
            $this->curve(); // 绘干扰线
        }

        $this->phrase();

        if ($this->cfg['distort']) {
            $this->distort(); // 扭曲验证码
        }
    }

    /**
     * 绘制验证码字符
     */
    protected function phrase()
    {
        // 验证码使用随机字体
        $ttf = dirname(__DIR__) . '/assets/font.ttf';

        // 绘验证码
        $phrase = [];

        $phraseNX = - $this->cfg['fontSize'] * 0.2; // 验证码第N个字符的左边距

        for ($i = 0; $i<$this->cfg['length']; $i++) {
            $phrase[$i] = $this->phraseSet[mt_rand(0, strlen($this->phraseSet)-1)];
            if ($i > 0 && in_array($phrase[$i-1], ['M', 'W'])) {
                // 最宽的 MW
                $phraseNX += $this->cfg['fontSize']*0.9;
            } elseif ($i > 0 && in_array($phrase[$i-1], ['B', 'G', 'H', 'N'])) {
                // 较宽易混淆的字符
                $phraseNX += $this->cfg['fontSize']*0.8;
            } elseif ($i > 0 && (is_numeric($phrase[$i-1])
                    || in_array($phrase[$i-1], [6, 7, 9, 'A', 'C', 'F', 'L', 'P', 'T', 'V']))) {
                // 不易混淆的字符 679ACFLPTV
                $phraseNX += $this->cfg['fontSize']*0.7;
            } else {
                $phraseNX += $this->cfg['fontSize']*0.6;
            }

            // 倾斜度
            $gradient = mt_rand(-20, 20);

            // 绘制一个验证码字符
            imagettftext($this->image, $this->cfg['fontSize'], $gradient, $phraseNX, $this->cfg['fontSize']*1.1, $this->color, $ttf, $phrase[$i]);
        }


        $this->phrase = implode('', $phrase);
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
     * （^_^）看到这里如果觉得有盗版嫌疑，可以搜索一下十多年前的代码，看看到底是谁盗版谁
     */
    protected function curve()
    {
        $px = $py = 0;

        $A = mt_rand(- $this->height / 2, $this->height / 2);   // 振幅
        $b = mt_rand(- $this->height / 4, $this->height / 4);   // Y轴方向偏移量
        $f = mt_rand(- $this->width / 4, $this->width / 4);     // X轴方向偏移量
        $T = mt_rand($this->width, $this->width * 2);      // 周期
        $w = (2* M_PI)/$T;

        $px1 = mt_rand(0, $this->width/4);  // 曲线横坐标起始位置
        $px2 = mt_rand($px1 + $this->width/3, $this->width);  // 曲线横坐标结束位置

        for ($px = $px1; $px <= $px2; $px ++) {
            if ($w == 0) {
                break;
            }

            $py = $A * sin($w*$px + $f)+ $b + $this->height/2;  // y = Asin(ωx+φ) + b
            $i = max(2, intval($this->cfg['fontSize']/10));
            while ($i > 0) {
                imagesetpixel($this->image, $px, $py + $i, $this->color);  // 这里(while)循环画像素点比imagettftext和imagestring用字体大小一次画出（不用这while循环）性能要好很多
                $i--;
            }
        }
    }

    /**
     * 画杂点
     * 往图片上写不同颜色的字母或数字
     */
    protected function noise()
    {
        for($i = 0; $i < $this->cfg['fontSize']/4; $i++){
            //杂点颜色
            $noiseColor = imagecolorallocate($this->image, mt_rand(100, 255), mt_rand(100, 255), mt_rand(100, 255));

            for ($j = 0; $j < 4; $j++) {
                // 绘杂点
                imagestring($this->image, 5, mt_rand(-10, $this->width), mt_rand(-10, $this->height), $this->phraseSet[mt_rand(0, 25)], $noiseColor);
            }
        }
    }

    /**
     * 绘制背景图片
     */
    protected function bgImg()
    {
        // 随机选择背景图片
        $imgId = mt_rand(1, 8);
        $bgImgPath = dirname(__DIR__) . "/assets/bgs/{$imgId}.jpg";
        list($width, $height) = @getimagesize($bgImgPath);

        // 复制背景图片设置
        $bgImage = @imagecreatefromjpeg($bgImgPath);
        $srcX = mt_rand(0, floor($width - $this->width));
        $srcY = mt_rand(0, floor($height - $this->height));

        @imagecopy($this->image, $bgImage, 0, 0, $srcX, $srcY, $this->width, $this->height);
        @imagedestroy($bgImage);
    }


    /**
     * 水平扭曲验证码图片
     */
    protected function distort()
    {
        $level = $this->cfg['distort'];

        $rgb = [];
        $direct = rand(0, 1);
        $width  = imagesx($this->image);
        $height = imagesy($this->image);

        $offset = mt_rand(3, 8)/10; // 偏移量

        for($y = 0; $y < $height; $y++) {
            // 获取像素图点阵像素
            for($x = 0; $x < $width; $x++) {
                $rgb[$x] = imagecolorat($this->image, $x , $y);
            }

            // 使用正弦函数水平方向扭曲图片（修改像素点x轴偏移）
            for($x = 0; $x < $width; $x++) {
                $r = sin($y / $height * 2 * M_PI - M_PI * $offset) * ($direct ? $level : -$level);
                imagesetpixel($this->image, $x + $r , $y , $rgb[$x]);
            }
        }
    }
}
