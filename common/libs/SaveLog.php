<?php
/**
 * Author: DengYongJun
 * Email: i@darkdin.com
 * Time: 2017/02/04/13:53
 */
namespace Shop\Libs;
use Shop\Libs\LibraryBase;

class SaveLog extends LibraryBase
{
    protected static $instance=null;
    protected $redis;

    public function __construct()
    {
        if($this->redis == null)
            $this->redis = $this->cache;
    }

    public function save($log_content_arr)
    {
        $log_content_arr['log_time'] = date('Y-m-d H:i:s'); // 日志记录时间
        $log_content = json_encode($log_content_arr,JSON_UNESCAPED_UNICODE);
        $this->redis->selectDb(10);
        $ret = $this->redis->rPush('syslog',$log_content);
        return $ret;
    }
}