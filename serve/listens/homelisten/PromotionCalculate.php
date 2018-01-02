<?php
/**
 * Created by PhpStorm.
 * User: 吴俊华
 * Date: 2016/10/12 0012
 * Time: 上午 11:16
 *
 * 促销计算侦听器(满减、满折、满赠、包邮、加价购)
 */
namespace Shop\Home\Listens;
use Shop\Home\Datas\BaiyangCouponRecordData;
use Shop\Models\BaiyangCouponEnum;
use Shop\Models\BaiyangPromotionEnum;
use Shop\Home\Datas\BaiyangShoppingCartData;
use Shop\Home\Datas\BaiyangCouponData;
use Shop\Models\OrderEnum;

class PromotionCalculate extends BaseListen
{
    /**
     * @desc 判断一个满减或满折活动是否满足门槛
     * @access public
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array $param
     *      - promotion：一个满减或满折活动，其中rule_value用json_decode转化后再传进来
     *      - goodsList：商品列表，商品属性字段：goods_id、brand_id、category_id、goods_number、promotion_price、promotion_total
     * @return array  结果信息
     *      - isCanUse    boolean        活动是否满足门槛
     *      - reduce_price  float       规则的优惠金额
     *      - lack_number   int|float   距离门槛还缺多少
     *      - unit          string      门槛单位
     *      - goodsList     array       参加当前活动后商品列表
     *      - copywriter    string      促销文案
     *      - message       string      提示语：差xxx,去凑单。
     *      - bought_number int|float   已买多少
     * @author 吴俊华
     */
    public function fullMinus($event,$class,$param)
    {
        $promotion = $param['promotion'];
        $goodsList = $param['goodsList'];
        //根据门槛排序
        $ruleArr = $this->arraySortByKey($promotion['rule_value'],'full_price','asc');
        $promotion['rule_value'] = $this->arraySortByKey($promotion['rule_value'],'full_price','desc');
        $discountRule = $ruleArr[0]; //较为容易达到的门槛
        $goodsTotal = 0;   // 满足活动条件的商品总价
        $goodsNumber = 0;  // 满足活动条件的商品总数量
        $reduce_price = 0; // 活动优惠的价格
        $lack_number = false;  // 距离门槛还缺多少
        $unit = 'yuan';    // 单位
        $isCanUse = false; // 活动是否满足门槛
        $message = '';  // 提示语

        // 获取商品总价和总数量
        foreach ($goodsList as $goodsInfo) {
            $goodsTotal = bcadd($goodsTotal, $goodsInfo['promotion_total'], 2);
            $goodsNumber += $goodsInfo['goods_number'];
        }
        if(!empty($goodsList)){
            $sort_array = []; // 价格由低到高排序
            foreach ($goodsList as $goodsInfo) {
                $sort_array[] = $goodsInfo['promotion_total'];
            }
            array_multisort($sort_array, SORT_ASC, $goodsList);
        }

        // 选择满足门槛最优惠的一条规则
        foreach($promotion['rule_value'] as $value) {
            $unit = $value['unit'];
            $goodsFull = $unit == 'yuan' ? $goodsTotal: $goodsNumber;
            if (bccomp($goodsFull, $value['full_price'], 2) !== -1) {
                if (isset($value['reduce_price']) && bccomp($value['reduce_price'], $goodsTotal, 2) === 1) {
                    $value['reduce_price'] = $goodsTotal;
                }
                if(isset($value['reduce_price'])) {
                    $temp_reduce_price = sprintf("%.2f", $value['reduce_price']);
                } else {
                    $temp_reduce_price =  bcmul($goodsTotal, (bcsub(1, bcdiv($value['discount_rate'], 10, 2), 2)), 2);
                }
                $reduce_price = bccomp($temp_reduce_price, $reduce_price, 2) == 1 ? $temp_reduce_price : $reduce_price;
                $isCanUse = true;
                $lack_number = 0;
                $tempReduceSum = 0;
                $supplierList = [];//计算不同商家的优惠金额
                // 如果促销活动满足门槛就修改促销价(按比例计算出各个商品的优惠金额)
                $goodsList = array_values($goodsList);
                foreach($goodsList as $key => $goodsInfo) {
                    if( count($goodsList) == $key+1 ) {
                        $tempReduce = bcsub($reduce_price, $tempReduceSum, 2);
                    } else {
                        $tempReduce = bcdiv(bcmul($reduce_price, $goodsInfo['promotion_total'], 2), $goodsTotal, 2);
                        $tempReduceSum = bcadd($tempReduceSum, $tempReduce, 2);
                    }
                    $goodsList[$key]['promotion_total'] = bcsub($goodsInfo['promotion_total'], $tempReduce, 2);
                    if (!isset($supplierList[$goodsInfo['supplier_id']])) {
                        $supplierList[$goodsInfo['supplier_id']] = $tempReduce;
                    }else {
                        $supplierList[$goodsInfo['supplier_id']] = bcadd($supplierList[$goodsInfo['supplier_id']], $tempReduce, 2);
                    }
                    if($goodsInfo['group_id'] == 0){
                        $goodsList[$key]['promotion_price'] = bcdiv($goodsList[$key]['promotion_total'], $goodsList[$key]['goods_number'], 2);
                    }
                }
                if($unit == BaiyangPromotionEnum::UNIT_JIAN){
                    $lack_number = (int)floor($lack_number);
                    $goodsFull = (int)floor($goodsFull);
                    $value['full_price'] = (int)floor($value['full_price']);
                }
                //促销文案
                $copywriter = '已买满'.$value['full_price'].BaiyangPromotionEnum::$FULL_UNIT[$value['unit']];
                if($promotion['promotion_type'] == BaiyangPromotionEnum::FULL_MINUS){
                    $copywriter .= '，已减'.$value['reduce_price'].'元';
                }else{
                    $copywriter .= '，已享'.$value['discount_rate'].'折';
                }
                if($promotion['promotion_scope'] != BaiyangPromotionEnum::ALL_RANGE){
                    $message = '再逛逛';
                }
                $proMessage = '立减'. $reduce_price .'元'; //促销列表下面的提示语(在价格下面的)
                return array('isCanUse'=> $isCanUse, 'reduce_price'=> $reduce_price,'lack_number'=> $lack_number, 'unit'=> $unit, 'goodsList'=> $goodsList,'copywriter' => $copywriter,'message' => $message,'pro_message' => $proMessage, 'bought_number'=> $goodsFull, 'supplierList'=> $supplierList);
            }
            $temp_lack_number = bcsub($value['full_price'], $goodsFull, 2);
            if ($lack_number === false) {
                $lack_number = $temp_lack_number;
            } else {
                $lack_number = $lack_number < $temp_lack_number ? $lack_number : $temp_lack_number;
            }
        }
        //已买多少
        $bought_number = $discountRule['unit'] == 'yuan' ? $goodsTotal: $goodsNumber;
        if($unit == BaiyangPromotionEnum::UNIT_JIAN){
            $lack_number = (int)floor($lack_number);
            $bought_number = (int)floor($bought_number);
            $discountRule['full_price'] = (int)floor($discountRule['full_price']);
        }
        //促销文案
        if(!empty($promotion['promotion_copywriter'])){
            $copywriter = $promotion['promotion_copywriter'];
        }else{
            $copywriter = '满'.$discountRule['full_price'].BaiyangPromotionEnum::$FULL_UNIT[$discountRule['unit']];
            if($promotion['promotion_type'] == BaiyangPromotionEnum::FULL_MINUS){
                $copywriter .= '减'.$discountRule['reduce_price'].'元';
            }else{
                $copywriter .= '享'.$discountRule['discount_rate'].'折';
            }
        }
        if($this->config->platform != OrderEnum::PLATFORM_PC && $bought_number){
            $copywriter .= '，还差'.$lack_number.BaiyangPromotionEnum::$FULL_UNIT[$unit];
        }
        if($promotion['promotion_scope'] != BaiyangPromotionEnum::ALL_RANGE){
            $message = '去凑单';
        }
        $proMessage =  '还差'.$lack_number.BaiyangPromotionEnum::$FULL_UNIT[$unit]; //促销列表下面的提示语(在价格下面的)
        return array('isCanUse'=> $isCanUse, 'reduce_price'=> $reduce_price, 'lack_number'=> $lack_number, 'unit'=> $unit, 'goodsList'=> $goodsList,'copywriter' => $copywriter,'message' => $message,'pro_message' => $proMessage,'bought_number' => $bought_number);
    }

