<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/4 0004
 * Time: 下午 6:01
 */
namespace Shop\Libs;

use Shop\Libs\LibraryBase;

class HproseClient extends LibraryBase
{
    protected static $instance=null;

    protected $hprose=null;
    protected $url=null;

    public function __construct()
    {
        include_once(ERP_PATH.'/src/Hprose.php');
    }

    public function SubServer($url)
    {
        return new HproseHttpClient($url);
    }
}