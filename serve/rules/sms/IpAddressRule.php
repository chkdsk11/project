<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2016/12/26 0026
 * Time: 17:44
 */

namespace Shop\Rules\Sms;

use Phalcon\Di;
use Shop\Home\Datas\BaiyangSmsData;
use Shop\Models\BaiyangSmsList;

/**
 * IP限制规则
 * Class IpAddressRule
 * @package Shop\Rules\Sms
 */
class IpAddressRule implements SmsRuleInterface
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
    public function handle(array $parameter = null)
    {

        if(empty($parameter) || empty($parameter['ip']) || !$this->cache){
            return true;
        }

        $minuteRateKey = 'sms.limit.rate.ip.'. $parameter['ip'] . '.'  . strtotime(date('Y-m-d H:i')) ;

        $currentCount = $this->cache->incre($minuteRateKey,1);
        if($currentCount === 1){
            $this->cache->expire($minuteRateKey,240);
        }

        if($currentCount > 100){
            //获取一分钟之前的次数
            $beforeOneMinuteKey =  'sms.limit.rate.ip.'. $parameter['ip'] . '.' . strtotime(date('Y-m-d H:i',strtotime('-1 Minute')));

            $beforeOneMinuteCount = $this->cache->incre($beforeOneMinuteKey,0);

            if($beforeOneMinuteCount >= 100){
                //获取二分钟之前的次数
                $beforeTwoMinuteKey = 'sms.limit.rate.ip.'. $parameter['ip'] . '.' . strtotime(date('Y-m-d H:i',strtotime('-2 Minute')));

                $beforeTwoMinuteCount = $this->cache->incre($beforeTwoMinuteKey,0);

                if($beforeTwoMinuteCount >= 100){
                    //var_dump($currentCount);exit;

                    $blackModel = new BaiyangSmsList();
                    $blackModel->ip_address = $parameter['ip'];
                    $blackModel->list_type = 'black';
                    $blackModel->list_info_type = 0;
                    $blackModel->create_time = date('Y-m-d H:i:s');
                    $blackModel->expire_time = 12* 3600;
                    $blackModel->create_at = 0;
                    $blackModel->create();

                    $parameter['description'] = "IP地址{$parameter['ip']}，三分钟内平均每分钟请求数量超过了100次，因此该手机号自动加入黑名单，errcode：60003|". $currentCount .'|'.$beforeOneMinuteCount.'|'.$beforeTwoMinuteCount;;
                    $parameter['original_data'] = json_encode($blackModel);
                    $parameter['log_type'] = 1;
                    BaiyangSmsData::getInstance()->addLog($parameter);

                    return ['errcode' => 60003, 'errmsg' => '当前手机号或IP已被加入黑名单。errcode = 60003|'. $currentCount .'|'.$beforeOneMinuteCount.'|'.$beforeTwoMinuteCount];
                }
            }
        }
        return true;
    }

}