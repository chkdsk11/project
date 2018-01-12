<?php
/**
 * 支付方式控制
 * User: CSL
 */
namespace Shop\Home\Datas;


class BaiyangPaymentData extends BaseData
{
    protected static $instance = null;
    /**
     * 单例
     * @return static
     */
    public static function getInstance()
    {
        if(empty(static::$instance)){
            static::$instance=new static();
        }
        return static::$instance;
    }

    /**
     * 根据端口号检查对应货到付款方式
     * @param $param
     *              - channel_subid int 端口号
     *              - goodsList array 商品信息
     * @return bool
     * @author CSL
     * @date 2018-01-02
     */
    public function checkCashOnDelivery($param)
    {
        if (!isset($param['channel_subid'])) {
            return false;
        }
        $result = $this->getData([
            'column' => '*',
            'table' => 'Shop\Models\BaiyangPayment',
            'where' => 'WHERE alias = :alias: AND channel = :channel: AND status = :status:',
            'bind' => [
                'alias' => 'cash',
                'channel' => in_array($param['channel_subid'],[85,89,90,91,95]) ? $param['channel_subid'] : 1,
                'status' => 1,
            ]
        ], true);
        if ($result) {
            $other_guide = $result['other_guide'] ? json_decode($result['other_guide'], true) : [];
            if (!isset($other_guide['except_good_id'])) {
                //货到付款没有排除商品ID
                return true;
            } elseif (isset($other_guide['except_good_id']) && isset($param['goodsList']) && $param['goodsList']) {
                //货到付款排除商品处理
                $except_good_id = array_flip(explode(',', $other_guide['except_good_id']));
                $ifFacePay = true;
                foreach ($param['goodsList'] as $goods) {
                    if (!isset($goods['goods_id']) && isset($goods['groupGoodsList'])) {
                        foreach ($goods['groupGoodsList'] as $val) {
                            if (isset($except_good_id[$val['goods_id']])) {
                                $ifFacePay = false;
                                break 2;
                            }
                        }
                    } else {
                        if (isset($except_good_id[$goods['goods_id']])) {
                            $ifFacePay = false;
                            break;
                        }
                    }
                }
                return $ifFacePay;
            }
        }
        return false;
    }

    /**
     * 根据端口号获取对应在线支付方式
     * @param $param
     * @return array|bool
     * @author CSL
     * @date 2018-01-02
     */
    public function getOnlinePayment($param)
    {
        if (!isset($param['channel_subid'])) {
            return false;
        }

        $result = $this->getData([
            'column' => '*',
            'table' => 'Shop\Models\BaiyangPayment',
            'where' => "WHERE alias <> 'cash' AND channel = :channel: AND status = :status:",
            'order' => 'ORDER BY sort ASC',
            'bind' => [
                'channel' => $param['channel_subid'],
                'status' => 1,
            ]
        ]);
        if (!$result && in_array($param['channel_subid'], [89,90,91])) {
            $result = $this->getData([
                'column' => '*',
                'table' => 'Shop\Models\BaiyangPayment',
                'where' => "WHERE alias <> 'cash' AND channel = :channel: AND status = :status:",
                'order' => 'ORDER BY sort ASC',
                'bind' => [
                    'channel' => 1,
                    'status' => 1,
                ]
            ]);
        }
        return $result;
    }
}