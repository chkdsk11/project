<?php
/**
 * @author 邓永军
 * @desc 用户表数据
 */
namespace Shop\Datas;
use Shop\Datas\BaseData;
use Shop\Models\BaiyangUser;
class BaiyangUserData extends BaseData
{
    protected static $instance = null;

    /**
     * @desc 通过手机号码获取用户id
     * @param $phone
     * @return bool
     * @author 邓永军
     */
    public function findUserIdByPhone($phone)
    {
        $info = BaseData::getInstance()->getData([
            'table' => 'Shop\Models\BaiyangUser',
            'column' => 'id',
            'where' => 'where phone = :phone:',
            'bind' => [
                'phone' => $phone
            ]
        ],1);
        if(!empty($info)){
            return $info['id'];
        }
        return false;
    }

    /**
     * 通过用户ID获取用户手机号等信息
     * @param $id
     * @return bool
     * @author yb
     */
    public function findPhoneByUserId($id){
        $info = BaseData::getInstance()->getData([
            'table' => 'Shop\Models\BaiyangUser',
            'column' => 'phone',
            'where' => 'where id = :id:',
            'bind' => [
                'id' => $id
            ]
        ],1);
        if(!empty($info)){
            return $info;
        }
        return false;
    }

}