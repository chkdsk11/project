<?php
/**
 * Created by PhpStorm.
 * User: Sary
 * Date: 2016/12/30
 * Time: 15:46
 */
namespace Shop\Home\Datas;

use Shop\Libs\Func;

class BaiyangMomApplyData extends BaseData
{

    protected static $instance=null;

    /**
     * 校验妈妈申请数据
     *
     * @param array $param
     * @return bool
     */
    public function checkMomApplyData($param)
    {
        if (!isset($param['user_id']) || !ctype_digit($param['user_id']))
        {
            return false;
        }
        if (!isset($param['platform']) || $param['platform'] != 'app')
        {
            return false;
        }
        if (!isset($param['user_name']) || empty($param['user_name'])) {
            return false;
        }
        if (!isset($param['upload_image']) || !is_string($param['upload_image'])) {
            return false;
        }
        if (!isset($param['udid']) || empty($param['udid'])) {
            return false;
        }
        if (!isset($param['mobile_channel']) || empty($param['mobile_channel'])) {
            return false;
        }

        if (!isset($param['download_channel']) || empty($param['download_channel'])) {
            return false;
        }
        $func = Func::getInstance();
        if (!isset($param['birth_time']) || !$func->isTimestamp($param['birth_time'])) {
            return false;
        }
        if (!isset($param['idcard']) || !is_string($param['idcard']) || !$func->isIdCard($param['idcard'])) {
            return false;
        }
        $param['act_id'] = isset($param['act_id']) ? $param['act_id'] : 0;
        return true;
    }

    /**
     * 添加妈妈申请记录
     * @param array $param 申请数据
     * @return bool|string
     */
    public function addMomApply($param)
    {
        $data = array(
            'table' => 'Shop\Models\BaiyangMomApply',
            'bind' => array(
                'user_id' => $param['user_id'],
                'user_name' => $param['user_name'],
                'image' => $param['upload_image'],
                'add_time' => time(),
                'birth_time' => $param['birth_time'],
                'idcard' => $param['idcard'],
                'udid' => $param['udid'],
                'mobile_channel' => $param['mobile_channel'],
                'download_channel' => $param['download_channel'],
                'act_id' => $param['act_id'],
                'reviewer' => '',
                'remark' => ''
            )
        );
        $ret = $this->addData($data, true);
        return $ret;
    }

    /**
     * 获取用户妈妈申请情况
     *
     * @param int $userId 用户id
     * @return array|bool
     */
    public function getMomApplyState($userId)
    {
        $param = array(
            'table' => '\Shop\Models\BaiyangMomApply',
            'column' => 'user_id,birth_time,status state,remark',
            'where' => 'WHERE user_id=:user_id:',
            'order' => " ORDER BY id DESC",
            'limit' => 'LIMIT 1',
            'bind' => array('user_id' => $userId)
        );
        $ret = $this->getData($param, true);
        return $ret;
    }

    /**
     * 获取妈妈申请不唯一数据
     *
     * @param string $idcard 身份证
     * @param string $udid 手机设备ID
     * @return array|bool
     */
    public function getMomApplyNotUniqueData($idcard, $udid)
    {
        $param = array(
            'table' => 'Shop\Models\BaiyangMomApply',
            'column' => 'user_id,idcard,udid',
            'where' => 'WHERE (idcard=:idcard: OR udid=:udid:) AND status IN(0,1)',
            'bind' => array(
                'idcard' => $idcard,
                'udid' => $udid
            )
        );
        $ret = $this->getData($param, true);
        return $ret;
    }

    /**
     * 获取妈妈申请状态语
     *
     * @param array $momApplyState 妈妈申请数据
     * @return array
     */
    public function getMomApplyStateTip($momApplyState)
    {
        $ret = array(
            'apply_tip' => '完善资料免费领取辣妈礼包',
            'apply_state' => '0'
        );
        //apply_state 2待审核,3审核通过,4审核不通过
        if ($momApplyState) {
            if (!$momApplyState['state']) {
                $ret = array(
                    'apply_tip' => '1个工作日内将审核完毕',
                    'apply_state' => '2'
                );
            } elseif ($momApplyState['state'] == 2) {
                $ret = array(
                    'apply_tip' => $momApplyState['remark'],
                    'apply_state' => '4'
                );
            } else {
                $ret = array(
                    'apply_tip' => '',
                    'apply_state' => '3'
                );
            }
        }
        return $ret;
    }

    /**
     * 获取按年月妈妈申请的数量
     *
     * @return array|bool
     */
    public function getMomYearMonthApplyList()
    {
        $param = array(
            'table' => 'Shop\Models\BaiyangMomApply',
            'column' => "FROM_UNIXTIME(birth_time,'%Y-%m') birth_date,count(*) birth_number",
            'where' => 'WHERE status=1 GROUP BY birth_date'
        );
        $ret = $this->getData($param);
        return $ret;
    }

    /**
     * 获取正确的宝宝的出生时间
     *
     * @param int $babyBirthTime 宝宝出生时间
     * @return false|int
     */
    public function getCorrectBabyBirthTime($babyBirthTime) {
        $babyBirthTime = strtotime(date('Y-m-d', strtotime('+1 day', $babyBirthTime))) - 1;
        return $babyBirthTime;
    }

}