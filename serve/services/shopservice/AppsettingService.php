<?php
/**
 * Created by PhpStorm.
 * User: 杨先生
 * Date: 2017/4/26
 * Time: 15:02
 */

namespace Shop\Services;
use Shop\Datas\AppsettingData;

class AppsettingService extends BaseService
{
    public $data;
    public $table;

    public function __construct()
    {
        $this->data =AppsettingData::getInstance();
        $this->table = '\Shop\Models\BaiyangConfig';
    }

    //获取海外购配置信息
    public function getaccesoriesconf(){
        $where="WHERE config_sign='displayAPPaccesories' ";
        $field='*';
       return  $this->data->getData([
                    'column'=>$field,
                    'table'=>$this->table,
                    'where'=>$where,
                    ],true);
    }
    //获取订单配置信息
    public function getorderconf(){
        $where="WHERE config_sign IN('order_audit','order_no_audit_goods_type','order_auto_audit_pass_time','product_ids')";
        $field='config_sign,config_value';
        return  $this->data->getData([
            'column'=>$field,
            'table'=>$this->table,
            'where'=>$where,
        ]);
    }

    public function getdata($id){
        $result = $this->data->getData([
            'column'=>'*',
            'table'=>$this->table,
            'where'=>'WHERE versions_id='.$id,
        ],true);
        return $result;
    }

    public function updateOrderConf($data){
        if(!$data){
            return $this->arrayData('修改失败！', '', '', 'error');
        }
        $columStr = "config_value=:config_value:";
        if($data['order_audit']!=''){
            $this->data->update($columStr,$this->table,['config_value'=>$data['order_audit']], "config_sign ='order_audit'");
        }
        if($data['order_no_audit_goods_type']){
            $this->data->update($columStr,$this->table,['config_value'=>implode(',',$data['order_no_audit_goods_type'])], "config_sign ='order_no_audit_goods_type'");
        }
        if($data['order_auto_audit_pass_time']!=''){
            $time = $data['order_auto_audit_pass_time'];
            if($data['time_unit'] =='i'){ $time = $time *60; }
            if($data['time_unit'] =='h'){ $time = $time *3600; }
            if($data['time_unit'] =='d'){ $time = $time *86400; }
            $this->data->update($columStr,$this->table,['config_value'=>$time], "config_sign ='order_auto_audit_pass_time'");
        }
        if($data['product_ids']){
            $this->data->update($columStr,$this->table,['config_value'=>$data['product_ids']], "config_sign ='product_ids'");
        }

        return $this->arrayData('修改成功！');
    }
    //修改
    public function editData($data){
        if(!$data){
            return $this->arrayData('修改失败！', '', '', 'error');
        }
        if(!$data['config_sign']){
            return $this->arrayData('修改失败！', '', '', 'error');
        }
        $where = "config_sign ='{$data['config_sign']}'";
        $columStr = '';
        if($data['config_value'] != '') {
            $columStr .= "config_value=:config_value:";
            $conditions['config_value'] = $data['config_value'];
        }
//        var_dump($conditions['config_value']);die;
        if($this->data->update($columStr,$this->table,$conditions, $where)){
            return $this->arrayData('修改成功！');
        }
        return $this->arrayData('修改失败！', '', '', 'error');
    }


}