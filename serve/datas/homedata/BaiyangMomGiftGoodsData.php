<?php
/**
 * 礼包相关商品
 *
 * Created by PhpStorm.
 * User: Sary
 * Date: 2016/12/27
 * Time: 16:51
 */

namespace Shop\Home\Datas;

class BaiyangMomGiftGoodsData extends BaseData
{

    protected static $instance=null;

    /**
     * 获取礼包活动商品关联的商品标签价格
     *
     * @param int $giftId 礼包id
     * @param int $goodsId 商品id
     * @return array|bool
     */
    public function getGiftActivityGoodsPrice($giftId, $goodsId)
    {
        $ret = $this->getData(array(
            'table' => 'Shop\Models\BaiyangMomActivityGoods AS mag',
            'join' => 'INNER JOIN Shop\Models\BaiyangGoodsPrice AS gp ON mag.tag_goods_id = gp.tag_goods_id',
            'column' => 'goods_id,limit_number,price',
            'where' => 'WHERE mag.gifts_id=:gift_id: AND gp.goods_id=:goods_id: AND gp.tag_id=1',
            'bind' => array(
                'gift_id' => $giftId,
                'goods_id' => $goodsId
            )
        ), true);
        return $ret;
    }
}