<?php
namespace Shop\Home\Datas;

class BaiyangTouchMachineOrderData extends BaseData {

    protected static $instance=null;

    /**
     * @DESC   插入触屏机设备号
     * @param  int $param
     * @return int
     * @author 柯琼远
     */
    public function insertMachineSn($param) {
        // 插入触屏机设备号
        if (!empty($param['machineSn'])) {
            $addData = array(
                'table' => '\Shop\Models\BaiyangTouchMachineOrder',
                'bind'  => array(
                    'order_sn' => $param['orderSn'],
                    'is_global' => $param['isGlobal'],
                    'machine_sn' => $param['machineSn'],
                )
            );
            if (!$this->addData($addData)) {
                return false;
            }
        }
        return true;
    }
}