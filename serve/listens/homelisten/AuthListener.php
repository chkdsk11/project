<?php
/**
 * 验证相关
 *
 * Created by PhpStorm.
 * User: Sary
 * Date: 2017/2/21
 * Time: 17:49
 */

namespace Shop\Home\Listens;

use Shop\Home\Datas\BaiyangConsigneeLimitData;
use Shop\Home\Datas\BaiyangUserConsigneeData;
use Shop\Libs\Curl;
use Shop\Libs\Func;
use Shop\Models\HttpStatus;

class AuthListener extends BaseListen
{

    /**
     * 身份证验证
     *
     * @param $event
     * @param $class
     * @param $param
     * @return array
     */
    public function idCardVerify($event,$class,$param)
    {
        if (!isset($param['user_id']) || !isset($param['platform'])
            || !isset($param['idCard']) || !isset($param['username'])) {
            return ['error' => 1,'code' => HttpStatus::PARAM_ERROR,'data' => []];
        }
        $param['idCard'] = (string)$param['idCard'];
        $param['idCard'] = strtoupper($param['idCard']);
        if(!Func::getInstance()->isIdCard($param['idCard'])) {
            return ['error' => 1,'code' => HttpStatus::PARAM_ERROR,'data' => []];
        }
        //检测姓名编码
        $aEncode = array("ASCII", "UTF-8", "GB2312", "GBK", "BIG5");
        $usernameEncode = mb_detect_encoding($param['username'], $aEncode);
        if ($usernameEncode != 'UTF-8') {
            $param['username'] = mb_convert_encoding($param['username'], 'UTF-8', $usernameEncode);
        }
        $consigneeLimitData = BaiyangConsigneeLimitData::getInstance();
        $aUsername = $consigneeLimitData->getConsigneeLimitBuyName($param['idCard']);
        if ($aUsername) {
            if ($aUsername['username'] == $param['username']) {
            	$this->updateUserConsignee($param);
                return ['error' => 0,'code' => HttpStatus::SUCCESS,'data' => []];
            } else {
                return ['error' => 0,'code' => HttpStatus::IDCARD_NAME_ERROR,'data' => []];
            }
        } else {
            $iMaxQueryNumber = $this->config->auth->idcard_max_query_number;
            $aUsernameQuery = $consigneeLimitData->getConsigneeLimitQueryNumber($param['user_id']);
            $data = array(
                'table' => 'Shop\Models\BaiyangConsigneeLimitQuery',
                'where' => 'WHERE user_id=:user_id:',
                'bind' => array('user_id'=>$param['user_id'])
            );
            if ($aUsernameQuery) {
                $queryTime = strtotime($aUsernameQuery['query_time']);
                $nowDate = date('Ymd');
                if ($nowDate == date('Ymd', $queryTime)) {
                    if ($aUsernameQuery['flag']) {
                        return ['error' => 1,'code' => HttpStatus::CRAZY_OPERATE,'data' => []];
                    } else {
                        $isOverQueryNumber = (($aUsernameQuery['spare_chance'] + 1 ) <= $iMaxQueryNumber);
                        $isExpire = ((time() - $queryTime) > 30);
                        if ($isOverQueryNumber && $isExpire)
                        {
                            $data['column'] = 'spare_chance=spare_chance+1';
                            $consigneeLimitData->updateData($data);
                        }elseif(!$isOverQueryNumber){
                            $data['column'] = 'flag=1';
                            $consigneeLimitData->updateData($data);
                            return ['error' => 1,'code' => HttpStatus::CRAZY_OPERATE,'data' => []];
                        }elseif (!$isExpire){
                            return ['error' => 1,'code' => HttpStatus::CRAZY_OPERATE_SOON,'data' => []];
                        }
                    }
                } else {
                    $data['column'] = 'spare_chance=1,flag=0';
                    $consigneeLimitData->updateData($data);
                }
            } else {
                $consigneeLimitData->addData(array(
                    'table' => 'Shop\Models\BaiyangConsigneeLimitQuery',
                    'bind' => array('user_id'=>$param['user_id'],'spare_chance' =>1)
                ));
            }
            $url = 'http://api.avatardata.cn/IdCardCertificate/Verify?key=e14b819588e0489281f4704c978cafa5&realname='.$param['username'].'&idcard='.$param['idCard'];
            $curl = new Curl();
            $result = $curl->api_curl($url);
            $this->log->error("身份验证接口返回信息:::". print_r($result,1));
            $result = json_decode($result, true);
            if (isset($result['result']['code']) && $result['result']['code'] == '1000')
            {
                $consigneeLimitData->addData(array(
                    'table' => 'Shop\Models\BaiyangConsigneeLimitBuy',
                    'bind' => array('card_sn'=>$param['idCard'],'consignee' =>$param['username'])
                ));
                $this->updateUserConsignee($param);
                return ['error' => 0,'code' => HttpStatus::SUCCESS,'data' => []];
            } else {
                return ['error' => 1,'code' => HttpStatus::IDCARD_NAME_ERROR,'data' => []];
            }
        }
    }
	
	private function updateUserConsignee ( array $param = array())
	{
		if (!isset($param['addressId']) || empty($param['addressId'])) {
		    return ['error' => 1,'code' => HttpStatus::PARAM_ERROR,'data' => []];
        }
		$BaiyangUserConsignee = BaiyangUserConsigneeData::getInstance();
		$data = array(
			'table' => 'Shop\Models\BaiyangUserConsignee',
			'column'    =>  "consignee_id = '{$param['idCard']}',identity_confirmed = 1",
			'where' => 'WHERE id=:id: and user_id = :user_id:',
			'bind' => array(
				'id'    =>  $param['addressId'],
				'user_id'   =>  $param['user_id']
			),
		);
		$BaiyangUserConsignee->updateData($data);
	}
}