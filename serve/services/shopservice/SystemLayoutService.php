<?php
/**
 * Created by PhpStorm.
 * User: CSL
 * Date: 2017/12/28
 * Time: 10:56
 */

namespace Shop\Services;

use Shop\Datas\BaseData;

class SystemLayoutService extends BaseService
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance=null;

    public $channelName = [
        '1' => '移动端',//含IOS、Android、WAP
        '95' => 'PC',
        '85' => '微商城',
        '89' => 'IOS',
        '90' => 'Android',
        '91' => 'WAP',
    ];

    /**
     * 获取所有支付方式
     * @return array|bool
     * @author CSL
     * @date 2017-12-29
     */
    public function getAllPayment()
    {
         $result = BaseData::getInstance()->getData([
            'column' => '*',
            'table' => 'Shop\Models\BaiyangPayment',
             'order' => 'ORDER BY channel DESC,sort ASC'
        ]);
        if (!$result){
            return false;
        }
        $payment = [];
        foreach ($result as $value) {
            if ($value['other_guide']) {
                $other_guide = json_decode($value['other_guide'], true);
                $value['other_guide'] = $other_guide['except_good_id'];
            }
            if ($value['alias'] === 'cash') {
                $payment[$value['channel']]['cash'] = $value;
            } else {
                $payment[$value['channel']]['line'][] = $value;
            }
        }
        unset($result);
        return $payment;
    }

    /**
     * 修改支付方式
     * @param $param
     *             - pc_cash int PC货到付款ID
     *             - pc_Alipay int PC支付宝ID
     *             - pc_WeChat int PC微信支付ID
     *             - pc_Unionpay int PC银联支付ID
     *             - mobile_cash int 移动端货到付款ID
     *             - mobile_Alipay int 移动端支付宝ID
     *             - mobile_WeChat int 移动端微信支付ID
     *             - mobile_Unionpay int 移动端银联支付ID
     *             - pc_online_payment int PC在线支付选择 1
     *             - mobile_online_payment int 移动端在线支付选择 1
     *             - pc_other_guide string PC货到付款排除商品
     *             - mobile_other_guide string 移动端货到付款排除商品
     * @return array
     * @author CSL
     * @date 2017-12-29
     */
    public function editPayment($param)
    {
        //所有支付方式更新为禁用
        $upData[] = [
            'set' => 'status = 0',
            'where' => '',
        ];
        $key = [];
        foreach ($this->channelName as $id => $name) {
            $key[] = 'cash_' . $id;
            $key[] = 'Alipay_' . $id;
            $key[] = 'WeChat_' . $id;
            $key[] = 'Unionpay_' . $id;
            //验证在线支付选择后，具体支付方式是否有选择
            if (isset($param['online_payment_' . $id]) && !isset($param['Alipay_' . $id]) && !isset($param['WeChat_' . $id]) && !isset($param['Unionpay_' . $id])) {
                return $this->arrayData('请选择一种 ' . $name . ' 在线支付方式','',$param,'error');
            }
            //更新货到付款排除商品
            if (isset($param['other_guide_' . $id])) {
                $other_guide = $this->verify_except_list($param['other_guide_' . $id], ' ' . $name . ' 支付控制');
                if (isset($other_guide['status'])) {
                    return $other_guide;
                }
                $other_guide = $other_guide ? json_encode(['except_good_id' => $other_guide]) : '';
                $upData[] = [
                    'set' => "other_guide = '{$other_guide}'",
                    'where' => "alias = 'cash' and channel = {$id}",
                ];
            }
        }
        //已选择的支付方式更新为启用
        $param = array_intersect_key($param, array_flip($key));
        if ($param) {
            $upData[] = [
                'set' => 'status = 1',
                'where' => 'id in ('.implode(',', array_values($param)).')',
            ];
        }
        // 开启事务
        $this->dbWrite->begin();
        $baseData = BaseData::getInstance();
        $isUp = true;
        //循环执行更新语句
        foreach ($upData as $data) {
            $result[] = $baseData->nativeUpdate($data['set'], 'baiyang_payment', $data['where']);
            if (!$result) {
                $isUp = false;
                break;
            }
        }
        if (!$isUp) {
            //执行sql失败回滚
            $this->dbWrite->rollback();
            return $this->arrayData('配置失败','','','error');
        }
        //提交事务
        $this->dbWrite->commit();
        return $this->arrayData('配置成功');
    }

    /**
     * 验证排除商品ID
     * @param $data string 商品ID
     * @param $msg string 错误时返回信息的一部分
     * @return array|bool|string
     * @author CSL
     * @date 2018-01-02
     */
    private function verify_except_list($data, $msg)
    {
        if (!$data) {
            return false;
        }
        $data = explode(',', $data);
        //去除重复ID
        $data = array_unique($data);
        $old_except_count = count($data);
        //验证纯数据
        $data = array_filter($data,'ctype_digit');
        $new_except_count = count($data);
        if ($old_except_count != $new_except_count) {
            return $this->arrayData("请正确输入{$msg}排除商品的ID", '', '', 'error');
        }
        return implode(',', $data);
    }
}