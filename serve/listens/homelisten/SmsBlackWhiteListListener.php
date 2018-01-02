<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2016/12/27 0027
 * Time: 16:52
 */

namespace Shop\Home\Listens;

use Shop\Models\BaiyangSmsList;


use Phalcon\Di;

/**
 * 黑白名单监听事件
 * Class SmsBlackWhiteListListener
 * @package Shop\Home\Listens
 */
class SmsBlackWhiteListListener extends BaseListen
{
    /**
     * @var \Shop\Libs\CacheRedis
     */
    protected $cache;

    public function __construct()
    {
        $this->cache = Di::getDefault()->getShared('cache');
        if(empty($this->cache) === false){
            $this->cache->selectDb(8);
        }else{
            $this->cache = false;
        }
    }

    public function addBlackAfter($event,$class,BaiyangSmsList $record)
    {
        if($this->cache){
            if(empty($record->phone) === false){
                $key = md5($record->phone);
                $this->cache->hSet('sms.black.list',$key,json_encode($record));
            }
            if(empty($record->ip_address) === false){
                $key = md5($record->ip_address);
                $this->cache->hSet('sms.black.list',$key,json_encode($record));
            }
            if(empty($record->phone) === false && empty($record->ip_address) === false){
                $key = md5(($record->phone?:'') . ($record->ip_address?:''));
                $this->cache->hSet('sms.black.list',$key,json_encode($record));
            }
        }
    }

    public function deleteBlackAfter($event,$class,BaiyangSmsList $record)
    {
        if($this->cache) {

            if (empty($record->phone) === false) {
                $key = md5($record->phone);
                $this->cache->hDel('sms.black.list', $key);
            }
            if (empty($record->ip_address) === false) {
                $key = md5($record->ip_address);
                $result = $this->cache->hDel('sms.black.list', $key);
            }
            if (empty($record->phone) === false && empty($record->ip_address) === false) {
                $key = md5(($record->phone ?: '') . ($record->ip_address ?: ''));
                $this->cache->hDel('sms.black.list', $key);
            }
        }
    }

    public function addWhiteAfter($event,$class,BaiyangSmsList $record)
    {
        if($this->cache){
            if(empty($record->phone) === false){
                $key = md5($record->phone);
                $this->cache->hSet('sms.white.list',$key,json_encode($record));
            }
            if(empty($record->ip_address) === false){
                $key = md5($record->ip_address);
                $this->cache->hSet('sms.white.list',$key,json_encode($record));
            }
            if(empty($record->phone) === false && empty($record->ip_address) === false){
                $key = md5(($record->phone?:'') . ($record->ip_address?:''));
                $this->cache->hSet('sms.white.list',$key,json_encode($record));
            }
        }

    }

    public function deleteWhiteAfter($event,$class,BaiyangSmsList $record)
    {
        if($this->cache){
            if (empty($record->phone) === false) {
                $key = md5($record->phone);
                $this->cache->hDel('sms.white.list', $key);
            }
            if (empty($record->ip_address) === false) {
                $key = md5($record->ip_address);
                $this->cache->hDel('sms.white.list', $key);
            }
            if (empty($record->phone) === false && empty($record->ip_address) === false) {
                $key = md5(($record->phone ?: '') . ($record->ip_address ?: ''));
                $this->cache->hDel('sms.white.list', $key);
            }
        }
    }
}