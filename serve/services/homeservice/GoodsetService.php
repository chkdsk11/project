<?php
/**
 * @desc 套餐
 * @author 邓永军
 */
namespace Shop\Home\Services;

use Shop\Home\Listens\PromotionGoodset;
use Phalcon\Events\Manager as EventsManager;


/**
 * Class GoodsetService
 * @package Shop\Home\Services
 */
class GoodsetService extends BaseService
{
    protected static $instance=null;

    public static function getInstance()
    {
        if(empty(static::$instance)){
            static::$instance = new GoodsetService();
        }
        $eventsManager = new EventsManager();
        $eventsManager->attach('good_set',new PromotionGoodset());
        static::$instance->setEventsManager($eventsManager);
        return static::$instance;
    }

    /**
     * @desc 获取套餐列表
     * @param $param
     *         - [
     *              'goods_id' 商品id列表
     *              'platform' 平台名称 [pc、app、wap]
     *          ]
     * @return mixed
     * @author 邓永军
     */
    public function getGoodSetList($param)
    {
        $list = $this->_eventsManager->fire('good_set:getGoodSetList',$this,$param);
        if(isset($param['format']) && !empty($param['format']) && $param['format'] == 1){
            return $this->uniteReturnResult($list['code'],$list['data']);
        }
        return $list;
    }
}