<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2016/12/20 0020
 * Time: 13:56
 */

namespace Shop\Home\Datas;

use Shop\Models\BaiyangSmsClient;
use Shop\Models\BaiyangSmsLog;
use Shop\Models\BaiyangSmsProvider;
use Shop\Models\BaiyangSmsRecords;
use Shop\Models\BaiyangSmsRelationship;
use Shop\Models\BaiyangSmsTemplate;
use Shop\Models\SmsTemplateStateEnum;

/**
 * Class BaiyangSmsData
 * @package Shop\Home\Datas
 *
 */
class BaiyangSmsData extends BaseData
{
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

    /**
     * 获取一个可用的短信服务商
     * @param string $providerCode 获取指定的短信提供商信息
     * @return null|array
     */
    public function getSmsProvider($providerCode = null)
    {
        if($providerCode != null){
            $result = BaiyangSmsProvider::query()
                ->where('provider_state = 0')
                ->andWhere('provider_code = :code:')
                ->bind(['code' => $providerCode])
                ->execute()
                ->toArray();

            return count($result) > 0 ? $result[0] : null;
        }
        $results = BaiyangSmsProvider::query()
            ->where('provider_state = 0')
            ->andWhere('remainder_quantity > 0')
            ->execute()
            ->toArray();

        if(count($results) > 1){

            $sum = '0';
            foreach ($results as $item){
                //如果当前比例设为1则直接返回该配置
                if(bccomp($item['scale'],'1',2) === 0){

                    return $item;
                }
                $sum = bcadd($sum ,$item['scale'],2);
            }
            //重新计算分配发送比例
            if(bccomp($sum,'1',2) !== 0){
                $sub = bcsub ('1',$sum,2);
                $div = bcdiv($sub,count($results),2);

                foreach ($results as &$item){
                    $item['scale'] = bcadd($div,$item['scale'],2);
                }
                unset($item);
            }
        }

        if(count($results) === 1){
            return $results[0];
        }elseif (count($results) > 1){
            $params = array_column($results,'scale','provider_code');
            asort($params);

            $interval = [];
            reset($params);

            $i = 0;
            while (list($key,$value) = each($params)){
                $interval[$key] = ['min' => $i  ,'max' => $i + ($value * 100)];
                $i = $value * 100;
            }


            $rand = mt_rand(1,99);

            foreach ($interval as $key=>$item){

                if(bccomp($rand,$item['min']) === 1 && bccomp($rand,$item['max']) <= 0){
                    foreach ($results as $provider){
                        if(strcasecmp($provider['provider_code'], $key) === 0){

                            return $provider;
                        }
                    }
                }
            }


        }

        return null;
    }

    /**
     * 获取一个可用的补发短信提供商
     * @param string|null $excludeProviderCode 排除的短信提供商编号
     * @return null|array
     */
    public function getReserveProvider($excludeProviderCode = null)
    {
        $query =  BaiyangSmsProvider::query()
            ->where('provider_state = 0')
            ->andWhere('remainder_quantity > 0');
        if(empty($excludeProviderCode) === false){
            $query = $query
                ->andWhere('provider_code <> :code:')
                ->bind(['code' => $excludeProviderCode]);
        }
        $provider = $query
            ->orderBy('priority DESC')
            ->execute()
            ->toArray();
        if(empty($provider) || count($provider) <= 0){
            return null;
        }
        return $provider;
    }
    /**
     * 获取指定编号的模板
     * @param $templateCode
     * @return BaiyangSmsTemplate|null
     */
    public function getSmsTemplate($templateCode)
    {
        $template = BaiyangSmsTemplate::findFirst([
           'template_code = :code:',
            'bind' => ['code' => $templateCode]
        ]);

        return $template;
    }

    /**
     * 获取短信补发可用的短信服务商信息
     * @param string $msgId 消息ID
     * @param string $phone 手机号
     * @return null
     */
    public function getRepeatSmsProvider($msgId, $phone)
    {
        $record = $this->getSmsRecord($msgId,$phone);
        if(empty($record)){
            return null;
        }
        $providerCode = $record->provider_code;
        if(empty($providerCode)){
            return null;
        }
        if(isset($record->send_type) && $record->send_type == 0){
            $providers =  BaiyangSmsProvider::query()
                ->where('provider_state = 0')
                ->andWhere('remainder_quantity > 0')
                ->andWhere('provider_code <> :code:')
                ->bind(['code' => $providerCode])
                ->orderBy('priority DESC')
                ->execute()
                ->toArray();
            if(empty($providers) === false){
                return $providers[0];
            }
        }
        return null;
    }

