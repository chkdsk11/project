<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2016/12/26 0026
 * Time: 14:24
 */

namespace Shop\Rules\Sms;

use Phalcon\Di;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\ManagerInterface;
use Shop\Home\Datas\BaiyangSmsData;
use Shop\Models\BaiyangSmsList;
use Shop\Models\BaiyangSmsLog;

/**
 * 处理黑名单
 * Class BlacklistRule
 * @package Shop\Rules\Sms
 */
class BlacklistRule implements SmsRuleInterface,EventsAwareInterface
{
    /**
     * @var ManagerInterface
     */
    protected $_eventsManager;
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

    public function handle(array $parameter = null)
    {
        if(empty($parameter) || (empty($parameter['ip']) && empty($parameter['phone']))){
            return false;
        }
        $phone = empty($parameter['phone']) ? '' : $parameter['phone'];
        $ip =  empty($parameter['ip']) ? '' : $parameter['ip'];
        //优先从换成中查询黑名单
        if($this->cache){
            $hashKeys = [];
            if(empty($phone) === false){
                $hashKeys[] = md5($phone);
            }
            if (empty($ip) === false){
                $hashKeys[] = md5($ip);
            }
            if(empty($phone) === false && empty($ip) === false){
                $hashKeys[] = md5($phone.$ip);
            }
            if(empty($hashKeys) === false) {
                //从redis中查询出所有可能匹配的项
               $blackItems = $this->cache->hMget('sms.black.list',$hashKeys);
               if(empty($blackItems) === false){
                   foreach ($blackItems as $key=>$item){
                        if($item !== false){
                            $cacheResult = json_decode($this->cache->hGet('sms.black.list', $key));
                            if (empty($cacheResult) === false) {
                                $expireTime = intval($cacheResult->expire_time);
                                //如果缓存已过期
                                if ($cacheResult->list_info_type == 0 && $expireTime > 0 && (time() - strtotime($cacheResult->create_time) > $expireTime)) {

                                    $oldRecord = BaiyangSmsList::findFirst($cacheResult->list_id);
                                    if($oldRecord){
                                        $oldRecord->delete();
                                        $this->_eventsManager->fire('sms_list:deleteBlackAfter', $this, $oldRecord);
                                    }else{
                                        $oldRecord = new BaiyangSmsList();
                                        $oldRecord->phone = $cacheResult->phone;
                                        $oldRecord->ip_address = $cacheResult->ip_address;
                                        $oldRecord->list_id = $cacheResult->list_id;

                                        $this->_eventsManager->fire('sms_list:deleteBlackAfter', $this, $oldRecord);
                                    }

                                } else {
                                    $parameter['description'] = "从 redis 中判断指定的手机号 {$phone} 或IP {$ip} 在黑名单中89。errcode:60003" . json_encode($blackItems);
                                    $parameter['original_data'] = json_encode($cacheResult);
                                    BaiyangSmsData::getInstance()->addLog($parameter);

                                    return ['errcode' => 60003, 'errmsg' => '当前手机号或IP已被加入黑名单'];
                                }
                            }
                        }
                   }
               }
            }
        }
        $conditions = "list_type = 'black'";
        $binds = [];

        if(empty($parameter['ip']) === false && empty($parameter['phone']) === false){
            $conditions .= ' AND (ip_address = :ip: OR phone = :phone:)';
            $binds['ip'] = $parameter['ip'];
            $binds['phone'] = $parameter['phone'];
        }elseif(empty($parameter['phone']) === false){
            $conditions .=  ' AND phone = :phone:';
            $binds['phone'] = $parameter['phone'];
        }elseif (empty($parameter['ip']) === false){
            $conditions .= ' AND ip_address = :ip:';
            $binds['ip'] = $parameter['ip'];
        }

        $result =  BaiyangSmsList::find([
            $conditions,
            'bind' => $binds
        ]);

        if(empty($result) || count($result) <= 0){
            return true;
        }

        foreach ($result as $index=>$item){
            //如果是系统自动添加，并且超过了12小时，则删除规则
            if($item->list_info_type == 0 && (time() - strtotime($item->create_time)) > $item->expire_time){

                if($item->delete()){
                    $this->_eventsManager->fire('sms_list:deleteBlackAfter', $this, $item);
                    unset($result[$index]);
                }
            }
        }

        if($result != null && count($result) > 0){

            $this->_eventsManager->fire('sms_list:addBlackAfter',$this,$result[0]);
            $parameter['description'] = "从数据库中判断指定的手机号 {$phone} 或IP {$ip} 在黑名单中。errcode:60003";
            $parameter['original_data'] = json_encode($result);
            BaiyangSmsData::getInstance()->addLog($parameter);

            return ['errcode' => 60003, 'errmsg' => '当前手机号或IP已被加入黑名单'];
        }
        return true;
    }


    public function setEventsManager(ManagerInterface $eventsManager)
    {
        $this->_eventsManager = $eventsManager;
    }

    public function getEventsManager()
    {
        return $this->_eventsManager;
    }

}