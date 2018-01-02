<?php
/**
 * 领取礼包相关
 *
 * Created by PhpStorm.
 * User: Sary
 * Date: 2016/12/28
 * Time: 15:17
 */

namespace Shop\Home\Datas;

class BaiyangMomGetGiftData extends BaseData
{

    protected static $instance = null;

    /**
     * 获取mom已领取礼包商品的记录
     *
     * @param int $userId 用户唯一id
     * @param int $giftId 礼包id
     * @param int $goodsId 商品id
     * @return array|bool
     */
    public function getMomHadGetGiftGoods($userId, $giftId, $goodsId)
    {
        $ret = $this->countData(array(
            'table' => 'Shop\Models\BaiyangMomGetGift',
            'where' => 'WHERE user_id=:user_id: AND gifts_id=:gifts_id: AND goods_id=:goods_id:',
            'bind' => array(
                'user_id' => $userId,
                'gifts_id' => $giftId,
                'goods_id' => $goodsId
            )
        ));
        return $ret;
    }

    /**
     * 添加妈妈领取礼包记录
     *
     * @param $param
     * @return bool|string
     */
    public function addMomGetGift($param)
    {
        $data = array(
            'table' => 'Shop\Models\BaiyangMomGetGift',
            'bind' => array(
                'user_id' => $param['user_id'],
                'gifts_id' => $param['gift_id'],
                'position' => $param['position'],
                'goods_id' => $param['goods_id'],
                'goods_number' => $param['goods_number'],
                'price' => $param['price'],
                'add_time' => time()
            )
        );
        $ret = $this->addData($data, true);
        return $ret;
    }

    /**
     * 获取用户每个礼包领取状态
     *
     * @param int $userId 用户id
     * @param string $relationArrayKey 按对应字段返回的关联数组
     * @return array|bool
     */
    public function getMomGetEachGiftStateList($userId, $relationArrayKey='')
    {
        $param = array(
            'table' => 'Shop\Models\BaiyangMomGetGift',
            'column' => 'gifts_id gift_id,ascription position',
            'where' => 'WHERE user_id=:user_id: GROUP BY gifts_id,ascription',
            'bind' => array(
                'user_id' => $userId
            )
        );
        $data = $this->getData($param);
        if ($relationArrayKey && $data) {
            return $this->relationArray($data, $relationArrayKey);
        }
        return $data;
    }

    /**
     * 按礼包id判断妈妈是否领取礼包
     *
     * @param int $userId 用户id
     * @param int $giftId 礼包id
     * @return array|bool
     */
    public function getMomHadGetSingleGift($userId, $giftId)
    {
        $param = array(
            'table' => 'Shop\Models\BaiyangMomGetGift',
            'column' => 'user_id,gifts_id gift_id,ascription position,goods_id,goods_pty goods_number,price',
            'where' => 'WHERE user_id=:user_id: AND gifts_id=:gift_id:',
            'bind' => array(
                'user_id' => $userId,
                'gift_id' => $giftId
            )
        );
        $ret = $this->getData($param);
        return $ret;
    }

    /**
     * 按礼包id获取妈妈已经购买的礼包商品列表
     *
     * @param int $userId 用户id
     * @param int $giftId 礼包id
     * @return array|bool
     */
    public function getMomHadGetGiftGoodsList($userId, $giftId)
    {
        $param = array(
            'table' => 'Shop\Models\BaiyangMomGetGift AS mgg',
            'join' => 'INNER JOIN Shop\Models\BaiyangGoods AS g ON mgg.goods_id=g.id
            LEFT JOIN Shop\Models\BaiyangMomTrialReport AS mtr ON mgg.user_id=mtr.user_id AND mgg.gifts_id = mtr.gifts_id',
            'column' => 'mgg.goods_id,g.goods_name,mtr.id report_id',
            'where' => 'WHERE mgg.user_id=:user_id: AND mgg.gifts_id=:gift_id: AND mgg.ascription=2',
            'bind' => array(
                'user_id' => $userId,
                'gift_id' => $giftId
            )
        );
        $ret = $this->getData($param);
        return $ret;
    }

    /**
     * 获取用户是否已经领取礼包并报告
     *
     * @param int $userId 用户id
     * @param int $giftId 礼包id
     * @return array|bool
     */
    public function getGiftHadGetAndReport($userId, $giftId)
    {
        $param = array(
            'table' => 'Shop\Models\BaiyangMomGetGift AS mtg',
            'join' => 'LEFT JOIN Shop\Models\BaiyangMomTrialReport AS mtr ON mtg.gifts_id=mtr.gifts_id AND mtg.user_id=mtr.user_id',
            'column' => 'mtg.gifts_id gift_id,mtr.id report_id',
            'where' => 'WHERE mtg.user_id=:user_id: AND mtg.gifts_id=:gift_id: AND ascription=2',
            'limit' => 'LIMIT 1',
            'bind' => array(
                'user_id' => $userId,
                'gift_id' => $giftId
            )
        );
        $ret = $this->getData($param, true);
        return $ret;
    }

