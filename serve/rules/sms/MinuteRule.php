<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2016/12/21 0021
 * Time: 17:35
 */

namespace Shop\Rules\Sms;


use Phalcon\Di;
use Shop\Home\Datas\BaiyangSmsData;
use Shop\Libs\RedisCache;
use Shop\Models\BaiyangSmsList;
use Shop\Models\BaiyangSmsLog;
use Shop\Models\BaiyangSmsRecords;

/**
 * 每分钟只能发一条规则
 * Class MinuteRule
 * @package Shop\Rules\Sms
 */
class MinuteRule implements SmsRuleInterface
{
    /**
     * @var RedisCache
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
        if(empty($parameter) ||  empty($parameter['phone'])){
            return false;
        }

        //如果启用了缓存，则直接使用缓存处理
        if($this->cache){
            $result = $this->useCacheHandle($parameter);
            if($result !== true){
                return $result;
            }
        }
        /***
         * 手机号码请求频率限制：同一个手机号码，间隔1分钟之后才能重新请求发送短信，对于同一分钟内，
         * 同个手机号码的其他请求，均忽略不响应请求，提示“操作过于频繁，请稍后再试”；
         */
        $record = BaiyangSmsRecords::findFirst([
            'phone = :phone: AND is_success = 0 AND create_time > :time:',
            'bind' => ['phone'=>$parameter['phone'], 'time' => date('Y-m-d H:i:s',time()- 60)]
        ]);

        if($record !== null && empty($record) === false){

            $parameter['description'] = "通过数据库短信发送记录判断，手机号 {$parameter['phone']} 一分钟内请求超过5次，errcode：60004";
            $parameter['original_data'] = json_encode($record);
            $parameter['log_type'] = 1;
            BaiyangSmsData::getInstance()->addLog($parameter);

            return ['errcode' => 60004, 'errmsg' => '操作过于频繁，请稍后再试'];
        }

        return true;
    }

    protected function useCacheHandle(array $parameter = null)
    {
        $dayKey = 'sms.limit.rate.phone.' . $parameter['phone'];

        /**
         * 手机号码每天请求数量限制：每个手机号码每天请求短信最大次数为10次，大于10次时，
         * 从最后一次请求开始计算，次日才能继续请求，提示“操作过于频繁，请稍后再试”；
         */
        $dayResult = $this->cache->incre($dayKey,1);

        if($dayResult === 1){
            $this->cache->expire($dayKey,strtotime(date('Y-m-d 23:59:59')) - time());
            $count = BaiyangSmsRecords::count([
                'create_time >= :time: AND phone = :phone:',
                'bind' => ['phone' => $parameter['phone'], 'time' =>  date('Y-m-d 00:00:01')]
            ]);

            if($count > 10) {
                $this->cache->incre($dayKey, 10);
                $this->cache->expire($dayResult, strtotime(date('Y-m-d 23:59:59') - time()));

                $parameter['description'] = "通过数据库判断，手机号 {$parameter['phone']} 一天内请求超过 {$count} 次，errcode：60005";
                $parameter['original_data'] = json_encode($count);
                $parameter['log_type'] = 1;
                BaiyangSmsData::getInstance()->addLog($parameter);

                return ['errcode' => 60005, 'errmsg' => '操作过于频繁，请稍后再试'];
            }

        }elseif ($dayResult > 10){
            $parameter['description'] = "通过缓存判断，手机号 {$parameter['phone']} 一天内请求超过 {$dayResult} 次，errcode：60005";
            $parameter['original_data'] = json_encode($dayResult);
            $parameter['log_type'] = 1;
            BaiyangSmsData::getInstance()->addLog($parameter);

            return ['errcode' => 60005, 'errmsg' => '操作过于频繁，请稍后再试'];
        }

        $minuteKey = 'sms.limit.phone.'.$parameter['phone'];

        /**
         *  如同一手机号码一分钟请求大于5次，则将该手机号码暂时加入黑名单，从最后一次请求时间计算，
         *  12小时后从黑名单中释放，提示“操作过于频繁，请稍后再试”；（一般前端都会限制1分钟后才能重新获取短信）
         */
        $count = $this->cache->incre($minuteKey,1) ;
        if($count === 1){
            $this->cache->expire($minuteKey,60 );
        }

        if($count > 5){

            $blackRecord = BaiyangSmsList::findFirst([
                'phone = :phone: AND list_type = :list_type:',
                'bind' => ['phone' => $parameter['phone'], 'list_type' => 'black']
            ]);
            //如果不在黑名单里面，则加入黑名单
            if(empty($blackRecord)) {
                $blackModel = new BaiyangSmsList();
                $blackModel->phone = $parameter['phone'];
                $blackModel->list_type = 'black';
                $blackModel->list_info_type = 0;
                $blackModel->create_time = date('Y-m-d H:i:s');
                $blackModel->expire_time = 12* 3600;
                $blackModel->create_at = 0;
                $blackModel->create();
            }
            $parameter['description'] = "通过缓存判断，手机号 {$parameter['phone']} 一分钟内请求超过 {$count} 次，因此该手机号自动加入黑名单，errcode：60006";
            $parameter['original_data'] = json_encode($blackRecord);
            $parameter['log_type'] = 1;
            BaiyangSmsData::getInstance()->addLog($parameter);

            return ['errcode' => 60006, 'errmsg' => '操作过于频繁，请稍后再试'];
        }
        return true;

    }
}