    /**
     * @desc 判断一个满赠活动是否满足门槛
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param
     *      - array [一维数组] promotion 一个满赠活动，其中rule_value用json_decode转化后再传进来
     *      - array [二维数组] goodsList 商品列表，商品属性字段：goods_id、brand_id、category_id、goods_number、promotion_total
     *      - array [二维数组] mutexList 商品的互斥数组,默认为空  ['7000800'=>[15,20]]
     *      - array [二维数组] joinList 商品参加过的活动数组,默认为空  ['7000800'=>[15,20]]
     * @return array 结果信息
     *      - bool          isCanUse       是否达到门槛(true|false)
     *      - int|float     lack_number    距离门槛还缺多少
     *      - string        unit           门槛单位
     *      - array         goodsList      参加满赠活动的商品列表
     *      - array         premiums_group 满足满赠活动门槛的赠品列表
     *      - copywriter    string      促销文案
     *      - message       string      提示语：差xxx,去凑单。
     *      - bought_number int|float   已买多少
     * @author 吴俊华
     */
    public function fullGift($event,$class,$param)
    {
        $promotion = $param['promotion'];
        //根据门槛排序
        $ruleArr = $this->arraySortByKey($promotion['rule_value'],'full_price','asc');
        $promotion['rule_value'] = $this->arraySortByKey($promotion['rule_value'],'full_price','desc');
        $discountRule = $ruleArr[0]; //较为容易达到的门槛
        $goodsList = $param['goodsList'];
        $goodsTotal = 0;   //满足活动条件的商品总价
        $goodsNumber = 0;  //满足活动条件的商品总数量
        $lack_number = 0;  //距离门槛还缺多少
        $unit = 'yuan';    //门槛单位
        $isCanUse = false; //活动是否满足门槛
        $message = '';  // 提示语
        //计算商品总价、总数量
        foreach($goodsList as $goodsKey => $goodsValue) {
            $goodsTotal = bcadd($goodsTotal, $goodsValue['promotion_total'], 2);
            $goodsNumber += $goodsValue['goods_number'];
        }

        //选择满足门槛最接近的一条规则
        foreach($promotion['rule_value'] as $ruleValue) {
            $unit = $ruleValue['unit'];
            $threshold = ($unit == 'yuan') ? $goodsTotal : $goodsNumber;
            //满足门槛时
            if(bccomp($threshold, $ruleValue['full_price'], 2) !== -1){
                //叠加时，判断赠品数是否需要乘以金额/门槛的倍数
                if($promotion['is_superimposed'] == 1){
                    $multiple = floor(bcdiv($threshold,$ruleValue['full_price'],2));
                    if($multiple > 1){
                        foreach($ruleValue['premiums_group'] as $key => $val){
                            $ruleValue['premiums_group'][$key]['premiums_number'] *= $multiple;
                        }
                    }
                }
                $isCanUse = true;
                $lack_number = 0;
                $premiumsGroup = $ruleValue['premiums_group'];
                $condition = [
                    'goods_id' => implode(',',array_column($premiumsGroup,'premiums_id')),
                    'platform' => $param['platform']
                ];
                $goodsDetail = $this->getGoodsDetail($condition);
                $stockArr = $this->func->getCanSaleStock($condition);

                // 赠品库存
                foreach ($goodsDetail as $kk => $vv){
                    if(is_array($stockArr)){
                        foreach ($stockArr as $k => $v){
                            if($vv['id'] == $k){
                                $goodsDetail[$kk]['stock'] = $v;
                                continue;
                            }
                        }
                    }else{
                        $goodsDetail[$kk]['stock'] = $stockArr;
                    }
                }
                foreach($premiumsGroup as $key => $val){
                    $sign = false;
                    foreach($goodsDetail as $kk => $vv){
                        if($val['premiums_id'] == $vv['id']){
                            // 下架，要排除掉
                            if($vv['sale'] == 0){
                                break;
                            }
                            $premiumsGroup[$key] = [
                                'goods_id' => $vv['id'],
                                'goods_name' => $vv['name'],
                                'goods_number' => $val['premiums_number'],
                                'goods_image' => $vv['goods_image'],
                                'specifications' => $vv['specifications'],
                                'stock_type' => $vv['is_use_stock'],
                                'stock' => $vv['stock'],
                                'product_type' => 1, //普通赠品
                                'returned_goods_time' => $vv['returned_goods_time'], //是否能退换货
                                'supplier_id' => isset($vv['supplier_id']) ? $vv['supplier_id'] : 0, //商家id
                            ];
                            $sign = true;
                            break;
                        }
                    }
                    if(!$sign){
                        unset($premiumsGroup[$key]);
                    }
                }
                $premiumsGroup = array_values($premiumsGroup);
                if($unit == BaiyangPromotionEnum::UNIT_JIAN){
                    $lack_number = (int)floor($lack_number);
                    $threshold = (int)floor($threshold);
                    $ruleValue['full_price'] = (int)floor($ruleValue['full_price']);
                }
                //促销文案
                $copywriter = '已买满'.$ruleValue['full_price'].BaiyangPromotionEnum::$FULL_UNIT[$ruleValue['unit']];
                if($promotion['promotion_scope'] != BaiyangPromotionEnum::ALL_RANGE){
                    $message = '再逛逛';
                }
                $proMessage = ''; //促销列表下面的提示语(在价格下面的)
                return ['isCanUse' => $isCanUse,'lack_number'=> $lack_number, 'unit'=> $unit,'goodsList'=> $goodsList,'premiums_group' => $premiumsGroup,'copywriter' => $copywriter,'message' => $message,'pro_message' => $proMessage,'bought_number' => $threshold];
            }
            //求差xxx元/件的最接近数
            $temp_lack_number = bcsub($ruleValue['full_price'],$threshold,2);
            if ($lack_number === 0) {
                $lack_number = $temp_lack_number;
            } else {
                $lack_number = $lack_number < $temp_lack_number ? $lack_number : $temp_lack_number;
            }
        }
        //已买多少
        $bought_number = ($discountRule['unit'] == 'yuan') ? $goodsTotal : $goodsNumber;
        if($unit == BaiyangPromotionEnum::UNIT_JIAN){
            $lack_number = (int)floor($lack_number);
            $bought_number = (int)floor($bought_number);
            $discountRule['full_price'] = (int)floor($discountRule['full_price']);
        }
        //促销文案
        if(!empty($promotion['promotion_copywriter'])){
            $copywriter = $promotion['promotion_copywriter'];
        }else{
            $copywriter = '满'.$discountRule['full_price'].BaiyangPromotionEnum::$FULL_UNIT[$discountRule['unit']].'送赠品';
        }
        if($this->config->platform != OrderEnum::PLATFORM_PC && $bought_number){
            $copywriter .= '，还差'.$lack_number.BaiyangPromotionEnum::$FULL_UNIT[$unit];
        }
        if($promotion['promotion_scope'] != BaiyangPromotionEnum::ALL_RANGE){
            $message = '去凑单';
        }
        $proMessage =  '还差'.$lack_number.BaiyangPromotionEnum::$FULL_UNIT[$unit]; //促销列表下面的提示语(在价格下面的)
        return ['isCanUse' => $isCanUse,'lack_number'=> $lack_number, 'unit'=> $unit,'goodsList'=> $goodsList,'premiums_group' => [],'copywriter' => $copywriter,'message' => $message,'pro_message' => $proMessage,'bought_number' => $bought_number];

    }