    /**
     * 根据消息ID和手机号查询短信发送记录
     * @param $msgId
     * @param $phone
     * @return null|BaiyangSmsRecords
     */
    public function getSmsRecord($msgId, $phone)
    {
        $record = BaiyangSmsRecords::findFirst([
           'msg_id = :msg_id: AND phone = :phone:',
            'bind' => ['msg_id' => $msgId, 'phone' => $phone]
        ]);
        return $record;
    }

    /**
     * 更新发送记录
     * @param array $params
     * @return bool
     */
    public function updateRecordByMsgId(array $params)
    {
        if(empty($params['msg_id']) || empty($params['phone'])){
            return false;
        }
        $record = BaiyangSmsRecords::findFirst([
            'msg_id = :msg_id: AND phone = :phone:',
            'bind' => ['msg_id' => $params['msg_id'], 'phone' => $params['phone']]
        ]);

        if($record !== null && empty($record) === false){
            if(isset($params['raw']) && empty($params['raw']) === false) {
                if(is_object($params['raw']) || is_array($params['raw'])) {
                    $record->provider_push_result = json_encode($params['raw'], JSON_UNESCAPED_UNICODE);
                }else{
                    $record->provider_push_result = $params['raw'];
                }
            }
            if(isset($params['errcode'])){
                $record->is_success = $params['errcode'];
            }
            $record->modify_at = 0;
            $record->modify_time = date('Y-m-d H:i:s');
            return $record->save();
        }
        return false;
    }

    /**
     * 更新短信提供商的发送，剩余数量
     * @param string $providerCode
     * @param int $remainderQuantity 剩余数量
     * @param int $sendQuantity 已发数量
     * @param null $reissueQuantity 补发数量
     * @return bool
     */
    public function updateQuantity($providerCode, $remainderQuantity = -1, $sendQuantity = 1, $reissueQuantity = null)
    {
        $provider = BaiyangSmsProvider::findFirst([
            'provider_code = :code:',
            'bind' => ['code' => $providerCode]
        ]);

        if(empty($provider) === false){
            if($remainderQuantity !== null){
                $provider->remainder_quantity = intval($provider->remainder_quantity) + $remainderQuantity;
            }

            if($sendQuantity !== null){
                $provider->send_quantity = intval($provider->send_quantity) + $sendQuantity;
            }
            if($reissueQuantity !== null){
                $provider->reissue_quantity = intval($provider->reissue_quantity) + $reissueQuantity;
            }
            return $provider->save();
        }
        return false;
    }

    /**
     * 自动启用或禁用指定场景的验证码
     * @param string $client_code
     * @param string $template_code
     * @param int $state
     * @return bool
     */
    public function enableCaptchaForTemplate($client_code,$template_code,$state = 0)
    {
        $client = BaiyangSmsClient::findFirst([
            'client_code = :code:',
            'bind' => ['code' => $client_code]
        ]);

        //如果该客户端不存在
        if(empty($client)){
            return false;
        }

        $template = BaiyangSmsTemplate::findFirst([
            'template_code = :code:',
            'bind' => ['code' => $template_code]
        ]);
        //如果短信场景不存在
        if(empty($template)){
            return false;
        }
        $relationship = BaiyangSmsRelationship::findFirst([
            'template_id = :id: AND client_id = :client_id:',
            'bind' => ['id' => $template->template_id, 'client_id' => $client->client_id]
        ]);
        //如果没有启用客户端
        if(empty($relationship)){
            $relationship = new BaiyangSmsRelationship();
            $relationship->client_id = $client->client_id;
            $relationship->template_id = $template->template_id;
            $relationship->create_at = 0;
            $relationship->create_time = date('Y-m-d H:i:s');
            $relationship->modify_time = date('Y-m-d H:i:s');
            $relationship->modify_at = 0;
            $relationship->is_enable_captcha = $state;
            $relationship->is_enable_client = SmsTemplateStateEnum::AUTO_ENABLE;
        }elseif ($relationship->is_enable_captcha == SmsTemplateStateEnum::MANUAL_DISABLED || $relationship->is_enable_captcha == SmsTemplateStateEnum::MANUAL_ENABLE){
            return true;
        }else{
            $relationship->modify_at = 0;
            $relationship->modify_time = date('Y-m-d H:i:s');
        }

        $relationship->is_enable_captcha = intval($state);

        return $relationship->save();
    }

