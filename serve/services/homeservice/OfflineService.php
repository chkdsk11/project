<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2017/3/23 0023
 * Time: 10:44
 */

namespace Shop\Home\Services;

use Shop\Home\Datas\BaiyangGoodsShoppingOfflineCart;
use Shop\Home\Datas\BaseData;
use Shop\Home\Datas\BaiyangSkuData;
use Shop\Models\BaiyangPromotionEnum;
use Shop\Models\HttpStatus;
use Phalcon\Events\Manager as EventsManager;
use Shop\Home\Listens\BaseListen;
use Shop\Home\Datas\BaiyangUserGoodsPriceTagData;

use Shop\Home\Listens\{
    O2OPromotionCalculate, PromotionGoodsDetail, O2OPromotionShopping,O2OPromotionGoodsDetail
};

use Shop\Home\Listens\PromotionGetGoodsDiscountPrice;
use Shop\Home\Listens\PromotionCalculate;
use Shop\Home\Listens\PromotionLimitBuy;
use Shop\Home\Listens\PromotionShopping;
use Shop\Home\Listens\MomListener;

/**
 * O2O 临时服务接口
 * Class OfflineService
 * @package Shop\Home\Services
 */
class OfflineService extends O2OBaseService
{
    /**
     * @var OfflineService
     */
    protected static $instance = null;

    // 加载监听器
    public static function getInstance() {
        if(empty(static::$instance)){
            static::$instance = new OfflineService();
        }
        $eventsManager = new EventsManager();

       // $eventsManager->attach('promotion',new PromotionLimitBuy());

        //促销计算侦听器(满减、满折、满赠、包邮、加价购)

        $eventsManager->attach('promotion',new O2OPromotionCalculate());
       // $eventsManager->attach('promotion',new PromotionShopping());

       // $eventsManager->attach('promotion',new MomListener());

        $eventsManager->attach('promotionInfo', new O2OPromotionGoodsDetail);

        /********************************O2O促销侦听器*********************************/
        $eventsManager->attach('promotion',new O2OPromotionShopping());

        /********************优惠券计算**************************/
       // $eventsManager->attach('coupon',new OrderFigureCoupon());

        $eventsManager->attach('baseListen',new BaseListen());
        static::$instance->setEventsManager($eventsManager);
        return static::$instance;
    }


