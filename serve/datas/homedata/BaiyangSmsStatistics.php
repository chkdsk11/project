<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2016/12/30 0030
 * Time: 10:55
 */

namespace Shop\Home\Datas;

/**
 * Class BaiyangSmsStatistics
 * @package Shop\Home\Datas
 * @property \Shop\Libs\CacheRedis $cache
 */
class BaiyangSmsStatistics extends BaseData
{
    /**
     * Redis 中有序列表储存的键名
     */
    const STATISTICS_LIST_KEY = 'SMS.STATISTICS_LIST_KEY';



    protected static $instance = null;
    /**
     * @var \Shop\Libs\CacheRedis
     */
    protected $redis;

    public function __construct()
    {
        if(isset($this->cache) && $this->cache){
            $this->redis = $this->cache->selectDb(8);
        }
    }

    /**
     * @return BaiyangSmsData
     */
    public static function getInstance()
    {
        if(empty(static::$instance)){
            static::$instance=new static();
        }
        return static::$instance;
    }

    public function pushCount($provider_code)
    {
        if($this->redis){

        }
    }

    public function getStatistics($provider_code,$start,$end)
    {
        if($this->redis){
            $startKey = 'sms.statistics.'. $provider_code .'.' .date('Y-m-d H:i',$start);
            $stopKey = 'sms.statistics.'. $provider_code .'.' .date('Y-m-d H:i',$start);
        }
    }
}