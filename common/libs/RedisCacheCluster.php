<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/3 0003
 * Time: 下午 9:20
 */

namespace Shop\Libs;

use Shop\Libs\LibraryBase;

class RedisCacheCluster extends LibraryBase
{
    protected static $instance=null;
    protected $Cluster=null;

    /*
     * 初始方法
     */
    public function __construct()
    {
        $this->Connect();
    }

    /**
     *  redis集群连接
     */
    protected function Connect()
    {
        $ClusterHosts=$this->config->ClusterRedis->master;
        $ConnTimeout=$this->config->ClusterRedis->connTimeout;
        $ReadTimeout=$this->config->ClusterRedis->readTimeout;
        if(empty($this->Cluster)) {
            try {
                $this->Cluster = new \RedisCluster(NULL, [$ClusterHosts[0], $ClusterHosts[1], $ClusterHosts[2], $ConnTimeout, $ReadTimeout]);
                $this->Cluster->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_IGBINARY);
            }catch (\Exception $e){

            }
        }
    }

    public function Set($key,$value,$expireTime=0)
    {
        if(!empty($expireTime)) {
            return $this->Cluster->set($key, $value, $expireTime);
        }else{
            return $this->Cluster->set($key,$value);
        }
    }

    public function Get($key)
    {
        return $this->Cluster->get($key);
    }
}
