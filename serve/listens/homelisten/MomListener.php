<?php
/**
 * 辣妈商品验证相关
 *
 * Created by PhpStorm.
 * User: Sary
 * Date: 2017/2/21
 * Time: 17:49
 */

namespace Shop\Home\Listens;

use Shop\Home\Datas\BaiyangGoodsPrice;
use Shop\Home\Datas\BaiyangMomGetGiftData;
use Shop\Models\HttpStatus;

class MomListener extends BaseListen
{

    /**
     * 辣妈商品限购检查(编辑购物车)
     *
     * @param $event
     * @param $class
     * @param $param
     *              array(
     *                  'user_id' => 1,
     *                  'goods_id' => 800001,
     *                  'price' => 10,
     *                  'goods_number' => 1,
     *                  'cart_goods_number' => 3
     *              )
     * @return array
     */
    public function momGiftGoodsLimitVerify($event,$class,$param)
    {
        $giftIsNotReport = BaiyangMomGetGiftData::getInstance()->getMomGiftIsNotReport($param['user_id']);
        if (!$giftIsNotReport) {
            if ($param['user_id'] && $param['goods_number'] != $param['cart_goods_number']) {
                $goodsTagPrice = BaiyangGoodsPrice::getInstance()->getGoodsTagPriceList($param['user_id'], $param['goods_id'], $param['price']);
                // 辣妈商品限购
                if (!empty($goodsTagPrice) && ($goodsTagPrice[$param['goods_id']]['tag_id'] == $this->config->mom->tag_id)) {
                    return ['error' => 1, 'code' => HttpStatus::GIFT_CANNOT_REPEAT_GET, 'data' => []];
                }
            }
        }
        return ['error' => 0,'code' => HttpStatus::SUCCESS,'data' => []];
    }

    /**
     * 妈妈礼包商品验证（加入购物车）
     *
     * @param array $param
     *              array(
     *                  'user_id' => 1,
     *                  'goods_id' => 800001,
     *                  'price' => 10,
     *                  'goods_number' => 1,
     *                  'tagPriceLimitCheck' => true (boolean)
     *              )
     * @return array
     */
    public function momGiftGoodsVerify($event, $class, array $param)
    {
        $momGetGiftData = BaiyangMomGetGiftData::getInstance();
        $giftIsNotReport = $momGetGiftData->getMomGiftIsNotReport($param['user_id']);
        if ($giftIsNotReport) {
            return ['error' => 1,'code' => HttpStatus::SUCCESS,'data' => []];
        }
        $goodsTagPrice = BaiyangGoodsPrice::getInstance()->getGoodsTagPriceList($param['user_id'], $param['goods_id'], $param['price']);
        if ($goodsTagPrice
            && ($goodsTagPrice[$param['goods_id']]['tag_id'] == $this->config->mom->tag_id)) {
            if ($param['goods_number'] <= $goodsTagPrice[$param['goods_id']]['limit_number']) {
                /*$giftIsNotReport = $momGetGiftData->getMomGiftIsNotReport($param['user_id']);
                if ($giftIsNotReport) {
                    return ['error' => 1,'code' => HttpStatus::GIFT_GOODS_CANNOT_GET,'data' => []];
                }*/
                $goods_id = $param['goods_id'];
                if ($param['tagPriceLimitCheck']) {
                    $goods_id = 0;
                }
                $giftGoodsHadGet = $momGetGiftData->momHadBuyGiftGoods($param['user_id'], $goodsTagPrice[$param['goods_id']]['gift_id'], $goods_id);
                if ($giftGoodsHadGet) {
                    // 是否有该辣妈活动商品订单
                    $giftGoodsHadBuy = $momGetGiftData->momHadBuyGiftGoods($param['user_id'], $goodsTagPrice[$param['goods_id']]['gift_id'], 0, 2);
                    // 需要检查辣妈限购或已生成订单
                    if ($param['tagPriceLimitCheck'] || $giftGoodsHadBuy) {
                        return ['error' => 1,'code' => HttpStatus::GIFT_CANNOT_REPEAT_GET,'data' => []];
                        // 在礼包详情添加购物车
                    } else {
                        // 更新购买历史数量
                        $giftGoodsHadBuy = $momGetGiftData->momHadBuyGiftGoods($param['user_id'], $goodsTagPrice[$param['goods_id']]['gift_id'], $goods_id, 1);
                        $momGetGiftData->updateData(array(
                            'table' => 'Shop\Models\BaiyangMomGetGift',
                            'column' => "goods_pty={$param['goods_number']}",
                            'where' => 'id=:id:',
                            'bind' => ['id' => $giftGoodsHadBuy['id']]
                        ));
                    }
                } else {
                    $momGetGiftData->addMomGetGift(array(
                        'user_id' => $param['user_id'],
                        'gift_id' => $goodsTagPrice[$param['goods_id']]['gift_id'],
                        'position' => 1,
                        'goods_number' => $param['goods_number'],
                        'price' => $goodsTagPrice[$param['goods_id']]['price'],
                        'goods_id' => $param['goods_id']
                    ));
                }
            } else {
                return ['error' => 1,'code' => HttpStatus::GOODS_NUMBER_OVER,'data' => []];
            }
        }
        return ['error' => 0,'code' => HttpStatus::SUCCESS,'data' => []];
    }