    /**
     * @desc 判断一个包邮是否满足门槛
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param
     *      - array [一维数组] promotion 一个包邮活动，其中rule_value用json_decode转化后再传进来
     *      - array [二维数组] goodsList 商品列表，商品属性字段：goods_id、brand_id、category_id、goods_number、promotion_total
     *      - array [二维数组] mutexList 商品的互斥数组,默认为空  ['7000800'=>[15,20]]
     *      - array [二维数组] joinList 商品参加过的活动数组,默认为空  ['7000800'=>[15,20]]
     * @return array 结果信息
     *      - bool          isCanUse     是否达到门槛(true|false)
     *      - int|float     lack_number  距离门槛还缺多少
     *      - int|float     full_number  门槛件数/金额
     *      - string        unit         门槛单位
     *      - array         goodsList    参加包邮活动的商品列表
     *      - copywriter    string       促销文案
     *      - message       string       提示语：差xxx,去凑单。
     *      - bought_number int|float    已买多少
     * @author 吴俊华
     */
    public function expressFree($event,$class,$param)
    {
        $promotion = $param['promotion'];
        $goodsList = $param['goodsList'];
        $goodsTotal = 0;   //满足活动条件的商品总价
        $goodsNumber = 0;  //满足活动条件的商品总数量
        $lack_number = 0;  //距离门槛还缺多少
        $isCanUse = false; //活动是否满足门槛
        $message = '';  // 提示语

        //计算商品总价、总数量
        foreach($goodsList as $goodsKey => $goodsValue) {
            $goodsTotal = bcadd($goodsTotal, $goodsValue['promotion_total'], 2);
            $goodsNumber += $goodsValue['goods_number'];
        }
        $unit = $promotion['rule_value'][0]['unit'];
        $fullPrice = (float)$promotion['rule_value'][0]['full_price'];
        $threshold = ($unit == 'yuan') ? $goodsTotal : $goodsNumber;
        //满足门槛时
        if(bccomp($threshold, $fullPrice, 2) !== -1){
            $isCanUse = true;
        }
        //不满足门槛时，求差xxx元/件的数
        if($isCanUse === false) {
            $lack_number = bcsub($fullPrice,$threshold,2);
            if($unit == BaiyangPromotionEnum::UNIT_JIAN) {
                $fullPrice = (int)floor($fullPrice);
                $lack_number = (int)floor($lack_number);
            }
            //促销文案
            if(!empty($promotion['promotion_copywriter'])){
                $copywriter = $promotion['promotion_copywriter'];
            }else{
                $copywriter = '满'.$fullPrice.BaiyangPromotionEnum::$FULL_UNIT[$unit].'包邮';
            }
            if($this->config->platform != OrderEnum::PLATFORM_PC && $threshold){
                $copywriter .= '，还差'.$lack_number.BaiyangPromotionEnum::$FULL_UNIT[$unit];
            }
            if($promotion['promotion_scope'] != BaiyangPromotionEnum::ALL_RANGE){
                $message = '去凑单';
            }
            $proMessage = '还差'.$lack_number.BaiyangPromotionEnum::$FULL_UNIT[$unit]; //促销列表下面的提示语(在价格下面的)
        }else{
            if($unit == BaiyangPromotionEnum::UNIT_JIAN) $fullPrice = (int)floor($fullPrice);
            $copywriter = '已买满'.$fullPrice.BaiyangPromotionEnum::$FULL_UNIT[$unit];
            if($promotion['promotion_scope'] != BaiyangPromotionEnum::ALL_RANGE){
                $message = '再逛逛';
            }
            $proMessage = ''; //促销列表下面的提示语(在价格下面的)
        }
        if($unit == BaiyangPromotionEnum::UNIT_JIAN){
            $fullPrice = (int)floor($fullPrice);
            $threshold = (int)floor($threshold);
        }
        return ['isCanUse' => $isCanUse,'lack_number'=> $lack_number,'full_number' => $fullPrice, 'unit'=> $unit,'goodsList'=> $goodsList,'copywriter' => $copywriter,'message' => $message,'pro_message' => $proMessage,'bought_number' => $threshold];
    }

