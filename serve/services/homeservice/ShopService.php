<?php

namespace Shop\Home\Services;

use Shop\Home\Datas\BaiyangGoodsTreatmentData;
use Shop\Home\Datas\BaiyangMomGiftGoodsData;
use Shop\Home\Datas\BaiyangUserData;
use Shop\Home\Services\BaseService;
use Shop\Home\Datas\BaseData;
use Shop\Home\Datas\BaiyangShoppingCartData;
use Shop\Home\Datas\BaiyangSkuData;
use Shop\Models\BaiyangPromotionEnum;
use Shop\Models\HttpStatus;
use Phalcon\Events\Manager as EventsManager;
use Shop\Home\Listens\PromotionGetGoodsDiscountPrice;
use Shop\Home\Listens\PromotionCalculate;
use Shop\Home\Listens\PromotionLimitBuy;
use Shop\Home\Listens\BaseListen;
use Shop\Home\Listens\PromotionShopping;
use Shop\Home\Listens\PromotionGoodsDetail;
use Shop\Home\Listens\MomListener;
use Shop\Home\Datas\BaiyangMomGetGiftData;
use Shop\Home\Datas\BaiyangYfzData;
use Shop\Home\Datas\BaiyangGoodsStockBondedData;
use Shop\Models\CacheKey;

class ShopService extends BaseService {
    
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance = null;

    // 加载监听器
    public static function getInstance() {
        if(empty(static::$instance)){
            static::$instance = new ShopService();
        }
        $eventsManager = new EventsManager();
        $eventsManager->attach('promotion',new PromotionGetGoodsDiscountPrice());
        $eventsManager->attach('promotion',new PromotionLimitBuy());
        $eventsManager->attach('promotion',new PromotionCalculate());
        $eventsManager->attach('promotion',new PromotionShopping());
        $eventsManager->attach('promotion',new MomListener());
        $eventsManager->attach('promotionInfo',new PromotionGoodsDetail());
        $eventsManager->attach('baseListen',new BaseListen());
        static::$instance->setEventsManager($eventsManager);
        return static::$instance;
    }


