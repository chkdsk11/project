<?php
/**
 * Created by PhpStorm.
 * User: 吴俊华
 * Date: 2016/10/12 0012
 * Time: 上午 11:16
 */
namespace Shop\Home\Listens;
use Shop\Home\Datas\BaseData;
use Shop\Home\Datas\BaiyangSkuData;
use Shop\Home\Datas\BaiyangPromotionData;
use Shop\Home\Datas\BaiyangFreghtTemplate;
use Shop\Home\Datas\BaiyangGoodsStockBondedData;
use Shop\Home\Datas\BaiyangLimitedLogData;
use Shop\Home\Datas\BaiyangGoodsTreatmentData;
use Shop\Home\Datas\BaiyangUserGoodsPriceTagData;
use Shop\Home\Datas\BaiyangOrderPromotionData;
use Shop\Models\HttpStatus;
use Shop\Models\BaiyangPromotionEnum;
use Shop\Home\Datas\BaiyangGoodsShoppingCart;
use Shop\Models\CacheKey;

class PromotionShopping extends BaseListen
{
    /**
     * @desc 计算套餐/限时优惠/疗程/会员价
     * @param $param
     * - cartGoodsList
     * - platform
     * - userId
     * - isTemp
     * @return array
     * @author 柯琼远
     */
    public function getGoodsDiscountInfo ($event,$class,$param) {
        $cartGoodsList = $param['cartGoodsList'];
        $promotionRequest = isset($param['promotion_request']) ? $param['promotion_request'] : false;
        $platform = $this->config->platform;
        $userId = $param['userId'];
        $isTemp = $param['isTemp'];
        $result = array('goodsList'=> array(), 'mutexList'=> array(), 'joinList'=> array());
        if (empty($cartGoodsList)) {
            return $result;
        }
        $baseDataInstance = BaseData::getInstance();
        $groupIdArr = array();
        // 遍历购物车商品列表进行整理
        if (!empty($cartGoodsList)) {
            $limitTime = $this->getProcessingPromotions($event,$class,['platform' => $platform, 'user_id' => $userId,'is_temp' => $isTemp,'promotion_type' => BaiyangPromotionEnum::LIMIT_TIME]);
            foreach ($cartGoodsList as $key => $value) {
                if ($value['goods_number'] < 1) continue;
                if ($value['group_id'] == 0) {
                    // 商品信息
                    $skuInfo = $this->getCartGoodsInfo($value['goods_id'],$promotionRequest);
                    if(empty($skuInfo)) continue;
                    $value = array_merge($value, $skuInfo);
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
                    // 附属赠品数量
                    foreach ($value['bind_gift'] as $gift_key => $gift_info) {
                        $value['bind_gift'][$gift_key]['goods_number'] = $gift_info['goods_number'] * $value['goods_number'];
                    }
                    $value['goods_status'] = $value['sale'] == 0 ? 1 : ($value['goods_number'] > $value['stock'] ? 2 : 0);
                    $value['promotion_price'] = $value['discount_price'] = $value['sku_price'];
                    $value['promotion_total']= $value['discount_total'] = $value['sku_total'] = bcmul($value['goods_number'], $value['sku_price'], 2);
                    $value['discountPromotion'] = array();
                    if ($value['is_global'] == 0) {
                        // 限时优惠/会员价/疗程，哪个最优惠就用哪个
                        $value = $this->getGoodsDiscountPrice($event,$class,[
                            'goodsInfo' => $value,
                            'platform' => $platform,
                            'user_id' => $userId,
                            'is_temp' => $isTemp,
                            'limitTime'=> $limitTime,
                            'handleMom'=> true,
                        ]);
                        if (isset($value['discountPromotion']) && !empty($value['discountPromotion'])) {
                            // 修改商品参加/互斥的促销活动数组
                            $this->func->editJoinMutex($result['mutexList'], $result['joinList'], $value['goods_id'], $value['discountPromotion']['mutex'], $value['discountPromotion']['promotion_type']);
                        }
                    }
                    $result['goodsList'][] = $value;
                } else {
                    // 套餐列表
                    if (in_array($value['group_id'], $groupIdArr)) continue;
                    $whereParam = array(
                        'column' => "gg.goods_id,gg.favourable_price,gg.goods_number,fg.group_name,fg.mutex,fg.start_time,fg.end_time",
                        'table'  => "\\Shop\\Models\\BaiyangGroupGoods as gg",
                        'join'   => "left join \\Shop\\Models\\BaiyangFavourableGroup as fg on fg.id = gg.group_id",
                        'where'  => "where gg.group_id = :groupId: and fg.{$platform}_platform = 1",
                        'bind'   => array('groupId'=> $value['group_id'])
                    );
                    $groupGoodsList = $baseDataInstance->getData($whereParam);
                    if (!empty($groupGoodsList)) {
                        $groupIdArr[] = $value['group_id'];
                        $groupInfo = array();
                        foreach ($groupGoodsList as $groupGoodsInfo) {
                            if ($value['goods_id'] == $groupGoodsInfo['goods_id']) {
                                $groupInfo['id'] = $value['id'];
                                $groupInfo['group_id'] = $value['group_id'];
                                $groupInfo['group_status'] = $groupGoodsInfo['start_time'] <= time() && $groupGoodsInfo['end_time'] >= time() ? 1 : 0;
                                $groupInfo['group_name'] = $groupGoodsInfo['group_name'];
                                $groupInfo['group_number'] = bcdiv($value['goods_number'], $groupGoodsInfo['goods_number'], 0);
                                $groupInfo['group_stock'] = 0;
                                $groupInfo['goods_number'] = 0;
                                $groupInfo['selected'] = $value['selected'];
                                $groupInfo['mutex'] = $groupGoodsInfo['mutex'];
                                if ($groupInfo['group_status'] == 0) {
                                    $groupInfo['selected'] = 0;//套餐失效不能选中
                                }
                                $groupInfo['sku_total'] = 0;
                                $groupInfo['discount_price'] = 0;
                                $groupInfo['discount_total'] = 0;
                            }
                        }
                        if (!empty($groupInfo)) {
                            $group_stock = NULL;// 套餐库存
                            $groupExist = true;
                            foreach ($groupGoodsList as $k => &$groupGoodsInfo) {
                                $skuInfo = $this->getCartGoodsInfo($groupGoodsInfo['goods_id']);
                                if (empty($skuInfo)) {
                                    $groupExist = false;break;
                                }
                                if ($skuInfo['sale'] == 0) {
                                    $groupInfo['selected'] = 0;//下架商品不能选中
                                    $groupInfo['group_status'] = 0;
                                }
                                if (!isset($groupInfo['supplier_id'])) {
                                    $groupInfo['supplier_id'] = $skuInfo['supplier_id'];
                                }
                                $groupInfo['discount_price'] = bcadd($groupInfo['discount_price'], bcmul($groupGoodsInfo['goods_number'], $groupGoodsInfo['favourable_price'], 2), 2);
                                $groupGoodsInfo = array_merge([
                                    'goods_id'=> $groupGoodsInfo['goods_id'],
                                    'goods_number'=> $groupGoodsInfo['goods_number'],
                                    'favourable_price'=> $groupGoodsInfo['favourable_price'],
                                ], $skuInfo);
                                $temp_group_stock = floor($groupGoodsInfo['stock']/$groupGoodsInfo['goods_number']);
                                if (is_null($group_stock)) {
                                    $group_stock = $temp_group_stock;
                                } else {
                                    $group_stock = $group_stock < $temp_group_stock ? $group_stock : $temp_group_stock;
                                }
                            }
                            if ($groupExist) {
                                if ($group_stock == 0) {
                                    $groupInfo['group_status'] = 0;//失效套餐
                                    $groupInfo['group_number'] = 1;//失效套餐改套餐数量为1
                                    $groupInfo['selected'] = 0;//库存不足不能选中
                                } elseif ($group_stock < $groupInfo['group_number']) {
                                    $groupInfo['group_number'] = $group_stock;//改变套餐数量为最大购买数量
                                }
                                $groupInfo['group_stock'] = $group_stock;
                                foreach ($groupGoodsList as $goodsInfo) {
                                    if(!isset($goodsInfo['stock'])) continue;
                                    $goodsInfo['goods_number'] = $goodsInfo['goods_number'] * $groupInfo['group_number'];
                                    // 附属赠品数量
                                    foreach ($goodsInfo['bind_gift'] as $gift_key => $gift_info) {
                                        $goodsInfo['bind_gift'][$gift_key]['goods_number'] = $gift_info['goods_number'] * $goodsInfo['goods_number'];
                                    }
                                    $goodsInfo['sku_total'] = bcmul($goodsInfo['goods_number'], $goodsInfo['sku_price'], 2);
                                    $goodsInfo['promotion_price'] = $goodsInfo['discount_price'] = $goodsInfo['favourable_price'];
                                    $goodsInfo['promotion_total'] = $goodsInfo['discount_total'] = bcmul($goodsInfo['goods_number'], $goodsInfo['favourable_price'], 2);
                                    $groupInfo['goods_number'] +=  $goodsInfo['goods_number'];
                                    $groupInfo['sku_total'] = bcadd($groupInfo['sku_total'], $goodsInfo['sku_total'], 2);
                                    $groupInfo['discount_total'] = bcadd($groupInfo['discount_total'], $goodsInfo['discount_total'], 2);
                                    $groupInfo['promotion_total'] = $groupInfo['discount_total'];
                                    $goodsInfo['goods_status'] = $goodsInfo['sale'] == 0 ? 1 : ($goodsInfo['goods_number'] > $goodsInfo['stock'] ? 3 : 0);
                                    unset($goodsInfo['favourable_price']);
                                    $groupInfo['groupGoodsList'][] = $goodsInfo;
                                }
                                // 修改商品参加/互斥的促销活动数组
                                $this->func->editJoinMutex($result['mutexList'], $result['joinList'], 'g'.$groupInfo['group_id'], $groupInfo['mutex'], 55);
                                unset($groupInfo['mutex']);
                                $result['goodsList'][] = $groupInfo;
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @desc 获取购物车商品信息（购物车需要用到的字段）
     * @param $goods_id（*）
     * @param $promotionRequest bool true|fasle  兼容凑单列表调用 (可填)
     * @return array
     * @author 柯琼远
     */
    private function getCartGoodsInfo($goods_id, $promotionRequest = false) {
        $platform = $this->config->platform;
        $goodsInfo = array();
        $method = $promotionRequest ? 'getSkuInfoLess' : 'getSkuInfo'; // 凑单活动列表不需要返回多品规
        $skuInfo = BaiyangSkuData::getInstance()->$method($goods_id, $platform);
        if (empty($skuInfo)) return [];
        if ($skuInfo['product_type'] != 0) return [];
        $goodsInfo['goods_id'] = $skuInfo['id'];
        $goodsInfo['supplier_id'] = isset($skuInfo['supplier_id']) ? $skuInfo['supplier_id'] : 0;
        $goodsInfo['spu_id'] = $skuInfo['spu_id'];
        $goodsInfo['goods_name'] = $skuInfo['name'];
        $goodsInfo['specifications'] = $skuInfo['specifications'];
        $goodsInfo['rule_name'] = isset($skuInfo['ruleName']) ? $skuInfo['ruleName'] : '';
        $goodsInfo['stock_type'] = $skuInfo['is_use_stock'];
        $goodsInfo['stock'] = $this->func->getCanSaleStock(['goods_id'=> $skuInfo['id'], 'platform'=> $platform]);
        $goodsInfo['sale'] =  $skuInfo['sale'];
        $goodsInfo['drug_type'] = $skuInfo['drug_type'];
        $goodsInfo['brand_id'] = $skuInfo['brand_id'];
        $goodsInfo['category_id'] = $skuInfo['category_id'];
        $goodsInfo['goods_image'] = $skuInfo['goods_image'];
        $goodsInfo['market_price'] = $skuInfo['sku_market_price'];
        $goodsInfo['sku_price'] = $skuInfo['sku_price'];
        $goodsInfo['bind_gift'] = array();
        $goodsInfo['returned_goods_time'] = isset($skuInfo['returned_goods_time']) ? $skuInfo['returned_goods_time'] : 0;
        // 附属赠品
        $bind_gift = isset($skuInfo['bind_gift']) && !empty($skuInfo['bind_gift']) ? json_decode($skuInfo['bind_gift'], true) : array();
        $bind_gift = $bind_gift && is_array($bind_gift) ? $bind_gift : array();
        foreach ($bind_gift as $key => $value) {
            $tempInfo = BaiyangSkuData::getInstance()->getSkuInfo($value['id'], $platform);
            if (!empty($tempInfo) && $tempInfo['sale'] == 1) {
                $goodsInfo['bind_gift'][] = array(
                    'goods_id'=> $tempInfo['id'],
                    'supplier_id'=> isset($tempInfo['supplier_id']) ? $tempInfo['supplier_id'] : 0,
                    'goods_name'=> $tempInfo['name'],
                    'goods_number'=> $value['num'],
                    'specifications'=> $tempInfo['specifications'],
                    'product_type'=> 2,
                    'stock_type'=> $tempInfo['is_use_stock'],
                    'stock'=> $this->func->getCanSaleStock(['goods_id'=> $tempInfo['id'], 'platform'=> $platform]),
                    'goods_image'=> $tempInfo['goods_image'],
                    'returned_goods_time' => isset($skuInfo['returned_goods_time']) ? $skuInfo['returned_goods_time'] : 0,
                );
            }
        }
        return $goodsInfo;
    }

    /**
     * @desc 删除没选中的商品
     * @param array $goodsList
     * @return array
     * @author 柯琼远
     */
    public function delNotSelectGoods($even, $class, $goodsList) {
        $result = array();
        foreach ($goodsList as $key => $value) {
            if ($value['selected'] == 1 && $value['goods_number'] > 0) {
                $result[] = $value;
            }
        }
        return $result;
    }

    /**
     * @desc 验证库存、下架、套餐失效
     * @param array $shoppingCart
     * @return array
     *  -   shoppingCart
     *  -   lackStockSku
     * @author 柯琼远
     */
    public function verifyOrderGoods($even, $class, $shoppingCart) {
        // 验证库存
        $singleGoodsList = array();
        foreach ($shoppingCart['goodsList'] as $key => $value) {
            if ($value['group_id'] == 0) {
                if (isset($singleGoodsList[$value['goods_id']])) {
                    $singleGoodsList[$value['goods_id']]['goods_number'] += $value['goods_number'];
                } else {
                    $singleGoodsList[$value['goods_id']] = [
                        'goods_id'=> $value['goods_id'],
                        'goods_name'=> $value['goods_name'],
                        'goods_number'=> $value['goods_number'],
                        'stock'=> $value['stock'],
                    ];
                }
            } else {
                foreach ($value['groupGoodsList'] as $k => $v) {
                    if (isset($singleGoodsList[$v['goods_id']])) {
                        $singleGoodsList[$v['goods_id']]['goods_number'] += $v['goods_number'];
                    } else {
                        $singleGoodsList[$v['goods_id']] = [
                            'goods_id'=> $v['goods_id'],
                            'goods_name'=> $v['goods_name'],
                            'goods_number'=> $v['goods_number'],
                            'stock'=> $v['stock'],
                        ];
                    }
                }
            }
        }
        foreach ($shoppingCart['increaseBuyList'] as $key => $value) {
            if (isset($singleGoodsList[$value['goods_id']])) {
                $singleGoodsList[$value['goods_id']]['goods_number'] += $value['goods_number'];
            } else {
                $skuInfo = $this->getCartGoodsInfo($value['goods_id']);
                $singleGoodsList[$value['goods_id']] = [
                    'goods_id'=> $value['goods_id'],
                    'goods_name'=> $skuInfo['goods_name'],
                    'goods_number'=> $value['goods_number'],
                    'stock'=> $skuInfo['stock'],
                ];
            }
        }
        $data = array();
        foreach ($singleGoodsList as $value) {
            if ($value['stock'] < $value['goods_number']) {
                $data[] = $value;
            }
        }
        if (!empty($data)) {
            $names = array();
            foreach ($data as $value) {
                $names[] = $value['goods_name'];

            }
            $names = implode('，', $names);
            return $class->uniteReturnResult(HttpStatus::NOT_ENOUGH_STOCK, $data, [$names]);
        }
        return ['status'=>HttpStatus::SUCCESS];
    }
	
	/**
	 * @desc 验证跨境库存、下架、套餐失效
	 * @param array $shoppingCart
	 * @return array
	 *  -   shoppingCart
	 *  -   lackStockSku
	 * @author sarcasme
	 */
	public function verifyGlobalOrderGoods($even, $class, $shoppingCart)
	{
		$platform = $this->config->platform;
		// 验证库存
		$singleGoodsList = array();
		foreach ($shoppingCart['goodsList'] as $key => $value)
		{
			if (isset($singleGoodsList[$value['goods_id']]))
			{
				$singleGoodsList[$value['goods_id']]['goods_number'] += $value['goods_number'];
			} else {
				$singleGoodsList[$value['goods_id']] = [
					'goods_id'=> $value['goods_id'],
					'goods_name'=> $value['goods_name'],
					'goods_number'=> $value['goods_number'],
					'stock'=> $value['stock'],
				];
			}
		}
		//判断是否有足够商品
		$data = array();
		foreach ($singleGoodsList as $value)
		{
			if ($value['stock'] < $value['goods_number']) {
				$data[] = $value;
			}
		}
		if (!empty($data))
		{
			$names = array();
			foreach ($data as $value) {
				$names[] = $value['goods_name'];
				
			}
			$names = implode('，', $names);
            return $class->uniteReturnResult(HttpStatus::NOT_ENOUGH_STOCK, $data, [$names]);
		}
		//判断是否是不同仓的商品
		#$bonds = array_unique(array_column($shoppingCart['goodsList'], 'bond'));
		#if (count($bonds) > 1) return ['status'=>HttpStatus::NOT_SAME_BOND, 'explain'=>'速愈素商品不能与其他商品一起购买，请单独下单购买!'];
		return ['status'=>HttpStatus::SUCCESS];
	}

    /**
     * @desc 购物车商品的促销活动显示（抬头活动，切换活动，固定的活动，参加的活动，不参加活动）
     * @param $param
     * - shoppingCartInfo
     * - $userId
     * - $isTemp
     * @return array
     * @author 柯琼远
     */
    public function getCartPromotion($event,$class,$param) {
        $shoppingCartInfo = $param['shoppingCartInfo'];
        $platform = $this->config->platform;
        $userId = $param['userId'];
        $isTemp = $param['isTemp'];
        $basicParam = [
            'platform' => $platform,
            'user_id'  => $userId,
            'is_temp'  => $isTemp,
            'promotion_type'  => '5,10,15,20,30,40',
        ];
        $promotionList = $this->getProcessingPromotions($event,$class,$basicParam);
        // 对全部促销活动优先级进行排序
        $promotionList = $this->sortPromotion($promotionList);
        $promotionList1 = array();//全场
        $promotionList2 = array();//非全场（排除单品限购）
        foreach ($promotionList as $value) {
            // 限购默认互斥套餐和疗程
            if ($value['promotion_type'] == '30') {
                $value['promotion_mutex'] = '45,55';
            }
            if ($value['promotion_scope'] == 'all') {
                $promotionList1[] = $value;
            } else {
                $promotionList2[] = $value;
            }
        }
        // 全场
        $allPromotionInfo = array(
            'switch_promotion'=> array(),
            'fixed_promotion'=> array(),
            'join_promotionList'=> array()
        );
        if (!empty($promotionList1)) {
            $redis = $this->cache;
            $redis->selectDb(2);
            $selected_promotion = $redis->getValue(CacheKey::ALL_CHANGE_PROMOTION.$isTemp.'_'.$userId);
            $switch_promotion = $this->getSwitchPromotionArray($promotionList1, $selected_promotion);
            $join_promotionList = $this->getJoinPromotionArray($promotionList1, $switch_promotion);
            $allPromotionInfo['switch_promotion'] = $switch_promotion;
            $allPromotionInfo['fixed_promotion'] = $allPromotionInfo['join_promotionList'] = $join_promotionList;
            $promotionList2 = array_merge($promotionList2, $allPromotionInfo['join_promotionList']);
        }
        $shoppingCartInfo['allPromotionInfo'] = $allPromotionInfo;
        // 非全场
        foreach ($shoppingCartInfo['goodsList'] as $key => $goodsInfo) {
            $promotionInfo = array(
                'default_promotion'=> array(),
                'switch_promotion'=> array(),
                'single_limitbuy'=> array(),
                'fixed_promotion'=> array(),
                'join_promotionList'=> array(),
                'not_join_promotionList'=> array()
            );
            // 套餐促销活动
            if ($goodsInfo['group_id'] != 0) {
                $singlePromotionList = array();
                foreach ($promotionList2 as $promotion) {
                    if ($this->func->isRelatedGroup($promotion, $goodsInfo, $shoppingCartInfo['mutexList'], $shoppingCartInfo['joinList'])) {
                        $singlePromotionList[] = $promotion;
                    }
                }
                if (!empty($singlePromotionList)) {
                    $join_promotionList = $this->getJoinPromotionArray($singlePromotionList);
                    $promotionInfo['default_promotion'] = $this->getDefaultPromotionArray($join_promotionList);
                    $promotionInfo['join_promotionList'] = $join_promotionList;
                    $promotionInfo['fixed_promotion'] = $this->getFixedPromotionArray($join_promotionList, $promotionInfo['default_promotion']);
                }
                $promotionInfo['not_join_promotionList'] = $this->getNotJoinPromotionArray($promotionInfo['join_promotionList'], $shoppingCartInfo['allPromotionInfo']['join_promotionList']);
            } else {
                // 获取与该商品相关的促销活动
                $singlePromotionList = array();
                foreach ($promotionList2 as $promotion) {
                    if ($this->func->isRelatedGoods($promotion, $goodsInfo, $shoppingCartInfo['mutexList'], $shoppingCartInfo['joinList'])) {
                        $singlePromotionList[] = $promotion;
                    }
                }
                if (!empty($singlePromotionList)) {
                    // 计算单品限购
                    $promotionInfo['single_limitbuy'] = $this->getSingleLimitbuy($singlePromotionList);
                    // 计算商品的切换活动列表
                    $switch_promotion = $this->getSwitchPromotionArray($singlePromotionList, $goodsInfo['selected_promotion']);
                    $promotionInfo['switch_promotion'] = $switch_promotion;
                    // 计算商品参加的活动列表和默认活动
                    $join_promotionList = $this->getJoinPromotionArray($singlePromotionList, $switch_promotion);
                    $default_promotion = $this->getDefaultPromotionArray($join_promotionList);
                    $promotionInfo['default_promotion'] = $default_promotion;
                    $promotionInfo['join_promotionList'] = $join_promotionList;
                    // 计算商品的固定活动列表
                    $fixed_promotion = $this->getFixedPromotionArray($join_promotionList, $default_promotion);
                    $promotionInfo['fixed_promotion'] = $fixed_promotion;
                }
                // 计算商品不参加的活动列表
                $promotionInfo['not_join_promotionList'] = $this->getNotJoinPromotionArray($promotionInfo['join_promotionList'], $shoppingCartInfo['allPromotionInfo']['join_promotionList']);
            }
            $shoppingCartInfo['goodsList'][$key]['promotionInfo'] = $promotionInfo;
        }
        return $shoppingCartInfo;
    }

    /**
     * @desc 获取切换促销活动数组
     * @param $promotionlist array
     * @param $selectedPromotion string
     * @return array
     * @author 柯琼远
     */
    private function getSwitchPromotionArray($promotionlist, $selectedPromotion = '') {
        $selectedPromotion = (string)$selectedPromotion;
        $tempArr = array();
        $result = array();
        foreach ($promotionlist as $value) {
            $isContinue = false;
            foreach ($tempArr as $v) {
                if ($value['promotion_id'] == $v['promotion_id']) {
                    $isContinue = true;
                    break;
                }
            }
            if ($isContinue) continue;
            $mutexArr = $this->getMutexPromotionArray($promotionlist, $value);
            $tempArr = array_merge($tempArr, $mutexArr);
            if (count($mutexArr) > 1) {
                $mutexArr = $this->sortPromotion($mutexArr);// 排序
                $selected_promotion = array();
                if (!empty($selectedPromotion)) {
                    foreach ($mutexArr as $k => $v) {
                        if (in_array($v['promotion_id'], explode(',', $selectedPromotion))) {
                            $selected_promotion = $v;
                            break;
                        }
                    }
                }
                $selected_promotion = empty($selected_promotion) ? $mutexArr[0] : $selected_promotion;
                $selected_promotion['selected'] = 1;
                $switch_promotion = array($selected_promotion);
                foreach ($mutexArr as $k => $v) {
                    if ($v['promotion_id'] == $selected_promotion['promotion_id']) {
                        continue;
                    }
                    if ($this->isMutexPromotion($v, $selected_promotion)) {
                        $v['selected'] = 0;
                        $switch_promotion[] = $v;
                    }
                }
                $result[] = $this->sortPromotion($switch_promotion);
            }
        }
        return $result;
    }

    /**
     * @desc 获取商品的单品限购活动
     * @param $promotionlist
     * @return array
     * @author 柯琼远
     */
    private function getSingleLimitbuy($promotionlist) {
        $single_limitbuy = array();
        foreach ($promotionlist as $key => $value) {
            if ($value['promotion_scope'] == 'single' and $value['promotion_type'] == '30') {
                $single_limitbuy = $value;
                break;
            }
        }
        return $single_limitbuy;
    }

    /**
     * @desc 获取商品的默认活动
     * @param $join_promotionList
     * @return array
     * @author 柯琼远
     */
    private function getDefaultPromotionArray($join_promotionList) {
        $default_promotion = array();
        foreach ($join_promotionList as $key => $value) {
            // 全场和单品限购不能做默认活动
            if ($value['promotion_scope'] != 'all' &&
                !($value['promotion_scope'] == 'single' and $value['promotion_type'] == '30')) {
                $default_promotion = $value;
                break;
            }
        }
        return $default_promotion;
    }

    /**
     * @desc 获取商品固定促销活动数组
     * @param $join_promotionList
     * @param $default_promotion
     * @return array
     * @author 柯琼远
     */
    private function getFixedPromotionArray($join_promotionList, $default_promotion) {
        $fixed_promotion = array();
        foreach ($join_promotionList as $key => $value) {
            // 排除全场活动
            if ($value['promotion_scope'] == 'all') {
                continue;
            }
            // 排除默认活动
            if (!empty($default_promotion) && $value['promotion_id'] == $default_promotion['promotion_id']) {
                continue;
            }
            // 排除单品限购
            if ($value['promotion_scope'] == 'single' and $value['promotion_type'] == '30') {
                continue;
            }
            $fixed_promotion[] = $value;
        }
        return $this->sortPromotion($fixed_promotion);
    }

    /**
     * @desc 获取商品参加的促销活动数组
     * @param $promotionlist
     * @param $switch_promotion
     * @return array
     * @author 柯琼远
     */
    private function getJoinPromotionArray($promotionlist, $switch_promotion = []) {
        $join_promotion = array();
        $otherPromotion = array();
        if (!empty($switch_promotion)) {
            foreach ($promotionlist as $value) {
                $temp = true;
                foreach ($switch_promotion as $v) {
                    foreach ($v as $_v) {
                        if ($value['promotion_id'] == $_v['promotion_id'] && $_v['selected'] == 0) {
                            $temp = false;
                        }
                    }
                }
                if($temp){
                    $otherPromotion[] = $value;
                }
            }
        } else {
            $otherPromotion = $promotionlist;
        }
        $otherPromotion = $this->sortPromotion($otherPromotion);
        foreach ($otherPromotion as $value) {
            $temp = true;
            foreach ($join_promotion as $v) {
                if ($this->isMutexPromotion($v, $value)) {
                    $temp = false;
                    break;
                }
            }
            if ($temp) {
                $join_promotion[] = $value;
            }
        }
        return $this->sortPromotion($join_promotion);
    }

    /**
     * @desc 获取商品不参加的促销活动数组
     * @param $join_promotionList
     * @param $all_join_promotionList
     * @return array
     * @author 柯琼远
     */
    private function getNotJoinPromotionArray($join_promotionList, $all_join_promotionList) {
        $notJoinPomotionList = array();
        foreach ($all_join_promotionList as $value) {
            $temp = true;
            foreach ($join_promotionList as $v) {
                if ($value['promotion_id'] == $v['promotion_id']) {
                    $temp = false;
                    break;
                }
            }
            if ($temp) {
                $notJoinPomotionList[] = $value;
            }
        }
        return $notJoinPomotionList;
    }

    /**
     * @desc 获取与一个促销活动有的互斥关联的促销活动数组
     * @param $promotionlist
     * @param $promotion
     * @param $mutexArr
     * @return array
     * @author 柯琼远
     */
    private function getMutexPromotionArray($promotionlist, $promotion, $mutexArr = []) {
        $tempArr = array();
        if (empty($mutexArr)) {
            $mutexArr[] = $promotion;
        }
        foreach($promotionlist as $key => $value) {
            $isContinue = false;
            foreach ($mutexArr as $k => $v) {
                if ($value['promotion_id'] == $v['promotion_id']) {
                    $isContinue = true;
                    break;
                }
            }
            if ($isContinue) {
                continue;
            }
            if ($this->isMutexPromotion($value, $promotion)) {
                $tempArr[] = $value;
                $mutexArr[] = $value;
            }
        }
        foreach ($tempArr as $key => $value) {
            $mutexArr = $this->getMutexPromotionArray($promotionlist, $value, $mutexArr);
        }
        return $mutexArr;
    }

    /**
     * @desc 判断两个活动是否互斥
     * @param $promotion1
     * @param $promotion2
     * @return boolean
     * @author 柯琼远
     */
    private function isMutexPromotion($promotion1, $promotion2) {
        // A互斥B
        if (!empty($promotion2['promotion_mutex'])) {
            if (in_array($promotion1['promotion_type'], explode(',', $promotion2['promotion_mutex']))) {
                return true;
            }
        }
        // B互斥A
        if (!empty($promotion1['promotion_mutex'])) {
            if (in_array($promotion2['promotion_type'], explode(',', $promotion1['promotion_mutex']))) {
                return true;
            }
        }
        // 满减或满折同级互斥
        if (in_array($promotion1['promotion_type'], [5,10]) && in_array($promotion2['promotion_type'], [5,10])) {
            if ($promotion1['promotion_scope'] == 'all' && $promotion2['promotion_scope'] == 'all') {
                return true;
            }
            if ($promotion1['promotion_scope'] != 'all' && $promotion2['promotion_scope'] != 'all') {
                return true;
            }
        }
        return false;
    }

    /**
     * @desc 获取验证限购活动的参数（活动列表和商品列表）
     * @param $shoppingCart
     * @return array
     * @author 柯琼远
     */
    public function getLimitBuyParam($even, $class, $shoppingCart) {
        $promotionList = array();
        $goodsList = array();
        foreach ($shoppingCart['goodsList'] as $key => $value) {
            $temp = false;
            if (!empty($value['promotionInfo']['single_limitbuy'])) {
                $promotionList[] = $value['promotionInfo']['single_limitbuy'];
                $temp = true;
            }
            foreach ($value['promotionInfo']['join_promotionList'] as $k => $v) {
                if ($v['promotion_type'] == 30) {
                    $promotionList[] = $v;
                    $temp = true;
                }
            }
            if ($temp) {
                $goodsList[] = $value;
            }
        }
        $limitBuyList = $this->clearTheSamePromotion($promotionList);
        return ['limitBuyList'=> $limitBuyList, 'goodsList'=> $goodsList];
    }

    /**
     * @desc 获取可用活动列表
     * @param $shoppingCartInfo
     * @return array
     * @author 柯琼远
     */
    public function getCanUsePromotion($even, $class, $shoppingCartInfo) {
        $canUserList = array();
        foreach ($shoppingCartInfo['goodsList'] as $key => $value) {
            if ($value['selected'] == 1) {
                $joinPromotionList = $value['promotionInfo']['join_promotionList'];
                foreach ($joinPromotionList as $k => $v) {
                    if ($v['resultInfo']['isCanUse']) {
                        $canUserList[] = $v;
                    }
                }
            }
        }
        // 去重
        return $this->clearTheSamePromotion($canUserList);
    }

    /**
     * @desc 设置购物车全部活动的缓存
     * @param $shoppingCartInfo
     * @return array
     * @author 柯琼远
     */
    public function makeOrderPromotionCache($even, $class, $shoppingCartInfo) {
        $promotionList = array();
        $goodsList = array();
        foreach ($shoppingCartInfo['goodsList'] as $key => $value) {
            // 凑单用的
            $joinPromotionList = $value['promotionInfo']['join_promotionList'];
            foreach ($joinPromotionList as $k => $v) {
                $promotionList[] = $v;
            }
            // 限购用的
            if ($value['group_id'] == 0 && $value['selected'] == 1) {
                $goodsList[] = [
                    'goods_id' => $value['goods_id'],
                    'group_id' => $value['group_id'],
                    'goods_number' => $value['goods_number'],
                    'is_global' => $value['is_global'],
                ];
            }
        }
        $promotionList = $this->clearTheSamePromotion($promotionList);
        $redis = $this->cache;
        $redis->selectDb(2);
        $redis->setValue(CacheKey::MAKE_ORDER_PROMOTION.$shoppingCartInfo['is_temp'].'_'.$shoppingCartInfo['user_id'], $promotionList);
        $redis->setValue(CacheKey::CART_LIMIT_BUY_KEY.$shoppingCartInfo['is_temp'].'_'.$shoppingCartInfo['user_id'], $goodsList);
    }

    /**
     * @desc 活动去重
     * @param $promotionList
     * @return array
     * @author 柯琼远
     */
    private function clearTheSamePromotion($promotionList) {
        $result = array();
        foreach ($promotionList as $key => $value) {
            $temp = true;
            foreach ($result as $k => $v) {
                if ($v['promotion_id'] == $value['promotion_id']) {
                    $temp = false;
                    break;
                }
            }
            if ($temp) {
                $result[] = $value;
            }
        }
        return $result;
    }

    /**
     * @desc 捆绑购物车商品
     * @param $shoppingCart
     * @return array
     * @author 柯琼远
     */
    public function bindCartGoods($even, $class, $shoppingCart) {
        $goodsList = $shoppingCart['goodsList'];
        $result = array();
        if (empty($goodsList)) {
            return $result;
        }
        $tempArr = array();
        foreach ($goodsList as $value) {
            $temp = false;
            foreach ($tempArr as $v) {
                if ($value['id'] == $v['id']) {
                    $temp = true;
                    break;
                }
            }
            if ($temp) {
                continue;
            }
            $bindOne = array('default_promotion'=> $value['promotionInfo']['default_promotion'], 'goodsList'=> array());
            unset($value['promotionInfo']['default_promotion']);
            $bindOne['goodsList'][] = $value;
            $tempArr[] = $value;
            if (!empty($bindOne['default_promotion'])) {
                foreach ($goodsList as $val) {
                    if (empty($val['promotionInfo']['default_promotion'])) {
                        continue;
                    }
                    $temp = false;
                    foreach ($tempArr as $v) {
                        if ($val['id'] == $v['id']) {
                            $temp = true;
                            break;
                        }
                    }
                    if ($temp) {
                        continue;
                    }
                    if ($bindOne['default_promotion']['promotion_id'] == $val['promotionInfo']['default_promotion']['promotion_id']) {
                        unset($val['promotionInfo']['default_promotion']);
                        $bindOne['goodsList'][] = $val;
                        $tempArr[] = $val;
                    }
                }
            }
            $result[] = $bindOne;
        }
        return array_merge(['moduleList'=> $result], $shoppingCart);
    }

    /**
     * @desc 加价购商品在购物车的显示
     * @param $shoppingCartInfo
     * @return array
     * @author 柯琼远
     */
    public function getIncreaseBuyShow ($even, $class, $shoppingCartInfo) {
        $shoppingCartInfo['allIncreaseBuyList'] = array();
        foreach ($shoppingCartInfo['increaseBuyList'] as $key => $value) {
            // 全场
            if ($value['promotion_scope'] == 'all') {
                $shoppingCartInfo['allIncreaseBuyList'][] = $value;
            } else {
                // 非全场
                $temp = false;
                foreach ($shoppingCartInfo['moduleList'] as $k => $v) {
                    if (empty($v['default_promotion'])) {
                        continue;
                    }
                    if ($v['default_promotion']['promotion_id'] == $value['promotion_id']) {
                        $index = count($v['goodsList']) - 1;
                        for ($i = $index; $i >= 0; $i--) {
                            if ($v['goodsList'][$i]['selected']) {
                                $index = $i;break;
                            }
                        }
                        $shoppingCartInfo['moduleList'][$k]['goodsList'][$index]['increaseBuyList'][] = $value;
                        $temp = true;
                        break;
                    }
                }
                if ($temp) {
                    continue;
                }
                $shoppingCartInfo['moduleList'] = array_reverse($shoppingCartInfo['moduleList']);
                foreach ($shoppingCartInfo['moduleList'] as $kk => $vv) {
                    $shoppingCartInfo['moduleList'][$kk]['goodsList'] = array_reverse($vv['goodsList']);
                }
                $temp = false;
                foreach ($shoppingCartInfo['moduleList'] as $kk => $vv) {
                    foreach ($vv['goodsList'] as $k => $v) {
                        if ($v['selected']) {
                            foreach ($v['promotionInfo']['join_promotionList'] as $_k => $_v) {
                                if ($_v['promotion_id'] == $value['promotion_id']) {
                                    $shoppingCartInfo['moduleList'][$kk]['goodsList'][$k]['increaseBuyList'][] = $value;
                                    $temp = true;
                                    break;
                                }
                            }
                            if ($temp) {
                                break;
                            }
                        }
                    }
                    if ($temp) {
                        break;
                    }
                }
                $shoppingCartInfo['moduleList'] = array_reverse($shoppingCartInfo['moduleList']);
                foreach ($shoppingCartInfo['moduleList'] as $kk => $vv) {
                    $shoppingCartInfo['moduleList'][$kk]['goodsList'] = array_reverse($vv['goodsList']);
                }
            }
        }
        // 如果商品没有换购品增加空数组
        foreach ($shoppingCartInfo['moduleList'] as $kk => $vv) {
            foreach ($vv['goodsList'] as $k => $v) {
                if (!isset($v['increaseBuyList'])) {
                    $shoppingCartInfo['moduleList'][$kk]['goodsList'][$k]['increaseBuyList'] = array();
                }
            }
        }
        return $shoppingCartInfo;
    }

    /**
     * @desc 赠品在购物车的显示
     * @param $shoppingCartInfo
     * @return array
     * @author 柯琼远
     */
    public function getgiftShow($even, $class, $shoppingCartInfo) {
        $shoppingCartInfo['allGiftList'] = array();
        $giftStock = array();//赠品库存数组
        foreach ($shoppingCartInfo['availPromotionList'] as $key => $value) {
            if ($value['promotion_type'] == 15) {
                foreach ($value['resultInfo']['premiums_group'] as $k => $v) {
                    $giftStock[$v['goods_id']] = $v['stock'];
                }
                // 全场
                if ($value['promotion_scope'] == 'all') {
                    foreach ($value['resultInfo']['premiums_group'] as $k => $v) {
                        $shoppingCartInfo['allGiftList'][] = $v;
                    }
                } else {
                    // 非全场
                    $temp = false;
                    foreach ($shoppingCartInfo['moduleList'] as $kk => $vv) {
                        if (empty($vv['default_promotion'])) {
                            continue;
                        }
                        if ($vv['default_promotion']['promotion_id'] == $value['promotion_id']) {
                            $index = count($vv['goodsList']) - 1;
                            for ($i = $index; $i >= 0; $i--) {
                                if ($vv['goodsList'][$i]['selected']) {
                                    $index = $i;break;
                                }
                            }
                            foreach ($value['resultInfo']['premiums_group'] as $k => $v) {
                                $shoppingCartInfo['moduleList'][$kk]['goodsList'][$index]['giftList'][] = $v;
                            }
                            $temp = true;
                            break;
                        }
                    }
                    if ($temp) {
                        continue;
                    }

                    $shoppingCartInfo['moduleList'] = array_reverse($shoppingCartInfo['moduleList']);
                    foreach ($shoppingCartInfo['moduleList'] as $kk => $vv) {
                        $shoppingCartInfo['moduleList'][$kk]['goodsList'] = array_reverse($vv['goodsList']);
                    }
                    $temp = false;
                    foreach ($shoppingCartInfo['moduleList'] as $kk => $vv) {
                        foreach ($vv['goodsList'] as $k => $v) {
                            if ($v['selected']) {
                                foreach ($v['promotionInfo']['join_promotionList'] as $_k => $_v) {
                                    if ($_v['promotion_id'] == $value['promotion_id']) {
                                        foreach ($value['resultInfo']['premiums_group'] as $__k => $__v) {
                                            $shoppingCartInfo['moduleList'][$kk]['goodsList'][$k]['giftList'][] = $__v;
                                        }
                                        $temp = true;
                                        break;
                                    }
                                }
                                if ($temp) {
                                    break;
                                }
                            }
                        }
                        if ($temp) {
                            break;
                        }
                    }
                    $shoppingCartInfo['moduleList'] = array_reverse($shoppingCartInfo['moduleList']);
                    foreach ($shoppingCartInfo['moduleList'] as $kk => $vv) {
                        $shoppingCartInfo['moduleList'][$kk]['goodsList'] = array_reverse($vv['goodsList']);
                    }
                }
            }
        }
        foreach ($shoppingCartInfo['moduleList'] as $key => $value) {
            foreach ($value['goodsList'] as $kk => $vv) {
                if (!isset($vv['giftList'])) {
                    // 如果商品没有赠品增加空数组
                    $shoppingCartInfo['moduleList'][$key]['goodsList'][$kk]['giftList'] = array();
                }
                // 把商品附属赠品加到赠品库存数组
                if ($vv['group_id'] == 0) {
                    foreach ($vv['bind_gift'] as $k => $v) {
                        $giftStock[$v['goods_id']] = $v['stock'];
                    }
                } else {
                    foreach ($vv['groupGoodsList'] as $k => $v) {
                        foreach ($v['bind_gift'] as $_k => $_v) {
                            $giftStock[$_v['goods_id']] = $_v['stock'];
                        }
                    }
                }
            }
        }
        // 把库存和库存提示语匹配上
        foreach ($shoppingCartInfo['moduleList'] as $key => $value) {
            foreach ($value['goodsList'] as $kk => $vv) {
                if ($vv['group_id'] == 0) {
                    $tempGift = [];
                    foreach ($vv['bind_gift'] as $k => $v) {
                        $v = $this->giftStockDesc($v, $giftStock);
                        if ($v['goods_number'] > 0) $tempGift[] = $v;
                    }
                    $shoppingCartInfo['moduleList'][$key]['goodsList'][$kk]['bind_gift'] = $tempGift;
                } else {
                    foreach ($vv['groupGoodsList'] as $k => $v) {
                        $tempGift = [];
                        foreach ($v['bind_gift'] as $_k => $_v) {
                            $_v = $this->giftStockDesc($_v, $giftStock);
                            if ($_v['goods_number'] > 0) $tempGift[] = $_v;
                        }
                        $shoppingCartInfo['moduleList'][$key]['goodsList'][$kk]['groupGoodsList'][$k]['bind_gift'] = $tempGift;
                    }
                }
                $tempGift = [];
                foreach ($vv['giftList'] as $k => $v) {
                    $v = $this->giftStockDesc($v, $giftStock);
                    if ($v['goods_number'] > 0) $tempGift[] = $v;
                }
                $shoppingCartInfo['moduleList'][$key]['goodsList'][$kk]['giftList'] = $tempGift;
            }
        }
        $tempGift = [];
        foreach ($shoppingCartInfo['allGiftList'] as $k => $v) {
            $v = $this->giftStockDesc($v, $giftStock);
            if ($v['goods_number'] > 0) $tempGift[] = $v;
        }
        $shoppingCartInfo['allGiftList'] = $tempGift;
        return $shoppingCartInfo;
    }

    /**
     * @desc 把库存和库存提示语匹配上
     * @param $giftInfo
     * @param $giftStock
     * @return array
     * @author 柯琼远
     */
    public function giftStockDesc($giftInfo, &$giftStock) {
        $giftInfo['stock_desc'] = "";
        if ($giftStock[$giftInfo['goods_id']] <= 0) {
            $giftInfo['goods_number'] = 0;
            $giftInfo['stock_desc'] = "该赠品已送完";
        } elseif ($giftStock[$giftInfo['goods_id']] < $giftInfo['goods_number']) {
            $giftInfo['goods_number'] = $giftStock[$giftInfo['goods_id']];
            $giftInfo['stock_desc'] = "该赠品剩余".$giftStock[$giftInfo['goods_id']]."件";
        }
        $giftInfo['stock'] = $giftStock[$giftInfo['goods_id']];
        $giftStock[$giftInfo['goods_id']] -= $giftInfo['goods_number'];
        return $giftInfo;
    }

    /**
     * @desc 加价购商品和赠品在确认订单页面的显示
     * @param $shoppingCartInfo
     * @return array
     * @author 柯琼远
     */
    public function getIncreaseBuyGiftShow($even, $class, $shoppingCartInfo) {
        $shoppingCartInfo['goodsList'] = array_reverse($shoppingCartInfo['goodsList']);
        // 赠品
        $shoppingCartInfo['allGiftList'] = array();
        $giftStock = array();//赠品库存
        foreach ($shoppingCartInfo['availPromotionList'] as $key => $value) {
            if ($value['promotion_type'] == 15) {
                foreach ($value['resultInfo']['premiums_group'] as $k => $v) {
                    $giftStock[$v['goods_id']] = $v['stock'];
                }
                // 全场
                if ($value['promotion_scope'] == 'all') {
                    foreach ($value['resultInfo']['premiums_group'] as $k => $v) {
                        $shoppingCartInfo['allGiftList'][] = $v;
                    }
                } else {
                    // 非全场
                    $temp = false;
                    foreach ($shoppingCartInfo['goodsList'] as $k => $v) {
                        foreach ($v['promotionInfo']['join_promotionList'] as $_k => $_v) {
                            if ($_v['promotion_id'] == $value['promotion_id']) {
                                foreach ($value['resultInfo']['premiums_group'] as $__k => $__v) {
                                    $shoppingCartInfo['goodsList'][$k]['giftList'][] = $__v;
                                }
                                $temp = true;
                                break;
                            }
                        }
                        if ($temp) {
                            break;
                        }
                    }
                }
            }
        }
        // 换购品
        $shoppingCartInfo['allIncreaseBuyList'] = array();
        foreach ($shoppingCartInfo['increaseBuyList'] as $key => $value) {
            // 全场
            if ($value['promotion_scope'] == 'all') {
                $shoppingCartInfo['allIncreaseBuyList'][] = $value;
            } else {
                // 非全场
                $temp = false;
                foreach ($shoppingCartInfo['goodsList'] as $k => $v) {
                    foreach ($v['promotionInfo']['join_promotionList'] as $_k => $_v) {
                        if ($_v['promotion_id'] == $value['promotion_id']) {
                            $shoppingCartInfo['goodsList'][$k]['increaseBuyList'][] = $value;
                            $temp = true;
                            break;
                        }
                    }
                    if ($temp) {
                        break;
                    }
                }
            }
        }
        $shoppingCartInfo['goodsList'] = array_reverse($shoppingCartInfo['goodsList']);

        foreach ($shoppingCartInfo['goodsList'] as $kk => $vv) {
            // 空赠品和空换购品添加空数组
            if (!isset($vv['giftList'])) {
                $shoppingCartInfo['goodsList'][$kk]['giftList'] = array();
            }
            if (!isset($vv['increaseBuyList'])) {
                $shoppingCartInfo['goodsList'][$kk]['increaseBuyList'] = array();
            }
            // 把商品附属赠品加到赠品库存数组
            if ($vv['group_id'] == 0) {
                foreach ($vv['bind_gift'] as $k => $v) {
                    $giftStock[$v['goods_id']] = $v['stock'];
                }
            } else {
                foreach ($vv['groupGoodsList'] as $k => $v) {
                    foreach ($v['bind_gift'] as $_k => $_v) {
                        $giftStock[$_v['goods_id']] = $_v['stock'];
                    }
                }
            }
        }

        // 把库存和库存提示语匹配上
        foreach ($shoppingCartInfo['goodsList'] as $key => $value) {
            if ($value['group_id'] == 0) {
                $tempGift = [];
                foreach ($value['bind_gift'] as $k => $v) {
                    $v = $this->giftStockDesc($v, $giftStock);
                    if ($v['goods_number'] > 0) $tempGift[] = $v;
                }
                $shoppingCartInfo['goodsList'][$key]['bind_gift'] = $tempGift;
            } else {
                foreach ($value['groupGoodsList'] as $k => $v) {
                    $tempGift = [];
                    foreach ($v['bind_gift'] as $_k => $_v) {
                        $_v = $this->giftStockDesc($_v, $giftStock);
                        if ($_v['goods_number'] > 0) $tempGift[] = $_v;
                    }
                    $shoppingCartInfo['goodsList'][$key]['groupGoodsList'][$k]['bind_gift'] = $tempGift;
                }
            }
            $tempGift = [];
            foreach ($value['giftList'] as $k => $v) {
                $v = $this->giftStockDesc($v, $giftStock);
                if ($v['goods_number'] > 0) $tempGift[] = $v;
            }
            $shoppingCartInfo['goodsList'][$key]['giftList'] = $tempGift;
        }
        $tempGift = [];
        foreach ($shoppingCartInfo['allGiftList'] as $k => $v) {
            $v = $this->giftStockDesc($v, $giftStock);
            if ($v['goods_number'] > 0) $tempGift[] = $v;
        }
        $shoppingCartInfo['allGiftList'] = $tempGift;
        return $shoppingCartInfo;
    }

    /**
     * @desc 梳理购物车列表的字段
     * @param $param
     * @return array
     * @author 柯琼远
     */
    public function getshoppingCartField($even, $class, $param) {
        $globalCartInfo = $param['globalCart'];
        $shoppingCartInfo = $param['shoppingCart'];
        // 全场满XX包邮
        $freightInfo = BaiyangFreghtTemplate::getInstance()->getFreightFreePrice();
        $result = array(
            'shoppingCart' => [
                'moduleList'=> $shoppingCartInfo['moduleList'],
                'allPromotionInfo'=> $shoppingCartInfo['allPromotionInfo'],
                'allIncreaseBuyList'=> $shoppingCartInfo['allIncreaseBuyList'],
                'allGiftList'=> $shoppingCartInfo['allGiftList'],
                'cartName' => "商城自营",
                'allSelected' => 1,
                'rxExist' => 0,
                'unavailExist' => 0,
                'totalQty' => 0,
                'selectQty' => 0,
                'freeFreightText' => isset($freightInfo[0]) ? "全场满{$freightInfo[0]}元包邮" : '',
                'freePrice' => "0.00",
                'totalPrice' => "0.00",
            ],
            'globalCart' => [
                'goodsList'=>$globalCartInfo['goodsList'],
                'cartName' => "海外优选",
                'allSelected' => 1,
                'rxExist' => 0,
                'unavailExist' => 0,
                'totalQty' => 0,
                'selectQty' => 0,
                'freeFreightText' => isset($freightInfo[1]) ? "全场满{$freightInfo[1]}元包邮" : '',
                'freePrice' => "0.00",
                'totalPrice' => "0.00",
            ],
            'isLogin' => 0,
            'allSelected' => 1,
            'rxExist' => 0,
            'unavailExist' => 0,
            'rxText' => "",//处方药文案
            'buttonName' => "",
            'totalQty' => 0,
            'selectQty' => 0,
            'freePrice' => "0.00",
            'totalPrice' => "0.00",
        );
        // 海外购
        foreach ($result['globalCart']['goodsList'] as $key => $value) {
            if ($value['selected'] == 1) {
                $result['globalCart']['totalPrice'] = bcadd($result['globalCart']['totalPrice'], $value['discount_total'], 2);
                $result['globalCart']['selectQty'] += $value['goods_number'];
            }
            $result['globalCart']['totalQty'] += $value['goods_number'];
            if ($value['group_id'] == 0) {
                if ($value['selected'] == 0 && $value['goods_status'] == 0) {
                    $result['globalCart']['allSelected'] = 0;
                }
                if ($value['goods_status'] != 0) {
                    $result['globalCart']['unavailExist'] = 1;
                }
                unset($result['globalCart']['goodsList'][$key]['promotion_price']);
                unset($result['globalCart']['goodsList'][$key]['promotion_total']);
            } else {
                if ($value['selected'] == 0 && $value['group_status'] == 1) {
                    $result['globalCart']['allSelected'] = 0;
                }
                if ($value['group_status'] == 0) {
                    $result['globalCart']['unavailExist'] = 1;
                }
                foreach ($value['groupGoodsList'] as $k => $v) {
                    unset($result['globalCart']['goodsList'][$key]['groupGoodsList'][$k]['promotion_price']);
                    unset($result['globalCart']['goodsList'][$key]['groupGoodsList'][$k]['promotion_total']);
                }
                unset($result['globalCart']['goodsList'][$key]['promotion_total']);
            }
        }

        // 非海外购
        $noAudits = $this->func->getConfigValue('order_no_audit_goods_type').',5';
        foreach ($result['shoppingCart']['moduleList'] as $key => $value) {
            foreach ($value['goodsList'] as $kk => $vv) {
                if ($vv['selected'] == 1) {
                    $result['shoppingCart']['totalPrice'] = bcadd($result['shoppingCart']['totalPrice'], $vv['discount_total'], 2);
                    $result['shoppingCart']['selectQty'] += $vv['goods_number'];
                }
                $result['shoppingCart']['totalQty'] += $vv['goods_number'];
                if ($vv['group_id'] == 0) {
                    if ($vv['selected'] == 1) {
                        if (strpos($noAudits, (string)$vv['drug_type']) === false) $result['shoppingCart']['rxExist'] = 1;
                    }
                    if ($vv['selected'] == 0 && $vv['goods_status'] == 0) {
                        $result['shoppingCart']['allSelected'] = 0;
                    }
                    if ($vv['goods_status'] != 0) {
                        $result['shoppingCart']['unavailExist'] = 1;
                    }
                    unset($result['shoppingCart']['moduleList'][$key]['goodsList'][$kk]['selected_promotion']);
                    unset($result['shoppingCart']['moduleList'][$key]['goodsList'][$kk]['increase_buy']);
                    unset($result['shoppingCart']['moduleList'][$key]['goodsList'][$kk]['promotion_price']);
                    unset($result['shoppingCart']['moduleList'][$key]['goodsList'][$kk]['promotion_total']);
                } else {
                    if ($vv['selected'] == 0 && $vv['group_status'] == 1) {
                        $result['shoppingCart']['allSelected'] = 0;
                    }
                    if ($vv['group_status'] == 0) {
                        $result['shoppingCart']['unavailExist'] = 1;
                    }
                    foreach ($vv['groupGoodsList'] as $k => $v) {
                        if ($vv['selected'] == 1) {
                            if (strpos($noAudits, (string)$v['drug_type']) === false) $result['shoppingCart']['rxExist'] = 1;
                        }
                        unset($result['shoppingCart']['moduleList'][$key]['goodsList'][$kk]['groupGoodsList'][$k]['promotion_price']);
                        unset($result['shoppingCart']['moduleList'][$key]['goodsList'][$kk]['groupGoodsList'][$k]['promotion_total']);
                    }
                    unset($result['shoppingCart']['moduleList'][$key]['goodsList'][$kk]['promotion_total']);
                }
                // 加价购
                foreach ($vv['increaseBuyList'] as $k => $v) {
                    if ($v['selected'] == 1) {
                        $result['shoppingCart']['totalPrice'] = bcadd($result['shoppingCart']['totalPrice'], $v['discount_total'], 2);
                        $result['shoppingCart']['selectQty'] += 1;
                    }
                    $result['shoppingCart']['totalQty'] += 1;

                }
                unset($result['shoppingCart']['moduleList'][$key]['goodsList'][$kk]['promotionInfo']['join_promotionList']);
            }
        }
        // 加价购
        foreach ($shoppingCartInfo['allIncreaseBuyList'] as $key => $value) {
            if ($value['selected'] == 1) {
                $result['shoppingCart']['totalPrice'] = bcadd($result['shoppingCart']['totalPrice'], $value['discount_total'], 2);
                $result['shoppingCart']['selectQty'] += 1;
            }
            $result['shoppingCart']['totalQty'] += 1;
        }
        // 满减满折优惠的金额
        foreach ($shoppingCartInfo['availPromotionList'] as $key => $value) {
            if ($value['promotion_type'] == 5 || $value['promotion_type'] == 10) {
                $result['shoppingCart']['freePrice'] = bcadd($result['shoppingCart']['freePrice'], $value['resultInfo']['reduce_price'], 2);
            }
        }
        unset($result['shoppingCart']['allPromotionInfo']['default_promotion']);
        unset($result['shoppingCart']['allPromotionInfo']['join_promotionList']);
        $result['shoppingCart']['totalPrice'] = bcsub($result['shoppingCart']['totalPrice'], $result['shoppingCart']['freePrice'], 2);
        // 合并海外购和百洋
        $result['allSelected'] = $result['shoppingCart']['allSelected'] && $result['globalCart']['allSelected'] ? 1 : 0;
        $result['unavailExist'] = $result['shoppingCart']['unavailExist'] || $result['globalCart']['unavailExist'] ? 1 : 0;
        $result['rxExist'] = $result['shoppingCart']['rxExist'] || $result['globalCart']['rxExist'] ? 1 : 0;
        $result['rxText'] = $result['rxExist'] ? "购买处方药需凭医生有效处方，服用请遵循医嘱。有关用药信息请咨询药师。" : "";
        $result['buttonName'] = $result['rxExist'] ? "提交预订" : "";
        $result['totalQty'] = $result['shoppingCart']['totalQty'] + $result['globalCart']['totalQty'];
        $result['selectQty'] = $result['shoppingCart']['selectQty'] + $result['globalCart']['selectQty'];
        $result['freePrice'] = bcadd($result['shoppingCart']['freePrice'], $result['globalCart']['freePrice'], 2);
        $result['totalPrice'] = bcadd($result['shoppingCart']['totalPrice'], $result['globalCart']['totalPrice'], 2);

        // 空数组转化成null
        foreach ($result['shoppingCart']['moduleList'] as $key => $value) {
            if (empty($value['default_promotion'])) {
                $result['shoppingCart']['moduleList'][$key]['default_promotion'] = null;
            }
            foreach ($value['goodsList'] as $kk => $vv) {
                if (isset($vv['discountPromotion']) && empty($vv['discountPromotion'])) {
                    $result['shoppingCart']['moduleList'][$key]['goodsList'][$kk]['discountPromotion'] = null;
                }
                if (empty($vv['promotionInfo']['single_limitbuy'])) {
                    $result['shoppingCart']['moduleList'][$key]['goodsList'][$kk]['promotionInfo']['single_limitbuy'] = null;
                }
            }
        }
        foreach ($result['globalCart']['goodsList'] as $key => $value) {
            if (isset($value['discountPromotion']) && empty($value['discountPromotion'])) {
                $result['globalCart']['goodsList'][$key]['discountPromotion'] = null;
            }
        }
        return $result;
    }
	
	/**
	 * 跨境购物车字段梳理
	 * @param $even
	 * @param $class
	 * @param $shoppingCartInfo
	 * @return array
	 * @author sarcasme
	 */
	public function getGlobalInfoField ($even, $class, $shoppingCartInfo)
	{
		$result = [
			'goodsList' => $shoppingCartInfo['goodsList'],
			'consigneeList' => array(), // 收货地址列表（先初始化）
            'paymentList' => [], // 支付方式列表
            'expressList' => [], // 配送方式列表
            'freightInfo' => ['freight'=>"0.00",'tips'=>['free_price'=>"0.00",'not_free_fee'=>"0.00",'lack_price'=>'0.00','promote_text'=>'']], // 运费信息
            'announcement' => "", // 配送公告
			'expressType' => 0, // 配送方式：0-普通快递,1-顾客自提,2-两小时达,3-当日达
			'goodsIds' => '', // 商品id字符串
			'isDummy' => 1, // 是否虚拟订单
			'paymentId' => 0, // 支付方式：0-在线支付，3 : 货到付款，默认：0
			'isExpressFree' => 0, // 是否包邮
			'totalQty' => 0, // 商品总数量
			'goodsTotalPrice' => '0.00', // 商品总价
			'costPrice' => '0.00', // 应付金额
			'taxAmount' =>  '0.00',   //应付税费
			'isBalance' =>  0,   //是否使用余额
			'minusPrice'    =>  '0.00', //满减金额
			'isGlobal'  =>  1,  //是否为海外购订单,
			'identityNumber'    =>  '', //身份证号码,
			'isShowGlobalAgreement' =>  0, //是否显示海外购协议
		];
		if( isset($shoppingCartInfo['is_first'])&&($shoppingCartInfo['is_first'] == 1) ) { $result['isShowGlobalAgreement'] = 1;}
		// 计算
		foreach ($shoppingCartInfo['goodsList'] as $item)
		{
			$result['goodsTotalPrice'] += bcmul($item['goods_price'], $item['goods_number'], 2);
			$result['taxAmount'] += $item['goods_tax_amount'];
			$result['totalQty'] += $item['goods_number'];
            if ($item['drug_type'] != 5) $result['isDummy'] = 0;
		}
		$result['costPrice'] = $result['goodsTotalPrice'] + $result['taxAmount'];
		// 删除多余的字段
		foreach ($result['goodsList'] as $key => $value)
		{
			if ($value['group_id'] == 0) {
				unset($result['goodsList'][$key]['selected_promotion']);
				unset($result['goodsList'][$key]['increase_buy']);
				unset($result['goodsList'][$key]['promotion_price']);
				unset($result['goodsList'][$key]['promotion_total']);
			} else {
				unset($result['goodsList'][$key]['promotion_total']);
				foreach ($value['groupGoodsList'] as $k => $v) {
					unset($result['goodsList'][$key]['groupGoodsList'][$k]['promotion_price']);
					unset($result['goodsList'][$key]['groupGoodsList'][$k]['promotion_total']);
				}
			}
			unset($result['goodsList'][$key]['promotionInfo']);
		}
		$goodsIdArr = array_column($result['goodsList'], 'goods_id');
		$result['goodsIds'] = implode(',', $goodsIdArr);
		return $result;
    }
    
    /**
     * @desc 梳理确认订单页面的字段
     * @param $shoppingCartInfo
     * @return array
     * @author 柯琼远
     */
    public function getOrderInfoField($even, $class, $shoppingCartInfo)
    {
        $result = [
            'goodsList' => $shoppingCartInfo['goodsList'],
            'allIncreaseBuyList' => $shoppingCartInfo['allIncreaseBuyList'],
            'allGiftList' => $shoppingCartInfo['allGiftList'],
            'couponList' => $shoppingCartInfo['couponList'], // 优惠券列表
            'couponInfo' => [], // 选中优惠券信息
            'consigneeList' => array(), // 收货地址列表（先初始化）
            'o2oInfo' => null, // O2O信息
            'paymentList' => [],
            'expressList' => [],
            'freightInfo' => ['freight'=>"0.00",'tips'=>['free_price'=>"0.00",'not_free_fee'=>"0.00",'lack_price'=>'0.00','promote_text'=>'']], // 运费信息
            'facePayTips' => ['free_price'=>"0.00",'not_free_fee'=>"0.00",'lack_price'=>'0.00','promote_text'=>''], // 到付提示
            'invoiceInfo' => ["if_receipt"=>0], // 发票信息
            'announcement' => "", // 配送公告
            'ifFacePay' => 0, // 是否可选货到付款
            'expressType' => 0, // 配送方式：0-普通快递,1-顾客自提,2-两小时达,3-当日达
            'expressText' => "",
            'goodsIds' => '', // 商品id字符串
            'isDummy' => 1, // 是否虚拟订单
            'hasSupplier' => 0, // 是否存在非自营商家
            'paymentId' => 0, // 支付方式：0-在线支付，3 : 货到付款，默认：0
            'balance' => '0.00', // 用户的余额
            'costBalance' => '0.00', // 订单花费的余额
            'isBalance' => 1, // 是否使用余额
            'isSetPwd' => 0, // 是否已设置支付密码
            'isNeedPwd' => 0, // 是否需要支付密码
            'rxExist' => 0, // 是否处方订单
            'isExpressFree' => 0, // 是否包邮
            'totalQty' => 0, // 商品总数量
            'goodsTotalPrice' => '0.00', // 商品总价
            'minusPrice' => '0.00', // 满减金额
            'rebatePrice' => '0.00', // 满折金额
            'freePrice' => '0.00', // 优惠金额
            'couponPrice' => '0.00', // 优惠券优惠金额
            'costPrice' => '0.00', // 应付金额
            'isGlobal' => 0, //是否为海外购订单
            'identityNumber' => '', //身份证号码
            'taxAmount' => '',//进口税
            'isShowGlobalAgreement' => 0,// 是否显示海外购协议
        ];
        $noAudits = $this->func->getConfigValue('order_no_audit_goods_type').',5';
        // 计算
        foreach ($shoppingCartInfo['goodsList'] as $key => $value) {
            $result['goodsTotalPrice'] = bcadd($result['goodsTotalPrice'], $value['discount_total'], 2);
            $result['totalQty'] += $value['goods_number'];
            foreach ($value['increaseBuyList'] as $k => $v) {
                $result['goodsTotalPrice'] = bcadd($result['goodsTotalPrice'], $v['discount_total'], 2);
                $result['totalQty'] += $v['goods_number'];
            }
        }
        foreach ($shoppingCartInfo['allIncreaseBuyList'] as $key => $value) {
            $result['goodsTotalPrice'] = bcadd($result['goodsTotalPrice'], $value['discount_total'], 2);
            $result['goodsIds'] .= ','.$value['goods_id'];
            $result['totalQty'] += $value['goods_number'];
        }
        $result['costPrice'] = $result['goodsTotalPrice'];
        foreach ($shoppingCartInfo['availPromotionList'] as $key => $value) {
            if ($value['promotion_type'] == 5) {
                $result['minusPrice'] = bcadd($result['minusPrice'], $value['resultInfo']['reduce_price'], 2);
            }
            if ($value['promotion_type'] == 10) {
                $result['rebatePrice'] = bcadd($result['rebatePrice'], $value['resultInfo']['reduce_price'], 2);
            }
            if ($value['promotion_type'] == 20) {
                $result['isExpressFree'] = 1;
            }
        }
        $result['freePrice'] = bcadd($result['minusPrice'], $result['rebatePrice'], 2);
        $result['costPrice'] = bcsub($result['costPrice'], $result['freePrice'], 2);
        // 删除多余的字段
        foreach ($result['goodsList'] as $key => $value) {
            if ($value['group_id'] == 0) {
                unset($result['goodsList'][$key]['selected_promotion']);
                unset($result['goodsList'][$key]['increase_buy']);
            }
            unset($result['goodsList'][$key]['promotionInfo']);
        }
        // 优惠券
        foreach ($shoppingCartInfo['couponList'] as $value) {
            if ($value['selected'] == 1) {
                $result['couponPrice'] = $value['coupon_price'];
                $result['couponInfo'] = $value;
                if ($value['coupon_type'] == 3) $result['isExpressFree'] = 1;
                break;
            }
        }
        $result['costPrice'] = bcsub($result['costPrice'], $result['couponPrice'], 2);
        // 虚拟订单、处方单、自营
        foreach ($shoppingCartInfo['goodsList'] as $key => $value) {
            if ($value['supplier_id'] != 1) $result['hasSupplier'] = 1;
            if ($value['group_id'] == 0) {
                if ($value['drug_type'] != 5) $result['isDummy'] = 0;
                //if ($value['drug_type'] == 1) $result['rxExist'] = 1;
                if (strpos($noAudits, (string)$value['drug_type']) === false) $result['rxExist'] = 1;
                $result['goodsIds'] .= ','.$value['goods_id'];
            } else {
                foreach ($value['groupGoodsList'] as $k => $v) {
                    if ($v['drug_type'] != 5) $result['isDummy'] = 0;
                    //if ($v['drug_type'] == 1) $result['rxExist'] = 1;
                    if (strpos($noAudits, (string)$v['drug_type']) === false) $result['rxExist'] = 1;
                    $result['goodsIds'] .= ','.$v['goods_id'];
                }
            }
        }
        $result['goodsIds'] = trim($result['goodsIds'],',');
        // 改为null
        foreach ($result['goodsList'] as $key => $value) {
            if (isset($value['discountPromotion']) && empty($value['discountPromotion'])) {
                $result['goodsList'][$key]['discountPromotion'] = null;
            }
        }
        $result['costPrice'] = $result['costPrice'] > 0 ? $result['costPrice'] : "0.00";
        return $result;
    }

	/**
	 * @desc 梳理提交订单的字段
	 * @param $shoppingCartInfo
	 * @return array
	 * @author 柯琼远
	 */
	public function getCommitOrderField($even, $class, $shoppingCartInfo) {
		$result = [
			'goodsList'          => [],
			'availPromotionList' => $shoppingCartInfo['availPromotionList'],
			'giftList'           => [],
			'couponInfo'         => [],
			'o2oInfo'            => [],
			'consigneeInfo'      => [],
			'supplierList'       => [],
            'hasSupplier'        => 0, // 是否存在非自营商家
			'goodsIds'           => '', // 商品id字符串
			'orderSn'            => '',
			'isDummy'            => 1, // 是否虚拟订单
			'rxExist'            => 0, // 是否处方订单
			'allRx'              => 1, // 是否全是处方药
			'needAudit'          => 0, // 是否需要审核
			'expressType'        => 0, // 配送方式:0-普通快递,1-顾客自提,2-两小时达,3-当日达（*）
			'shopId'             => 0,
			'paymentId'          => 0, // 支付方式：0-在线支付，3-货到付款
			'paymentName'        => '',
			'invoiceType'        => 0, //发票类型 0不需要 1个人 2单位
			'invoiceHeader'      => '',
			'taxpayerNumber'     => '',
			'buyerMessage'       => '',
			'isExpressFree'      => 0,
			'userId'             => 0,
			'unionUserId'        => '',
			'inviteCode'         => '',
			'more_platform_sign' => '',//育学园
			'phone'              => '',
			'addressId'          => 0,
			'o2oTime'            => 0,
			'isBalance'          => 0,
			'isGlobal'           => 0,
			'balance'            => '0.00',
			'payPassword'        => '',
			'costBalance'        => '0.00',
			'expendSn'           => '',//余额支付的流水号
			'callbackPhone'      => '',
			'ordonnancePhoto'    => '',
			'status'             => 'paying',
			'discount_remark'    => '',
			'goodsTotalPrice'    => '0.00', // 商品总价
			'couponPrice'        => '0.00', // 优惠券优惠金额
			'youhuiPrice'        => '0.00', // 满减+满折金额
			'orderDiscountMoney' => '0.00', // 满减+满折+优惠券金额
			'freight'            => '0.00',
			'costPrice'          => '0.00', // 应付金额
		];
        $noAudits = $this->func->getConfigValue('order_no_audit_goods_type').',5';
		foreach ($shoppingCartInfo['goodsList'] as $key => $value) {
			$result['goodsTotalPrice'] = bcadd($result['goodsTotalPrice'], $value['discount_total'], 2);
			foreach ($value['promotionInfo']['join_promotionList'] as $limitbuyInfo) {
				if ($limitbuyInfo['promotion_type'] == BaiyangPromotionEnum::LIMIT_BUY) {
					$value['limitBuyIdList'][] = $limitbuyInfo['promotion_id'];
				}
			}
			unset($value['promotionInfo']);
			if ($value['group_id'] == 0) {
				$result['goodsList'][] = $value;
				$result['goodsIds'] .= ','.$value['goods_id'];
				if ($value['drug_type'] != 5) $result['isDummy'] = 0;
				if ($value['drug_type'] == 1) $result['rxExist'] = 1;
				if ($value['drug_type'] != 1) $result['allRx'] = 0;
				if (strpos($noAudits, (string)$value['drug_type']) === false) $result['needAudit'] = 1;
			} else {
				foreach ($value['groupGoodsList'] as $k => $v) {
					$v['group_id'] = $value['group_id'];
                    $v['supplier_id'] = $value['groupGoodsList'][0]['supplier_id'];
					$result['goodsList'][] = $v;
					$result['goodsIds'] .= ','.$v['goods_id'];
					if ($v['drug_type'] != 5) $result['isDummy'] = 0;
					if ($v['drug_type'] == 1) $result['rxExist'] = 1;
					if ($v['drug_type'] != 1) $result['allRx'] = 0;
                    if (strpos($noAudits, (string)$v['drug_type']) === false) $result['needAudit'] = 1;
					unset($v['promotion_price'],$v['promotion_total']);
				}
			}
		}
		foreach ($shoppingCartInfo['increaseBuyList'] as $key => $value) {
			$result['goodsTotalPrice'] = bcadd($result['goodsTotalPrice'], $value['discount_total'], 2);
			$result['goodsList'][] = $value;
			$result['goodsIds'] .= ','.$value['goods_id'];
		}
		$result['costPrice'] = $result['goodsTotalPrice'];
		foreach ($result['availPromotionList'] as $key => $value) {
            $result['discount_remark'] .= $value['resultInfo']['copywriter']."；";
			if ($value['promotion_type'] == 5 || $value['promotion_type'] == 10) {
				$result['youhuiPrice'] = bcadd($result['youhuiPrice'], $value['resultInfo']['reduce_price'], 2);
			} elseif ($value['promotion_type'] == 15) {
				$result['giftList'] = array_merge($result['giftList'], $value['resultInfo']['premiums_group']);
			} elseif ($value['promotion_type'] == 20) {
				$result['isExpressFree'] = 1;
			}
		}
		// 优惠券
		foreach ($shoppingCartInfo['couponList'] as $value) {
			if ($value['selected'] == 1) {
				$result['couponPrice'] = $value['coupon_price'];
				$result['couponInfo']  = $value;
                if ($value['coupon_type'] == 3) $result['isExpressFree'] = 1;
				break;
			}
		}
        // 赠品库存不足的分配规则
        $giftStock = array();
        foreach ($result['giftList'] as $key => $value) {
            $giftStock[$value['goods_id']] = $value['stock'];
        }
        foreach ($result['giftList'] as $key => $value) {
            $result['giftList'][$key] = $this->giftStockDesc($value, $giftStock);
        }
		$result['orderDiscountMoney'] = bcadd($result['couponPrice'], $result['youhuiPrice'], 2);
        $result['goodsIds'] = trim($result['goodsIds'],',');
		$result['costPrice'] = bcsub($result['costPrice'], $result['orderDiscountMoney'], 2);
        $result['costPrice'] = $result['costPrice'] > 0 ? $result['costPrice'] : "0.00";
        // 拆单
		return $this->splitOrder($result);
	}

    /**
     * @desc 拆单
     * @param $result array
     * @return array
     * @author 柯琼远
     */
    private function splitOrder($result) {
        $supplierList = [];
        // 商家列表
        foreach ($result['goodsList'] as $key => $value) {
            if (isset($supplierList[$value['supplier_id']])) {
                // 商品价格
                $supplierList[$value['supplier_id']]['goodsTotalPrice'] = bcadd($supplierList[$value['supplier_id']]['goodsTotalPrice'], $value['discount_total'], 2);
                // 订单优惠价格
                $promotion_total = isset($value['promotion_total']) ? $value['promotion_total'] : $value['discount_total'];
                $supplierList[$value['supplier_id']]['orderDiscountMoney'] = bcadd($supplierList[$value['supplier_id']]['orderDiscountMoney'], bcsub($value['discount_total'], $promotion_total, 2), 2);
                // 商品列表
                $supplierList[$value['supplier_id']]['goodsList'][] = $value;
                if ($value['drug_type'] == 1) {
                    $supplierList[$value['supplier_id']]['rxExist'] = 1;//包含处方药
                } else {
                    $supplierList[$value['supplier_id']]['allRx'] = 0;//全是处方药
                }
                if ($value['drug_type'] != 5) $supplierList[$value['supplier_id']]['isDummy'] = 0;//不全是虚拟商品
            } else {
                $supplierList[$value['supplier_id']] = [
                    'supplier_id' => $value['supplier_id'],//商家ID
                    'orderSn' => '',//子订单订单号，先初始化处理
                    'costPrice' => "0.00",//子订单总费用
                    'freight' => "0.00",//子订单运费
                    'costBalance' => "0.00",//分摊余额，先初始化处理
                    'goodsTotalPrice' => $value['discount_total'],//商品价格
                    'couponPrice' => "0.00",//优惠券
                    'youhuiPrice' => "0.00",//满减满折
                    'orderDiscountMoney' => bcsub($value['discount_total'], $value['promotion_total'], 2),//优惠券+满减满折
                    'rxExist' => $value['drug_type'] == 1 ? 1 : 0,
                    'allRx' => $value['drug_type'] == 1 ? 1 : 0,
                    'isDummy' => $value['drug_type'] == 5 ? 1 : 0,//全是虚拟商品 1 是， 0 不是
                    'goodsList' => [$value],//商品列表
                    'giftList' => [],//赠品列表
                ];
            }
        }
        // 赠品列表
        foreach ($result['giftList'] as $key => $value) {
            if (isset($supplierList[$value['supplier_id']])) {
                $supplierList[$value['supplier_id']]['giftList'][] = $value;
            }
        }
        // 按商品价格的升序排序
        $sort = [];
        foreach ($supplierList as $key => $value) $sort[] = $value['goodsTotalPrice'];
        array_multisort($sort, SORT_ASC, $supplierList);
        // 计算金额
        foreach ($supplierList as $key => $value) {
            foreach ($value['goodsList'] as $k => $v) {
                if (isset ($v['coupon_total']) && !empty($v['coupon_total'])) {
                    // 优惠券价格分摊
                    $supplierList[$key]['couponPrice'] = bcadd($supplierList[$key]['couponPrice'], $v['coupon_total'], 2);
                }
            }
            // 满减满折价格分摊
            $supplierList[$key]['youhuiPrice'] = bcsub($supplierList[$key]['orderDiscountMoney'], $supplierList[$key]['couponPrice'], 2);
            // 订单总金额
            $supplierList[$key]['costPrice'] = bcsub($value['goodsTotalPrice'], $supplierList[$key]['orderDiscountMoney'], 2);
        }
        if (count($supplierList) > 1 || $supplierList[0]['supplier_id'] != 1) {
            $result['hasSupplier'] = 1;
        }
        $result['supplierList'] = $supplierList;
        return $result;
    }

    /**
     * @desc 梳理提交海外购订单的字段
     * @param $shoppingCartInfo
     * @return array
     * @author 舒露
     */
    public function getCommitGlobalOrderField($even, $class, $shoppingCartInfo) {
        $result = [
            'goodsList'          => $shoppingCartInfo['goodsList'],
	        'bond'                  =>  $shoppingCartInfo['bond'], //所属保税区
            'consigneeInfo'      => [],
            'goodsIds'           => '', // 商品id字符串
            'orderSn'            => '',
            'isDummy'            => 1, // 是否虚拟订单
            'expressType'        => 0, // 配送方式:0-普通快递,1-顾客自提,2-两小时达,3-当日达（*）
            'paymentId'          => 0, // 支付方式：0-在线支付，3-货到付款
            'paymentName'        => '',
            'buyerMessage'      => '',
            'isExpressFree'      => 0,
            'userId'             => 0,
            'unionUserId'        => '',
            'phone'              => '',
            'addressId'          => 0,
            'isGlobal'           => 1,
            'callbackPhone'      => '',
            'ordonnancePhoto'    => '',
            'status'             => 'paying',
            'goodsTotalPrice'    => '0.00', // 商品总价
	        'order_tax_amount'  =>  '0.00', //总税费
            'freight'            => '0.00',
            'costPrice'          => '0.00', // 应付金额
	        'totalCount'        =>  0, //总数量
	        'goods_price'       =>  0, //商品总价
	        'insure_amount'     =>  0, //保费
	        'shopId'           =>  $shoppingCartInfo['shopId'],
        ];
        foreach ($shoppingCartInfo['goodsList'] as $item) {
        	$result['order_tax_amount'] = bcadd($result['order_tax_amount'], $item['goods_tax_amount'], 2);
            $result['goodsTotalPrice'] = bcadd($result['goodsTotalPrice'], bcmul($item['goods_price'], $item['goods_number'], 2), 2);
            $result['totalCount'] += $item['goods_number'];
            #$result['goods_price'] = bcadd($result['goods_price'], $item['sku_price'], 2);
            if ($item['drug_type'] != 5) $result['isDummy'] = 0;
        }
        $result['costPrice'] = bcadd($result['goodsTotalPrice'], $result['order_tax_amount'], 2);
        $result['goodsIds'] = implode( ',', array_column($result['goodsList'], 'goods_id'));
        return $result;
    }

    /**
     * @desc 获取以优惠券为断点，分割后的活动数组
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param
     *       - array shoppingCartGoodsInfo  购物车商品活动信息
     *       - array basicParam   基础信息:platform、user_id、is_temp等
     * @return array [] 分割后的活动数组
     * @author 吴俊华
     */
    public function getSplitPromotions($event, $class, $param)
    {
        $shoppingCartGoodsInfo = $param['shoppingCartGoodsInfo'];
        $basicParam = $param['basicParam'];
        $joinPromotion = []; //商品参加的促销活动
        $beforeCoupon = []; //优先级在优惠券之前的活动
        $afterCoupon = [];  //优先级在优惠券之后的活动
        //商品参加的促销活动去重
        foreach($shoppingCartGoodsInfo as $value){
            if(isset($value['promotionInfo']['join_promotionList']) && !empty($value['promotionInfo']['join_promotionList'])){
                foreach($value['promotionInfo']['join_promotionList'] as $val){
                    $joinPromotion[$val['promotion_id']] = $val;
                }
            }
        }
        //促销活动根据活动优先级、使用范围排序
        $joinPromotion = $this->sortPromotion($joinPromotion);
        //促销活动匹配商品(组装侦听器需要的参数)
        $promotionGoods = $this->promotionMatchJoinGoods($basicParam,$joinPromotion,$shoppingCartGoodsInfo);

        //以优惠券为分界线，设置断点。
        foreach($promotionGoods as $value){
            if($value['promotion']['promotion_type'] == BaiyangPromotionEnum::EXPRESS_FREE
                || ($value['promotion']['promotion_type'] == BaiyangPromotionEnum::FULL_GIFT
                    && $value['promotion']['promotion_is_real_pay'] == 1)){
                $afterCoupon[] = $value;
            }else{
                $beforeCoupon[] = $value;
            }
        }
        return ['beforeCoupon' => $beforeCoupon,'afterCoupon' => $afterCoupon];
    }

    /**
     * @desc 促销活动匹配参加的商品信息 (组装侦听器需要的参数)
     * @param array $basicParam 基础参数(platform、user_id等)
     * @param array $joinPromotion 商品参加的所有促销活动信息
     * @param array $shoppingCartGoodsInfo 购物车的商品、活动信息
     * @return array $joinGoodsList 已参加活动的商品信息
     * @author 吴俊华
     */
    private function promotionMatchJoinGoods($basicParam,$joinPromotion,$shoppingCartGoodsInfo)
    {
        $promotionGoods = [];
        $sort_array = []; //对购物车商品列表按固定格式排序
        foreach ($shoppingCartGoodsInfo as $value) {
            $sort_array[] = isset($value['goods_id']) ? $value['goods_id'] : $value['group_id'];
        }
        array_multisort($sort_array, SORT_DESC, $shoppingCartGoodsInfo);
        foreach($joinPromotion as $promotionKey => $promotionValue){
            foreach($shoppingCartGoodsInfo as $key => $value){
                //只匹配选中的商品
//                if($value['selected'] == 1){
                    //组装侦听器需要的参数
                    foreach($value['promotionInfo']['join_promotionList'] as $val){
                        if($promotionValue['promotion_id'] == $val['promotion_id']){
                            $promotionGoods[$promotionKey]['promotion'] = $promotionValue;
                            $promotionGoods[$promotionKey]['promotion']['rule_value'] = json_decode($promotionGoods[$promotionKey]['promotion']['rule_value'],true);
                            $promotionGoods[$promotionKey]['platform'] = $basicParam['platform'];
                            // 选中的商品放进计算的门槛里
                            if($shoppingCartGoodsInfo[$key]['selected'] == 1){
                                if($shoppingCartGoodsInfo[$key]['group_id'] == 0){
                                    //商品
                                    $promotionGoods[$promotionKey]['goodsList'][] = [
                                        'goods_id' => $shoppingCartGoodsInfo[$key]['goods_id'],
                                        'supplier_id' => $shoppingCartGoodsInfo[$key]['supplier_id'],
                                        'group_id' => $shoppingCartGoodsInfo[$key]['group_id'],
                                        'goods_number' => $shoppingCartGoodsInfo[$key]['goods_number'],
                                        'brand_id' => $shoppingCartGoodsInfo[$key]['brand_id'],
                                        'category_id' => $shoppingCartGoodsInfo[$key]['category_id'],
                                        'drug_type' => $shoppingCartGoodsInfo[$key]['drug_type'],
                                        'promotion_price' => $shoppingCartGoodsInfo[$key]['promotion_price'],
                                        'discount_price' => $shoppingCartGoodsInfo[$key]['discount_price'],
                                        'promotion_total' => $shoppingCartGoodsInfo[$key]['promotion_total'],
                                        'discount_total' => $shoppingCartGoodsInfo[$key]['discount_total'],
                                        'selected' => $shoppingCartGoodsInfo[$key]['selected'],
                                    ];
                                }else{
                                    //套餐
                                    $promotionGoods[$promotionKey]['goodsList'][] = [
                                        'goods_id' => 0,
                                        'supplier_id' => $shoppingCartGoodsInfo[$key]['supplier_id'],
                                        'group_id' => $shoppingCartGoodsInfo[$key]['group_id'],
                                        'group_number' => $shoppingCartGoodsInfo[$key]['group_number'],
                                        'goods_number' => $shoppingCartGoodsInfo[$key]['goods_number'],
                                        'promotion_total' => $shoppingCartGoodsInfo[$key]['promotion_total'],
                                        'discount_total' => $shoppingCartGoodsInfo[$key]['discount_total'],
                                        'groupGoodsList' => $shoppingCartGoodsInfo[$key]['groupGoodsList'],
                                        'selected' => $shoppingCartGoodsInfo[$key]['selected'],
                                    ];
                                }
                            }
                        }

                    }
//                }

            }
        }
        if(!empty($promotionGoods)){
            foreach ($promotionGoods as $key => $value){
                // 没有选中的商品作初始化
                if(!isset($value['goodsList'])){
                    $promotionGoods[$key]['goodsList'] = [];
                }
            }
        }
        return $promotionGoods;
    }

    /**
     * @desc  修改商品参加满减/满折活动后的价格
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param
     *       - array joinGoodsList  参加过满减/满折的商品信息
     *       - array shoppingCartGoodsInfo   购物车的商品、活动信息
     *       - array afterCoupon
     * @return array [] 优惠后的商品信息
     * @author 吴俊华
     */
    public function changeGoodsPromotionTotal($event, $class, $param)
    {
        $joinGoodsList = $param['joinGoodsList'];
        $shoppingCartGoodsInfo = $param['shoppingCartGoodsInfo'];
        $afterCoupon = $param['afterCoupon'];
        foreach($joinGoodsList as $value){
            //修改购物车里所有商品的优惠价,准备提供给优惠券使用
            foreach($shoppingCartGoodsInfo as $kk => $vv){
                //商品
                if($value['group_id'] == 0 && $vv['group_id'] == 0){
                    if($value['goods_id'] == $vv['goods_id']){
                        $shoppingCartGoodsInfo[$kk]['promotion_total'] = $value['promotion_total'];
                    }
                }else{
                    //套餐
                    if($value['group_id'] == $vv['group_id']){
                        $shoppingCartGoodsInfo[$kk]['promotion_total'] = $value['promotion_total'];
                    }
                }
            }
            //修改实付满赠、包邮的优惠价[promotion_total]
            foreach($afterCoupon as $kk => $vv){
                foreach($vv['goodsList'] as $k => $v){
                    if(($value['group_id'] == 0 && $value['goods_id'] == $v['goods_id'])
                        || ($value['group_id'] > 0 && $value['group_id'] == $v['group_id'])){
                        $afterCoupon[$kk]['goodsList'][$k]['promotion_total'] = $value['promotion_total'];
                    }
                }
            }
        }
        return ['shoppingCartGoodsInfo' => $shoppingCartGoodsInfo,'afterCoupon' => $afterCoupon];
    }

    /**
     * @desc 购物车商品参加的活动匹配侦听器计算后的活动门槛信息
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param
     *     -array  $promotionGoods
     *        - array [一维数组] promotion 一个满赠活动，其中rule_value用json_decode转化后再传进来
     *        - array [二维数组] goodsList 商品列表，商品属性字段：goods_id、brand_id、category_id、goods_number、promotion_total
     *    -array  $shoppingCartGoodsInfo 购物车商品、活动信息[全场或非全场]
     *    -bool   $open  兼容全场范围的开关，全场范围时不匹配优惠价
     * @return array $shoppingCartGoodsInfo 促销信息匹配回购物车列表
     * @author 吴俊华
     */
    public function shoppingCartMatchPromotion($event, $class, $param)
    {
        $promotionGoods = $param['promotionGoods'];
        $shoppingCartGoodsInfo = $param['shoppingCartGoodsInfo'];
        $open = $param['open'];
        foreach($promotionGoods as $key => $value){
            foreach($shoppingCartGoodsInfo as $kk => $vv){
                //匹配头部促销活动信息
                if(!empty($vv['promotionInfo']['default_promotion'])){
                    if($vv['promotionInfo']['default_promotion']['promotion_id'] == $value['promotion']['promotion_id']){
                        $shoppingCartGoodsInfo[$kk]['promotionInfo']['default_promotion']['resultInfo'] = $value['promotion']['resultInfo'];
                    }

                }
                //匹配固定促销活动信息
                if(!empty($vv['promotionInfo']['fixed_promotion'])){
                    foreach($vv['promotionInfo']['fixed_promotion'] as $fixKey => $fixVal){
                        if($fixVal['promotion_id'] == $value['promotion']['promotion_id']){
                            $shoppingCartGoodsInfo[$kk]['promotionInfo']['fixed_promotion'][$fixKey]['resultInfo'] = $value['promotion']['resultInfo'];
                        }
                    }
                }
                //匹配参加促销活动信息[ join = default + fix ]
                if(!empty($vv['promotionInfo']['join_promotionList'])){
                    foreach($vv['promotionInfo']['join_promotionList'] as $joinKey => $joinVal){
                        if($joinVal['promotion_id'] == $value['promotion']['promotion_id']){
                            $shoppingCartGoodsInfo[$kk]['promotionInfo']['join_promotionList'][$joinKey]['resultInfo'] = $value['promotion']['resultInfo'];
                        }

                    }
                }
                //全场范围不用匹配优惠价
                if($open === true){
                    if(isset($value['goodsList'])){
                        foreach($value['goodsList'] as $k => $v){
                            if(($vv['group_id'] == 0 && $vv['goods_id'] == $v['goods_id']) || ($vv['group_id'] > 0 && $vv['group_id'] == $v['group_id'])){
                                $shoppingCartGoodsInfo[$kk]['promotion_total'] = $v['promotion_total'];
                            }
                        }
                    }
                }
            }
        }
        return $shoppingCartGoodsInfo;
    }

    /**
     * @desc 释放促销活动的无用数据
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param
     *         -array  promotionsInfo 促销活动信息
     * @return array $promotionsInfo 处理后的促销活动信息
     * @author 吴俊华
     */
    public function freedUselessData($event, $class, $param)
    {
        $promotionsInfo = $param['promotionsInfo'];
        foreach($promotionsInfo as $key => $value){
            if(!empty($value['promotionInfo']['default_promotion'])){
                $promotionsInfo[$key]['promotionInfo']['default_promotion'] = $this->savePromotionColumn($value['promotionInfo']['default_promotion']);
            }
            if(!empty($value['promotionInfo']['switch_promotion'])){
                foreach($value['promotionInfo']['switch_promotion'] as $kk => $vv){
                    foreach($vv as $k => $v){
                        $promotionsInfo[$key]['promotionInfo']['switch_promotion'][$kk][$k] = $this->savePromotionColumn($v);
                    }
                }
            }
            if(isset($value['promotionInfo']['single_limitbuy']) && !empty($value['promotionInfo']['single_limitbuy'])){
                $promotionsInfo[$key]['promotionInfo']['single_limitbuy'] = $this->savePromotionColumn($value['promotionInfo']['single_limitbuy']);
            }
            if(!empty($value['promotionInfo']['fixed_promotion'])){
                foreach($value['promotionInfo']['fixed_promotion'] as $kk => $vv){
                    $promotionsInfo[$key]['promotionInfo']['fixed_promotion'][$kk] = $this->savePromotionColumn($vv);
                }
            }
            if(!empty($value['promotionInfo']['join_promotionList'])){
                foreach($value['promotionInfo']['join_promotionList'] as $kk => $vv){
                    $promotionsInfo[$key]['promotionInfo']['join_promotionList'][$kk] = $this->savePromotionColumn($vv);
                }
            }
            if(isset($value['promotionInfo']['not_join_promotionList']) && !empty($value['promotionInfo']['not_join_promotionList'])){
                foreach($value['promotionInfo']['not_join_promotionList'] as $kk => $vv){
                    $promotionsInfo[$key]['promotionInfo']['not_join_promotionList'][$kk] = $this->savePromotionColumn($vv);
                }
            }

        }
        return $promotionsInfo;
    }

    /**
     * @desc 保留促销活动必要字段
     * @param array $promotion 促销活动信息
     * @return array $promotionsInfo 处理后的促销活动信息
     * @author 吴俊华
     */
    private function savePromotionColumn($promotion)
    {
        unset($promotion['resultInfo']['increaseBuyList']);
        $promotionInfo = [];
        $promotionInfo['promotion_id'] = $promotion['promotion_id'];
        $promotionInfo['promotion_title'] = $promotion['promotion_title'];
        $promotionInfo['promotion_type'] = $promotion['promotion_type'];
        $promotionInfo['promotion_scope'] = $promotion['promotion_scope'];
        $promotionInfo['member_tag'] = isset($promotion['member_tag']) ? $promotion['member_tag'] : 0;
        $promotionInfo['join_times'] = isset($promotion['join_times']) ? $promotion['join_times'] : 0;
        if(isset($promotion['selected'])){
            $promotionInfo['selected'] = $promotion['selected'];
        }
        if(isset($promotion['resultInfo'])){
            $promotionInfo['resultInfo'] = $promotion['resultInfo'];
        }
        return $promotionInfo;
    }

    /**
     * @desc 校验用户选中的换购品
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param
     *         -array  shoppingCartGoodsInfo 购物车的商品、活动信息
     *         -array  increaseBuyList 用户选中的换购品列表
     * @return array $increaseBuyList 校验后的用户选中的换购品列表
     * @author 吴俊华
     */
    public function checkSelectedChangeGoods($event, $class, $param)
    {
        $shoppingCartGoodsInfo = $param['shoppingCartGoodsInfo'];
        $increaseBuyList = $param['increaseBuyList'];
        //购物车的加价购活动
        $increaseBuyPromotion = [];
        foreach($shoppingCartGoodsInfo as $value){
            if(!empty($value['promotionInfo']['join_promotionList'])){
                foreach($value['promotionInfo']['join_promotionList'] as  $vv){
                    if($vv['promotion_type'] == BaiyangPromotionEnum::INCREASE_BUY){
                        $increaseBuyPromotion[$vv['promotion_id']] = $vv;
                    }
                }
            }
        }
        $increaseBuyPromotion = array_values($increaseBuyPromotion);

        $changeBuyGoods = []; //侦听器处理完的所有满足门槛的换购品
        foreach($increaseBuyPromotion as $value){
            if($value['resultInfo']['isCanUse'] == true){
                foreach($value['resultInfo']['increaseBuyList'] as $vv){
                    $changeBuyGoods[] = $vv;
                }
            }
        }
        $goodsIdArr = array_unique(array_column($changeBuyGoods,'goods_id'));
        $promotionsIdArr = array_unique((array_column($changeBuyGoods,'promotion_id')));
        //校验用户在购物车里选中的换购品
        foreach($increaseBuyList as $key => $value){
            if(!in_array($value['increase_buy'],$promotionsIdArr) || !in_array($value['goods_id'],$goodsIdArr)){
                unset($increaseBuyList[$key]);
                continue;
            }
            foreach($changeBuyGoods as $kk => $vv){
                if($value['increase_buy'] == $vv['promotion_id']){
                    if($vv['goods_id'] == $value['goods_id']){
                        $increaseBuyList[$key] = array_merge($increaseBuyList[$key],$vv);
                    }
                }
            }
        }
        $increaseBuyList = array_values($increaseBuyList);
        return $increaseBuyList;
    }

    /**
     * @desc 获取购物车商品状态 (0:正常 1:下架 2:缺货 3:售罄)
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param
     *       - array shoppingCartGoodsInfo  购物车商品活动信息
     *       - array basicParam   基础信息:platform、user_id、is_temp等
     * @return array $shoppingCartGoodsInfo 获取商品状态后的购物车信息
     * @author 吴俊华
     */
    public function getGoodsStatus($event, $class, $param)
    {
        $shoppingCartGoodsInfo = $param['shoppingCartGoodsInfo'];
        $basicParam = $param['basicParam'];
        //进行中的限时优惠和限购
        $param = [
            'platform' => $basicParam['platform'],
            'user_id' => $basicParam['user_id'],
            'is_temp' => $basicParam['is_temp'],
            'promotion_type' => BaiyangPromotionEnum::LIMIT_BUY.','.BaiyangPromotionEnum::LIMIT_TIME,
        ];
        $promotionArr = $this->getProcessingPromotions('','',$param);
        $basicParam['promotion'] = $promotionArr;
        //获取商品状态(0:正常 1:下架 2:缺货 3:售罄)
        foreach($shoppingCartGoodsInfo as $key => $value){
            $goodsStatus = 0;
            //商品
            if($value['group_id'] == 0){
                if($value['sale'] == 0){
                    $goodsStatus = 1;
                }else{
                    //可售库存为0时，判断显示缺货还是售罄
                    if($value['stock'] < 1){
                        $goodsStatus = 2;
                        $condition = [
                            'goods_id' => $value['goods_id'],
                            'brand_id' => $value['brand_id'],
                            'category_id' => $value['category_id'],
                            'drug_type' => $value['drug_type'],
                        ];
                        if($this->isJoinAssignPromotion($condition,$basicParam)){
                            $goodsStatus = 3;
                        }
                    }
                }
                $shoppingCartGoodsInfo[$key]['goods_status'] = $goodsStatus;
            }else{
                //套餐
                foreach($value['groupGoodsList'] as $kk => $vv){
                    $goodsStatus = 0;
                    if($vv['sale'] == 0){
                        $goodsStatus = 1;
                    }else{
                        //可售库存为0时，商品状态为售罄
                        if($vv['stock'] < 1){
                            $goodsStatus = 3;
                        }
                    }
                    $shoppingCartGoodsInfo[$key]['groupGoodsList'][$kk]['goods_status'] = $goodsStatus;
                }
            }
        }
        return $shoppingCartGoodsInfo;
    }

    /**
     * @desc 验证商品是否参加会员价/疗程/限时优惠/限购
     * @param array $salesId 商品id、品牌id、品类id
     * @param array $basicParam 基础信息:platform、user_id、is_temp
     * @return bool true|false 结果信息 (true为有参加，false为没参加)
     * @author 吴俊华
     */
    private function isJoinAssignPromotion($salesId,$basicParam)
    {
        $param = [
            'goods_id' => $salesId['goods_id'],
            'platform' => $basicParam['platform'],
            'user_id' => $basicParam['user_id'],
            'is_temp' => $basicParam['is_temp'],
        ];
        //会员标签
        if(BaiyangUserGoodsPriceTagData::getInstance()->getUserGoodsPriceTag($param,true)){
            return true;
        }
        //疗程
        if(BaiyangGoodsTreatmentData::getInstance()->getGoodsTreatment($param,false)){
            return true;
        }
        $promotionArr = isset($basicParam['promotion']) ? $basicParam['promotion'] : [];
        if(!empty($promotionArr)){
            foreach($promotionArr as $promotion){
                if($this->func->isRelatedGoods($promotion, $salesId)){
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @desc 处理购物车列表限购活动的标识 [非全场使用范围]
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param
     *         -array  shoppingCartGoodsInfo 促销活动信息
     * @return array $shoppingCartGoodsInfo 处理后的促销活动信息
     * @author 吴俊华
     */
    public function handleLimitBuyPromotion($event, $class, $param)
    {
        $shoppingCartGoodsInfo = $param['shoppingCartGoodsInfo'];
        $limitBuyInfo = [];
        foreach($shoppingCartGoodsInfo as $key => $value){
            //套餐和疗程不参加限购活动
            if($value['group_id'] == 0 && (empty($value['discountPromotion']) || (!empty($value['discountPromotion']) && $value['discountPromotion']['promotion_type'] != BaiyangPromotionEnum::TREATMENT))){
                //品牌、品类、多单品限购活动
                foreach($value['promotionInfo']['join_promotionList'] as $kk => $vv){
                    if($vv['promotion_type'] == BaiyangPromotionEnum::LIMIT_BUY &&
                        $vv['promotion_scope'] != BaiyangPromotionEnum::ALL_RANGE &&
                        $vv['promotion_scope'] != BaiyangPromotionEnum::SINGLE_RANGE){
                        if(!isset($limitBuyInfo[$value['id']])){
                            $limitBuyInfo[$value['id']]['id'] = $value['id'];
                            $limitBuyInfo[$value['id']]['goods_id'] =  $value['goods_id'];
                            $limitBuyInfo[$value['id']]['brand_id'] =  $value['brand_id'];
                            $limitBuyInfo[$value['id']]['category_id'] =  $value['category_id'];
                            $limitBuyInfo[$value['id']]['group_id'] = $value['group_id'];
                            $limitBuyInfo[$value['id']]['limitBuyList'] = [$vv['promotion_id'] => $vv];
                        }else{
                            $limitBuyInfo[$value['id']]['limitBuyList'] = array_merge($limitBuyInfo[$value['id']]['limitBuyList'],[$vv['promotion_id'] => $vv]);
                        }
                    }
                }
            }
        }
        if(empty($limitBuyInfo)){
            return $shoppingCartGoodsInfo;
        }
        //根据限购单位和限购数排序
        foreach($limitBuyInfo as $key => $value){
            $limitBuyInfo[$key]['limitBuyList'] = $this->sortLimitBuyList($value['limitBuyList']);
        }

        $newLimitBuyInfo = [];
        foreach($limitBuyInfo as $key => $value){
            foreach($value['limitBuyList'] as $kk => $vv){
                $newLimitBuyInfo[$key]['id'] = $value['id'];
                $newLimitBuyInfo[$key]['goods_id'] = $value['goods_id'];
                $newLimitBuyInfo[$key]['group_id'] = $value['group_id'];
                if(!isset($newLimitBuyInfo[$key]['limitBuyList'][$vv['limit_unit']])){
                    $newLimitBuyInfo[$key]['limitBuyList'][$vv['limit_unit']] = $vv;
                }
            }
        }
        unset($limitBuyInfo);
        //限购活动生成最终的促销文案
        $limitBuyList =  $this->generateLimitBuyCopywriter($newLimitBuyInfo);
        //购物车匹配限购活动生成最终的促销文案
        $shoppingCartGoodsInfo = $this->matchLimitBuyCopywriter($shoppingCartGoodsInfo,$limitBuyList);
        return $shoppingCartGoodsInfo;
    }

    /**
     * @desc 生成购物车里限购活动的最终促销文案
     * @param array $limitBuyInfo 限购活动列表
     * @return array $limitBuyList 生成促销文案后的限购活动列表
     * @author 吴俊华
     */
    private function generateLimitBuyCopywriter($limitBuyInfo)
    {
        $limitBuyList = [];
        foreach($limitBuyInfo as $key => $value){
            $copywriter = '活动商品总限购';
            $unitStr = '';
            $limitBuyList[$key]['id'] = $value['id'];
            $limitBuyList[$key]['goods_id'] = $value['goods_id'];
            $limitBuyList[$key]['group_id'] = $value['group_id'];
            $limitBuyList[$key]['limitBuyInfo']['promotion_id'] = implode(',',array_column($value['limitBuyList'],'promotion_id'));
            $limitBuyList[$key]['limitBuyInfo']['promotion_title'] = implode(',',array_column($value['limitBuyList'],'promotion_title'));
            $limitBuyList[$key]['limitBuyInfo']['promotion_type'] = BaiyangPromotionEnum::LIMIT_BUY;
            $limitBuyList[$key]['limitBuyInfo']['promotion_scope'] = implode(',',array_column($value['limitBuyList'],'promotion_scope'));
            $limitBuyList[$key]['limitBuyInfo']['resultInfo']['isCanUse'] = true;
            $limitBuyList[$key]['limitBuyInfo']['resultInfo']['bought_number'] = 0;
            $limitBuyList[$key]['limitBuyInfo']['resultInfo']['lack_number'] = 0;
            $limitUnit = array_column($value['limitBuyList'],'limit_unit');
            $limitNumber = array_column($value['limitBuyList'],'limit_number');
            for($i = 0;$i < count($limitUnit);$i++){
                $copywriter.= $limitNumber[$i].BaiyangPromotionEnum::$LimitBuyUnit[$limitUnit[$i]].'、';
                $unitStr .= BaiyangPromotionEnum::$LimitBuyUnitEN[$limitUnit[$i]].',';
            }
            $unitStr = rtrim($unitStr,',');
            $copywriter = rtrim($copywriter,'、');
            $limitBuyList[$key]['limitBuyInfo']['resultInfo']['unit'] = $unitStr;
            $limitBuyList[$key]['limitBuyInfo']['resultInfo']['copywriter'] = $copywriter;
            $limitBuyList[$key]['limitBuyInfo']['resultInfo']['message'] = '';
            $limitBuyList[$key]['limitBuyInfo']['resultInfo']['pro_message'] = '';
        }
        $limitBuyList = array_values($limitBuyList);
        return $limitBuyList;
    }

    /**
     * @desc 购物车匹配限购活动的最终促销文案
     * @param array $shoppingCartGoodsInfo 购物车列表信息
     * @param array $limitBuyList 限购活动列表
     * @return array $shoppingCartGoodsInfo 匹配后的购物车列表信息
     * @author 吴俊华
     */
    private function matchLimitBuyCopywriter($shoppingCartGoodsInfo,$limitBuyList)
    {
        foreach($shoppingCartGoodsInfo as $key => $value){
            //套餐和疗程不参加限购活动
            if(($value['group_id'] > 0) || ($value['group_id'] == 0 && !empty($value['discountPromotion']) && $value['discountPromotion']['promotion_type'] == BaiyangPromotionEnum::TREATMENT)){
                if(!empty($value['promotionInfo']['default_promotion']) && $value['promotionInfo']['default_promotion']['promotion_type'] == BaiyangPromotionEnum::LIMIT_BUY){
                    $shoppingCartGoodsInfo[$key]['promotionInfo']['default_promotion'] = [];
                }
            }
            if(!empty($value['promotionInfo']['fixed_promotion'])){
                foreach($value['promotionInfo']['fixed_promotion'] as $kk => $vv){
                    if($vv['promotion_type'] == BaiyangPromotionEnum::LIMIT_BUY){
                        unset($shoppingCartGoodsInfo[$key]['promotionInfo']['fixed_promotion'][$kk]);
                    }
                }
            }
            if(!empty($value['promotionInfo']['join_promotionList'])){
                foreach($value['promotionInfo']['join_promotionList'] as $kk => $vv){
                    if($vv['promotion_type'] == BaiyangPromotionEnum::LIMIT_BUY){
                        $shoppingCartGoodsInfo[$key]['promotionInfo']['join_promotionList'][$kk]['resultInfo'] = [
                            'isCanUse' => true,
                            'bought_number' => 0,
                            'lack_number' => 0,
                            'unit' => '',
                            'copywriter' => '',
                            'message' => '',
                            'pro_message' => '',
                        ];
                    }
                }
            }
        }
        //参加限购的商品匹配回购物车列表 (限购列表已排除套餐和疗程的商品)
        foreach($limitBuyList as $limitBuykey => $limitBuyValue){
            foreach($shoppingCartGoodsInfo as $key => $value){
                if($value['group_id'] == 0){
                    if($value['goods_id'] == $limitBuyValue['goods_id']){
                        if(!empty($value['promotionInfo']['default_promotion'])){
                            if($value['promotionInfo']['default_promotion']['promotion_type'] == BaiyangPromotionEnum::LIMIT_BUY){
                                $shoppingCartGoodsInfo[$key]['promotionInfo']['default_promotion'] = $limitBuyValue['limitBuyInfo'];
                            }else{
                                $shoppingCartGoodsInfo[$key]['promotionInfo']['fixed_promotion'] = array_merge($shoppingCartGoodsInfo[$key]['promotionInfo']['fixed_promotion'],[$limitBuyValue['limitBuyInfo']]);
                            }
                        }
                    }
                }
            }
        }
        return $shoppingCartGoodsInfo;
    }

    /**
     * @desc 处理购物车列表限购活动的标识 [全场使用范围]
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param
     *         -array  allPromotionInfo 促销活动信息
     * @return array $promotionInfo 处理后的促销活动信息
     * @author 吴俊华
     */
    public function handleLimitBuyAllPromotion($event, $class, $param)
    {
        $allPromotionInfo = $param['allPromotionInfo'][0]['promotionInfo'];
        foreach($allPromotionInfo as $key => $value){
            if(!empty($value)){
                if($key == 'default_promotion' && $value['promotion_type'] == BaiyangPromotionEnum::LIMIT_BUY){
                    $allPromotionInfo[$key]['resultInfo']['isCanUse'] = true;
                    $allPromotionInfo[$key]['resultInfo']['lack_number'] = 0;
                    $allPromotionInfo[$key]['resultInfo']['unit'] = BaiyangPromotionEnum::$LimitBuyUnitEN[$value['limit_unit']];
                    $allPromotionInfo[$key]['resultInfo']['copywriter'] = '限购'.$value['limit_number'].BaiyangPromotionEnum::$LimitBuyUnit[$value['limit_unit']];
                    $allPromotionInfo[$key]['resultInfo']['message'] = '';
                }
                if($key == 'fixed_promotion' || $key == 'join_promotionList'){
                    foreach($value as $kk => $vv){
                        if($vv['promotion_type'] == BaiyangPromotionEnum::LIMIT_BUY){
                            $allPromotionInfo[$key][$kk]['resultInfo']['isCanUse'] = true;
                            $allPromotionInfo[$key][$kk]['resultInfo']['lack_number'] = 0;
                            $allPromotionInfo[$key][$kk]['resultInfo']['unit'] = BaiyangPromotionEnum::$LimitBuyUnitEN[$vv['limit_unit']];
                            $allPromotionInfo[$key][$kk]['resultInfo']['copywriter'] = '限购'.$vv['limit_number'].BaiyangPromotionEnum::$LimitBuyUnit[$vv['limit_unit']];
                            $allPromotionInfo[$key][$kk]['resultInfo']['message'] = '';
                        }
                    }
                }
            }
        }
        $promotionInfo[0]['promotionInfo'] = $allPromotionInfo;
        return $promotionInfo;
    }

    /**
     * @desc 对实付满赠、包邮活动进行排除互斥商品并修改优惠价 (商品参加过优惠券活动的)
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param
     *         -array  promotionList    优先级在优惠券后面的促销活动 (实付满赠、包邮活动)
     *         -array  couponGoodsList  参加了优惠券的商品列表
     *         -array  mutexList 商品的互斥数组，默认为空  ['7000800'=>[15,20]]
     *         -array  joinList  商品参加过的活动数组，默认为空  ['7000800'=>[15,20]]
     * @return array $promotionInfo 处理后的促销活动信息
     * @author 吴俊华
     */
    public function excludeGoods($event, $class, $param)
    {
        $promotionList = $param['promotionList'];
        $couponGoodsList = $param['couponGoodsList'];
        $mutexList = $param['mutexList'];
        $joinList = $param['joinList'];
        //排除互斥活动商品
        foreach($promotionList as $key => $value){
            foreach($value['goodsList'] as $kk => $vv){
                //套餐遍历套餐里面的商品
                if($vv['group_id'] > 0){
                    if(!$this->func->isRelatedGroup($value['promotion'],$vv,$mutexList,$joinList)){
                        unset($promotionList[$key]['goodsList'][$kk]);
                    }
                }else{
                    if(!$this->func->isRelatedGoods($value['promotion'],$vv,$mutexList,$joinList)){
                        unset($promotionList[$key]['goodsList'][$kk]);
                    }
                }
            }
            $promotionList[$key]['goodsList'] = array_values($promotionList[$key]['goodsList']);
        }

        //修改优惠价promotion_total
        foreach($promotionList as $key => $value){
            foreach($couponGoodsList as $val){
                foreach($value['goodsList'] as $kk => $vv){
                    if($val['goods_id'] == $vv['goods_id'] && $val['group_id'] == $vv['group_id']){
                        $promotionList[$key]['goodsList'][$kk]['promotion_total'] = $val['promotion_total'];
                        //普通商品需要修改单价
                        if($val['group_id'] == 0){
                            $promotionList[$key]['goodsList'][$kk]['promotion_price'] = $val['promotion_price'];
                        }
                    }
                }
            }
        }
        return $promotionList;
    }

    /**
     * @desc 初始化所有促销活动的格式(所有参加的活动加入resultInfo，不包含限购活动)
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param
     *    -array  $shoppingCartGoodsInfo 购物车商品、活动信息[全场或非全场]
     * @return array $shoppingCartGoodsInfo 促销信息匹配回购物车列表
     * @author 吴俊华
     */
    public function initializePromotions($event, $class, $param)
    {
        $shoppingCartGoodsInfo = $param['shoppingCartGoodsInfo'];
        foreach ($shoppingCartGoodsInfo as $key => $value){
            if(isset($value['promotionInfo']['default_promotion']) && !empty($value['promotionInfo']['default_promotion'])){
                if($value['promotionInfo']['default_promotion']['promotion_type'] != BaiyangPromotionEnum::LIMIT_BUY){
                    $value['promotionInfo']['default_promotion']['rule_value'] = json_decode($value['promotionInfo']['default_promotion']['rule_value'], true);
                    $copywriter = $this->generatePromotionsCopywriter($value['promotionInfo']['default_promotion']);
                    $ruleValue = $value['promotionInfo']['default_promotion']['rule_value'];
                    $unit = !empty($ruleValue) && isset($ruleValue[0]['unit']) ? $ruleValue[0]['unit'] : 'yuan';
                    $shoppingCartGoodsInfo[$key]['promotionInfo']['default_promotion']['resultInfo'] = [
                        'isCanUse' => false,
                        'bought_number' => 0,
                        'lack_number' => 0,
                        'unit' => $unit,
                        'copywriter' => $copywriter,
                        'message' => '',
                        'pro_message' => '',
                    ];
                }else{
                    $shoppingCartGoodsInfo[$key]['promotionInfo']['default_promotion']['resultInfo'] = [
                        'isCanUse' => true,
                        'bought_number' => 0,
                        'lack_number' => 0,
                        'unit' => '',
                        'copywriter' => '',
                        'message' => '',
                        'pro_message' => '',
                    ];
                }
            }
            if(!empty($value['promotionInfo']['join_promotionList'])){
                foreach ($value['promotionInfo']['join_promotionList'] as $kk => $vv){
                    if($vv['promotion_type'] != BaiyangPromotionEnum::LIMIT_BUY){
                        $vv['rule_value'] = json_decode($vv['rule_value'], true);
                        $copywriter = $this->generatePromotionsCopywriter($vv);
                        $unit = !empty($vv['rule_value']) && isset($vv['rule_value'][0]['unit']) ? $vv['rule_value'][0]['unit'] : 'yuan';
                        $shoppingCartGoodsInfo[$key]['promotionInfo']['join_promotionList'][$kk]['resultInfo'] = [
                            'isCanUse' => false,
                            'bought_number' => 0,
                            'lack_number' => 0,
                            'unit' => $unit,
                            'copywriter' => $copywriter,
                            'message' => '',
                            'pro_message' => '',
                        ];
                    }else{
                        $shoppingCartGoodsInfo[$key]['promotionInfo']['join_promotionList'][$kk]['resultInfo'] = [
                            'isCanUse' => true,
                            'bought_number' => 0,
                            'lack_number' => 0,
                            'unit' => '',
                            'copywriter' => '',
                            'message' => '',
                            'pro_message' => '',
                        ];
                    }
                }
            }
            if(!empty($value['promotionInfo']['fixed_promotion'])){
                foreach ($value['promotionInfo']['fixed_promotion'] as $kk => $vv){
                    if($vv['promotion_type'] != BaiyangPromotionEnum::LIMIT_BUY){
                        $vv['rule_value'] = json_decode($vv['rule_value'], true);
                        $copywriter = $this->generatePromotionsCopywriter($vv);
                        $unit = !empty($vv['rule_value']) && isset($vv['rule_value'][0]['unit']) ? $vv['rule_value'][0]['unit'] : 'yuan';
                        $shoppingCartGoodsInfo[$key]['promotionInfo']['fixed_promotion'][$kk]['resultInfo'] = [
                            'isCanUse' => false,
                            'bought_number' => 0,
                            'lack_number' => 0,
                            'unit' => $unit,
                            'copywriter' => $copywriter,
                            'message' => '',
                            'pro_message' => '',
                        ];
                    }else{
                        $shoppingCartGoodsInfo[$key]['promotionInfo']['fixed_promotion'][$kk]['resultInfo'] = [
                            'isCanUse' => true,
                            'bought_number' => 0,
                            'lack_number' => 0,
                            'unit' => '',
                            'copywriter' => '',
                            'message' => '',
                            'pro_message' => '',
                        ];
                    }
                }
            }
            if(!empty($value['promotionInfo']['switch_promotion'])){
                foreach ($value['promotionInfo']['switch_promotion'] as $kk => $vv){
                    foreach ($vv as $k => $v){
                        if($v['promotion_type'] != BaiyangPromotionEnum::LIMIT_BUY){
                            $v['rule_value'] = json_decode($v['rule_value'], true);
                            $copywriter = $this->generatePromotionsCopywriter($v);
                            $unit = !empty($v['rule_value']) && isset($v['rule_value'][0]['unit']) ? $v['rule_value'][0]['unit'] : 'yuan';
                            $shoppingCartGoodsInfo[$key]['promotionInfo']['switch_promotion'][$kk][$k]['resultInfo'] = [
                                'isCanUse' => false,
                                'bought_number' => 0,
                                'lack_number' => 0,
                                'unit' => $unit,
                                'copywriter' => $copywriter,
                                'message' => '',
                                'pro_message' => '',
                            ];
                        }else{
                            $shoppingCartGoodsInfo[$key]['promotionInfo']['switch_promotion'][$kk][$k]['resultInfo'] = [
                                'isCanUse' => true,
                                'bought_number' => 0,
                                'lack_number' => 0,
                                'unit' => '',
                                'copywriter' => '',
                                'message' => '',
                                'pro_message' => '',
                            ];
                        }
                    }
                }
            }
            if(isset($value['promotionInfo']['not_join_promotionList']) && !empty($value['promotionInfo']['not_join_promotionList'])){
                foreach ($value['promotionInfo']['not_join_promotionList'] as $kk => $vv){
                    if($vv['promotion_type'] != BaiyangPromotionEnum::LIMIT_BUY){
                        $vv['rule_value'] = json_decode($vv['rule_value'], true);
                        $copywriter = $this->generatePromotionsCopywriter($vv);
                        $unit = !empty($vv['rule_value']) && isset($vv['rule_value'][0]['unit'])  ? $vv['rule_value'][0]['unit'] : 'yuan';
                        $shoppingCartGoodsInfo[$key]['promotionInfo']['not_join_promotionList'][$kk]['resultInfo'] = [
                            'isCanUse' => false,
                            'bought_number' => 0,
                            'lack_number' => 0,
                            'unit' => $unit,
                            'copywriter' => $copywriter,
                            'message' => '',
                            'pro_message' => '',
                        ];
                    }else{
                        $shoppingCartGoodsInfo[$key]['promotionInfo']['not_join_promotionList'][$kk]['resultInfo'] = [
                            'isCanUse' => true,
                            'bought_number' => 0,
                            'lack_number' => 0,
                            'unit' => BaiyangPromotionEnum::$LimitBuyUnitEN[$vv['limit_unit']],
                            'copywriter' => '活动商品总限购'.$vv['limit_number'].BaiyangPromotionEnum::$LimitBuyUnit[$vv['limit_unit']],
                            'message' => '',
                            'pro_message' => '',
                        ];
                    }
                }
            }
            // 单品限购的促销文案 (只有商品才有，套餐没有)
            if(isset($value['group_id']) && $value['group_id'] == 0){
                if(isset($value['promotionInfo']['single_limitbuy']) && !empty($value['promotionInfo']['single_limitbuy'])){
                    foreach (json_decode($value['promotionInfo']['single_limitbuy']['rule_value'],true) as $val){
                        if($val['id'] == $value['goods_id']){
                            $shoppingCartGoodsInfo[$key]['promotionInfo']['single_limitbuy']['resultInfo'] = [
                                'isCanUse' => true,
                                'bought_number' => 0,
                                'lack_number' => 0,
                                'unit' => 'item',
                                'copywriter' => '限购'.$val['promotion_num'].BaiyangPromotionEnum::$LIMIT_UNIT[1],
                                'message' => '',
                                'pro_message' => '',
                            ];
                        }
                    }
                }
            }
        }
        return $shoppingCartGoodsInfo;
    }

    /**
     * @desc 获取商品限时优惠信息
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param
     *          - int    goods_id   商品id
     *          - double sku_price  商品价格
     *          - int    user_id    用户id
     *          - int    is_temp    是否临时用户
     * @return array []|$limitTimeInfo 限时优惠信息 [一维数组]
     *          - int    id     主键id
     *          - string limit_time_title  活动名称
     *          - int    goods_id   商品id
     *          - double price      参加限时优惠后的价格
     *          - int    offer_type 优惠类型(1:折扣 2:优惠价)
     *          - double rebate     折扣
     *          - int    end_time   活动结束时间
     *          - string mutex      互斥活动(多个以逗号隔开)
     *          - int    promotion_type  活动类型(35:限时优惠)
     * @author 吴俊华
     */
    public function getLimitTimeInfo($event, $class, $param)
    {
        $platform = $this->config->platform;
        $condition = [
            'goodsInfo' => [
                'goods_id' => $param['goods_id'],
                'sku_price' => $param['sku_price'],
            ],
            'platform' => $platform,
        ];

        $limitTimeArr = $this->getProcessingPromotions($event,$class,['platform' => $platform, 'user_id' => $param['user_id'],'is_temp' => $param['is_temp'],'promotion_type' => BaiyangPromotionEnum::LIMIT_TIME]);
        if(empty($limitTimeArr)){
            return [];
        }
        $chooseLimitTime = [];
        foreach ($limitTimeArr as $value){
            if(in_array($param['goods_id'], explode(',', $value['condition']))){
                $chooseLimitTime = $value;
                break;
            }
        }
        if(empty($chooseLimitTime)){
            return [];
        }
        $limitTimeInfo = $this->verifyLimitTime($condition, $chooseLimitTime);
        return $limitTimeInfo;
    }

    /**
     * @desc 删除购物车里过期活动的换购品
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param
     *         -array  shoppingCartGoodsInfo 购物车的商品、活动信息
     *         -array  increaseBuyList 用户选中的换购品列表
     * @return bool $result true|false 删除结果
     * @author 吴俊华
     */
    public function deleteInvalidChangeGoods($event, $class, $param)
    {
        $shoppingCartGoodsInfo = $param['shoppingCartGoodsInfo'];
        $increaseBuyList = $param['increaseBuyList'];
        $idArr = []; //要删除无效换购品的购物车主键id
        $progressingIncreaseBuy = []; //进行中的加价购活动
        $increaseBuyArr = array_column($increaseBuyList,'increase_buy','id');
        foreach ($shoppingCartGoodsInfo as $value){
            if(!empty($value['promotionInfo']['join_promotionList'])){
                foreach ($value['promotionInfo']['join_promotionList'] as $val){
                    if($val['promotion_type'] == BaiyangPromotionEnum::INCREASE_BUY){
                        if(!isset($progressingIncreaseBuy[$val['promotion_id']])){
                            $progressingIncreaseBuy[$val['promotion_id']] = $val['promotion_id'];
                        }
                    }
                }
            }
        }

        //获取要删除的购物车主键id
        if(empty($progressingIncreaseBuy)){
            $idArr = array_keys($increaseBuyArr);
        }else{
            foreach ($increaseBuyArr as $key => $value){
                if(!in_array($value, $progressingIncreaseBuy)){
                    $idArr[] = $key;
                }
            }
        }
        if(!empty($idArr)){
            $idStrs = implode(',', array_unique($idArr));
            $result = BaseData::getInstance()->deleteData([
                'table' => 'Shop\Models\BaiyangGoodsShoppingCart',
                'where' => "where id in ({$idStrs})",
            ]);
            return $result;
        }
        return false;
    }

    /**
     * @desc 计算商品、套餐里的每个商品的优惠单价和优惠总价
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param
     *         -array  promotionsInfo 促销活动信息
     * @return array $promotionsInfo 处理后的促销活动信息
     * @author 吴俊华
     */
    public function calculateGoodsPromotionPrice($event, $class, $param)
    {
        $promotionsInfo = $param['promotionsInfo'];
        foreach($promotionsInfo as $key => $value){
            // 计算商品的优惠单价
            if($value['group_id'] == 0){
                $promotionsInfo[$key]['promotion_price'] = bcdiv($value['promotion_total'],$value['goods_number'],2);
            }else{
                // 计算套餐里每个商品的优惠单价
                $reducePrice = bcsub($value['discount_total'],$value['promotion_total'],2);
                if($reducePrice > 0){
                    $tempReduceSum = 0;
                    foreach ($value['groupGoodsList'] as $kk => $vv){
                        if( count($value['groupGoodsList']) == $kk+1 ) {
                            // 减少误差，保证总价相等
                            $tempReduce = bcsub($reducePrice, $tempReduceSum, 2);
                        } else {
                            $tempReduce = bcdiv(bcmul($reducePrice, $vv['discount_total'], 2), $value['discount_total'], 2);
                            $tempReduceSum = bcadd($tempReduceSum, $tempReduce, 2);
                        }
                        $promotionsInfo[$key]['groupGoodsList'][$kk]['promotion_total'] = bcsub($vv['promotion_total'], $tempReduce, 2);
                        $promotionsInfo[$key]['groupGoodsList'][$kk]['promotion_price'] = bcdiv($promotionsInfo[$key]['groupGoodsList'][$kk]['promotion_total'],$vv['goods_number'],2);
                    }
                }
            }
        }
        return $promotionsInfo;
    }

    /**
     * @desc 购物车列表匹配使用优惠券后的promotion_total、promotion_price
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param
     *       - array shoppingCartGoodsInfo  购物车商品活动信息
     * @return array shoppingCartGoodsInfo 处理后的购物车列表信息
     * @author 吴俊华
     */
    public function matchCouponPromotionTotal($event, $class, $param)
    {
        $shoppingCartGoodsInfo = $param['shoppingCartGoodsInfo'];
        $goodsList = $param['goodsList'];
        //修改优惠价promotion_total、promotion_price
        foreach($shoppingCartGoodsInfo as $key => $value){
            foreach($goodsList as $val){
                if($value['group_id'] == 0){
                    if($val['goods_id'] == $value['goods_id']){
                        $shoppingCartGoodsInfo[$key]['promotion_total'] = $val['promotion_total'];
                        $shoppingCartGoodsInfo[$key]['promotion_price'] = $val['promotion_price'];
                    }
                }else{
                    if($val['group_id'] == $value['group_id']){
                        $shoppingCartGoodsInfo[$key]['promotion_total'] = $val['promotion_total'];
                    }
                }
            }
        }
        return $shoppingCartGoodsInfo;
    }

    /**
     * @desc 解绑会员标签
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param
     *       - array availPromotionList
     * @return array userId
     * @author 吴俊华
     */
    public function unbindUserTag($event, $class, $param) {
        if(isset($param['availPromotionList']) && !empty($param['availPromotionList'])){
            $baseData = BaseData::getInstance();
            foreach ($param['availPromotionList'] as $value) {
                if($value['member_tag'] > 0 && $value['join_times'] > 0){
                    //用户参加过的促销活动
                    $userPromotion = BaiyangOrderPromotionData::getInstance()->getUserPromotions(['user_id' => $param['userId']],$value['promotion_id']);
                    $joinNumbers = !empty($userPromotion) ? $userPromotion[0]['counts'] : 0;
                    if($value['join_times'] <= $joinNumbers){
                        // 解绑会员标签
                        $baseData->deleteData([
                            'table' => '\Shop\Models\BaiyangUserGoodsPriceTag',
                            'where' => 'where user_id = :user_id: and tag_id = :tag_id:',
                            'bind' => ['user_id' => $param['userId'],'tag_id' => $value['member_tag']],
                        ]);
                        // 释放参加该活动的次数
                        $baseData->updateData([
                            'table' => '\Shop\Models\BaiyangOrderPromotionDetail',
                            'column' => 'is_delete = 1',
                            'where' => 'where user_id = :user_id: and promotion_id = :promotion_id:',
                            'bind' => ['user_id' => $param['userId'],'promotion_id' => $value['promotion_id']],
                        ]);
                    }
                }
            }
        }
    }

    /**
     * @desc 购物车商品匹配参加优惠券的优惠金额coupon_total
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param
     *    -array  couponList 优惠券信息
     *    -array  shoppingCartGoodsInfo 购物车商品、活动信息[全场或非全场]
     *    -bool   open  兼容全场范围的开关，全场范围时不匹配优惠价
     * @return array $shoppingCartGoodsInfo 促销信息匹配回购物车列表
     * @author 吴俊华
     */
    public function shoppingCartMatchCouponTotal($event, $class, $param)
    {
        $couponGoodsList = $param['couponGoodsList'];
        $shoppingCartGoodsInfo = $param['shoppingCartGoodsInfo'];
        // 匹配coupon_total
        foreach ($couponGoodsList as $key => $value){
            foreach ($shoppingCartGoodsInfo as $kk => $vv){
                // 套餐
                if($value['group_id'] > 0){
                    if($value['group_id'] == $vv['group_id']){
                        $shoppingCartGoodsInfo[$kk]['coupon_total'] = isset($value['coupon_total']) ? $value['coupon_total'] : 0;
                        $shoppingCartGoodsInfo[$kk]['groupGoodsList'] = $value['groupGoodsList'];
                    }
                }elseif($value['group_id'] == $vv['group_id']){
                    // 普通商品
                    if($value['goods_id'] == $vv['goods_id']){
                        $shoppingCartGoodsInfo[$kk]['coupon_total'] = isset($value['coupon_total']) ? $value['coupon_total'] : 0;
                    }
                }
            }
        }
        return $shoppingCartGoodsInfo;
    }
}