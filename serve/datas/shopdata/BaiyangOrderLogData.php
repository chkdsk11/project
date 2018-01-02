<?php
/**
 * User: Chensonglu
 */
namespace Shop\Datas;

use Shop\Models\BaiyangOrderLog;

class BaiyangOrderLogData extends BaseData
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance=null;

    /**
     * 添加订单LOG
     * @param array $param 订单信息
     * @return bool|string
     * @author Chensonglu
     */
    public function addOrderLog($param)
    {
        if (!$param || !isset($param['order_sn']) || !$param['order_sn']) {
            return false;
        }
        $data = [
            'order_sn' => $param['order_sn'],
            'log_time' => time(),
            'log_content' => serialize($param),
            'pcname'      => php_uname(),
            'ipname'      => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
            'user_id' => $this->session->get('admin_id'),
        ];
        return $this->insert('Shop\Models\BaiyangOrderLog',$data);
    }
}