<?php
/**
 * @author 秦亮
 */
namespace Shop\Home\Datas;
class BaiyangOrderGoodsReturnReasonData extends BaseData
{
    protected static $instance=null;

    /**
     * 查询退款信息
     * @param array $param
     *       string service_sn 服务单号
     * @return array
     * @author 秦亮
     */
    public function getOrderGoodsReturnReason($param, $returnOne = false)
    {
        $reasonCondition = [
            'table' => '\Shop\Models\BaiyangOrderGoodsReturnReason',
            'column' => 'id,user_id,order_sn,return_type,reason,status,refund_amount,real_amount,'.
                        'service_sn,pay_fee,express_no,express_company,shop_name,return_way,explain,add_time',
            'bind' => [
                'service_sn' => (string)$param['service_sn'],
            ],
            'where' => 'where service_sn = :service_sn:',
        ];
        return $this->getData($reasonCondition, $returnOne);
    }
    
    
    /**
     * 获取下单用户来源
     * @param array $param
     *       string service_sn 服务单号
     *       int user_id 用户ID
     * @return array
     * @author 秦亮
     */
    public function getUserPlatform($param, $returnOne = false)
    {
        $platformCondition = [
            'table' => '\Shop\Models\BaiyangOrderGoodsReturnReason r',
            'join' => 'left join \Shop\Models\BaiyangOrder o on r.order_sn = o.order_sn',
            'column' => 'o.more_platform_sign',
            'bind' => [
                'user_id'    => (int)$param['user_id'],
                'service_sn' => (string)$param['service_sn'],
            ],
            'where' => 'where r.user_id = :user_id: and r.service_sn = :service_sn:'
        ];
    
        return $this->getData($platformCondition, $returnOne);
    }
    
}