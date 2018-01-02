<?php
/**
 * Created by PhpStorm.
 * User: Chensonglu
 * Date: 2017/5/26
 * Time: 11:16
 */

namespace Shop\Home\Listens;

use Shop\Models\HttpStatus;
use Shop\Libs\ExpressText;

class ExpressListener extends BaseListen
{
    /**
     * 快递公司
     * @param $param
     * @return array
     * @author Chensonglu
     */
    public function expressCompany($event,$class,$param)
    {
        $postid =  isset($param['postid']) ? (string)$param['postid'] : '';
        if(!$postid){
            return ['error' => 1,'code' => HttpStatus::PARAM_ERROR,'data' => $param];
        }
        $url = "http://www.kuaidi100.com/autonumber/autoComNum?text=".$postid;
        $result = @file_get_contents($url);
        $result = json_decode($result,true);
        if($result['auto']){
            $text = ExpressText::getInstance()->expressCode();
            $list =array_slice( $result['auto'],0,5);
            foreach($list as &$row){
                if(isset($text[$row['comCode']])){
                    $row['comCode'] = $text[$row['comCode']];
                }
            }
            return ['error' => 0,'code' => HttpStatus::SUCCESS,'data' => array_column($list,'comCode')];
        }else{
            return ['error' => 1,'code' => HttpStatus::NO_DATA,'data' => []];
        }
    }

    /**
     * 物流进度
     * @param $param
     * @return array
     * @author Chensonglu
     */
    public function getLogistics($event,$class,$param)
    {
        $postid =  isset($param['postid']) ? (string)$param['postid'] : '';
        if(!$postid){
            return ['error' => 1,'code' => HttpStatus::PARAM_ERROR,'data' => $param];
        }
        $url = "http://www.kuaidi100.com/autonumber/autoComNum?text=".$postid;
        $result = @file_get_contents($url);
        $result = json_decode($result,true);

        $data = [];
        if($result['auto']){
            $text = ExpressText::getInstance()->expressCode();
            $company_code = trim($result['auto'][0]['comCode']);
            $url = "http://www.kuaidi100.com/query?type={$company_code}&postid={$param['postid']}&id=1&valicode=&temp=0.".time();

            $res = @file_get_contents($url);

            $res = json_decode($res,true);

            if($res['data']){
                $data['express_company'] = isset($text[$result['auto'][0]['comCode']])
                    ? $text[$result['auto'][0]['comCode']] : '';
                $data['list'] = $res['data'];
            }
        }
        return ['error' => 0,'code' => HttpStatus::SUCCESS,'data' => $data];
    }
}