    /**
     * 自动启用或禁用指定场景的某个客户端
     * @param string $client_code
     * @param string $template_code
     * @param int $state
     * @return bool
     */
    public function enableClientForTemplate($client_code,$template_code,$state = 0)
    {
        $client = BaiyangSmsClient::findFirst([
            'client_code = :code:',
            'bind' => ['code' => $client_code]
        ]);

        //如果该客户端不存在
        if (empty($client)) {
            return false;
        }

        $template = BaiyangSmsTemplate::findFirst([
            'template_code = :code:',
            'bind' => ['code' => $template_code]
        ]);
        //如果短信场景不存在
        if (empty($template)) {
            return false;
        }
        $relationship = BaiyangSmsRelationship::findFirst([
            'template_id = :id: AND client_id = :client_id:',
            'bind' => ['id' => $template->template_id, 'client_id' => $client->client_id]
        ]);

        //如果没有启用客户端
        if (empty($relationship)) {
            $relationship = new BaiyangSmsRelationship();
            $relationship->client_id = $client->client_id;
            $relationship->template_id = $template->template_id;
            $relationship->create_at = 0;
            $relationship->create_time = date('Y-m-d H:i:s');
            $relationship->modify_time = date('Y-m-d H:i:s');
            $relationship->modify_at = 0;
            $relationship->is_enable_client = intval($state);
            $relationship->is_enable_captcha = SmsTemplateStateEnum::AUTO_DISABLED;

        } elseif ($relationship->is_enable_client == SmsTemplateStateEnum::MANUAL_DISABLED ) {
            return true;
        } else {
            $relationship->modify_at = 0;
            $relationship->modify_time = date('Y-m-d H:i:s');
        }

        $relationship->is_enable_client = intval($state);
        $result = $relationship->save();

        return $result;
    }
    /**
     * 获取指定场景和客户端的关系
     * @param string $client_code
     * @param string $template_code
     * @return bool|BaiyangSmsRelationship
     */
    public function getRelationship($client_code,$template_code)
    {
        $client = BaiyangSmsClient::findFirst([
            'client_code = :code:',
            'bind' => ['code' => $client_code]
        ]);

        //如果该客户端不存在
        if(empty($client)){
            return false;
        }

        $template = BaiyangSmsTemplate::findFirst([
            'template_code = :code:',
            'bind' => ['code' => $template_code]
        ]);
        //如果短信场景不存在
        if(empty($template)){
            return false;
        }
        $relationship = BaiyangSmsRelationship::findFirst([
            'template_id = :id: AND client_id = :client_id:',
            'bind' => ['id' => $template->template_id, 'client_id' => $client->client_id]
        ]);
        return $relationship;
    }

    /**
     * 查询指定场景和客户端是否启用了验证码
     * @param string $client_code
     * @param string $template_code
     * @param bool $update_cache
     * @return bool|int 0 手动启用/1手动禁用 / 2 自动启用 / 3 自动禁用
     */
    public function isEnableCaptcha($client_code, $template_code, $update_cache = false)
    {
        if(!$update_cache && $this->redis){
            $key = md5($client_code.'.'.$template_code);
            $cacheResult = json_decode($this->redis->hGet('sms.relationship',$key));
            if($cacheResult !== false){
                return $cacheResult->is_enable_captcha;
            }
        }
        $client = BaiyangSmsClient::findFirst([
            'client_code = :code:',
            'bind' => ['code' => $client_code]
        ]);

        //如果该客户端不存在
        if(empty($client)){
            return false;
        }

        $template = BaiyangSmsTemplate::findFirst([
            'template_code = :code:',
            'bind' => ['code' => $template_code]
        ]);
        //如果短信场景不存在
        if(empty($template)){
            return false;
        }
        $relationship = BaiyangSmsRelationship::findFirst([
            'template_id = :id: AND client_id = :client_id:',
            'bind' => ['id' => $template->template_id, 'client_id' => $client->client_id]
        ]);

        if(empty($relationship)){
            return false;
        }
        if($this->redis){
            $key = md5($client_code.'.'.$template_code);
            $this->redis->hSet('sms.relationship',$key,$relationship);
        }
        return $relationship->is_enable_captcha;
    }