    /**
     * @desc 判断一个加价购是否满足门槛
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param
     *      - array [一维数组] promotion 一个加价购活动，其中rule_value用json_decode转化后再传进来
     *      - array [二维数组] goodsList 商品列表，商品属性字段：goods_id、brand_id、category_id、goods_number、promotion_total
     *      - array [二维数组] increaseBuyList 换购商品列表，商品属性字段：goods_id、goods_number、increase_buy(加价购活动id)、selected等
     * @return array 结果信息
     *      - bool          isCanUse         是否达到门槛(true|false)
     *      - int|float     lack_number      距离门槛还缺多少
     *      - string        unit             门槛单位
     *      - array         goodsList        参加加价购活动的商品列表
     *      - array         change_group     满足加价购活动门槛的换购品列表
     *      - array         increaseBuyList  购物车列表里用户选中的换购品列表
     *      - copywriter    string           促销文案
     *      - message       string           提示语：差xxx,去凑单。
     *      - bought_number int|float        已买多少
     * @author 吴俊华
     */
    public function increaseBuy($event,$class,$param)
    {
        $promotion = $param['promotion'];
        $goodsList = $param['goodsList'];
        $increaseBuyList = $param['increaseBuyList']; //用户选中的换购品列表
        //根据门槛排序
        $ruleArr = $this->arraySortByKey($promotion['rule_value'],'full_price','asc');
        $promotion['rule_value'] = $this->arraySortByKey($promotion['rule_value'],'full_price','desc');
        $discountRule = $ruleArr[0]; //较为容易达到的门槛
        $goodsTotal = 0;   //满足活动条件的商品总价
        $goodsNumber = 0;  //满足活动条件的商品总数量
        $lack_number = 0;  //距离门槛还缺多少
        $unit = 'yuan';    //门槛单位
        $isCanUse = false; //活动是否满足门槛
        $promotionsId = [];//用户选中的换购品参加的加价购活动id(需要去重)
        $message = '';  // 提示语

        //计算商品总价、总数量
        foreach($goodsList as $goodsKey => $goodsValue) {
            $goodsTotal = bcadd($goodsTotal, $goodsValue['promotion_total'], 2);
            $goodsNumber += $goodsValue['goods_number'];
        }
        $changeGroup = []; //当前加价购活动的所有换购品信息
        //换购品匹配商品信息
        foreach($promotion['rule_value'] as $ruleValue) {
            $unit = $ruleValue['unit'];
            $threshold = ($unit == 'yuan') ? $goodsTotal : $goodsNumber;
            $comResult = bccomp($threshold, $ruleValue['full_price'], 2);
            $changeGoodsGroup = $ruleValue['reduce_group'];
            $condition = [
                'goods_id' => implode(',',array_column($changeGoodsGroup,'product_id')),
                'platform' => $param['platform']
            ];
            $goodsDetail = $this->getGoodsDetail($condition);
            $stockArr = $this->func->getCanSaleStock($condition);

            // 换购品库存
            foreach ($goodsDetail as $kk => $vv){
                if(is_array($stockArr)){
                    foreach ($stockArr as $k => $v){
                        if($vv['id'] == $k){
                            $goodsDetail[$kk]['stock'] = $v;
                            continue;
                        }
                    }
                }else{
                    $goodsDetail[$kk]['stock'] = $stockArr;
                }
            }
            foreach($changeGoodsGroup as $key => $val){
                foreach($goodsDetail as $kk => $vv){
                    $goodsStatus = 0;
                    //缺货
                    if($vv['stock'] < 1){
                        $goodsStatus = 2;
                    }
                    //下架
                    if($vv['sale'] == 0){
                        $goodsStatus = 1;
                    }
                    if($val['product_id'] == $vv['id']){
                        if($vv['product_type'] != 0){
                            continue;
                        }
                        if($ruleValue['unit'] == BaiyangPromotionEnum::UNIT_JIAN) $ruleValue['full_price'] = (int)floor($ruleValue['full_price']);
                        $changeGroup[] = [
                            'goods_id' => $vv['id'],
                            'goods_name' => $vv['name'],
                            'specifications' => $vv['specifications'],
                            'stock_type' => $vv['is_use_stock'],
                            'drug_type' => $vv['drug_type'],
                            'brand_id' => $vv['brand_id'],
                            'category_id' => $vv['category_id'],
                            'sale' => $vv['sale'],
                            'stock' => $vv['stock'],
                            'goods_image' => $vv['goods_image'],
                            'sku_price' => $vv['sku_price'],
                            'market_price' => $vv['sku_market_price'],
                            'discount_price' => $val['reduce_price'],
                            'product_type' => $vv['product_type'],
                            'selected' => 0,
                            'can_select' => ($comResult == -1 || $goodsStatus != 0) ? 0 : 1,
                            'goods_status' => $goodsStatus,
                            'supplier_id' => isset($vv['supplier_id']) ? $vv['supplier_id'] : 0, //商家id
                            'returned_goods_time' => $vv['returned_goods_time'], //是否能退换货
                            'full_price' => $ruleValue['full_price'], // 门槛数(金额/件数)
                            'threshold' => '满'.$ruleValue['full_price'].BaiyangPromotionEnum::$FULL_UNIT[$ruleValue['unit']].'可换购', //门槛文案
                        ];
                    }
                }
            }
        }
        $changeGroup = $this->sortChangeGroup($changeGroup);

        $newIncreaseBuyList = [];
        $shoppingCartData = BaiyangShoppingCartData::getInstance();
        //校验前台的换购品是否满足门槛
        if(!empty($increaseBuyList)){
            $changeGoodsId = array_column($changeGroup,'goods_id');
            $promotionsId = array_unique(array_column($increaseBuyList,'increase_buy'));
            foreach($increaseBuyList as $key => $value){
                if(!in_array($promotion['promotion_id'],$promotionsId) || !in_array($value['goods_id'],$changeGoodsId)){
                    // 删除购物车表：用户选中的换购品却不在后台的加价购活动里面的(异常换购品)
                    // $shoppingCartData->deleteShoppingCart(['ids' => $value['id']]);
                    unset($increaseBuyList[$key]);
                    continue;
                }
                if($value['increase_buy'] == $promotion['promotion_id']){
                    foreach($changeGroup as $kk => $vv){
                        if($vv['can_select'] == 1){
                            if($value['goods_id'] == $vv['goods_id']){
                                if(!isset($newIncreaseBuyList[$vv['can_select'].$vv['goods_id']])){
                                    $newIncreaseBuyList[$vv['can_select'].$vv['goods_id']] = [
                                        'promotion_id' => $promotion['promotion_id'],
                                        'promotion_scope' => $promotion['promotion_scope'],
                                        'goods_id' => $vv['goods_id'],
                                        'goods_name' => $vv['goods_name'],
                                        'goods_number' => 1,
                                        'specifications' => $vv['specifications'],
                                        'stock_type' => $vv['stock_type'],
                                        'drug_type' => $vv['drug_type'],
                                        'brand_id' => $vv['brand_id'],
                                        'category_id' => $vv['category_id'],
                                        'sale' => $vv['sale'],
                                        'stock' => $vv['stock'],
                                        'goods_image' => $vv['goods_image'],
                                        'sku_price' => $vv['sku_price'],
                                        'market_price' => $vv['market_price'],
                                        'sku_total' => $vv['sku_price'],
                                        'discount_price' => $vv['discount_price'],
                                        'discount_total' => $vv['discount_price'],
                                        'goods_status' => $vv['goods_status'],
                                        'product_type' => $vv['product_type'],
                                        // 换购品状态异常时，不能选中
                                        'selected' => $vv['goods_status'] == 0 ? 1 : 0,
                                        'supplier_id' => isset($vv['supplier_id']) ? $vv['supplier_id'] : 0, //商家id
                                        'returned_goods_time' => $vv['returned_goods_time'], //是否能退换货
                                        'full_price' => $vv['full_price'], // 门槛数(金额/件数)
                                        'threshold' => $vv['threshold'], //文门槛案
                                    ];
                                }
                                $changeGroup[$kk]['selected'] = 1;
                            }
                        }else{
                            // 删除购物车表：不满足门槛的换购品
                            // $shoppingCartData->deleteShoppingCart(['ids' => $value['id']]);
                            $changeGroup[$kk]['selected'] = 0;
                        }
                    }
                }
            }
            if(!empty($newIncreaseBuyList)){
                $newIncreaseBuyList = array_values($newIncreaseBuyList);
            }
        }

        $newChangeGroup = [];
        foreach($changeGroup as $key => $value){
            //下架、缺货的换购品不能选
            if($changeGroup[$key]['goods_status'] != 0){
                $changeGroup[$key]['can_select'] = 0;
            }
            //去重：换购品相同时，留下优惠价较低的商品
            if($changeGroup[$key]['can_select'] == 1){
                if(!isset($newChangeGroup[$changeGroup[$key]['can_select'].$changeGroup[$key]['goods_id']])){
                    $newChangeGroup[$changeGroup[$key]['can_select'].$changeGroup[$key]['goods_id']] = $changeGroup[$key];
                }
            }else{
                $newChangeGroup[] = $changeGroup[$key];
            }
        }
        $newChangeGroup = array_values($newChangeGroup);

        //判断门槛(返回所有的换购品)
        foreach($promotion['rule_value'] as $ruleValue) {
            $unit = $ruleValue['unit'];
            $threshold = ($unit == 'yuan') ? $goodsTotal : $goodsNumber;
            //满足门槛时
            if(bccomp($threshold, $ruleValue['full_price'], 2) !== -1){
                $isCanUse = true;
                $lack_number = 0;
                $change_group = $this->arraySortByKey($ruleValue['reduce_group'],'reduce_price','asc');
                //促销文案
                if($unit == BaiyangPromotionEnum::UNIT_JIAN){
                    $lack_number = (int)floor($lack_number);
                    $threshold = (int)floor($threshold);
                    $ruleValue['full_price'] = (int)floor($ruleValue['full_price']);
                }
                $copywriter = '已满'.$ruleValue['full_price'].BaiyangPromotionEnum::$FULL_UNIT[$ruleValue['unit']];
                if(in_array($promotion['promotion_id'],$promotionsId)){
                    $copywriter .= '，已换购商品';
                    if($promotion['promotion_scope'] != BaiyangPromotionEnum::ALL_RANGE){
                        $message = '重新换购';
                    }
                }else{
                    $copywriter .= '，可加'.$change_group[0]['reduce_price'].'元换购商品';
                    if($promotion['promotion_scope'] != BaiyangPromotionEnum::ALL_RANGE){
                        $message = '去换购';
                        if($param['platform'] == OrderEnum::PLATFORM_PC){
                            $message = '再逛逛';
                        }
                    }
                }
                $proMessage = '可换购商品'; //促销列表下面的提示语(在价格下面的)

                return ['isCanUse' => $isCanUse,'lack_number'=> $lack_number, 'unit'=> $unit,'goodsList'=> $goodsList,'change_group' => $newChangeGroup,'increaseBuyList' => $newIncreaseBuyList,'copywriter' => $copywriter,'message' => $message,'pro_message' => $proMessage,'bought_number' => $threshold];
            }
            //求差xxx元/件的最接近数
            $temp_lack_number = bcsub($ruleValue['full_price'],$threshold,2);
            if ($lack_number === 0) {
                $lack_number = $temp_lack_number;
            } else {
                $lack_number = $lack_number < $temp_lack_number ? $lack_number : $temp_lack_number;
            }
        }
        //不满足门槛时
        if(!empty($newIncreaseBuyList)){
            foreach($newIncreaseBuyList as $key => $value){
                if($value['increase_buy'] == $promotion['promotion_id']){
                    unset($newIncreaseBuyList[$key]);
                    continue;
                }
            }
            if(!empty($newIncreaseBuyList)){
                $newIncreaseBuyList = array_values($newIncreaseBuyList);
            }
        }
        //已买多少
        $bought_number = ($discountRule['unit'] == 'yuan') ? $goodsTotal : $goodsNumber;
        if($unit == BaiyangPromotionEnum::UNIT_JIAN){
            $lack_number = (int)floor($lack_number);
            $bought_number = (int)floor($bought_number);
            $discountRule['full_price'] = (int)floor($discountRule['full_price']);
        }
        //促销文案
        if(!empty($promotion['promotion_copywriter'])){
            $copywriter = $promotion['promotion_copywriter'];
        }else{
            $copywriter = '满'.$discountRule['full_price'].BaiyangPromotionEnum::$FULL_UNIT[$discountRule['unit']].'，即可换购商品';
        }
        if($this->config->platform != OrderEnum::PLATFORM_PC && $bought_number){
            $copywriter .= '，还差'.$lack_number.BaiyangPromotionEnum::$FULL_UNIT[$unit];
        }
        if($promotion['promotion_scope'] != BaiyangPromotionEnum::ALL_RANGE){
            $message = '去凑单';
        }
        $proMessage =  '还差'.$lack_number.BaiyangPromotionEnum::$FULL_UNIT[$unit]; //促销列表下面的提示语(在价格下面的)
        return ['isCanUse' => $isCanUse,'lack_number'=> $lack_number, 'unit'=> $unit,'goodsList'=> $goodsList,'change_group' => $newChangeGroup,'increaseBuyList' => $newIncreaseBuyList,'copywriter' => $copywriter,'message' => $message,'pro_message' => $proMessage,'bought_number' => $bought_number];
    }