    /**
     * @desc 购物车列表
     * @param array $param
     *       -user_id int 用户ID（*）
     *       -platform string 平台：pc,wap,app（*）
     * @return array
     * @author 柯琼远
     */
    public function shoppingCart($param) {

        // 格式化参数
        $userId = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $isTemp = isset($param['is_temp']) && in_array($param['is_temp'], [0, 1]) ? (int)$param['is_temp'] : 0;
        $platform = isset($param['platform']) ? (string)$param['platform'] : "app";
        // 判断参数是否合法
        if ($userId < 1 || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param'=> $param]);
        }
        // 获取购物车商品列表
        $whereParam = ['user_id'=> $userId,'is_temp'=> $isTemp];
        $list = BaiyangGoodsShoppingOfflineCart::getInstance()->getShoppingCart($whereParam);

        if(empty($list) === false) {

            $promotionList = array();



            $promotionObject = new O2OPromotionShopping();

            foreach ($list as $index=>$item){

                $value = $promotionObject->getCartGoodsInfo($item['goods_id']);
                if(empty($value)){
                    continue;
                }
                $value = array_merge($item,$value);
                if ($value['sale'] == 0) {
                    $value['selected'] = 0;//下架商品不能选中
                }
                // 库存不足处理
                if ($value['stock'] <= 0) {
                    $value['goods_number'] = 1;
                    $value['selected'] = 0;//库存不足不能选中
                } elseif ($value['goods_number'] > $value['stock']) {
                    $value['goods_number'] = $value['stock'];
                }
                $value['goods_status'] = $value['sale'] == 0 ? 1 : ($value['goods_number'] > $value['stock'] ? 2 : 0);
//                $value['promotion_price'] = $value['discount_price'] = $value['sku_price'];
//                $value['promotion_total']= $value['discount_total'] = $value['sku_total'] = bcmul($value['goods_number'], $value['sku_price'], 2);


                $value['goodsId'] = $item['goods_id'];
                $value['price'] = $value['sku_price'];
                $value['user_id'] = $userId;
                if(is_array($value['bind_gift']) && count($value['bind_gift']) > 0) {
                    foreach ($value['bind_gift'] as $i=>$gift) {
                        $value['bind_gift'][$i]['goods_number'] = intval($gift['goods_number']) * $value['goods_number'];
                    }
                }

                $promotionList[] = $value;

            }
     
            // 判断用户是否绑定标签
            //$tagSign = BaiyangUserGoodsPriceTagData::getInstance()->isUserPriceTag(['user_id' => $userId, 'is_temp' => $isTemp]);
            $resData = $this->_eventsManager->fire('promotionInfo:getPromotionGoodsPrice', $this, array(
                'platform' => $param['platform'],
                'goodsList' => $promotionList,
                'user_id' => $userId,
                'is_temp' => 0
            ));

            $totalPrice = 0;
            $allSelected = 1;
            $isrx = false;
            $totalQty = 0;
            $selectQty = 0;

            foreach ($resData as $index => &$item){
                if($item['selected'] == 1){
                    $price = bcmul($item['price'],$item['goods_number'],2);
                    $totalPrice = bcadd($price,$totalPrice,2);
                    $selectQty += intval($item['goods_number']);
                }
                $totalQty += intval($item['goods_number']);

                if($allSelected != 0 && $item['selected'] == 0){
                    $allSelected = 0;
                }
                if(!$isrx && $item['drug_type'] == 1){
                    $isrx = true;
                }
                unset($item['promotion_total']);
            }
            $result = array(
                'shoppingCart' => [
                    'moduleList'=>['goodsList' => $resData] ,
                    'cartName' => "青岛百洋健康药房(青岛总店)",
                    'allSelected' => $allSelected,
                    'totalQty' => $totalQty,
                    'selectQty' => $selectQty,
                    'totalPrice' => $totalPrice,
                ],
                'isLogin' =>  $isTemp == 1 ? 0 : 1,
                'allSelected' => $allSelected,
                'rxText' => $isrx ?  "购买处方药需凭医生有效处方，服用请遵循医嘱。有关用药信息请咨询药师。" :"",//处方药文案
                'totalQty' => $totalQty,
                'selectQty' => $selectQty,
                'totalPrice' => $totalPrice,
            );

        }


        $result['isLogin'] = $isTemp == 1 ? 0 : 1;
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $result);
    }

    /**
     * @desc 商品加入购物车
     * @param array $param
     *       -goods_id  int 商品id（*）
     *       -goods_number int 商品数量（*）
     *       -user_id int 用户ID（*）
     *       -is_temp int 是否临时用户（取值：0，1），默认：0
     *       -from string 来源，支持：yfz，mom，baiy，默认：baiy
     *       -platform string 平台：pc,wap,app
     * @return array
     * @author 柯琼远|李飞麟
     */
    public function addGoodsToCart($param) {
        $param['action'] = 'add';
        $result = $this->addOrEditGoodsNumber($param);
        if ($result['status'] == HttpStatus::SUCCESS) {
            $result['data'] = [
                'add_number' => (int)$param['goods_number'],
            ];
        }
        return $result;
    }

    /**
     * @desc 修改购物车商品数量
     * @param array $param
     *       -goods_id  int 商品id（*）
     *       -goods_number int 商品数量（*）
     *       -user_id int 用户ID（*）
     *       -is_temp int 是否临时用户（取值：0，1），默认：0
     *       -platform string 平台：pc,wap,app
     * @return array
     * @author 柯琼远|李飞麟
     */
    public function editCartGoodsNumber($param) {
        $param['action'] = 'edit';
        $result = $this->addOrEditGoodsNumber($param);
        if ($result['status'] == HttpStatus::SUCCESS) {
            return $this->shoppingCart($param);
        }
        return $result;
    }

    /**
     * @desc 删除购物车商品
     * @param array $param
     *       -goods_id  int 商品id（*）
     *       -user_id int 用户ID（*）
     *       -increase_buy  int 加价购活动ID，0表示不是加价购商品，默认0
     *       -is_temp int 是否临时用户（取值：0，1），默认：0
     * @return array
     * @author 柯琼远|李飞麟
     */
    public function removeCartGoods($param) {
        $goodsId = isset($param['goods_id']) ? (int)$param['goods_id'] : 0;
        $increaseBuy = isset($param['increase_buy']) ? (int)$param['increase_buy'] : 0;
        $userId = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $isTemp = isset($param['is_temp']) ? (int)$param['is_temp'] : 0;
        // 判断参数是否合法
        if ($goodsId < 1 || $userId < 1 || !in_array($isTemp, [0,1]) || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param'=> $param]);
        }
        $shoppingCartInstance = BaiyangGoodsShoppingOfflineCart::getInstance();
        // 获取商品的购物车信息
        $paramData = array(
            'user_id'=> $userId,
            'is_temp'=> $isTemp,
            'goods_id'=> $goodsId,
            'group_id'=> 0,
            'increase_buy'=> $increaseBuy
        );
        $shoppingCartInfo = $shoppingCartInstance->getShoppingCart($paramData, true);
        if (empty($shoppingCartInfo)) {
            return $this->uniteReturnResult(HttpStatus::NOT_CART_GOODS, ['param'=> $param]);
        }
        // 辣妈
//        $this->_eventsManager->fire('promotion:deleteMomGiftGetGoods', $this, [
//            'user_id'            => $userId,
//            'goods_id_list'      => array($goodsId),
//        ]);
        // 删除购物车商品
        $ret = $shoppingCartInstance->deleteShoppingCart($paramData);
        if ($ret) {
            return $this->shoppingCart($param);
        } else {
            return $this->uniteReturnResult(HttpStatus::DELETE_ERROR, ['param'=> $param]);
        }
    }

    /**
     * @desc 清空购物车
     * @param array $param
     *       -user_id int 用户ID（*）
     *       -is_temp int 是否临时用户（取值：0，1），默认：0
     *       -is_global int 是否海外购（取值：0，1），默认：0
     * @return array
     * @author 柯琼远|李飞麟
     */
    public function clearCart($param) {
        $userId = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $isTemp = isset($param['is_temp']) ? (int)$param['is_temp'] : 0;
        $isGlobal = isset($param['is_global']) ? (int)$param['is_global'] : 0;
        // 判断参数是否合法
        if ($userId < 1 || !in_array($isTemp, [0,1]) || !in_array($isGlobal, [0,1]) || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param'=> $param]);
        }
        $shoppingCartInstance = BaiyangGoodsShoppingOfflineCart::getInstance();
        // 获取商品的购物车信息
        $paramData = array('user_id'=> $userId,'is_temp'=> $isTemp,'is_global'=> $isGlobal);
        $shoppingCartList = $shoppingCartInstance->getShoppingCart($paramData);
        if (empty($shoppingCartList)) {
            return $this->uniteReturnResult(HttpStatus::NOT_CART_GOODS, ['param'=> $param]);
        }
//        // 辣妈
//        $this->_eventsManager->fire('promotion:deleteMomGiftGetGoods', $this, [
//            'user_id'            => $param['user_id'],
//            'goods_id_list'      => array_column($shoppingCartList, 'goods_id'),
//        ]);
        // 删除购物车商品
        $ret = $shoppingCartInstance->deleteShoppingCart($paramData);
        if ($ret) {
            return $this->shoppingCart($param);
        } else {
            return $this->uniteReturnResult(HttpStatus::OPERATE_ERROR, ['param'=> $param]);
        }
    }

    /**
     * @desc 批量删除多个商品或者套餐
     * @param array $param
     *       -goods_id_list  array 商品id列表
     *       -group_id_list  array 套餐id列表
     *       -user_id int 用户ID（*）
     *       -is_temp int 是否临时用户（取值：0，1），默认：0
     * @return array
     * @author 柯琼远|李飞麟
     */
    public function batchRemoveCart($param) {
        // 格式化参数
        $goodsIdList = isset($param['goods_id_list']) ? (array)$param['goods_id_list'] : [];
        $groupIdList = isset($param['group_id_list']) ? (array)$param['group_id_list'] : [];
        $userId = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $isTemp = isset($param['is_temp']) ? (int)$param['is_temp'] : 0;
        foreach ($goodsIdList as $key => $value) {
            $goodsIdList[$key] = (int)$value;
        }
        foreach ($groupIdList as $key => $value) {
            $groupIdList[$key] = (int)$value;
        }
        $goodsIdList = array_filter($goodsIdList);
        $groupIdList = array_filter($groupIdList);
        // 判断参数是否合法
        if ((empty($goodsIdList) && empty($groupIdList)) || $userId < 1 || !in_array($isTemp, [0,1]) || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param'=> $param]);
        }
        $shoppingCartInstance = BaiyangGoodsShoppingOfflineCart::getInstance();
        // 辣妈
//        if (!empty($goodsIdList)) {
//            $this->_eventsManager->fire('promotion:deleteMomGiftGetGoods', $this, [
//                'user_id'            => $param['user_id'],
//                'goods_id_list'      => $goodsIdList,
//            ]);
//        }
        // 删除购物车商品
        $ret = $shoppingCartInstance->batchDeleteShoppingCart([
            'user_id'  => $userId,
            'is_temp'  => $isTemp,
            'goods_ids'=> implode(',', $goodsIdList),
            'group_ids'=> implode(',', $groupIdList)
        ]);
        if ($ret) {
            return $this->shoppingCart($param);
        } else {
            return $this->uniteReturnResult(HttpStatus::DELETE_ERROR, ['param'=> $param]);
        }
    }

    /**
     * @desc 改变购物车商品选择状态
     * @param array $param
     *       -goods_id  int 商品id（*）
     *       -user_id int 用户ID（*）
     *       -increase_buy  int 加价购活动ID，0表示不是加价购商品，默认0
     *       -is_temp int 是否临时用户（取值：0，1），默认：0
     *       -selected int 选择状态（取值：0，1），默认：0
     * @return array
     * @author 柯琼远|李飞麟
     */
    public function selectCartGoods($param) {
        $goodsId = isset($param['goods_id']) ? (int)$param['goods_id'] : 0;
        $userId = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $increaseBuy = isset($param['increase_buy']) ? (int)$param['increase_buy'] : 0;
        $isTemp = isset($param['is_temp']) ? (int)$param['is_temp'] : 0;
        $selected = isset($param['selected']) ? (int)$param['selected'] : 0;
        // 判断参数是否合法
        if ($goodsId < 1 || $userId < 1 || !in_array($isTemp, [0,1]) || !in_array($selected, [0,1]) || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param'=> $param]);
        }
        $shoppingCartInstance = BaiyangGoodsShoppingOfflineCart::getInstance();
        // 获取商品的购物车信息
        $paramData = array(
            'user_id'=> $userId,
            'is_temp'=> $isTemp,
            'group_id'=> 0,
            'goods_id'=>$goodsId,
            'increase_buy'=>$increaseBuy
        );
        $shoppingCartInfo = $shoppingCartInstance->getShoppingCart($paramData, true);
        if (empty($shoppingCartInfo)) {
            return $this->uniteReturnResult(HttpStatus::NOT_CART_GOODS, ['param'=> $param]);
        }
        // 更新状态
        $ret = $shoppingCartInstance->updateShoppingCart($paramData, ['selected'=> $selected]);
        if ($ret) {
            return $this->shoppingCart($param);
        } else {
            return $this->uniteReturnResult(HttpStatus::OPERATE_ERROR, ['param'=> $param]);
        }
    }

    /**
     * @desc 购物车全选/全不选
     * @param array $param
     *       -user_id int 用户ID（*）
     *       -is_temp int 是否临时用户（取值：0，1），默认：0
     *       -selected int 选择状态（取值：0，1），默认：0
     *       -type int 全选的类型，0-普通商品，1-海外购，2-全部，默认：0
     * @return array
     * @author 柯琼远|李飞麟
     */
    public function selectAll($param) {
        $userId = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $isTemp = isset($param['is_temp']) ? (int)$param['is_temp'] : 0;
        $selected = isset($param['selected']) ? (int)$param['selected'] : 0;
        $type = isset($param['type']) ? (int)$param['type'] : 0;
        // 判断参数是否合法
        if ($userId < 1 || !in_array($isTemp, [0,1]) || !in_array($selected, [0,1]) || !in_array($type, [0,1,2]) || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param'=> $param]);
        }
        $shoppingCartInstance = BaiyangGoodsShoppingOfflineCart::getInstance();
        // 获取商品的购物车信息
        $paramData = array(
            'user_id'=> $userId,
            'is_temp'=> $isTemp,
            'is_global'=> $type != 2 ? $type : null
        );
        $shoppingCartInfo = $shoppingCartInstance->getShoppingCart($paramData, true);
        if (empty($shoppingCartInfo)) {
            return $this->uniteReturnResult(HttpStatus::NOT_CART_GOODS, ['param'=> $param]);
        }
        // 更新状态
        $ret = $shoppingCartInstance->updateShoppingCart($paramData, ['selected'=> $selected]);
        if ($ret) {
            return $this->shoppingCart($param);
        } else {
            return $this->uniteReturnResult(HttpStatus::OPERATE_ERROR, ['param'=> $param]);
        }
    }

    /**
     * @desc 加入或修改商品数量
     * @param array $param
     *       -goods_id  int 商品id（*）
     *       -goods_number int 商品数量（*）
     *       -user_id int 用户ID（*）
     *       -is_temp int 是否临时用户（取值：0，1），默认：0
     *       -platform string 平台：pc,wap,app
     * @return array
     * @author 柯琼远|李飞麟
     */
    private function addOrEditGoodsNumber($param) {
        // 格式化参数
        $param['goods_id'] = isset($param['goods_id']) ? (int)$param['goods_id'] : 0;
        $param['goods_number'] = isset($param['goods_number']) ? (int)$param['goods_number'] : 0;
        $param['user_id'] = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $param['is_temp'] = isset($param['is_temp']) ? (int)$param['is_temp'] : 0;

        // 判断参数是否合法
        if ($param['goods_id'] < 1 || $param['goods_number'] < 1 || $param['user_id'] < 1 || !in_array($param['is_temp'], [0,1]) || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param'=> $param]);
        }

        $param['group_id'] = 0;
        $param['increase_buy'] = 0;
        // 获取商品的购物车信息
        $shoppingCartInfo = BaiyangGoodsShoppingOfflineCart::getInstance()->getShoppingCart([
            'user_id' => $param['user_id'],
            'is_temp' => $param['is_temp'],
            'goods_id'=> $param['goods_id'],
            'group_id'=> $param['group_id'],
            'cart_type' => 1,
            'increase_buy'=> $param['increase_buy']
        ], true);
        if ($param['action'] == 'add') {
            $param['goods_number'] = !empty($shoppingCartInfo) ? $param['goods_number'] + $shoppingCartInfo['goods_number'] : $param['goods_number'];
        }

        $param['shoppingCartInfo'] = $shoppingCartInfo;
        // 设置购物车商品数量
        return $this->setNumToCart($param);
    }


    /**
     * @desc 设置购物车商品数量
     * @param  $param
     * @return array
     * @author 柯琼远|李飞麟
     */
    private function setNumToCart($param) {
        $shoppingCartInfo = $param['shoppingCartInfo'];
        $platform = $this->config->platform;
        $param['from'] = isset($param['from']) ? (string)$param['from'] : 'normal';
        $param['action'] = isset($param['action']) ? (string)$param['action'] : 'add';
        if ($param['action'] == "edit" && empty($shoppingCartInfo)) {
            return $this->uniteReturnResult(HttpStatus::NOT_CART_GOODS, ['param'=> $param]);
        }

        $baseDataInstance = BaseData::getInstance();
        $shoppingCartInstance = BaiyangGoodsShoppingOfflineCart::getInstance();
        // 获取商品信息
        $goodsInfo = BaiyangSkuData::getInstance()->getSkuInfo($param['goods_id'], $platform);
        // 判断商品是否存在
        if (empty($goodsInfo)) {
            return $this->uniteReturnResult(HttpStatus::NOT_GOOD_INFO, ['param'=> $param]);
        }
        // 判断商品是否是赠品
        if ($goodsInfo['product_type'] != 0) {
            return $this->uniteReturnResult(HttpStatus::GIFT_CANNOT_ADDTOCART, ['param'=> $param]);
        }
        // 判断商品是否已下架
        if ($goodsInfo['sale'] == 0) {
            return $this->uniteReturnResult(HttpStatus::NOT_ON_SALE, ['param' => $param, 'goodsInfo' => $goodsInfo],[$goodsInfo['name']]);
        }
        //不能为海外购商品、第三方发货商品,返回不能O2O配送
        $goods_check = BaiyangSkuData::getInstance()->getData(array(
            'table' => '\Shop\Models\BaiyangGoods',
            'column' => 'id,is_global,behalf_of_delivery',
            'where' => 'where id = :id:',
            'bind' => array(
                'id' => $param['goods_id']
            )
                ), true);
        if($goods_check['behalf_of_delivery'] == 1){
            return $this->uniteReturnResult(HttpStatus::NOT_GOOD_INFO, ['param'=> $param]);
        }
        if($goodsInfo['is_global'] == 1){
            return $this->uniteReturnResult(HttpStatus::NOT_GOOD_INFO, ['param'=> $param]);
        }
        // 判断处方药能不能加到购物车
        if ($param['action'] == "add" && $goodsInfo['drug_type'] == 1 && $this->func->getDisplayAddCart($platform) == 0) {
            return $this->uniteReturnResult(HttpStatus::RX_CANNOT_ADD_TO_CART, ['param'=> $param]);
        }

        // 购物车已有的该商品数量
        $tempNumber = !empty($shoppingCartInfo) ? (int)$shoppingCartInfo['goods_number'] : 0;
        if ($param['goods_number'] > $tempNumber) {
            // 判断是否超过最大购买数量
            if ($param['goods_number'] > 200) {
                return $this->uniteReturnResult(HttpStatus::OVER_SINGLE_BUY_NUM, ['param'=> $param]);
            }

            if ($goodsInfo['is_global'] == 0) {

                if ($param['group_id'] == 0 && $param['increase_buy'] == 0) {

                    // 商品不参加疗程时才判断限购
                    $ret = $this->_eventsManager->fire('promotionInfo:getGoodsDiscountPrice', $this, [
                        'goodsInfo' => [
                            'goods_id'=> $param['goods_id'],
                            'goods_number'=> $param['goods_number'],
                            'sku_price'=> $goodsInfo['sku_price']
                        ],
                        'platform' => $platform,
                        'user_id' => $param['user_id'],
                        'is_temp' => $param['is_temp'],
                    ]);
                    if (!(isset($ret['discountPromotion']['promotion_type']) && $ret['discountPromotion']['promotion_type'] == BaiyangPromotionEnum::TREATMENT)) {
                        // 判断限购
                        $limitBuyInfo = $this->_eventsManager->fire('promotion:countLimitNumToCart', $this, [
                            'goods_id'  => $param['goods_id'],
                            'goods_number'=> $param['goods_number'] - $tempNumber,
                            'platform' => $platform,
                            'user_id'   => $param['user_id'],
                            'is_temp'   => $param['is_temp']
                        ]);
                        if ($limitBuyInfo['error'] != 0) {

                            return $this->uniteReturnResult($limitBuyInfo['code'], ['param'=>$param], $limitBuyInfo['data']);
                        }
                    }
                    // APP端需要判断辣妈（易复诊不判断辣妈）
                    if ($platform == "app") {
                        // 获取限时优惠后的价格
//                        $limitTimeInfo = $this->_eventsManager->fire('promotionInfo:getLimitTimeInfo',$this,[
//                            'goods_id' => $param['goods_id'],
//                            'sku_price' => $goodsInfo['sku_price'],
//                        ]);
 //                       $price = isset($limitTimeInfo['price']) ? $limitTimeInfo['price'] : $goodsInfo['sku_price'];
                        // 添加
//                        if ($param['action'] == "add" && ($param['from'] == 'mom' || $param['from'] == 'normal')) {
//                            $ret = $this->_eventsManager->fire('promotion:momGiftGoodsVerify', $this, [
//                                'user_id'            => $param['user_id'],
//                                'goods_id'           => $param['goods_id'],
//                                'price'              => $price,
//                                'goods_number'       => $param['goods_number'],
//                                'tagPriceLimitCheck' => $param['from'] == 'mom' ? false : true
//                            ]);
//                            if ($ret['code'] != HttpStatus::SUCCESS) {
//                                print_r($param);
//                                exit("a");
//                                return $this->uniteReturnResult($ret['code'], ['param'=> $param]);
//                            }
//                        }
                        // 编辑
//                        if ($param['action'] == "edit") {
//                            $ret = $this->_eventsManager->fire('promotion:momGiftGoodsLimitVerify', $this, [
//                                'user_id'            => $param['user_id'],
//                                'goods_id'           => $param['goods_id'],
//                                'price'              => $price,
//                                'goods_number'       => $param['goods_number'],
//                                'cart_goods_number'  => $shoppingCartInfo['goods_number'],
//                            ]);
//                            if ($ret['code'] != HttpStatus::SUCCESS) {
//                                return $this->uniteReturnResult($ret['code'], ['param'=> $param]);
//                            }
//                        }
                    }
                }

                // 判断库存
                $stock = $this->func->getCanSaleStock(['goods_id'=> $param['goods_id'], 'platform'=> $platform]);
                $goodsNunberList = $shoppingCartInstance->getShoppingCart([
                    'user_id'   => $param['user_id'],
                    'goods_id'  => $param['goods_id'],
                    'cart_type' => 1,
                    'is_temp'   => $param['is_temp'],
                ]);
                $otherNunber = 0;
                foreach ($goodsNunberList as $value) {
                    $otherNunber += $value['goods_number'];
                }

                $otherNunber -= isset($shoppingCartInfo['goods_number']) ? $shoppingCartInfo['goods_number'] : 0;
                $goodsNunberTotal = $otherNunber + $param['goods_number'];
                if ($goodsNunberTotal > $stock) {

                    return $this->uniteReturnResult(HttpStatus::NOT_ENOUGH_STOCK, ['stock'=> $stock], ['']);
                }
            }
        }

        // 修改
        if (!empty($shoppingCartInfo)) {
            $ret = $shoppingCartInstance->updateShoppingCart([
                'user_id'=> $param['user_id'],
                'is_temp'=> $param['is_temp'],
                'goods_id'=> $param['goods_id'],
               // 'group_id'=> $param['group_id'],
                'cart_type'         => 1,
                'increase_buy'=> $param['increase_buy']
            ], [
                'goods_number'=> $param['goods_number'],
                'selected'=> 1,
            ]);
            if ($ret) {
                return $this->uniteReturnResult(HttpStatus::SUCCESS);
            } else {
                return $this->uniteReturnResult(HttpStatus::FAILED, ['param'=> $param]);
            }
        } else {
            // 添加
            $ret = $baseDataInstance->addData([
                'table' => "\\Shop\\Models\\BaiyangGoodsShoppingOfflineCart",
                'bind'  => [
                    'user_id'           => $param['user_id'],
                    'goods_id'          => $param['goods_id'],
                    //'group_id'          => $param['group_id'],
                    'brand_id'          => $goodsInfo['brand_id'],
                    'goods_number'      => $param['goods_number'],
                    'is_temp'           => $param['is_temp'],
                    'add_time'          => time(),
                  //  'is_global'         => $goodsInfo['is_global'],
                    'cart_type'         => 1,
                    'selected'          => 1,
                    'increase_buy'      => $param['increase_buy'],
                ]
            ]);
            if ($ret) {
                return $this->uniteReturnResult(HttpStatus::SUCCESS);
            } else {
                if ($param['action'] == "add") {
                    return $this->uniteReturnResult(HttpStatus::ADD_ERROR, ['param'=> $param]);
                } else {
                    return $this->uniteReturnResult(HttpStatus::EDIT_ERROR, ['param'=> $param]);
                }
            }
        }
    }

    /**
     * @desc 获取购物车数量
     * @param array $param
     *       -user_id int 用户ID（*）
     *       -is_temp int 是否临时用户（取值：0，1），默认：0
     *       - cart_type int 购物车类型： 0 普通购物车/ 1 O2O购物车
     * @return array
     * @author 柯琼远|李飞麟
     */
    public function getCartNumber($param) {
        // 格式化参数
        $userId = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $isTemp = isset($param['is_temp']) && in_array($param['is_temp'], [0, 1]) ? (int)$param['is_temp'] : 0;
        // 判断参数是否合法
        if ($userId < 1 || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param'=> $param]);
        }
        $platform = $this->config->platform;
        $totalNumber = 0;
        $list = BaiyangGoodsShoppingOfflineCart::getInstance()->getShoppingCart([
            'user_id'=> $userId,
            'cart_type' => 1,
//            'is_temp'=> $isTemp
        ]);
        $groupIdArr = [];
        foreach ($list as $value) {
            if ($value['increase_buy'] > 0) {
                $totalNumber += $value['goods_number'];
                continue;
            }
            if ($value['goods_number'] < 1) continue;
            if ($value['group_id'] == 0) {
                // 商品信息
                $skuInfo = BaiyangSkuData::getInstance()->getSkuInfo($value['goods_id'], $platform);
                if(empty($skuInfo)) continue;
                if ($skuInfo['product_type'] != 0) continue;
                $stock = $this->func->getCanSaleStock(['goods_id'=> $value['goods_id'], 'platform'=> $platform]);
                // 库存不足处理
                $goodsNumber = $value['goods_number'];
                if ($stock <= 0) {
                    $goodsNumber = 1;
                } elseif ($value['goods_number'] > $stock) {
                    $goodsNumber = $stock;
                }
                $totalNumber += $goodsNumber;
            } else {
                //O2O暂不支持套餐
                continue;
                // 套餐列表
                if (in_array($value['group_id'], $groupIdArr)) continue;
                $groupGoodsList = baseData::getInstance()->getData([
                    'column' => "gg.goods_id,gg.goods_number",
                    'table'  => "\\Shop\\Models\\BaiyangGroupGoods as gg",
                    'join'   => "left join \\Shop\\Models\\BaiyangFavourableGroup as fg on fg.id = gg.group_id",
                    'where'  => "where gg.group_id = :groupId: and fg.{$platform}_platform = 1",
                    'bind'   => array('groupId'=> $value['group_id'])
                ]);
                if (!empty($groupGoodsList)) {
                    $groupIdArr[] = $value['group_id'];
                    $groupNumber = 0;// 套餐的总数
                    $singleNumber = 0;// 一个套餐的商品数量
                    foreach ($groupGoodsList as $v) {
                        if ($value['goods_id'] == $v['goods_id']) {
                            $groupNumber = bcdiv($value['goods_number'], $v['goods_number'], 0);
                        }
                        $singleNumber += $v['goods_number'];
                    }
                    if ($groupNumber > 0) {
                        $group_stock = NULL;// 套餐库存
                        $groupExist = true;
                        foreach ($groupGoodsList as $k => $v) {
                            $skuInfo = BaiyangSkuData::getInstance()->getSkuInfo($v['goods_id'], $platform);
                            if (empty($skuInfo)) {
                                $groupExist = false;break;
                            }
                            if ($skuInfo['product_type'] != 0) {
                                $groupExist = false;break;
                            }
                            if ($skuInfo['product_type'] != 0) continue;
                            $stock = $this->func->getCanSaleStock(['goods_id'=> $v['goods_id'], 'platform'=> $platform]);
                            $temp_group_stock = floor($stock/$v['goods_number']);
                            if (is_null($group_stock)) {
                                $group_stock = $temp_group_stock;
                            } else {
                                $group_stock = $group_stock < $temp_group_stock ? $group_stock : $temp_group_stock;
                            }
                        }
                        if ($groupExist) {
                            if ($group_stock == 0) $groupNumber = 1;
                            elseif ($group_stock < $groupNumber) $groupNumber = $group_stock;
                            $totalNumber += $groupNumber * $singleNumber;
                        }
                    }
                }
            }
        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS, ['totalNumber'=> $totalNumber]);
    }

    /**
     * 获取购物车推荐商品
     * @param $param
     * @return \array[]
     */
    public function getCartRecommendedProducts($param)
    {
        if (!$this->verifyRequiredParam($param)) {

            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        if (!isset($param['user_id']) || !isset($param['is_temp'])) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $page = isset($param['page']) ? intval($param['page']) : 1;
        $size = isset($param['size']) ? intval($param['size']) : 10;

        $result = BaiyangGoodsShoppingOfflineCart::getInstance()->getCartRecommendProducts(60);

        if ($result === false || empty($result)) {
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
        $array = array();
        $filter = 'id,spu_id,sku_price,sku_market_price,name,subheading_name,goods_image,small_path,drug_type,sale';

        foreach ($result as $v) {
            $tmp = BaiyangSkuData::getInstance()->getSkuInfo($v['id'], $param['platform']);
            if (isset($tmp['sale']) && $tmp['sale'] == 1) {
                $array[] = $this->filterData($filter, $tmp);
            }
        }

        $start = ($page -1) * $size;

        $goodsList = array();

        $promotionObject = new O2OPromotionShopping();

        foreach ($result as $v) {
            $value = $promotionObject->getCartGoodsInfo($v['id']);
            if(empty($value) === false){
                $goodsList[] = $value;
            }
        }

        if($start < count($goodsList)){
            $goodsList = array_slice($goodsList,$start,$size);
        }else{
            $goodsList = [];
        }

        if( count($goodsList) > 0 ){
            return $this->uniteReturnResult(HttpStatus::SUCCESS,$goodsList);
        }else{
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
    }
}