<?php
/**
 * Created by Zend Studio.
 * User: Administrator
 * Date: 2016/3/23
 * Time: 14:00
 * Library 基类
 */

namespace Shop\Libs;

use Phalcon\Mvc\User\Plugin;

class LibraryBase extends Plugin
{
    protected static $instance=null;

    public static function getInstance()
    {
        if(empty(static::$instance)){
            static::$instance=new static();
        }
        return static::$instance;
    }
}