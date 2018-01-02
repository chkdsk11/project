<?php
/**
 * 商品
 */
namespace Shop\Home\Datas;
class BaiyangGoods extends BaseData
{
    protected static $instance=null;

    /**
     * 获取商品列表
     *
     * @param string $strGoodsId 商品id列
     * @param int $momTagId 妈妈标签id
     * @return array|bool
     */
    public function getGoodsList($strGoodsId, $momTagId)
    {
        $param = array(
            'table' => 'Shop\Models\BaiyangGoods AS g',
            'column' => 'g.id AS goods_id,g.virtual_stock,g.v_stock stock,g.is_use_stock ,g.goods_name,g.goods_image,
g.pricie_special,g.rate_of_praise,g.price market_price,g.comment_number,g.medicine_type,g.show_praise,gp.price',
            'join' => 'LEFT JOIN Shop\Models\BaiyangGoodsPrice gp ON g.id = gp.goods_id',
            'where' => "WHERE gp.tag_id=:tag_id: and g.id IN({$strGoodsId}) AND g.status=1",
            'bind' => array(
                'tag_id' => $momTagId
            )
        );
        $ret = $this->getData($param);
        return $ret;
    }

    /**
     * 获取商品库存类型
     * @param $goodsId
     * @return array|bool
     */
    public function getGoodsStockType($goodsId)
    {
        $result = $this->getData([
            'table'     =>      '\Shop\Models\BaiyangGoods',
            'column'    =>      'is_use_stock',
            'where'     =>      'where id = '.(int)$goodsId,
        ], true);
        return $result;
    }
}