    /**
     * @desc 订单页面可用的优惠券列表
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array $param
     *      - array  basicParam 基础参数:platform、user_id、is_temp
     *      - array  shoppingCartGoodsList 购物车商品信息
     *      - array  mutexList  互斥数组，如：['7000800'=>[15,20]]
     *      - array  joinList   商品参加活动的数组，如：['7000800'=>[15,20]]
     *      - string recordId   优惠券领取id
     *      - string action     orderInfo:购物车结算 commitOrder:提交订单 coupon:切换优惠券
     * @return array
     *      - couponList 优惠券列表
     *      - joinList 商品参加活动的数组
     *      - goodList 购物车商品信息
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getCouponList($event,$class,$param)
    {
        $basicParam = $param['basicParam'];
        $userId = $basicParam['user_id'];
        $platform = $basicParam['platform'];
        $shoppingCartGoodsList = $param['shoppingCartGoodsList'];
        $joinList = $param['joinList'];
        $mutexList = $param['mutexList'];
        $recordId = $param['recordId'];
        $action = $param['action'];
        // 初始化
        $couponInfo = [
            'couponList' => [],
            'joinList' => $joinList,
            'goodsList' => $shoppingCartGoodsList,
        ];

        $couponData = BaiyangCouponData::getInstance();
        //获取当前用户已经领取的的优惠券
        $userAllCoupon = $couponData->getCurrentUserCoupon($userId,$platform);
        //根据优惠券类型(是否绝对优惠券)进行时间上控制并加入到新的该时间段优惠券列表里
        $list = $couponData->getCurrentUserCouponInTime($userAllCoupon,time());
        if(empty($list)) return $couponInfo;  // 该用户没有优惠券
        // 所有优惠券都匹配上购物车的所有商品、套餐
        $couponList = [];
        foreach ($list as $key => $value){
            $couponList[$key] = [
                'coupon' => $value,
                'goodsList' => $shoppingCartGoodsList,
            ];
        }

        // 优惠券排除互斥、不参加的商品、套餐
        foreach ($couponList as $key => $value){
            foreach ($value['goodsList'] as $kk => $vv) {
                //排除互斥的套餐
                if ($vv['group_id'] > 0) {
                    if (!$this->isSatisfyCouponGroup($value['coupon'], $vv, $mutexList, $joinList)) {
                        unset($couponList[$key]['goodsList'][$kk]);
                    }
                } else {
                    //排除互斥活动商品
                    if (!$this->isSatisfyCoupon($value['coupon'], $vv, $mutexList, $joinList)) {
                        unset($couponList[$key]['goodsList'][$kk]);
                    }
                }
            }
             // 去除包邮券
            if($couponList[$key]['coupon']['coupon_type'] == 3){
                unset($couponList[$key]);
                continue;
            }
             // 去除购物车里所有商品、套餐都不参加的优惠券
            if(empty($couponList[$key]['goodsList'])){
                unset($couponList[$key]);
            }
        }
        if(empty($couponList)) return $couponInfo;  // 购物车列表没有商品、套餐参加优惠券
        $couponList = array_values($couponList);
        // 计算门槛
        foreach ($couponList as $key => $value){
            $result = $this->coupon($value);
            // 过滤不达到门槛的优惠券
            if(!$result['isCanUse']){
                unset($couponList[$key]);
                continue;
            }
            $couponList[$key]['coupon']['discount'] = $result['discount_price'];
            $couponList[$key]['coupon']['is_selected'] = 0;
            $couponList[$key]['coupon']['expiration'] = $this->getExpirationInfo($value['coupon'],$userId);
            $couponList[$key]['goodsList'] = $result['goodsList'];
        }
        if(empty($couponList)) return $couponInfo;  // 没有到达门槛的优惠券
        $couponList = array_values($couponList);
        // 根据优惠金额和有效期排序
        $sort1 = [];
        $sort2 = [];
        foreach ($couponList as $key => $value) {
            $sort1[] = $value['coupon']['discount'];
            $sort2[] = $value['coupon']['expiration'];
        }
        array_multisort($sort1, SORT_DESC, $sort2, SORT_DESC, $couponList);

        $newCouponList = []; // 计算、选中后的优惠券列表
        $joinGoodsList = []; // 参加选中优惠券后的商品、套餐
        $choose = false; // 优惠券是否已选中
        foreach ($couponList as $key => $value){
            // 购物车结算(加载"提交订单页面"时)
            if($action == 'orderInfo'){
                if($key == 0){
                    $couponList[$key]['coupon']['is_selected'] = 1;
                    $joinGoodsList = array_values($value['goodsList']);
                }
            }else{
                // 提交订单和切换优惠券
                if($value['coupon']['record_id'] == $recordId && !$choose){
                    $couponList[$key]['coupon']['is_selected'] = 1;
                    $joinGoodsList = array_values($value['goodsList']);
                    $choose = true;
                }
            }
            $newCouponList[] = $couponList[$key]['coupon'];
        }
        // 匹配coupon_total
        foreach ($couponList as $key => $value){
            if($value['coupon']['is_selected'] == 1){
                foreach ($value['goodsList'] as $ky => $vl){
                    foreach ($shoppingCartGoodsList as $kk => $vv){
                        // 套餐
                        if($vl['group_id'] > 0){
                            if($vl['group_id'] == $vv['group_id']){
                                $shoppingCartGoodsList[$kk]['coupon_total'] = isset($vl['coupon_total']) ? $vl['coupon_total'] : 0;
                                $shoppingCartGoodsList[$kk]['groupGoodsList'] = $vl['groupGoodsList'];
                            }
                        }elseif($vl['group_id'] == $vv['group_id']){
                            // 普通商品
                            if($vl['goods_id'] == $vv['goods_id']){
                                $shoppingCartGoodsList[$kk]['coupon_total'] = isset($vl['coupon_total']) ? $vl['coupon_total'] : 0;
                            }
                        }
                    }
                }
            }
        }
        unset($couponList);

        if(!empty($joinGoodsList)){
            // 修改参加的数组
            foreach ($joinGoodsList as $kk => $vv) {
                if ($vv['group_id'] > 0) {
                    $this->func->editJoinMutex($mutexList, $joinList, 'g'.$vv['group_id'], '', BaiyangPromotionEnum::COUPON);
                } else {
                    $this->func->editJoinMutex($mutexList, $joinList, $vv['goods_id'], '', BaiyangPromotionEnum::COUPON);
                }
            }
            // 修改购物车的商品、套餐参加完优惠券的优惠价
            foreach ($shoppingCartGoodsList as $key => $value){
                foreach ($joinGoodsList as $kk => $vv){
                    // 套餐
                    if($value['group_id'] > 0 && $vv['group_id'] > 0 ){
                        if($value['group_id'] == $vv['group_id']){
                            $shoppingCartGoodsList[$key] = $vv;
                        }
                    }else{
                        // 普通商品
                        if($value['goods_id'] == $vv['goods_id']){
                            $shoppingCartGoodsList[$key] = $vv;
                        }
                    }
                }
            }
        }
        $couponInfo = [
            'couponList' => $this->handleCouponData($newCouponList, $userId, $platform),  // 处理优惠券其他字段
            'joinList' => $joinList,
            'goodsList' => $shoppingCartGoodsList,
        ];
        return $couponInfo;
    }

    /**
     * @desc 判断一张优惠券是否满足门槛 (满减、满折、包邮券)
     * @access public
     * @param array $param
     *      - coupon：一张优惠券
     *      - goodsList：商品列表，商品属性字段：goods_id、brand_id、category_id、goods_number、promotion_price、promotion_total
     * @return array  结果信息
     *      - isCanUse        bool        活动是否满足门槛
     *      - discount_price  float       优惠券的优惠金额(满折券要计算出来)
     *      - goodsList       array       参加当前活动后商品列表
     * @author 吴俊华
     */
    private function coupon($param)
    {
        $promotion = $param['coupon'];
        $goodsList = $param['goodsList'];
        $goodsTotal = 0;   // 满足优惠券条件的商品总价
        $goodsNumber = 0;  // 满足优惠券条件的商品总数量
        $discountPrice = 0; // 优惠券的优惠金额(满折券要计算出来)
        $isCanUse = false; // 优惠券是否满足门槛
        // 获取商品总价和总数量
        foreach ($goodsList as $goodsInfo) {
            $goodsTotal = bcadd($goodsTotal, $goodsInfo['promotion_total'], 2);
            $goodsNumber += $goodsInfo['goods_number'];
        }
        if(!empty($goodsList)){
            $sort_array = []; // 价格由低到高排序
            foreach ($goodsList as $goodsInfo) {
                $sort_array[] = $goodsInfo['promotion_total'];
            }
            array_multisort($sort_array, SORT_ASC, $goodsList);
        }

        $unit = $promotion['discount_unit'];
        $goodsFull = $unit == 2 ? $goodsNumber: $goodsTotal;
        if (bccomp($goodsFull, $promotion['min_cost'], 2) !== -1) {
            // 满折券
            if($promotion['coupon_type'] == 2){
                $discountPrice =  bcmul($goodsTotal, (bcsub(1, bcdiv($promotion['coupon_value'], 10, 2), 2)), 2);
            }else{
                // 满减、包邮券
                $discountPrice = sprintf("%.2f", $promotion['coupon_value']);
            }
            $isCanUse = true;
            $tempReduceSum = 0;
            // 如果促销活动满足门槛就修改促销价(按比例计算出各个商品的优惠金额)
            if($promotion['coupon_type'] == 1 || $promotion['coupon_type'] == 2){
                $goodsList = array_values($goodsList);
                foreach($goodsList as $key => $goodsInfo) {
                    if( count($goodsList) == $key+1 ) {
                        $tempReduce = bcsub($discountPrice, $tempReduceSum, 2);
                    } else {
                        $tempReduce = bcdiv(bcmul($discountPrice, $goodsInfo['promotion_total'], 2), $goodsTotal, 2);
                        $tempReduceSum = bcadd($tempReduceSum, $tempReduce, 2);
                    }
                    $goodsList[$key]['coupon_total'] = $tempReduce;
                    $goodsList[$key]['promotion_total'] = bcsub($goodsInfo['promotion_total'], $tempReduce, 2);
                    if($goodsInfo['group_id'] == 0){
                        $goodsList[$key]['promotion_price'] = bcdiv($goodsList[$key]['promotion_total'], $goodsList[$key]['goods_number'], 2);
                    } else {
                        // 补差计算套餐商品优惠券优惠金额
                        $tempSum1 = 0;
                        foreach ($goodsInfo['groupGoodsList'] as $k => $v) {
                            if( count($goodsInfo['groupGoodsList']) == $k+1 ) {
                                $tempReduce1 = bcsub($tempReduce, $tempSum1, 2);
                            } else {
                                $tempReduce1 = bcdiv(bcmul($tempReduce, $v['discount_total'], 2), $goodsInfo['discount_total'], 2);
                                $tempSum1 = bcadd($tempSum1, $tempReduce1, 2);
                            }
                            $goodsList[$key]['groupGoodsList'][$k]['coupon_total'] = $tempReduce1;
                        }
                    }
                }
            }
            return ['isCanUse' => $isCanUse, 'discount_price' => $discountPrice , 'goodsList' => $goodsList];
        }
        return ['isCanUse' => $isCanUse, 'discount_price' => $discountPrice , 'goodsList' => $goodsList];
    }

