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
 *
 * @package     wf.captcha
 * @author      cm <cmpan@qq.com>
 * @link        http://docs.windwork.org/manual/wf.captcha.html
 * @since       1.0
 */
interface CaptchaInterface 
{
    public function getPhrase();

    /**
     * out put captcha image
     *
     * @param int $quality
     * @return mixed
     */
    public function output($quality = 90);

    /**
     * get image content
     *
     * @param int $quality
     * @return mixed
     */
    public function get($quality = 90);
}

