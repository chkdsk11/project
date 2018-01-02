<?php
/**
 * Created by PhpStorm.
 * User: 吴俊华
 * Date: 2016/10/12 0012
 * Time: 上午 11:35
 */

namespace Shop\Home\Services;
use Shop\Home\Datas\BaiyangSkuData;
use Shop\Home\Datas\BaseData;
use Shop\Home\Listens\PromotionLimitBuy;
use Shop\Home\Listens\PromotionGetGoodsDiscountPrice;
use Shop\Home\Listens\PromotionCalculate;
use Shop\Home\Listens\PromotionCoupon;
use Shop\Home\Listens\PromotionGoodsDetail;
use Shop\Home\Listens\PromotionShopping;
use Phalcon\Events\Manager as EventsManager;
//use Phalcon\Events\Event;
use Shop\Home\Datas\BaiyangGoodsStockChangeLogData;
use Shop\Models\HttpStatus;
use Shop\Home\Datas\BaiyangGoodsShoppingCart;
use Shop\Home\Datas\BaiyangPromotionData;

class PromotionService extends BaseService
{
    protected static $instance = null;

    /**
     * @desc 重写实例化方法
     * @return class
     * @author 吴俊华
     */
    public static function getInstance()
    {
        if(empty(static::$instance)){
            static::$instance = new PromotionService();
        }
        $eventsManager = new EventsManager();
        $eventsManager->attach('promotion',new PromotionLimitBuy());
        $eventsManager->attach('promotion',new PromotionGetGoodsDiscountPrice());
        $eventsManager->attach('promotion',new PromotionCalculate());
        $eventsManager->attach('promotion',new PromotionCoupon());
        $eventsManager->attach('promotionInfo',new PromotionGoodsDetail());
        $eventsManager->attach('promotion',new PromotionShopping());
        static::$instance->setEventsManager($eventsManager);
        return static::$instance;
    }

    public function test($param)
    {
        return 111;
        $result = $this->_eventsManager->fire('promotionInfo:getPromotionInfoByGoodsId',$this,$param);
        return $this->uniteReturnResult($result['code'],$result['data']);
    }

    /**
     * @desc 根据商品id获取促销活动的信息 【商品详情】
     * @param array $param
     *       -int goods_id 商品id
     *       -string platform 平台【pc、app、wap】
     * @return array $result 商品详情的促销活动
     * @author 吴俊华
     */
    public function getPromotionInfoByGoodsId($param)
    {
//        $sql = "select id,`order` from baiyang_test";
//        $stmt = $this->dbRead->prepare($sql);
//        $stmt->execute();
//        $ret = $stmt->fetchall(\PDO::FETCH_ASSOC);
//        $sql     = 'UPDATE baiyang_test SET `order` = 1 WHERE `id` = 8000159';
//        $ret = $this->dbWrite->execute($sql);
//        print_r($ret);die;
        $this->verifyRequiredParam($param);
        $result = $this->_eventsManager->fire('promotionInfo:getPromotionInfoByGoodsId',$this,$param);
        return $this->uniteReturnResult($result['code'],$result['data']);
    }

    /**
     * @desc 根据商品id获取促销活动的标识【商品列表】
     * @param array $param
     *       -int goods_id 商品id
     *       -string platform 平台【pc、app、wap】
     * @return array $result 商品列表的促销活动
     * @author 吴俊华
     */
    public function getGoodsPromotionSign($param)
    {
        $this->verifyRequiredParam($param);
        $result = $this->_eventsManager->fire('promotionInfo:getGoodsPromotionSign',$this,$param);
        return $this->uniteReturnResult($result['code'],$result['data']);
    }

    /**
     * @desc 活动凑单
     * @param array $param
     *       -int promotion_id 商品id
     *       -string platform 平台【pc、app、wap】
     * @return array $result 商品详情的促销活动
     * @author 吴俊华
     */
    public function getPromotionGoodsInfoById($param)
    {
        $this->verifyRequiredParam($param);
        $result = $this->_eventsManager->fire('promotionInfo:getPromotionGoodsInfoById',$this,$param);
        return $this->uniteReturnResult($result['code'],$result['data']);
    }

