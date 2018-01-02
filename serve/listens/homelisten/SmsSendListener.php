<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2016/12/21 0021
 * Time: 11:24
 */

namespace Shop\Home\Listens;

use Phalcon\Di;
use Shop\Home\Datas\BaiyangSmsData;
use Shop\Models\BaiyangSmsProvider;
use Shop\Models\BaiyangSmsRecords;

class SmsSendListener extends BaseListen
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

    protected function updateBalanceFromProvider()
    {
        $key = 'sms_balance.count';
        if($this->cache && !$this->cache->get($key)){

        }
    }
    /**
     * 短信发送之前触发
     * @param $event
     * @param $class
     * @param $params
     */
    public function beforeSend($event,$class,$params)
    {

    }

    /**
     * 短信发送之后
     * @param $event
     * @param $class
     * @param $params
     * @return bool
     */
    public function afterSend($event,$class,$params)
    {
        if(!isset($params['errcode']) ||
            !isset($params['provider_code']) ||
            !isset($params['phone']) ||
            !isset($params['content']) ||
            !isset($params['client_code'])
        ){
            return false;
        }
        $record = new BaiyangSmsRecords();
        $record->provider_code = $params['provider_code'];
        $record->phone = $params['phone'];
        $record->content = $params['content'];
        $record->client_code = $params['client_code'];
        $record->ip_address = isset($params['ip']) ? $params['ip'] : null;
        $record->session_id = isset($params['session_id']) ? $params['session_id'] : null;
        $record->user_agent = isset($params['user_agent']) ? $params['user_agent'] : null;
        $record->msg_id = isset($params['msg_id']) ? $params['msg_id'] : null;
        $record->provider_result = isset($params['raw']) ? $params['raw'] : null;
        $record->is_success = $params['errcode'] == 0 ? 0 : 1;
        $record->send_type = $params['send_type'];
        $record->create_time = date('Y-m-d H:i:s');
        $record->create_at = isset($params['user_id']) ? $params['user_id'] : 0;
        $record->template_code = $params['template_code'];
        $record->remark = isset($params['remark']) ? $params['remark'] : null;

        $result =  $record->create();
        if($result === false){
            $messages = $record->getMessages();

            foreach ($messages as $message) {
                //echo $message, "\n";
            }
        }

        //  $parameter['description'] = "客户端 {$record->client_code} ，场景 {$record->template_code} 发送了短信，结果： ". boolval($record->is_success);
        if(isset($params['description'])){
            $parameter['description'] = $params['description'];
        }
        $parameter['original_data'] = json_encode($record);
        $parameter = array_merge($params,$parameter);
        BaiyangSmsData::getInstance()->addLog($parameter);

        //如果发送成功
        if($params['errcode'] == 0){
            if($params['send_type'] == 0){
                BaiyangSmsData::getInstance()->updateQuantity($params['provider_code'],-1,1);
            }elseif($params['send_type'] == 1){
                BaiyangSmsData::getInstance()->updateQuantity($params['provider_code'],-1,1,1);
            }

        }

        if(isset($params['balance'])){
            $provider = BaiyangSmsProvider::findFirst([
                'provider_code = :code:',
                'bind' => ['code' => $params['provider_code']]
            ]);
            $provider->remainder_quantity = intval($params['balance']);
            $provider->save();
        }
        return $result;
    }

}