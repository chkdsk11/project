<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/4 0004
 * Time: 下午 4:18
 */

namespace Shop\Libs;

use Shop\Libs\LibraryBase;

class Hprose extends LibraryBase
{
    protected static $instance=null;

    protected $hprose=null;

    public function __construct()
    {
        include_once(ERP_PATH.'/src/Hprose.php');
        if(empty($this->hprose)){
            $this->hprose=new \HproseHttpServer();
        }
    }

    public function SetDebug($opt=false)
    {
        if($opt){
            $this->hprose->setDebugEnabled($opt);
        }
    }

    /**
     * @param $func
     * @param $class
     * 发布方法
     */
    public function PublishClassFunc($func,$class)
    {
        $this->hprose->addMethod($func,$class);
    }

    /**
     * @param $class
     * @param string $alias
     * 发布对象
     */
    public function PublishClass($class,$alias='')
    {
        if(empty($alias)){
            $this->hprose->addInstanceMethods($class);
        }else{
            $this->hprose->addInstanceMethods($class,NULL,$alias);
        }
    }

    /**
     * 开启服务
     */
    public function Handler()
    {
        $this->hprose->handle();
    }
}