    /**
     * @desc 改变商品数量时验证库存、疗程、促销活动等信息
     * @param array $param
     *       -int|string goods_id 商品id(多个以逗号隔开)
     *       -int|string goods_number 商品数量(多个以逗号隔开)
     *       -string platform 平台【pc、app、wap】
     * @return array $result 商品详情的促销活动
     * @author 吴俊华
     */
    public function verifyGoodsRelatedInfo($param)
    {
        $this->verifyRequiredParam($param);
        //疗程价
        $goodsTreatmentPrice = $this->getGoodsTreatmentPrice($param);
        if(is_integer($goodsTreatmentPrice)){
            return $this->uniteReturnResult($goodsTreatmentPrice);
        }
        //商品信息
        //$goodsInfo = $this->getGoodsDetail($param);
        //商品库存变化记录
        $goodsStockChange = BaiyangGoodsStockChangeLogData::getInstance()->getGoodsStockChange($param);
        //商品可售库存
        $canSaleStock = $this->func->getCanSaleStock($param);

    }

    /**
     * @desc 测试限购侦听器
     * @param array $param
     *       -int goods_id 商品id
     *       -int goods_number 商品数量
     *       -string platform 平台【pc、app、wap】
     *       -int user_id 用户id (临时用户或真实用户id)
     *       -int is_temp 是否为临时用户 (1为临时用户、0为真实用户)
     * @return array $result 商品限购信息
     * @author 吴俊华
     */
    public function limitBuy($param)
    {
        $result = $this->_eventsManager->fire('promotion:countLimitNumToCart',$this,$param);
        //$result = $this->_eventsManager->fire('promotion:limitBuy',$this,$param);
        return $this->uniteReturnResult($result['code'],$result['data']);
    }

    /**
     * @desc 测试满赠侦听器
     * @param array $param
     * @return array $result
     * @author 吴俊华
     */
    public function fullGift($param)
    {
        $result = $this->_eventsManager->fire('promotion:fullGift',$this,$param);
        return $this->uniteReturnResult($result['code'],$result['data']);
    }

    /**
     * @desc 测试加价购侦听器
     * @param array $param
     * @return array $result
     * @author 吴俊华
     */
    public function increaseBuy($param)
    {
        $result = $this->_eventsManager->fire('promotion:increaseBuy',$this,$param);
        return $this->uniteReturnResult($result['code'],$result['data']);
    }

    /**
     * @desc 测试包邮侦听器
     * @param array $param
     * @return array $result
     * @author 吴俊华
     */
    public function expressFree($param)
    {
        $result = $this->_eventsManager->fire('promotion:expressFree',$this,$param);
        return $this->uniteReturnResult($result['code'],$result['data']);
    }

    /**
     * @desc 测试购物的商品促销活动信息
     * @param array $param
     *       -string platform 平台【pc、app、wap】
     *       -int user_id 用户id (临时用户或真实用户id)
     *       -int is_temp 是否为临时用户 (1为临时用户、0为真实用户)
     * @return array $result
     * @author 吴俊华
     */
    public function shoppingPromotion($param)
    {
        $require = $this->judgeRequireParam($param,'platform');
        if(!empty($require)){
            return $require;
        }
        //获取当前用户的购物车信息
        $shoppingCartItems = BaiyangGoodsShoppingCart::getInstance()->getCartGoodsInfo(['user_id' => $param['user_id'],'is_temp' => $param['is_temp'],'group_id' => 0,'selected' => 1]);
        //进行中的促销活动
        $promotionList = BaiyangPromotionData::getInstance()->getPromotionsInfo(['platform' => $param['platform'], 'user_id' => $param['user_id']]);
        if(empty($promotionList)){
            return ['error' => 0,'code' => HttpStatus::NOT_PROCESSING_PROMOTION,'data' => []];
        }
        //商品信息
        $goodsDetail = $this->getGoodsDetail(['goods_id' => implode(',',array_column($shoppingCartItems,'goods_id')),'platform' => $param['platform'] ]);
        $goodsDetail = $this->filterData('id,category_id,brand_id,sku_price',$goodsDetail);
        $shoppingCartGoods = [];
        //合并购物车和商品信息
        foreach ($goodsDetail as $value) {
            foreach ($shoppingCartItems as $val) {
                if ($value['id'] == $val['goods_id'] && $val['group_id'] == 0) {
                    $shoppingCartGoods[] = [
                        'goods_id' => $val['goods_id'],
                        'category_id' => $value['category_id'],
                        'brand_id' => $value['brand_id'],
                        'group_id' => $val['group_id'],
                        'goods_number' => $val['goods_number'],
                        'is_global' => $val['is_global'],
                    ];
                }
            }
        }

        //商品匹配促销活动
        $goodsPromotion = [];
        foreach($promotionList as $promotionKey => $promotionValue){
            foreach($shoppingCartGoods as $goodsKey => $goodsValue){
                if($this->func->isRelatedGoods($promotionValue,$goodsValue)){
                    if(isset($goodsPromotion[$goodsValue['goods_id']]) && !empty($goodsPromotion[$goodsValue['goods_id']])){
                        $goodsPromotion[$goodsValue['goods_id']]['promotionInfo'][$promotionValue['promotion_id']] = $promotionValue;
                    }else{
                        $goodsPromotion[$goodsValue['goods_id']] = [
                            'goods_id' => $goodsValue['goods_id'],
                            'category_id' => $goodsValue['category_id'],
                            'brand_id' => $goodsValue['brand_id'],
                            'group_id' => $goodsValue['group_id'],
                        'goods_number' => $goodsValue['goods_number'],
                            'is_global' => $goodsValue['is_global'],

                        ];
                        $goodsPromotion[$goodsValue['goods_id']]['promotionInfo'][$promotionValue['promotion_id']] = $promotionValue;
                    }
                }
            }
        }

    }

