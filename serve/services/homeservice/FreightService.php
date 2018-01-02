<?php
/**
 * @desc 配送运费
 */
namespace Shop\Home\Services;

use Phalcon\Events\Manager as EventsManager;
use Shop\Home\Listens\FreightListener;

class FreightService extends BaseService {

    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance = null;

    // 加载监听器
    public static function getInstance() {
        if(empty(static::$instance)){
            static::$instance = new FreightService();
        }
        $eventsManager = new EventsManager();
        $eventsManager->attach('freight',new FreightListener());
        static::$instance->setEventsManager($eventsManager);
        return static::$instance;
    }

    /**
     * @desc  查看O2O配送信息
     * @param $param array
     *       - consigneeInfo array 收货地址信息(*)
     * @return array
     * @author 柯琼远
     */
    public function getO2OExpressInfo($param) {
        return $this->_eventsManager->fire('freight:getO2OExpressInfo', $this, $param);
    }

    /**
     * @desc 获取O2O配送运费
     * @param array $param
     *       -time            int    配送时间段起始时间（时间戳）（*）
     *       - total          float  订单金额（*）
     *       - consigneeInfo  array  收货地址信息(*)
     * @return array
     * @author 柯琼远
     */
    public function getO2OExpressFee($param) {
        return $this->_eventsManager->fire('freight:getO2OExpressFee', $this, $param);
    }

    /**
     * @desc 获取普通运费
     * @param array $param
     *       -goods_ids string 商品ID字符串，用英文逗号隔开（*）
     *       -region_id   int    type=0 or 1时表示省ID，type=2时表示门店ID（*）
     *       -type      int    0-在线支付，1-货到付款，2-顾客自提，默认：0
     *       -total     float  订单总额（*）
     * @return array
     *       - freight     float  运费
     *       - tips        array  提示语需要的数据
     *              - free_price     float    包邮门槛
     *              - not_free_fee   float    不包邮运费
     *              - lack_price     float    还差多少钱包邮
     * @author 柯琼远
     */
    public function getFreightFee($param) {
        return $this->_eventsManager->fire('freight:getFreightFee', $this, $param);
    }
}