    /**
     * 查询指定场景和客户端是否启用了短信通道
     * @param string $client_code
     * @param string $template_code
     * @param bool $update_cache
     * @return bool|int 0 手动启用/1手动禁用 / 2 自动启用 / 3 自动禁用
     */
    public function isEnableClient($client_code, $template_code, $update_cache = false)
    {
        if(!$update_cache && $this->redis){
            $key = md5($client_code.'.'.$template_code);
            $cacheResult = json_decode($this->redis->hGet('sms.relationship',$key));
            if($cacheResult !== false && isset($cacheResult->is_enable_client)){
                return $cacheResult->is_enable_client;
            }
        }
        $client = BaiyangSmsClient::findFirst([
            'client_code = :code:',
            'bind' => ['code' => $client_code]
        ]);

        //如果该客户端不存在
        if(empty($client)){
            return false;
        }

        $template = BaiyangSmsTemplate::findFirst([
            'template_code = :code:',
            'bind' => ['code' => $template_code]
        ]);
        //如果短信场景不存在
        if(empty($template)){
            return false;
        }
        $relationship = BaiyangSmsRelationship::findFirst([
            'template_id = :id: AND client_id = :client_id:',
            'bind' => ['id' => $template->template_id, 'client_id' => $client->client_id]
        ]);

        if(empty($relationship)){
            return false;
        }
        if($this->redis){
            $key = md5($client_code.'.'.$template_code);
            $this->redis->hSet('sms.relationship',$key,$relationship);
        }
        return $relationship->is_enable_client;
    }

    /**
     * 添加日志
     * @param array $params
     * @return bool
     */
    public function addLog(array $params)
    {
        $model = new BaiyangSmsLog();
        $model->create_time = date('Y-m-d H:i:s');
        $model->client_ip_address = isset($params['client_ip_address']) ? $params['client_ip_address']: null;
        $model->ip_address = isset($params['ip']) ? $params['ip'] : null;
        $model->client_code = isset($params['client_code']) ? $params['client_code'] : null;
        $model->description = isset($params['description']) ? $params['description'] : json_encode(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT,5),JSON_UNESCAPED_UNICODE);
        $model->original_data = isset($params['original_data']) ? $params['original_data'] : json_encode(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT,5)[0]['args'],JSON_UNESCAPED_UNICODE);
        $model->phone = isset($params['phone']) ? $params['phone'] : null;
        $model->sms_content = isset($params['content']) ? $params['content'] : null;
        $model->session_id = isset($params['session_id']) ? $params['session_id'] : null;
        $model->log_type = isset($params['log_type']) ? $params['log_type'] : 0;
        $model->template_code = isset($params['template_code']) ? $params['template_code'] : null;
        $model->user_agent = isset($params['user_agent']) ? $params['user_agent'] : null;


        return $model->save();

    }

    /**
     * 获取指定时间内发送短信次数最多的场景的客户端
     * @param int|null $time
     * @return array|null
     */
    public function getTemplateAndClientByMinute($time = null)
    {
        $time = $time ?: time();

        //查找一分钟内发送次数最高的场景，并加入缓存黑名单中。
        $record = BaiyangSmsRecords::query()
            ->where('is_success = 0 AND create_time > :time: ')
            ->bind(['time' => date('Y-m-d H:i:s',$time - 60)])
            ->groupBy('template_code,client_code')
            ->orderBy('total_count DESC')
            ->columns(['template_code','client_code','COUNT(0) AS total_count'])
            ->limit(1)
            ->execute()->getFirst();

        if(empty($record) === false) {
            return ['template_code' => $record->template_code, 'client_code' => $record->client_code, 'total_count' => $record->total_count];
        }
        return null;
    }
}
