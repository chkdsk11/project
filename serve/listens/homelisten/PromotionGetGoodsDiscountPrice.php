<?php
/**
 * Created by PhpStorm.
 * User: 吴俊华
 * Date: 2016/10/12 0012
 * Time: 上午 11:34
 */
namespace Shop\Home\Listens;


use Shop\Models\BaiyangPromotionEnum;

class PromotionGetGoodsDiscountPrice extends BaseListen
{

    /**
     * 获取商品限时优惠价格
     *
     * @param $event
     * @param $class
     * @param $param
     * @return array
     */
    public function getGoodsLimitOfferPrice($event,$class,$param)
    {
        $limitOfferList = $this->getProcessingPromotions('', '', ['platform' => $param['platform'], 'user_id' => $param['user_id'], 'is_temp' => $param['is_temp'], 'promotion_type' => BaiyangPromotionEnum::LIMIT_TIME]);
        $goodsOfferList = array();
        $goods_id_list = array_keys($param['goods_id_list']);
        foreach ($limitOfferList as $key => $value) {
            $condition = explode(',', $value['condition']);
            $goodsList = array_intersect($goods_id_list, $condition);
            if (!$goodsList) {
                continue;
            }
            foreach ($goodsList as $goods_id) {
                //if ($value['member_tag'] == 0 || ($value['member_tag'] > 0 && in_array($goods_id, $value['tag_goods_id']))) {
                    $param['goodsInfo'] = array(
                        'goods_id' => $goods_id,
                        'sku_price' => $param['goods_id_list'][$goods_id]
                    );
                    $offer = $this->verifyLimitTime($param, $value);
                    if ($offer) {
                        $goodsOfferList[$goods_id] = $offer;
                    }
                //}
            }
        }
        return $goodsOfferList;
    }

}