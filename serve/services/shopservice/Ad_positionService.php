<?php
/**
 * Created by PhpStorm.
 * User: 杨先生
 * Date: 2017/4/27
 * Time: 16:52
 */

namespace Shop\Services;
use Shop\Datas\Ad_positionData;

class Ad_positionService extends BaseService
{
    //获取所有的父级
    public function getAllParent(){
        $field='id,adpositionid_name';
        $where ='parent_id = 0';
        $data = array();
        $result = Ad_positionData::getInstance()->select($field,'\Shop\Models\AdPosition',$data,$where);
        $return = [
            'res' => 'success',
            'data' => $result,
        ];
        return $return;
    }

    public function getPositions(){
        $field='id,adpositionid_name';
        $where ='1=1';
        $data = array();
        $result = Ad_positionData::getInstance()->select($field,'\Shop\Models\AdPosition',$data,$where);
        $return = [
            'res' => 'success',
            'data' => $result,
        ];
        return $return;
    }
    //获取所有的子级
    public function getAllChild($id = 0,$type = 'pid',$act = true,$enable = false)
    {
        //查询条件
        $table = '\Shop\Models\AdPosition';
        $where = ' parent_id=:id:';
        $data['id'] = (int)$id;
        $field = 'id,adpositionid_name';
        $result = Ad_positionData::getInstance()->select($field,$table,$data,$where);
        return $this->arrayData('','',$result,'success');
    }


}