    /**
     * @desc 处理优惠券数据结构  (生成优惠券文案、有效期等)
     * @param array $couponList 优惠券列表
     * @param int $user_id 用户id
     * @param string $platform 平台
     * @return array $newCouponList 处理后的优惠券列表信息
     * @author 邓永军
     */
    private function handleCouponData($couponList,$user_id,$platform)
    {
        $newCouponList = [];
        foreach ($couponList as $key => $value){
            $newCouponList[] = [
                'id' => $value['coupon_id'],
                'record_id' => $value['record_id'],
                'coupon_sn' => $value['coupon_sn'],
                'coupon_name' => $value['coupon_name'],
                'tips' => $this->gotCouponTips($value),
                'start_provide_time' => $value['start_provide_time'],
                'end_provide_time' => $value['end_provide_time'],
                'coupon_value'=>$value['coupon_value'],
                'coupon_type'=>$value['coupon_type'],
                'min_cost'=>$value['min_cost'],
                'discount_unit' => $value['discount_unit'],
                'coupon_number'=>$value['coupon_number'],
                'got_num' => $this->countCouponHasBring($user_id,$value['coupon_sn']),
                'is_over_bring_limit' => $this->isUserOverBringLimit($user_id,$value['coupon_sn'],$value['limit_number']),
                'type' => 1,
                'use_range'=>$value['use_range'],
                $value['use_range'] => $this->getIdsFromUseRange($value['use_range'])!=''?$value[$this->getIdsFromUseRange($value['use_range'])]:'',
                'validitytype' => $value['validitytype'],
                'relative_validity' => $value['relative_validity'],
                'start_use_time' => $value['start_use_time'],
                'end_use_time' => $value['end_use_time'],
                'ban_join_rule' => $value['ban_join_rule'],
                'url' => $value[strtolower($platform).'_url'],
                'is_over' => $this->getCouponIsOver($value),
                'expiration' => $value['expiration'],
                'user_limit' => $value['limit_number'],
                'remain_time' => $this->getExpirationInfo($value,$user_id) - time(),
                'server_time' => time(),
                'coupon_description' => $value['coupon_description'],
                'expiration_tips' => $this->getExpirationInfo($value,$user_id,1),
                'rest_got_num' => $value['limit_number'] - $this->countCouponHasBring($user_id,$value['coupon_sn']),
                'discount' => $value['discount'],
                'is_selected' => $value['is_selected']

            ];
        }
        return $newCouponList;
    }


