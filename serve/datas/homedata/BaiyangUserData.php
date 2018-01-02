<?php
/**
 * @author 邓永军
 */
namespace Shop\Home\Datas;

use Shop\Models\BaiyangUser;
/**
 * Class BaiyangUserData
 * @package Shop\Home\Datas
 * @todo 写下注释
 */
class BaiyangUserData extends BaseData
{
    protected static $instance=null;

    public static function getInstance($className = __CLASS__){
        return parent::getInstance($className);
    }

    public function getPhone($user_id)
    {
        $phone=$this->getData([
            'column'=>'phone',
            'table'=>'\Shop\Models\BaiyangUser',
            'where'=>'where id = :user_id:',
            'bind'=>[
                'user_id'=>$user_id
            ]
        ],1);
        if(isset($phone) && !empty($phone))return $phone['phone'];
        return '';
    }

    /**
     * 获取用户信息
     *
     * @param int $userId  用户id
     * @return array
     */
    public function getUserInfo($userId, $column = '*')
    {
        $userInfo = $this->getData([
            'column'=> $column,
            'table' => '\Shop\Models\BaiyangUser',
            'where' => 'where id = :user_id:',
            'bind'  => ['user_id'=> (int)$userId]
        ],1);
        return $userInfo;
    }

    /**
     * 获取用户union_id
     *
     * @param int $userId  用户id
     * @return array|bool
     */
    public function getUserUnion_id($userId)
    {
        $ret = $this->getData([
            'column'=>'union_user_id',
            'table'=>'\Shop\Models\BaiyangUser',
            'where'=>'where id = :user_id:',
            'bind'=>[
                'user_id'=>$userId
            ]
        ],1);
        return $ret;
    }

    /**
     * @desc 修改用户信息
     * @param array $param
     *         -string column 修改的字段
     *         -string where 条件
     *         -array bind 修改内容(参数绑定)
     * @return bool true|false 结果信息
     * @author 吴俊华
     */
    public function editUserInfo($param)
    {
        $condition = [
            'table' => '\Shop\Models\BaiyangUser',
            'column' => $param['column'],
            'where' => 'where '.$param['where'],
            'bind' => $param['bind'],
        ];
        return $this->updateData($condition);
    }

    /**
     * 获取用户信息
     *
     * @param int $phone  用户id
     * @return array
     */
    public function getUserInfoByPhone($phone, $column = '*')
    {
        $userInfo = $this->getData([
            'column'=> $column,
            'table' => '\Shop\Models\BaiyangUser',
            'where' => 'where phone = :phone:',
            'bind'  => ['phone'=> $phone]
        ],1);
        return $userInfo;
    }
}