    /**
     * 获取妈妈领取礼包商品的列表
     *
     * @param int $userId
     * @param string $sGiftId 礼包id列
     * @param string $sGoodsId 商品id列
     * @return array|bool
     */
    public function getMomGetGiftGoodsList($userId,$sGoodsId)
    {
        $param = array(
            'table' => 'Shop\Models\BaiyangMomGetGift',
            'column' => 'gifts_id gift_id,goods_id,ascription position',
            'where' => "WHERE user_id=:user_id: AND goods_id IN({$sGoodsId}) 
            GROUP BY gifts_id,goods_id,ascription",
            'bind' => array(
                'user_id' => $userId
            )
        );
        $data = $this->getData($param);
        return $data;
    }

    /**
     * 获取妈妈领取的礼包是否有未评价
     *
     * @param int $userId
     * @return bool
     */
    public function getMomGiftIsNotReport($userId)
    {
        $param = array(
            'table' => 'Shop\Models\BaiyangMomGetGift AS mtg',
            'join' => 'LEFT JOIN Shop\Models\BaiyangMomTrialReport AS mtr ON mtg.user_id=mtr.user_id AND mtg.gifts_id=mtr.gifts_id',
            'column' => 'mtg.gifts_id gift_id,mtr.id report_id',
            'where' => 'WHERE mtg.user_id=:user_id: AND ascription=2 GROUP BY mtg.gifts_id',
            'bind' => array(
                'user_id' => $userId,
            )
        );
        $data = $this->getData($param);
        $isNotReport = false;
        if ($data) {
            foreach ($data as $item) {
                if (!$item['report_id']) {
                    return true;
                }
            }
        }
        return $isNotReport;
    }

    /**
     * 判断妈妈是否购买了当前礼包
     *
     * @param int $userId 用户id
     * @param int $giftId 礼包id
     * @param int $goodsId 商品id
     * @param int $position （购物车还是订单  1为购物车，2为订单）
     * @param bool $zeroPrice 是否0元价格
     * @return array|bool
     */
    public function momHadBuyGiftGoods($userId, $giftId, $goodsId=0, $position=0,$zeroPrice=false)
    {
        $bind = array(
            'user_id' => $userId,
            'gift_id' => $giftId
        );
        $where = '';
        if ($goodsId) {
            $where .= ' AND goods_id=:goods_id: ';
            $bind['goods_id'] = $goodsId;
        }
        if ($position) {
            $where .= ' AND ascription=:position: ';
            $bind['position'] = $position;
        }
        if ($zeroPrice) {
            $where .= ' AND price<0 ';
        }
        $param = array(
            'table' => 'Shop\Models\BaiyangMomGetGift',
            'column' => 'id,gifts_id gift_id,goods_id,ascription position',
            'where' => "WHERE user_id=:user_id: AND gifts_id=:gift_id: {$where}",
            'bind' => $bind
        );
        $data = $this->getData($param, true);
        return $data;
    }

    /**
     * 获取mom取礼包商品数量的记录
     *
     * @param int $userId 用户唯一id
     * @param int $giftId 礼包id
     * @param int $goodsId 商品id
     * @return array|bool
     */
    public function getMomGetGiftGoods($userId, $giftId, $goodsId)
    {
        $ret = $this->countData(array(
            'table' => 'Shop\Models\BaiyangMomGetGift',
            'column' => 'goods_pty',
            'where' => 'WHERE user_id=:user_id: AND gifts_id=:gifts_id: AND goods_id=:goods_id:',
            'bind' => array(
                'user_id' => $userId,
                'gifts_id' => $giftId,
                'goods_id' => $goodsId
            )
        ), true);
        return $ret;
    }

    /**
     * 防止用户刷辣妈商品
     *
     * @param array $param  $param = array(
                                'user_id' => 98,
                                'goods_id' => 1,
                                'price' => 100,
                                'goods_number' => 1,
                                'mom_tag_price' => BaiyangGoodsPrice::getInstance()->getUserTagPriceList($param['user_id'],$param['goodsInfo']['goods_id']);
                            );
     */
    public function checkHadAddGiftGoods(array $param)
    {
        if (isset($param['mom_tag_price']['gift_id']) && ($param['mom_tag_price']['price'] < $param['price'])) {
            $giftGoodsHadBuy = $this->momHadBuyGiftGoods($param['user_id'], $param['mom_tag_price']['gift_id'], $param['goods_id']);
            if (empty($giftGoodsHadBuy)) {
                // 添加购买历史
                if ($param['goods_number'] > $param['mom_tag_price']['limit_number']) {
                    $number = $param['mom_tag_price']['limit_number'];
                } else {
                    $number = $param['goods_number'];
                }
                $this->addMomGetGift(array(
                    'user_id' => $param['user_id'],
                    'gift_id' => $param['mom_tag_price']['gift_id'],
                    'position' => 1,
                    'goods_number' => $number,
                    'price' => $param['mom_tag_price']['price'],
                    'goods_id' => $param['goods_id']
                ));
            }
        }
    }

}