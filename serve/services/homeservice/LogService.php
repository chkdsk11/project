<?php
/**
 * Author: DengYongJun
 * Email: i@darkdin.com
 * Time: 2017/02/07/13:33
 */
namespace Shop\Home\Services;

use Shop\Libs\SaveLog;

class LogService extends BaseService
{
    protected static $instance=null;

    public static function getInstance()
    {
        if(empty(static::$instance)){
            static::$instance = new LogService();
        }
        return static::$instance;
    }

    public function save($param)
    {
        $ret = SaveLog::getInstance()->save($param);
        if($ret > 0){
            return [ 'status' => 1 , 'message' => '发送成功' ];
        }else{
            return [ 'status' => 0 , 'message' => '发送失败' ];
        }
    }
}