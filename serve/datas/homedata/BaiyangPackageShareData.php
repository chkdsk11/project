<?php
/**
 * Created by PhpStorm.
 * User: 梁育权
 * Date: 2017/06/20
 */
namespace Shop\Home\Datas;

class BaiyangPackageShareData extends BaseData
{
    protected static $instance=null;

    public static function getInstance($className = __CLASS__){
        return parent::getInstance($className);
    }

    /**
     * @desc 插入领取记录
     * @param $param
     * @return bool
     */
    public function insertPackageShare($param){
        $addData = array(
            'table' => '\Shop\Models\BaiyangPackageShare',
            'bind'  => array(
                'package_id'            => isset($param['package_id'])?(int)$param['package_id']:0,
                'share_user_id'         => isset($param['user_id'])?(int)$param['user_id']:0,
                'package_total_number' => isset($param['package_total_number'])?(int)$param['package_total_number']:0,
                'share_time'    =>  time()
            )
        );
        $result = $this->addData($addData,true);
        if (!$result) {
            return false;
        }
        return $result;
    }
}