    /**
     * @desc 购物车列表
     * @param array $param
     *       -user_id int 用户ID（*）
     *       -is_temp int 是否临时用户（取值：0，1），默认：0
     *       -platform string 平台：pc,wap,app（*）
     *       -promotion_request bool true|fasle  兼容凑单列表调用 (可填)
     * @return array
     * @author 柯琼远
     */
    public function shoppingCart($param) {
        // 格式化参数
        $userId = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $isTemp = isset($param['is_temp']) && in_array($param['is_temp'], [0, 1]) ? (int)$param['is_temp'] : 0;
        $platform = isset($param['platform']) ? (string)$param['platform'] : "";
        $promotionRequest = isset($param['promotion_request']) ? $param['promotion_request'] : false;
        // 判断参数是否合法
        if ($userId < 1 || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param'=> $param]);
        }
        // 获取购物车商品列表
        $whereParam = ['user_id'=> $userId,'is_temp'=> $isTemp];
        $list = BaiyangShoppingCartData::getInstance()->getShoppingCart($whereParam);
        $globalList = array();  // 海外购商品列表
        $cartList = array();  // 普通商品列表
        $increaseBuyList = array();// 加价购商品列表
        foreach ($list as $value) {
            if ($value['is_global'] == 0) {
                if ($value['increase_buy'] == 0) $cartList[] = $value;
                else $increaseBuyList[] = $value;
            } else {
                $globalList[] = $value;
            }
        }
        // 套餐/限时优惠/疗程/会员价
        $listenParam = ['cartGoodsList'=> $globalList, 'userId'=> $userId, 'isTemp'=> $isTemp,'promotion_request' => $promotionRequest];
        $globalCart = $this->_eventsManager->fire('promotion:getGoodsDiscountInfo',$this,$listenParam);
        $listenParam['cartGoodsList'] = $cartList;
        $shoppingCart = $this->_eventsManager->fire('promotion:getGoodsDiscountInfo',$this,$listenParam);
        $shoppingCart['moduleList'] = array();
        $shoppingCart['allPromotionInfo'] = null;
        $shoppingCart['allIncreaseBuyList'] = array();
        $shoppingCart['allGiftList'] = array();
        $shoppingCart['availPromotionList'] = array();
        $redis = $this->cache;
        $redis->selectDb(2);
        $redis->delete(CacheKey::MAKE_ORDER_PROMOTION.$isTemp.'_'.$userId);
        // 获取促销活动
        if (!empty($shoppingCart['goodsList'])) {
            // 加价购商品特别处理
            $shoppingCart['increaseBuyList'] = $increaseBuyList;
            // 显示促销活动
            $shoppingCart = $this->_eventsManager->fire('promotion:getCartPromotion',$this,[
                'shoppingCartInfo'=> $shoppingCart,
                'userId'=> $userId,
                'isTemp'=> $isTemp
            ]);
            // 计算活动门槛
            $shoppingCart = $this->getGoodsPromotionInfo($shoppingCart, ['platform' => $platform, 'user_id' => $userId, 'is_temp' => $isTemp], 'shoppingCart');
            // 获取到达门槛的活动列表
            $shoppingCart['availPromotionList'] = $this->_eventsManager->fire('promotion:getCanUsePromotion',$this,$shoppingCart);
            // 设置活动缓存
            $this->_eventsManager->fire('promotion:makeOrderPromotionCache',$this,array_merge($shoppingCart,['user_id'=>$userId, 'is_temp'=>$isTemp]));
            // 把相同默认活动的商品捆绑在一起显示
            $shoppingCart = $this->_eventsManager->fire('promotion:bindCartGoods',$this,$shoppingCart);
            // 加价购商品显示
            $shoppingCart = $this->_eventsManager->fire('promotion:getIncreaseBuyShow',$this,$shoppingCart);
            // 满赠赠品显示
            $shoppingCart = $this->_eventsManager->fire('promotion:getgiftShow',$this,$shoppingCart);
        }
        $result = array('shoppingCart'=> $shoppingCart, 'globalCart'=> $globalCart);
        // 梳理购物车的需求的字段 ,
        $result = $this->_eventsManager->fire('promotion:getshoppingCartField',$this, $result);
        $result['isLogin'] = $isTemp == 1 ? 0 : 1;
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $result);
    }

    /**
     * @desc 合并临时用户购物车
     * @param array $param
     *       - login_user_id int 登录用户（*）
     *       - temp_user_id int 用户ID（*）
     * @return array
     * @author 柯琼远
     */
    public function unionTempCart($param) {
        $loginUserId = isset($param['login_user_id']) ? (int)$param['login_user_id'] : 0;
        $tempUserId = isset($param['temp_user_id']) ? (int)$param['temp_user_id'] : 0;
        $param['from'] = "login";
        // 判断参数是否合法
        if ($loginUserId < 1 || $tempUserId < 1 || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param'=> $param]);
        }
        $shoppingCartInstance = BaiyangShoppingCartData::getInstance();
        $baseDataInstance = BaseData::getInstance();
        // 获取临时用户购物车列表
        $cartList = $shoppingCartInstance->getShoppingCart([
            'user_id'=> $tempUserId,
            'is_temp'=> 1,
        ]);
        $increaseBuyList = [];//换购品列表
        $goodsList = [];//商品列表
        $groupList = [];//套餐列表
        $groupIdArr = [];
        foreach ($cartList as $key => $value) {
            if ($value['increase_buy'] > 0) {
                $increaseBuyList[] = $value;
            } elseif ($value['group_id'] == 0) {
                $goodsList[] = $value;
            } else {
                // 套餐列表
                if (in_array($value['group_id'], $groupIdArr)) continue;
                $groupGoodsList = $baseDataInstance->getData([
                    'column' => "goods_id,goods_number",
                    'table'  => "\\Shop\\Models\\BaiyangGroupGoods",
                    'where'  => "where group_id = :group_id:",
                    'bind'   => array('group_id'=> $value['group_id'])
                ]);
                $value['group_number'] = 0;
                foreach ($groupGoodsList as $groupGoodsInfo) {
                    if ($value['goods_id'] == $groupGoodsInfo['goods_id']) {
                        $value['group_number'] = bcdiv($value['goods_number'], $groupGoodsInfo['goods_number'], 0);
                        break;
                    }
                }
                $groupList[] = $value;
                $groupIdArr[] = $value['group_id'];
            }
        }
        // 加入购物车
        foreach ($increaseBuyList as $key => $value) {
            $this->addIncreaseBuy(array_merge($param,[
                'user_id' => $loginUserId,
                'is_temp' => 0,
                'goods_id'=> $value['goods_id'],
                'increase_buy'=> $value['increase_buy'],
            ]));
        }
        foreach ($goodsList as $key => $value) {
            $ret = $this->addGoodsToCart(array_merge($param,[
                'user_id' => $loginUserId,
                'is_temp' => 0,
                'goods_id'=> $value['goods_id'],
                'goods_number'=> $value['goods_number'],
            ]));
            if ($ret['status'] == HttpStatus::SUCCESS && !empty($value['selected_promotion'])) {
                $this->switchPromotion(array_merge($param,[
                    'user_id' => $loginUserId,
                    'is_temp' => 0,
                    'goods_id'=> $value['goods_id'],
                    'promotion_ids'=> $value['selected_promotion'],
                ]));
            }
        }
        foreach ($groupList as $key => $value) {
            $this->addGroupToCart(array_merge($param,[
                'user_id' => $loginUserId,
                'is_temp' => 0,
                'group_id'=> $value['group_id'],
                'group_number'=> $value['group_number'],
            ]));
        }
        // 删除临时用户购物车
        $shoppingCartInstance->deleteShoppingCart([
            'user_id'=> $tempUserId,
            'is_temp'=> 1,
        ]);
        // 全场切换活动缓存
        $redis = $this->cache;
        $redis->selectDb(2);
        $cacheValue = $redis->getValue(CacheKey::ALL_CHANGE_PROMOTION.'1_'.$tempUserId);
        if (!empty($cacheValue)) {
            $redis->setValue(CacheKey::ALL_CHANGE_PROMOTION.'0_'.$loginUserId, $cacheValue);
        }
        $redis->delete(CacheKey::ALL_CHANGE_PROMOTION.'1_'.$tempUserId);
        return $this->uniteReturnResult(HttpStatus::SUCCESS);
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
     * @author 柯琼远
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
     * @author 柯琼远
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
     * @author 柯琼远
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
        $shoppingCartInstance = BaiyangShoppingCartData::getInstance();
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
        $this->_eventsManager->fire('promotion:deleteMomGiftGetGoods', $this, [
            'user_id'            => $userId,
            'goods_id_list'      => array($goodsId),
        ]);
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
     * @author 柯琼远
     */
    public function clearCart($param) {
        $userId = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $isTemp = isset($param['is_temp']) ? (int)$param['is_temp'] : 0;
        $isGlobal = isset($param['is_global']) ? (int)$param['is_global'] : 0;
        // 判断参数是否合法
        if ($userId < 1 || !in_array($isTemp, [0,1]) || !in_array($isGlobal, [0,1]) || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param'=> $param]);
        }
        $shoppingCartInstance = BaiyangShoppingCartData::getInstance();
        // 获取商品的购物车信息
        $paramData = array('user_id'=> $userId,'is_temp'=> $isTemp,'is_global'=> $isGlobal);
        $shoppingCartList = $shoppingCartInstance->getShoppingCart($paramData);
        if (empty($shoppingCartList)) {
            return $this->shoppingCart($param);
        }
        // 辣妈
        $this->_eventsManager->fire('promotion:deleteMomGiftGetGoods', $this, [
            'user_id'            => $param['user_id'],
            'goods_id_list'      => array_column($shoppingCartList, 'goods_id'),
        ]);
        // 删除购物车商品
        $ret = $shoppingCartInstance->deleteShoppingCart($paramData);
        if ($ret) {
            return $this->shoppingCart($param);
        } else {
            return $this->uniteReturnResult(HttpStatus::DELETE_ERROR, ['param'=> $param]);
        }
    }

    /**
     * @desc 套餐加入购物车
     * @param array $param
     *       -group_id  int 套餐id（*）
     *       -group_number int 套餐数量（*）
     *       -user_id int 用户ID（*）
     *       -is_temp int 是否临时用户（取值：0，1），默认：0
     *       -platform string 平台：pc,wap,app
     * @return array
     * @author 柯琼远
     */
    public function addGroupToCart($param) {
        $param['action'] = 'add';
        return $this->addOrEditGroupNumber($param);
    }

    /**
     * @desc 修改购物车套餐数量
     * @param array $param
     *       -group_id  int 套餐id（*）
     *       -group_number int 套餐数量（*）
     *       -user_id int 用户ID（*）
     *       -is_temp int 是否临时用户（取值：0，1），默认：0
     *       -platform string 平台：pc,wap,app
     * @return array
     * @author 柯琼远
     */
    public function editCartGroupNumber($param) {
        $param['action'] = 'edit';
        $result = $this->addOrEditGroupNumber($param);
        if ($result['status'] == HttpStatus::SUCCESS) {
            return $this->shoppingCart($param);
        }
        if ($result['status'] == HttpStatus::ADD_ERROR) {
            $this->uniteReturnResult(HttpStatus::EDIT_ERROR, $result['data']);
        }
        return $result;
    }

    /**
     * @desc 删除购物车套餐
     * @param array $param
     *       -group_id  int 套餐id（*）
     *       -user_id int 用户ID（*）
     *       -is_temp int 是否临时用户（取值：0，1），默认：0
     * @return array
     * @author 柯琼远
     */
    public function removeCartGroup($param) {
        $groupId = isset($param['group_id']) ? (int)$param['group_id'] : 0;
        $userId = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $isTemp = isset($param['is_temp']) ? (int)$param['is_temp'] : 0;
        // 判断参数是否合法
        if ($groupId < 1 || $userId < 1 || !in_array($isTemp, [0,1]) || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param'=> $param]);
        }
        $shoppingCartInstance = BaiyangShoppingCartData::getInstance();
        // 获取商品的购物车信息
        $paramData = array('user_id'=> $userId,'is_temp'=> $isTemp,'group_id'=> $groupId);
        $shoppingCartInfo = $shoppingCartInstance->getShoppingCart($paramData, true);
        if (empty($shoppingCartInfo)) {
            return $this->uniteReturnResult(HttpStatus::NOT_CART_GOODS, ['param'=> $param]);
        }
        // 删除购物车商品
        $ret = $shoppingCartInstance->deleteShoppingCart($paramData);
        if ($ret) {
            return $this->shoppingCart($param);
        } else {
            return $this->uniteReturnResult(HttpStatus::DELETE_ERROR, ['param'=> $param]);
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
     * @author 柯琼远
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
        $shoppingCartInstance = BaiyangShoppingCartData::getInstance();
        // 辣妈
        if (!empty($goodsIdList)) {
            $this->_eventsManager->fire('promotion:deleteMomGiftGetGoods', $this, [
                'user_id'            => $param['user_id'],
                'goods_id_list'      => $goodsIdList,
            ]);
        }
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
     * @author 柯琼远
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
        $shoppingCartInstance = BaiyangShoppingCartData::getInstance();
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
     * @desc 改变购物车套餐选择状态
     * @param array $param
     *       -group_id  int 商品id（*）
     *       -user_id int 用户ID（*）
     *       -is_temp int 是否临时用户（取值：0，1），默认：0
     *       -selected int 选择状态（取值：0，1），默认：0
     * @return array
     * @author 柯琼远
     */
    public function selectCartGroup($param) {
        $groupId = isset($param['group_id']) ? (int)$param['group_id'] : 0;
        $userId = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $isTemp = isset($param['is_temp']) ? (int)$param['is_temp'] : 0;
        $selected = isset($param['selected']) ? (int)$param['selected'] : 0;
        // 判断参数是否合法
        if ($groupId < 1 || $userId < 1 || !in_array($isTemp, [0,1]) || !in_array($selected, [0,1]) || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param'=> $param]);
        }
        $shoppingCartInstance = BaiyangShoppingCartData::getInstance();
        // 获取商品的购物车信息
        $paramData = array(
            'user_id'=> $userId,
            'is_temp'=> $isTemp,
            'group_id'=> $groupId,
        );
        $shoppingCartInfo = $shoppingCartInstance->getShoppingCart($paramData, true);
        if (empty($shoppingCartInfo)) {
            return $this->uniteReturnResult(HttpStatus::GROUP_NOT_EXIST, ['param'=> $param]);
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
     * @author 柯琼远
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
        $shoppingCartInstance = BaiyangShoppingCartData::getInstance();
        // 获取商品的购物车信息
        $paramData = array(
            'user_id'=> $userId,
            'is_temp'=> $isTemp,
            'increase_buy'=> 0,
            'is_global'=> $type != 2 ? $type : null
        );
        $shoppingCartInfo = $shoppingCartInstance->getShoppingCart($paramData, true);
        if (empty($shoppingCartInfo)) {
            return $this->shoppingCart($param);
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
     * @author 柯琼远
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
        $shoppingCartInfo = BaiyangShoppingCartData::getInstance()->getShoppingCart([
            'user_id' => $param['user_id'],
            'is_temp' => $param['is_temp'],
            'goods_id'=> $param['goods_id'],
            'group_id'=> $param['group_id'],
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
     * @desc 加入或修改套餐数量
     * @param array $param
     *       -group_id  int 套餐id（*）
     *       -group_number int 套餐数量（*）
     *       -user_id int 用户ID（*）
     *       -is_temp int 是否临时用户（取值：0，1），默认：0
     *       -platform string 平台：pc,wap,app
     * @return array
     * @author 柯琼远
     */
    private function addOrEditGroupNumber($param) {
        $param['group_id'] = isset($param['group_id']) ? (int)$param['group_id'] : 0;
        $param['group_number'] = isset($param['group_number']) ? (int)$param['group_number'] : 0;
        $param['user_id'] = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $param['is_temp'] = isset($param['is_temp']) ? (int)$param['is_temp'] : 0;
        if ($param['group_id'] < 1 || $param['group_number'] < 1 || $param['user_id'] < 1 || !in_array($param['is_temp'], [0,1]) || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param'=> $param]);
        }
        $baseDataInstance = BaseData::getInstance();
        // 查询套餐的商品列表
        $goodsList = $baseDataInstance->getData([
            'column' => "gg.group_id,gg.goods_id,gg.goods_number,fg.group_name,fg.end_time,fg.start_time",
            'table'  => "\\Shop\\Models\\BaiyangGroupGoods as gg",
            'join'   => "inner join \\Shop\\Models\\BaiyangFavourableGroup as fg on fg.id = gg.group_id",
            'where'  => "where gg.group_id = :groupId: and {$this->config->platform}_platform = 1",
            'bind'   => array('groupId'=> $param['group_id'])
        ]);
        if (empty($goodsList)) {
            return $this->uniteReturnResult(HttpStatus::GROUP_NOT_EXIST, ['param'=> $param]);
        }
        if ($goodsList[0]['start_time'] > time() || $goodsList[0]['end_time'] < time()) {
            return $this->uniteReturnResult(HttpStatus::GROUP_OVERDUE, ['param'=> $param], [$goodsList[0]['group_name']]);
        }
        // 开启事务
        $this->dbWrite->begin();
        $shoppingCartInstance = BaiyangShoppingCartData::getInstance();
        // 把套餐里的商品逐个加入购物车
        $add_number = 0;
        foreach ($goodsList as $value) {
            $param['goods_id'] = $value['goods_id'];
            $param['goods_number'] = $value['goods_number'] * $param['group_number'];
            $add_number += $param['goods_number'];
            // 获取商品的购物车信息
            $shoppingCartInfo = $shoppingCartInstance->getShoppingCart([
                'user_id'=> $param['user_id'],
                'is_temp'=> $param['is_temp'],
                'goods_id'=> $param['goods_id'],
                'group_id'=> $param['group_id']
            ], true);
            if ($param['action'] == 'add') {
                $param['goods_number'] = !empty($shoppingCartInfo) ? $param['goods_number'] + $shoppingCartInfo['goods_number'] : $param['goods_number'];
            }
            $param['shoppingCartInfo'] = $shoppingCartInfo;
            $param['increase_buy'] = 0;
            // 设置购物车商品数量
            $ret = $this->setNumToCart($param);
            if ($ret['status'] != HttpStatus::SUCCESS) {
                $this->dbWrite->rollback();
                return $ret;
            }
        }
        $this->dbWrite->commit();
        $data = $param['action'] == 'add' ? ['add_number'=> $add_number] : [];
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $data);
    }

    /**
     * @desc 设置购物车商品数量
     * @param  $param
     * @return array
     * @author 柯琼远
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
        $shoppingCartInstance = BaiyangShoppingCartData::getInstance();
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
            return $this->uniteReturnResult(HttpStatus::NOT_ON_SALE, ['param' => $param, 'goodsInfo' => $goodsInfo], [$goodsInfo['name']]);
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
                    $ret = $this->_eventsManager->fire('promotion:getGoodsDiscountPrice', $this, [
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
                        $limitTimeInfo = $this->_eventsManager->fire('promotion:getLimitTimeInfo',$this,[
                            'goods_id' => $param['goods_id'],
                            'sku_price' => $goodsInfo['sku_price'],
                            'user_id'   => $param['user_id'],
                            'is_temp'   => $param['is_temp']
                        ]);
                        $price = isset($limitTimeInfo['price']) ? $limitTimeInfo['price'] : $goodsInfo['sku_price'];
                        // 添加
                        if ($param['action'] == "add" && ($param['from'] == 'mom' || $param['from'] == 'normal')) {
                            $ret = $this->_eventsManager->fire('promotion:momGiftGoodsVerify', $this, [
                                'user_id'            => $param['user_id'],
                                'goods_id'           => $param['goods_id'],
                                'price'              => $price,
                                'goods_number'       => $param['goods_number'],
                                'tagPriceLimitCheck' => $param['from'] == 'mom' ? false : true
                            ]);
                            if ($ret['code'] != HttpStatus::SUCCESS) {
                                return $this->uniteReturnResult($ret['code'], ['param'=> $param]);
                            }
                        }
                        // 编辑
                        if ($param['action'] == "edit") {
                            $ret = $this->_eventsManager->fire('promotion:momGiftGoodsLimitVerify', $this, [
                                'user_id'            => $param['user_id'],
                                'goods_id'           => $param['goods_id'],
                                'price'              => $price,
                                'goods_number'       => $param['goods_number'],
                                'cart_goods_number'  => $shoppingCartInfo['goods_number'],
                            ]);
                            if ($ret['code'] != HttpStatus::SUCCESS) {
                                return $this->uniteReturnResult($ret['code'], ['param'=> $param]);
                            }
                        }
                    }
                }
                // 判断库存
                $stock = $this->func->getCanSaleStock(['goods_id'=> $param['goods_id'], 'platform'=> $platform]);
                $goodsNunberList = $shoppingCartInstance->getShoppingCart([
                    'user_id'  => $param['user_id'],
                    'goods_id' => $param['goods_id'],
                    'is_temp'  => $param['is_temp'],
                ]);
                $otherNunber = 0;
                foreach ($goodsNunberList as $value) {
                    $otherNunber += $value['goods_number'];
                }
                $otherNunber -= isset($shoppingCartInfo['goods_number']) ? $shoppingCartInfo['goods_number'] : 0;
                $goodsNunberTotal = $otherNunber + $param['goods_number'];
                if ($goodsNunberTotal > $stock) {
                    return $this->uniteReturnResult(HttpStatus::NOT_ENOUGH_STOCK, ['stock'=> $stock], [$goodsInfo['name']]);
                }
            } else {
                // 判断海外购库存
                $stock = $this->func->getCanSaleStock(['goods_id'=> $param['goods_id'], 'platform'=> $platform]);
                if ($param['goods_number'] > $stock) {
                    return $this->uniteReturnResult(HttpStatus::NOT_ENOUGH_STOCK, ['stock'=> $stock], [$goodsInfo['name']]);
                }
            }
        }
        // 修改
        if (!empty($shoppingCartInfo)) {
            $updateData = [
                'goods_number'=> $param['goods_number'],
                'selected'=> 1,
            ];
            if ($param['action'] == "add") $updateData['add_time'] = time();
            $ret = $shoppingCartInstance->updateShoppingCart([
                'user_id'=> $param['user_id'],
                'is_temp'=> $param['is_temp'],
                'goods_id'=> $param['goods_id'],
                'group_id'=> $param['group_id'],
                'increase_buy'=> $param['increase_buy']
            ], $updateData);
            if ($ret) {
                return $this->uniteReturnResult(HttpStatus::SUCCESS);
            } else {
                return $this->uniteReturnResult(HttpStatus::FAILED, ['param'=> $param]);
            }
        } else {
            // 添加
            $ret = $baseDataInstance->addData([
                'table' => "\\Shop\\Models\\BaiyangGoodsShoppingCart",
                'bind'  => [
                    'user_id'           => $param['user_id'],
                    'goods_id'          => $param['goods_id'],
                    'group_id'          => $param['group_id'],
                    'brand_id'          => $goodsInfo['brand_id'],
                    'goods_number'      => $param['goods_number'],
                    'is_temp'           => $param['is_temp'],
                    'add_time'          => time(),
                    'is_global'         => $goodsInfo['is_global'],
                    'selected'          => 1,
                    'increase_buy'      => $param['increase_buy'],
                ]
            ]);
            if ($ret) {
                return $this->uniteReturnResult(HttpStatus::SUCCESS);
            } else {
                if ($param['action'] == "add") {
                    return $this->uniteReturnResult(HttpStatus::ADD_TO_CART_FAILED, ['param'=> $param]);
                } else {
                    return $this->uniteReturnResult(HttpStatus::EDIT_ERROR, ['param'=> $param]);
                }
            }
        }
    }

    /**
     * @desc 切换优惠活动
     * @param array $param
     *       -user_id int 用户ID（*）
     *       -is_temp int 是否临时用户（取值：0，1），默认：0
     *       -goods_id int 商品ID，如果不为空表示商品的切换优惠，否则是全场的切换优惠
     *       -promotion_ids string 选中的活动id，多个选中活动用逗号隔开
     * @return array
     * @author 柯琼远
     */
    public function switchPromotion($param) {
        $userId = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $isTemp = isset($param['is_temp']) && in_array($param['is_temp'], [0, 1]) ? (int)$param['is_temp'] : 0;
        $from = isset($param['from']) ? (string)$param['from'] : "normal";
        $selectedPromotion = isset($param['promotion_ids']) ? (string)$param['promotion_ids'] : '';
        $selectedPromotion = explode(',', $selectedPromotion);
        foreach ($selectedPromotion as $key => $value) {
            $selectedPromotion[$key] = (int)$value;
        }
        $selectedPromotion = implode(',', $selectedPromotion);
        // 判断参数是否合法
        if ($userId < 1 || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param'=> $param]);
        }
        if (isset($param['goods_id']) && !empty($param['goods_id'])) {
            $shoppingCartInstance = BaiyangShoppingCartData::getInstance();
            $whereParam = array(
                'user_id'=> $userId,
                'goods_id'=> (int)$param['goods_id'],
                'is_temp'=> $isTemp,
                'group_id'=> 0,
            );
            $ret = $shoppingCartInstance->updateShoppingCart($whereParam, ['selected_promotion'=> $selectedPromotion]);
            if ($ret && $from == "normal") {
                return $this->shoppingCart($param);
            } else {
                return $this->uniteReturnResult(HttpStatus::OPERATE_ERROR, ['param'=> $param]);
            }
        } else {
            $redis = $this->cache;
            $redis->selectDb(2);
            $redis->setValue(CacheKey::ALL_CHANGE_PROMOTION.$isTemp.'_'.$userId, $selectedPromotion);
            return $this->shoppingCart($param);
        }
    }

    /**
     * @desc 选择换购商品
     * @param array $param
     *       -user_id int 用户ID（*）
     *       -is_temp int 是否临时用户（取值：0，1），默认：0
     *       -goods_id int 商品ID（*）
     *       -increase_buy int 换购活动ID（*）
     *       -platform string 平台
     * @return array
     * @author 柯琼远
     */
    public function addIncreaseBuy($param) {
        $param['user_id'] = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $param['is_temp'] = isset($param['is_temp']) && in_array($param['is_temp'], [0, 1]) ? (int)$param['is_temp'] : 0;
        $param['goods_id'] = isset($param['goods_id']) ? (int)$param['goods_id'] : 0;
        $param['increase_buy'] = isset($param['increase_buy']) ? (int)$param['increase_buy'] : 0;
        $param['from'] = isset($param['from']) ? (string)$param['from'] : 'normal';
        // 判断参数是否合法
        if ($param['user_id'] < 1 || $param['increase_buy'] < 1 || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        $shoppingCartInstance = BaiyangShoppingCartData::getInstance();
        // 先删除
        $shoppingCartInstance->deleteShoppingCart([
            'user_id'=> $param['user_id'],
            'is_temp'=> $param['is_temp'],
            'group_id'=> 0,
            'increase_buy'=> $param['increase_buy']
        ]);
        if ($param['goods_id'] > 0) {
            $param['goods_number'] = 1;
            $param['group_id'] = 0;
            $param['shoppingCartInfo'] = array();
            $result = $this->setNumToCart($param);
            if ($result['status'] == HttpStatus::SUCCESS && $param['from'] == "normal") {
                return $this->shoppingCart($param);
            }
            return $result;
        } else {
            return $this->uniteReturnResult(HttpStatus::SUCCESS);
        }
    }

    /**
     * @desc 切换品规接口
     * @param array $param
     *       -user_id int 用户ID（*）
     *       -is_temp int 是否临时用户（取值：0，1），默认：0
     *       -goods_id int 商品ID（*）
     *       -old_goods_id int 旧商品ID（*）
     *       -platform string 平台
     * @return array
     * @author 柯琼远
     */
    public function changeCartGoods($param) {
        $userId = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $isTemp = isset($param['is_temp']) && in_array($param['is_temp'], [0, 1]) ? (int)$param['is_temp'] : 0;
        $goodsId = isset($param['goods_id']) ? (int)$param['goods_id'] : 0;
        $oldGoodsId = isset($param['old_goods_id']) ? (int)$param['old_goods_id'] : 0;
        // 判断参数是否合法
        if ($userId < 1 || $goodsId < 1 || $oldGoodsId < 1 || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        $shoppingCartInstance = BaiyangShoppingCartData::getInstance();
        // 删除购物车商品
        $whereParam = array(
            'user_id'=> $userId,
            'is_temp'=> $isTemp,
            'group_id'=> 0,
            'increase_buy'=> 0,
            'goods_id'=> $oldGoodsId,
        );
        $shoppingCartInstance->deleteShoppingCart($whereParam);
        // 辣妈
        $this->_eventsManager->fire('promotion:deleteMomGiftGetGoods', $this, [
            'user_id'            => $userId,
            'goods_id_list'      => array($oldGoodsId),
        ]);
        // 添加切换后的商品到购物车
        $param['goods_number'] = 1;
        $result = $this->addGoodsToCart($param);
        if ($result['status'] == HttpStatus::SUCCESS) {
            return $this->shoppingCart($param);
        }
        if ($result['status'] == HttpStatus::ADD_ERROR) {
            $this->uniteReturnResult(HttpStatus::OPERATE_ERROR, $result['data']);
        }
        return $result;
    }

    /**
     * @desc 购物车拆分套餐
     * @param array $param
     *       -user_id int 用户ID（*）
     *       -is_temp int 是否临时用户（取值：0，1），默认：0
     *       -goods_id int 删除套餐中的商品ID，默认：0，0表示都不删除
     *       -group_id int 套餐ID（*）
     * @return array
     * @author 柯琼远
     */
    public function cartSplitGroup($param) {
        $userId = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $isTemp = isset($param['is_temp']) && in_array($param['is_temp'], [0, 1]) ? (int)$param['is_temp'] : 0;
        $groupId = isset($param['group_id']) ? (int)$param['group_id'] : 0;
        $goodsId = isset($param['goods_id']) ? (int)$param['goods_id'] : 0;
        // 判断参数是否合法
        if ($userId < 1 || $groupId < 1 || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        $shoppingCartInstance = BaiyangShoppingCartData::getInstance();
        // 获取商品的购物车信息
        $goodsList = $shoppingCartInstance->getShoppingCart([
            'user_id'=>   $userId,
            'is_temp'=>   $isTemp,
            'group_id'=>  $groupId,
        ]);
        if (empty($goodsList)) {
            return $this->uniteReturnResult(HttpStatus::GROUP_NOT_EXIST, ['param' => $param]);
        }
        // 删除临时用户购物车
        $shoppingCartInstance->deleteShoppingCart([
            'user_id'=>   $userId,
            'is_temp'=>   $isTemp,
            'group_id'=>  $groupId,
        ]);
        foreach ($goodsList as $key => $value) {
            if ($value['goods_id'] == $goodsId) {
                continue;
            }
            $this->addGoodsToCart(array_merge($param, [
                'user_id'=>   $userId,
                'is_temp'=>   $isTemp,
                'goods_id'=>  $value['goods_id'],
                'goods_number'=>  $value['goods_number'],
            ]));
        }
        return $this->shoppingCart($param);
    }

    /**
     * @desc 获取购物车数量
     * @param array $param
     *       -user_id int 用户ID（*）
     *       -is_temp int 是否临时用户（取值：0，1），默认：0
     * @return array
     * @author 柯琼远
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
        $list = BaiyangShoppingCartData::getInstance()->getShoppingCart([
            'user_id'=> $userId,
            'is_temp'=> $isTemp,
            'increase_buy'=> 0,
        ]);
        $groupIdArr = [];
        foreach ($list as $value) {
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
     * @desc 易复疹商品加入购物车
     * @param array $param
     *       -user_id  int  用户ID（*）
     *       -goods_id_list array 商品id列表（*）
     *       -yfz_prescription_id int 易复疹处方ID（*）
     *       -platform string 平台：pc,wap,app
     * @return array
     * @author ZHQ
     */
    public function yfzAddGoodsToCart($param)
    {
        $param['goods_id_list'] = $param['goods_id_list'] ?: null;
        $param['yfz_prescription_id'] = isset($param['yfz_prescription_id']) ?$param['yfz_prescription_id']: null;

        if (!isset($param['user_id']) || !isset($param['platform'])
            || (!is_array($param['goods_id_list'])) && empty($param['yfz_prescription_id'])) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        if ($param['goods_id_list']) {
            $param['goods_id_list'] = array_filter($param['goods_id_list'],
                function ($value) {
                    return is_numeric($value) && $value > 0;
                }
            );
            if (empty($param['goods_id_list'])) {
                return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
            }
            $arr = [];
            foreach($param['goods_id_list'] as $key=>$val){
                $arr[] = $key;
            }

            $param['goods_id_list_val'] = implode(',', $param['goods_id_list']);
            $param['goods_id_list'] = implode(',', $arr);
        } else {
            $param['yfz_prescription_id'] = $param['yfz_prescription_id'];
        }
        $user = BaiyangUserData::getInstance()->getUserUnion_id($param['user_id']);
        if ($user) {
            $param['union_user_id'] = $user['union_user_id'];
            $YfzData = BaiyangYfzData::getInstance();
            $prescriptionGoodsList = $YfzData->getPrescriptionGoodsList($param);
        } else {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }

        if ($prescriptionGoodsList) {
            $add_number = 0;
            foreach ($prescriptionGoodsList as $goods) {
                $ret = $this->addGoodsToCart(array(
                    'user_id' => $param['user_id'],
                    'goods_id' => $goods['good_id'],
                    'goods_number' => $goods['good_number'],
                    'platform' => $param['platform'],
                    'from' => 'yfz',
                    'channel_subid' => $param['channel_subid'],
                    'udid' => $param['udid']
                ));
                if ($ret['status'] != HttpStatus::SUCCESS) {
                    continue;
                }
                $add_number += $ret['data']['add_number'];
            }
            if ($add_number) {
                return $this->uniteReturnResult(HttpStatus::SUCCESS, array('add_number' => $add_number));
            } else {
                return $this->uniteReturnResult(HttpStatus::ADD_ERROR);
            }
        }else {
            $ret = $this->uniteReturnResult(HttpStatus::PRESCRIPTION_CANNOT_ADDTOCART);
        }
        return $ret;
    }

    /**
     * @param $param
     * @return array|\array[]
     */
    public function momAddGoodsToCart($param)
    {
        if (!isset($param['user_id']) || (!isset($param['platform']) && $param['platform'] != 'app')
            || !isset($param['goods_id']) && !isset($param['gift_id'])
        ) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $momGetGiftData = BaiyangMomGetGiftData::getInstance();
        $momHasGetGiftGoods = $momGetGiftData->getMomGetGiftGoods($param['user_id'], $param['gift_id'], $param['goods_id']);
        $momGiftGoodsData = BaiyangMomGiftGoodsData::getInstance();
        $momGiftGoodsPriceOne = $momGiftGoodsData->getGiftActivityGoodsPrice($param['gift_id'], $param['goods_id']);
        if ($momHasGetGiftGoods && $momGiftGoodsPriceOne && ($momHasGetGiftGoods >= $momGiftGoodsPriceOne['limit_number'])) {
            return $this->uniteReturnResult(HttpStatus::GIFT_CANNOT_REPEAT_GET);
        } else {
            if(isset($momGiftGoodsPriceOne['limit_number'])){
                if ($momGiftGoodsPriceOne['limit_number'] >= 1) {
                    $param['from'] = 'mom';
                    $param['tagPriceLimitCheck'] = false;
                    $param['goods_number'] = 1;
                    $ret = $this->addGoodsToCart($param);
                    return $ret;
                } else {
                    return $this->uniteReturnResult(HttpStatus::GOODS_NUMBER_OVER);
                }
            } else {
                return $this->uniteReturnResult(HttpStatus::GOODS_UNKONN_GONE);
            }
        }
    }

    /**
     * @desc 获取用户的购物车商品数量
     * @param array $param
     *       -int user_id  用户id
     *       -int is_temp  是否临时用户(1:临时 0:真实，默认0)
     *       -string platform  平台：pc、wap、app
     *       -int    channel_subid  渠道号
     *       -string udid  手机唯一id(app端必填)
     * @return array
     * @author 吴俊华
     */
    public function getShoppingCartGoodsCounts($param)
    {
        // 格式化参数
        $userId = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $isTemp = isset($param['is_temp']) && in_array($param['is_temp'], [0, 1]) ? (int)$param['is_temp'] : 0;
        $platform = isset($param['platform']) ? (string)$param['platform'] : '';
        // 判断参数是否合法
        if ($userId < 1 || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        // 获取购物车商品数量 (不算换购品)
        $counts = BaseData::getInstance()->getData([
            'table' => 'Shop\Models\BaiyangGoodsShoppingCart',
            'column' => 'sum(goods_number) as goods_number',
            'where' => 'where user_id = :user_id: and is_temp = :is_temp: and increase_buy = 0',
            'bind' => [
                'user_id' => $userId,
                'is_temp' => $isTemp,
            ]
        ],true);
        $result['counts'] = $counts ? $counts['goods_number'] : 0;
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $result);
    }

}
