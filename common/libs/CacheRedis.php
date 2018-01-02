<?php
/**
 * User: 康涛
 * Date: 2016/3/23
 * Time: 14:00
 * Update:2016/8/9
 * 已重写phalcon redis类，使用igbinary对数组进行序列化
 *
 */
namespace Shop\Libs;

use Phalcon\Cache\Backend\Redis as ShopRedis;
use Shop\Models\CacheKey;
use Shop\Models\OrderEnum;
use Shop\Models\CacheGoodsKey;

class CacheRedis extends ShopRedis
{

    protected $frontend;
    protected $options;
    /**
     * @var \Redis
     */
    protected $_redis=null;
    
    /**
     * 
     * @param unknown $frontend
     * @param unknown $options
     */
    public function __construct($frontend, $options)
    {
        $this->frontend=$frontend;
        $this->options=$options;
        $this->_connect();
    }

    /**
     * 重写连接
     */
    public function _connect()
    {
        if (empty($this->_redis)) {
            try {
                $this->_redis = new \Redis();
                if ($this->options['persistent']) {
                    $this->_redis->pconnect($this->options['host'], $this->options['port']);
                } else {
                    $this->_redis->connect($this->options['host'], $this->options['port']);
                }
                if(isset($this->options['auth']) && !empty($this->options['auth'])) {
                    $this->_redis->auth($this->options['auth']);
                }
                if(isset($this->options['select_db']) && !empty($this->options['select_db'])) {
                    $this->_redis->select($this->options['select_db']);
                }
                // SERIALIZER_IGBINARY需要安装php的igbinary扩展
                $this->_redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_IGBINARY);
            } catch (\Exception $exception) {
                echo $exception->getMessage();
            }
        }
    }
    
    /**
     * @param int $db
     * @param number $db
     */
    public function selectDb($db = 0)
    {
        if(isset($this->options['select_db']) && !empty($this->options['select_db'])) {
            return $this->_redis->select($this->options['select_db']);
        }
        return $this->_redis->select(0);
    }
    
    /**
     * @param $key
     * @param $value=string|[]
     * @param $expireTime
     * @return bool
     * 设置key与值
     */
    public function setValue($key,$value,$expireTime=0)
    {
        // 把所有除session的key给存起来
        $this->setAllRedisKeys($key);
        if(empty($expireTime)) {
            return $this->_redis->set($key, $value);
        }else{
            return $this->_redis->set($key,$value,$expireTime);
        }
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getValue($key)
    {
        return $this->_redis->get($key);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function delete($key)
    {
        return $this->_redis->delete($key);
    }

    /**
     * @param $key
     */
    public function setKeyExpireTime($key,$expireTime)
    {
        return $this->_redis->setTimeout($key,$expireTime);
    }
    public function expire($key,$ttl)
    {
        return $this->_redis->expire($key,$ttl);
    }

    /**
     * @param $key
     * @param int $value
     * @return mixed
     */
    public function incre($key,$value=1)
    {
        return $this->_redis->incrBy($key,$value);
    }

    /**
     * @param $key
     * @param int $value
     * @return mixed
     */
    public function decre($key,$value=1)
    {
        return $this->_redis->decrBy($key,$value);
    }

    /**
     * @param $dbNode
     * 刷新指定的库
     */
    public function flushDb($dbNode)
    {
        if(!empty($dbNode)) {
            if(isset($this->options['select_db']) && !empty($this->options['select_db'])) {
                $this->_redis->select($this->options['select_db']);
            }else {
                $this->_redis->select($dbNode);
            }
            return $this->_redis->flushDb();
        }
    }

    /**
     * @param string $key
     * @param number $sort
     * @param string $value
     * @return []
     * @return bool
     */
    public function zAdd($key,$sort,$value)
    {
        return $this->_redis->zAdd($key,$sort,$value);
    }

    /**
     * @param string $key
     * @param int $offset
     * @param int $limit
     * @return []
     */
    public function zRange($key,$offset,$limit)
    {
        return $this->_redis->zRange($key,$offset,$limit);
    }

    /**
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function zDelete($key,$value)
    {
        return $this->_redis->zDelete($key,$value);
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function rPush($key,$value)
    {
        return $this->_redis->rPush($key,$value);
    }

    /**
     * @param $channel
     * @param $message
     * @return mixed
     */
    public function publish($channel, $message)
    {
        return $this->_redis->publish($channel, $message);
    }

    /**
     * @param $channel_patterns
     * @param $callback
     * @return mixed
     */
    public function psubscribe($channel_patterns,$callback){
        return $this->_redis->psubscribe($channel_patterns,$callback);
    }

    /**
     * @param $channel
     * @param $callback
     * @return mixed
     */
    public function subscribe($channel,$callback)
    {
        return $this->_redis->subscribe($channel,$callback);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function lPop($key)
    {
        return $this->_redis->lPop($key);
    }

    /**
     * @param $key
     * @param $start
     * @param $end
     * @return mixed
     */
    public function lRange($key, $start, $end)
    {
        return $this->_redis->lRange($key, $start, $end);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function lLen($key)
    {
        return $this->_redis->lLen($key);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function watch($key)
    {
        return $this->_redis->WATCH($key);
    }

    /**
     * @return mixed
     */
    public function multi()
    {
        return $this->_redis->MULTI();
    }

    /**
     * @return mixed
     */
    public function exec(){
        return $this->_redis->EXEC();
    }

    /**
     * @return mixed
     */
    public function discard()
    {
        return $this->_redis->DISCARD();
    }

    /**
     * Redis Hset 命令用于为哈希表中的字段赋值 。
     * 如果哈希表不存在，一个新的哈希表被创建并进行 HSET 操作。
     * 如果字段已经存在于哈希表中，旧值将被覆盖。
     * @param $key
     * @param $hashKey
     * @param $value
     * @return int
     */
    public function hSet($key,$hashKey,$value)
    {
        return $this->_redis->hSet($key,$hashKey,$value);
    }
    /**
     * HRedis Hmset 命令用于同时将多个 field-value (字段-值)对设置到哈希表中。
     * 此命令会覆盖哈希表中已存在的字段。
     * 如果哈希表不存在，会创建一个空哈希表，并执行 HMSET 操作。
     * @param $key
     * @param array $hashKeys
     * @return bool
     */
    public function hMset($key,array $hashKeys)
    {
        return $this->_redis->hMset($key,$hashKeys);
    }

    /**
     * 返回哈希表 key 中，一个或多个给定域的值。
     * 如果给定的域不存在于哈希表，那么返回一个 nil 值。
     * 因为不存在的 key 被当作一个空哈希表来处理，所以对一个不存在的 key 进行 HMGET 操作将返回一个只带有 nil 值的表。
     * @param $key
     * @param array $hashKeys
     * @return array
     */
    public function hMget($key,array $hashKeys)
    {
        return $this->_redis->hMGet($key,$hashKeys);
    }
    /**
     * Redis Hdel 命令用于删除哈希表 key 中的一个或多个指定字段，不存在的字段将被忽略。
     *
     * @param $key
     * @param $hashKey
     * @return int
     */
    public function hDel($key, $hashKey)
    {
        return $this->_redis->hDel($key,$hashKey);
    }

    /**
     * Redis Hget 命令用于返回哈希表中指定字段的值。
     * @param $key
     * @param $hashKey
     * @return string
     */
    public function hGet($key, $hashKey)
    {
        return $this->_redis->hGet($key,$hashKey);
    }

    /**
     * Redis Hsetnx 命令用于为哈希表中不存在的的字段赋值 。
     * 如果哈希表不存在，一个新的哈希表被创建并进行 HSET 操作。
     * 如果字段已经存在于哈希表中，操作无效。
     * 如果 key 不存在，一个新哈希表被创建并执行 HSETNX 命令。
     * @param $key
     * @param $hashKey
     * @param $value
     * @return bool
     */
    public function hSetnx($key, $hashKey, $value)
    {
        return $this->_redis->hSetNx($key, $hashKey,$value);
    }

    /**
     * Redis Hexists 命令用于查看哈希表的指定字段是否存在。
     * @param $key
     * @param $hashKey
     * @return bool
     */
    public function hExists ($key, $hashKey)
    {
        return $this->_redis->hExists($key, $hashKey);
    }

    /**
     * [keys 取所有的缓存key]--- 云redis服务器不能用这个方法
     * @param  string $key [description]
     * @return [type]      [description]
     */
    public function keys($key = "*")
    {
        return $this->_redis->keys($key);
    }

    /**
     * [setAllRedisKeys 集合存储key]
     * @param string $key [description]
     */
    public function setAllRedisKeys($key = '')
    {
        $category_key_res = $this->prefixCategory($key);
        // 不是分类key
        if(empty($category_key_res)){
            $all_keys = CacheKey::SOA_ALL_REDIS_KEYS_ARR;
            $pattern = "/^{$all_keys}/";
            $preg_res = preg_match($pattern, $key);
            if($key && empty($preg_res)){
                $AllRedisKeys = $this->smembersSet($all_keys);
                if(empty($AllRedisKeys)){
                    // 第一个
                    $keys_tmp_key =  $all_keys . '1';
                    $this->saddSet($keys_tmp_key,$key);
                    $this->saddSet($all_keys,$keys_tmp_key);
                }else{
                    $k = 1;
                    $open_new_k = false;
                    foreach ($AllRedisKeys as $_key => $_value) {
                        if($this->scardSet($_value) < 1000){
                            $this->saddSet($_value,$key);
                            $open_new_k = false;
                        }else{
                            $open_new_k = true;
                            $k++;
                        }
                    }
                    if($open_new_k){
                        $keys_tmp_key =  $all_keys . $k;
                        $this->saddSet($keys_tmp_key,$key);
                        $this->saddSet($all_keys,$keys_tmp_key);
                    }
                }
            }
        }
    }

    /**
     * [getAllRedisKeys 取集合存储key]
     * @return [type] [description]
     */
    public function getAllRedisKeys()
    {
        return $this->smembersSet(CacheKey::SOA_ALL_REDIS_KEYS_ARR);
    }

    /**
     * [saddSet 添加集合]
     * @param  string $key   [description]
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function saddSet($key = '',$value = '')
    {
        if($key && $value){
            return $this->_redis->sadd($key, $value);
        }
        return 0;
    }

    /**
     * [sremSet 移除集合中一个]
     * @param  string $key   [description]
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function sremSet($key = '',$value = '')
    {
        if($key && $value){
             return $this->_redis->srem($key, $value);
        }
        return 0;
    }

    /**
     * [scardSet 取集合数量]
     * @param  string $key [description]
     * @return [type]      [description]
     */
    public function scardSet($key = '')
    {
        if($key){
            return $this->_redis->scard($key);
        }
        return 0;
    }

    /**
     * [smembersSet 取集合数据]
     * @param  string $key [description]
     * @return [type]      [description]
     */
    public function smembersSet($key = '')
    {
        if($key){
            return $this->_redis->smembers($key);
        }
        return '';
    }

    /**
     * [prefixCategoryArr redis索引分类key]
     * @return [type] [description]
     */
    public function prefixCategoryArr()
    {
        $data = [
            // CacheKey
            CacheKey::MAKE_ORDER_PROMOTION => '凑单促销列表的前缀',
            CacheKey::CART_LIMIT_BUY_KEY => '购物车限购用到的数据的前缀',
            CacheKey::ALL_CHANGE_PROMOTION => '切换全场活动的前缀',
            CacheKey::ORDER_SN => '生成订单号的前缀',
            CacheKey::EXCHANGE_COUPON_FAIL => '获取用户失败前缀',
            CacheKey::ES_STOCK_KEY => '同步库存到ES的队列KEY',
            CacheKey::EFFECTIVE_PROMOTION => '有效的促销活动',
            CacheKey::GOODS_SET => '商品套餐前缀',
            CacheKey::REGION_LIST => '省市区',
            CacheKey::REGION_KEYS_LIST => '省市区key',
            CacheKey::CPS_ORDER_KEY => 'CPS KEY',
            CacheKey::ERP_ORDER_RETURN_REASON_NOTICE => 'erp退款申请',
            CacheKey::ALL_PRODUCT_RULE => '所有商品品规 ID为key name为value',
            CacheKey::ALL_Region => '获取所有地区',
            CacheKey::COUPON_ADD_ACTCODE => '所有商品品规 ID为key name为value',
            // CacheGoodsKey
            CacheGoodsKey::SKU_INFO => 'sku详情信息的缓存前缀',
            CacheGoodsKey::SKU_DEFAULT => 'sku默认信息的缓存前缀',
            CacheGoodsKey::SKU_AD => 'sku广告信息的缓存前缀',
            CacheGoodsKey::SKU_SPU => 'spu信息的缓存前缀',
            CacheGoodsKey::SKU_IMG => 'sku图片信息的缓存前缀',
            CacheGoodsKey::SPU_IMG => 'sku默认图片信息的缓存前缀',
            CacheGoodsKey::CATEGORY_RULE => '分类品规关系信息的缓存前缀',
            CacheGoodsKey::CATEGORY_SON => '所有子分类信息的缓存前缀',
            CacheGoodsKey::RULE_NAME => '多品规信息的缓存前缀',
            CacheGoodsKey::SPU_RULE_VALUE => '相同spu商品多品规信息的缓存前缀',
            CacheGoodsKey::SKU_CATEGORY_BACKSTAGE => '后台分类信息的缓存前缀',
            CacheGoodsKey::SKU_HOT => '热门信息的缓存前缀',
            CacheGoodsKey::SKU_RECOMMEND => '推荐信息的缓存前缀',
            CacheGoodsKey::SKU_TIMING => '定时上下架sku信息',
            CacheGoodsKey::SKU_TIMING_TIME => '定时上下架时间信息',
            CacheGoodsKey::SKU_BRAND_NAME => '品牌名缓存前缀',
            CacheGoodsKey::SKU_VIDEO => '视频缓存前缀',
            CacheGoodsKey::SKU_INSTRUCTION => '说明书缓存前缀',
            CacheGoodsKey::GLOBAL_GOODS => '海外购商品缓存',
            CacheGoodsKey::SPU_RULE_VALUE_ALL => '相同品规名下所有品规值',
            CacheGoodsKey::BRAND_COUNT => '获取所有品牌总数的缓存前缀',
            CacheGoodsKey::BRAND_LIST => '获取所有品牌列表数据的缓存前缀',
            CacheGoodsKey::BRAND_GOODS_COUNT => '获取指定品牌商品总数的缓存前缀',
            CacheGoodsKey::BRAND_GOODS_LIST => '获取指定品牌商品列表数量的缓存前缀',
            CacheGoodsKey::ONE_BRAND_INFO => '获取单个品牌信息的缓存前缀',
            // OrderEnum
            OrderEnum::USER_ORDER_LOCK_KEY => '用户订单读写前辍',
            OrderEnum::ORDER_SN_KEY => '订单号生成key',
            CacheKey::PC_ALL_AD => 'PC广告位缓存',
            CacheKey::PC_LevelCategoryList => 'PC商品分类列表缓存',
            CacheKey::PC_LevelRegionList => 'PC地区列表缓存',
        ];
        return $data;
    }

    /**
     * [prefixCategory 索引分类]
     * @param  string $key [description]
     * @return [type]      [description]
     */
    public function prefixCategory($CategoryKey = '')
    {
        if($CategoryKey){
            $prefixCategoryArr = $this->prefixCategoryArr();
            if($prefixCategoryArr){
                foreach ($prefixCategoryArr as $key => $value) {
                    $pattern = "/^{$key}/";
                    $res = preg_match($pattern, $CategoryKey);
                    if($res){
                        $key .= "_index__";
                        $this->saddSet($key,$CategoryKey);
                        return true;
                    }
                }
            }
        }
        return false;
    }
}