    /**
     * 删除妈妈领取礼包的商品(删除购物车商品)
     *
     * @param $event
     * @param $class
     * @param array $param
     *              array(
     *                  'user_id' => 1,
     *                  'goods_id_list' => array(80001,80002)
     *              )
     */
    public function deleteMomGiftGetGoods($event, $class, array $param)
    {
        $sGoodsId = implode(',', $param['goods_id_list']);
        $goodsTagPriceList = BaiyangGoodsPrice::getInstance()->getGoodsTagPriceList($param['user_id'], $sGoodsId);
        if ($goodsTagPriceList) {
            foreach ($param['goods_id_list'] as $goods_id) {
                if (isset($goodsTagPriceList[$goods_id])
                    && $goodsTagPriceList[$goods_id]['tag_id'] == $this->config->mom->tag_id) {
                    BaiyangGoodsPrice::getInstance()->deleteData(array(
                        'table' => 'Shop\Models\BaiyangMomGetGift',
                        'where' => "WHERE user_id=:user_id: AND gifts_id=:gift_id: AND goods_id=:goods_id: AND ascription=1",
                        'bind' => array(
                            'user_id' => $param['user_id'],
                            'gift_id' => $goodsTagPriceList[$goods_id]['gift_id'],
                            'goods_id' => $goods_id
                        )
                    ));
                }
            }
        }
    }

    /**
     * 检查提交订单商品是否有辣妈商品
     *
     * @param $event
     * @param $class
     * @param array $param
     *              array(
     *                  'user_id' => 1,
     *                  'goods_group_list' => array(0=> array(array(
     *                                              'goods_id'=>12,
     *                                              'gift_id' => 6,
     *                                              'price' => 100,
     *                                              'tag_id' => 1 ,//标签id
     *                                              )))  //所有商品列表，套餐也算
     *              )
     * @return array
     */
    public function checkOrderMomGoods($event, $class, array $param)
    {
        $where = "";
        $momGetGiftData = BaiyangMomGetGiftData::getInstance();
        $giftIsNotReport = $momGetGiftData->getMomGiftIsNotReport($param['user_id']);
        if (!$giftIsNotReport) {
            foreach ($param['goods_group_list'] as $group_id => $goodsList) {
                if ($group_id == 0) {
                    foreach ($goodsList as $key => $goods) {
                        if (isset($param['user_id']) && $goods['tag_id'] == $this->config->mom->tag_id) {
                            $giftGoodsHadBuy = $momGetGiftData->momHadBuyGiftGoods($param['user_id'], $goods['gift_id'], 0, 2, true);
                            if ($giftGoodsHadBuy && $goods['price'] <= 0) {
                                return ['error' => 1, 'code' => HttpStatus::GIFT_ONLY_ONE_BUY, 'data' => []];
                            }
                            $where .= "{$goods['goods_id']},";
                        }
                    }
                }
            }
            if ($where) {
                $where = trim($where, ',');
                $where = " user_id = {$param['user_id']} AND goods_id IN ({$where}) AND gifts_id={$goods['gift_id']}";
            }
        }
        return ['error' => 0,'code' => HttpStatus::SUCCESS,'data' => ['where' => $where]];
    }
}