    /**
     * @desc 获取有效期信息
     * @param $item
     * @param $isShowTips
     * @param $user_id
     * @return string
     */
    private function getExpirationInfo($item,$user_id,$isShowTips = 0)
    {
        if($item['validitytype'] == 1){
            $expiration = $item['end_use_time'];
            if($isShowTips == 0){
                return  $expiration;
            }else{
                return '有效期至'.date('Y-m-d',$expiration);
            }
        }else{
            $relative_time = BaiyangCouponRecordData::getInstance()->CouponHasBringInfo($user_id,$item['coupon_sn']);
            if($isShowTips == 0){
                if($relative_time > 0){
                    $expiration = $relative_time + ($item['relative_validity'] * 24 * 3600);
                    return $expiration;
                }else{
                    $expiration = $relative_time;
                    return $expiration;
                }
            }else{
                return '有效期自领取'.$item['relative_validity'].'天有效';
            }
        }
    }
    /**
     * @desc 判断该优惠券是否领取完
     * @param $value
     * @return int
     * @author 邓永军
     */
    private function getCouponIsOver($value){
        if($value['coupon_number'] == 0){
            return 0;
        }else{
            if($value['coupon_number'] - $value['bring_number'] > 0 ){
                return 0;
            }else{
                return 1;
            }
        }
    }
    /**
     * @desc 根据使用范围返回对应要获取的数据库字段
     * @param $type 使用范围的值
     * @return string
     * @author 邓永军
     */
    private function getIdsFromUseRange($type)
    {
        switch ($type)
        {
            case BaiyangCouponEnum::ALL_RANGE:
                return '';
                break;
            case BaiyangCouponEnum::SINGLE_RANGE:
                return 'product_ids';
                break;
            case BaiyangCouponEnum::BRAND_RANGE:
                return 'brand_ids';
                break;
            case BaiyangCouponEnum::CATEGORY_RANGE:
                return 'category_ids';
                break;
        }
    }
    /**
     * @desc 是否超过限制
     * @param $user_id
     * @param $coupon_sn
     * @param $limit_number
     * @return int
     * @author 邓永军
     */
    private function isUserOverBringLimit($user_id,$coupon_sn,$limit_number)
    {
        if($limit_number == 0 ){
            return 0;
        }
        if($this->countCouponHasBring($user_id,$coupon_sn) >= $limit_number){
            return 1;
        }else{
            return 0;
        }
    }
    /**
     * @desc 获取优惠券tips
     * @param $item
     * @return string
     * @author 邓永军
     */
    private function gotCouponTips($item)
    {
        switch ($item['coupon_type'])
        {
            case '1':
                return '满'.$item['min_cost'].'元减'.$item['coupon_value'].'元';
                break;
            case '2':
                if($item['discount_unit'] == 1){
                    $unit='元';
                }else{
                    $unit='件';
                }
                return '满'.$item['min_cost'].$unit.'享'.$item['coupon_value'].'折';
                break;
            case '3':
                return '满'.$item['min_cost'].'元包邮';
                break;
        }
    }
    /**
     * @desc 判断套餐是否满足促销活动条件
     * @param array $promotion 促销活动信息
     * @param array $groupInfo 套餐信息 [group_id=>int,groupGoodsList=>array]
     * @param array $mutexList 商品的互斥数组,默认为空  ['7000800'=>[15,20]]
     * @param array $joinList 商品参加过的活动数组,默认为空  ['7000800'=>[15,20]]
     * @return bool true|false 满足返回true,不满足返回false
     * @author 吴俊华
     */
    private function isSatisfyCouponGroup($promotion, $groupInfo, $mutexList = [], $joinList = [])
    {
        foreach ($groupInfo['groupGoodsList'] as $value) {
            if (!$this->isSatisfyCoupon($promotion, $value)) {
                return false;
            }
        }
        // 其他活动互斥当前活动
        if(isset($mutexList['g'.$groupInfo['group_id']]) && !empty($mutexList['g'.$groupInfo['group_id']])) {
            if (in_array(25, $mutexList['g'.$groupInfo['group_id']])) {
                return false;
            }
        }
        return true;
    }

