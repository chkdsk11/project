<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2016/12/26 0026
 * Time: 13:30
 */

namespace Shop\Rules\Sms;

use Phalcon\Di;
use Phalcon\Events\ManagerInterface;
use Phalcon\Events\EventsAwareInterface;
use Shop\Models\BaiyangSmsList;

/**
 * 白名单
 * Class WhitelistRule
 * @package Shop\Rules\Sms
 */
class WhitelistRule implements SmsRuleInterface,EventsAwareInterface
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
        //优先缓存成中查询白名单
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
                $blackItems = $this->cache->hMget('sms.white.list',$hashKeys);

                if(empty($blackItems) === false){
                    foreach ($blackItems as $key=>$item){
                        if($item !== false){
                            $cacheResult = json_decode($this->cache->hGet('sms.white.list', $key));
                            if (empty($cacheResult) === false) {
                                return ['action' => 'right'];
                            }
                        }
                    }
                }
            }
        }

        $conditions = "list_type = 'white'";
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

        $result =  BaiyangSmsList::findFirst([
            $conditions,
            'bind' => $binds
        ]);

        if(empty($result) === false){
            return ['action' => 'right'];
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