    /**
     * @desc 获取商品最优惠价(计算对比：疗程、会员价、限时优惠)
     * @param array $param
     *       -string platform 平台【pc、app、wap】
     *       -int user_id 用户id (临时用户或真实用户id)
     *       -int is_temp 是否为临时用户 (1为临时用户、0为真实用户)
     * @return array $result 商品限购信息
     * @author 吴俊华
     */
    public function getGoodsDiscountPrice($param)
    {
        $require = $this->judgeRequireParam($param,'platform');
        if(!empty($require)){
            return $require;
        }
        $param['goodsInfo'] = $param['goodsList'][0];
        $result = $this->_eventsManager->fire('promotion:getGoodsDiscountPrice',$this,$param);
        return $result;
    }

    /**
     * @desc 获取商品限时优惠信息 【测试】
     * @param array $param
     *       -int goods_id 商品id
     *       -double sku_price 商品价格
     * @return array $result 商品限时优惠信息
     * @author 吴俊华
     */
    public function getLimitTimeInfo($param)
    {
        if (!$this->verifyRequiredParam($param)){
            return $this->uniteReturnResult(HttpStatus::SYSTEM_ERROR);
        }
        $result = $this->_eventsManager->fire('promotion:getLimitTimeInfo',$this,$param);
        return $result;
    }

    /**
     * @desc 根据品牌id获取促销活动 【测试】
     * @param array $param
     *       -int brand_id 品牌id
     * @return array $result
     * @author 吴俊华
     */
    public function getPromotionsByBrandId($param)
    {
        if (!$this->verifyRequiredParam($param)){
            return $this->uniteReturnResult(HttpStatus::SYSTEM_ERROR);
        }
        $result = $this->_eventsManager->fire('promotionInfo:getPromotionsByBrandId',$this,$param);
        return $result;
    }

    /**
     * @desc 根据活动类型获取参加该促销活动的商品信息 【测试】
     * @param array $param
     *       -mixed promotion_type 促销活动类型(多个以逗号隔开)
     * @return array $result
     * @author 吴俊华
     */
    public function getPromotionGoodsInfoByType($param)
    {
        if (!$this->verifyRequiredParam($param)){
            return $this->uniteReturnResult(HttpStatus::SYSTEM_ERROR);
        }
        $result = $this->_eventsManager->fire('promotionInfo:getPromotionGoodsInfoByType',$this,$param);
        return $result;
    }

    /**
     * 获取商品限时优惠价格
     *
     * @param $param
     * @return \array[]|mixed
     */
    public function getGoodsLimitOffer($param)
    {
        if(!isset($param['platform']) || !isset($param['goods_id_list']) || !is_array($param['goods_id_list']) || !isset($param['user_id'])) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $limitOfferList = $this->_eventsManager->fire('promotion:getGoodsLimitOfferPrice',$this,$param);
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $limitOfferList);
    }

    public function testOrder($param)
    {
        $condition['table'] = '\Shop\Models\Test';
        $condition['bind'] = $param;
        $phql = "INSERT INTO {$condition['table']} (user_id, order_sn, total_sn) VALUES (604286, 33445, 33445)";
        $res = $this->modelsManager->executeQuery($phql);
        if (is_object($res)) {
            return $res->success();
        }
        return false;
        $res = BaseData::getInstance()->addData($condition);
        return $res;
    }


}