    /**
     * @desc 判断商品是否满足优惠券条件
     * @param array $coupon    优惠券活动信息
     * @param array $goodsInfo 商品信息
     * @param array $mutexList 商品的互斥数组,默认为空  ['7000800'=>[15,20]]
     * @param array $joinList 商品参加过的活动数组,默认为空  ['7000800'=>[15,20]]
     * @return bool true|false 满足返回true,不满足返回false
     * @author 吴俊华
     */
    private function isSatisfyCoupon($coupon, $goodsInfo, $mutexList = [], $joinList = [])
    {
        $promotionType = BaiyangPromotionEnum::COUPON;
        // 使用范围：全场、品类、品牌、单品
        switch ($coupon['use_range']) {
            case BaiyangPromotionEnum::ALL_RANGE:
                $keyIdName = '';
                $condition = '';
                break;
            case BaiyangPromotionEnum::CATEGORY_RANGE:
                $keyIdName = 'category_id';
                $condition = $coupon['category_ids'];
                break;
            case BaiyangPromotionEnum::BRAND_RANGE:
                $keyIdName = 'brand_id';
                $condition = $coupon['brand_ids'];
                break;
            case BaiyangPromotionEnum::SINGLE_RANGE:
                $keyIdName = 'goods_id';
                $condition = $coupon['product_ids'];
                break;
            default:
                $keyIdName = '';
                $condition = '';
                break;
        }
        // 判断处方优惠券与处方商品
        if($coupon['drug_type'] == 'rx' && $goodsInfo['drug_type'] != 1){
            return false;
        }
        if($coupon['drug_type'] == 'non_rx' && $goodsInfo['drug_type'] == 1){
            return false;
        }
        if ($keyIdName != '' && !in_array($goodsInfo[$keyIdName], explode(',', $condition))) {
            return false;
        }
        // 其他活动互斥当前活动
        if(isset($mutexList[$goodsInfo['goods_id']]) && !empty($mutexList[$goodsInfo['goods_id']])) {
            if (in_array($promotionType, $mutexList[$goodsInfo['goods_id']])) {
                return false;
            }
        }

        $exceptArr = json_decode($coupon['ban_join_rule'],true);
        $exceptGoodsId = isset($exceptArr['single']) ? $exceptArr['single'] : '';
        $exceptBrandId = isset($exceptArr['brand']) ? $exceptArr['brand'] : '';
        $exceptCategoryId = isset($exceptArr['category']) ? $exceptArr['category'] : '';
        // 排除的商品ID
        if(in_array($goodsInfo['goods_id'], explode(',', $exceptGoodsId))) {
            return false;
        }
        // 排除的品牌ID
        if(in_array($goodsInfo['brand_id'], explode(',', $exceptBrandId))) {
            return false;
        }
        // 获取该分类下的所有子分类(主要是3级分类)
        if(!empty($exceptCategoryId)){
            $exceptCategoryId = $this->func->getAllCategoryId($exceptCategoryId);
        }
        // 排除的品类ID
        if(in_array($goodsInfo['category_id'], explode(',', $exceptCategoryId))) {
            return false;
        }
        return true;
    }

}