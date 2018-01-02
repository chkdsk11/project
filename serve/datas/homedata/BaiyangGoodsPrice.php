<?php
/**
 * 商品价格
 */
namespace Shop\Home\Datas;
class BaiyangGoodsPrice extends BaseData
{
    protected static $instance=null;

    /**
     * 获取商品标签价格列表
     *
     * @param int $userId 用户id
     * @param string $strGoodsId 商品id列
     * @param int $pregnant 1孕中/2孕后
     * @param int $momTagId 妈妈标签id
     * @return array|bool
     */
    public function getGoodsMomTagPriceList($userId, $strGoodsId, $pregnant, $momTagId=3)
    {
        $param = array(
            'table' => 'Shop\Models\BaiyangGoodsPrice AS gp',
            'column' => 'gp.tag_id, gp.goods_id,gp.limit_number,gp.price, gp.rebate, gp.type, gp.mutex,c.generation age_group, c.gifts_id gift_id, gpt.tag_name',
            'join' => 'LEFT JOIN Shop\Models\BaiyangUserGoodsPriceTag pt ON pt.tag_id = gp.tag_id 
                    LEFT JOIN Shop\Models\BaiyangGoodsPriceTag gpt ON gpt.tag_id = gp.tag_id
                    LEFT JOIN Shop\Models\BaiyangMomActivityGoods ag ON ag.tag_goods_id = gp.tag_goods_id 
                    LEFT JOIN Shop\Models\BaiyangMomGiftActivity c ON c.gifts_id = ag.gifts_id',
            'where' => "WHERE pt.user_id=:user_id: AND gpt.status=1 AND pt.status=1 AND gp.goods_id IN({$strGoodsId}) 
            AND c.attribute=1 AND c.pregnant=:pregnant: AND gp.tag_id=:tag_id:",
            'order' => 'ORDER BY gp.goods_id ASC,gp.price ASC',
            'bind' => array(
                'user_id' => $userId,
                'pregnant' => $pregnant,
                'tag_id' => $momTagId
            )
        );
        $ret = $this->getData($param);
        return $ret;
    }

    /**
     * @param int $userId 用户id
     * @param str $strGoodsId 商品id列
     * @param int $momTagId 妈妈标签id
     * @param null $price 商品价格
     * @return array|bool
     */
    public function getGoodsPriceList($userId, $strGoodsId, $momTagId=1, $price=null)
    {
        $param = array(
            'table' => 'Shop\Models\BaiyangGoodsPrice AS bgp',
            'column' => 'bgp.goods_id,bgp.price, bgpt.tag_name, bgp.limit_number qty, bgp.tag_id, 0 gift_id',
            'join' => 'INNER JOIN Shop\Models\BaiyangGoodsPriceTag AS bgpt ON bgp.tag_id = bgpt.tag_id AND bgpt.status=1 
                    INNER JOIN Shop\Models\BaiyangUserGoodsPriceTag AS bugpt ON bgp.tag_id = bugpt.tag_id',
            'where' => "WHERE bugpt.user_id=:user_id: AND bgp.goods_id IN({$strGoodsId}) AND bgp.tag_id<>:tag_id: ",
            'order' => 'ORDER BY bgp.price ASC',
            'bind' => array(
                'user_id' => $userId,
                'tag_id' => $momTagId
            )
        );
        if (!is_null($price)) {
            $param['where'] .= ' AND bgp.price<:price:';
            $param['bind']['price'] = $price;
        }
        $ret = $this->getData($param);
        return $ret;
    }

    /**
     * 获取用户标签价格列表
     *
     * @param int $userId 用户id
     * @param string $sGoodsId 商品id列
     * @param bool $hadBuy 是否已经购买
     * @param bool $isShowPrice 显示价格
     * @return array
     */
    public function getUserTagPriceList($userId, $sGoodsId, $hadBuy=false, $isShowPrice=false)
    {
        if (!$userId || empty($sGoodsId)) {
            return array();
        }
        $momApplyState = BaiyangMomApplyData::getInstance()->getMomApplyState($userId);
        $data = array();
        if ($momApplyState && $momApplyState['state'] == 1) {
            if (time() > (strtotime('+1 day', $momApplyState['birth_time']) - 1)) {
                $pregnant = 2;
            } else {
                $pregnant = 1;
            }
            $momTagId = $this->config['mom']['tag_id'];
            if (!$momTagId) {
                $momTagId = 1;
            }
            $momGoodsPriceList = $this->getGoodsMomTagPriceList($userId, $sGoodsId, $pregnant, $momTagId);
            if ($momGoodsPriceList) {
                $momGetGiftGoodsList = BaiyangMomGetGiftData::getInstance()->getMomGetGiftGoodsList($userId, $sGoodsId);
                $momHadGetGiftGoodsList = array();
                if ($momGetGiftGoodsList) {
                    foreach ($momGetGiftGoodsList as $goods) {
                        $momHadGetGiftGoodsList[$goods['gift_id']][$goods['goods_id']][$goods['position']] = $goods;
                    }
                }
                $babyBirthTime = BaiyangMomApplyData::getInstance()->getCorrectBabyBirthTime($momApplyState['birth_time']);
                $nowTime = time();
                foreach ($momGoodsPriceList as $goodsPrice) {
                    $ageGroup = explode('-', $goodsPrice['age_group']);
                    list($startTime, $endTime) = BaiyangMomGiftActivityData::getInstance()->getGiftStartEndTime($ageGroup, $babyBirthTime, $pregnant);
                    if ($nowTime <= $startTime) {
                        $getState = 5;
                    } else {
                        if (isset($momHadGetGiftGoodsList[$goodsPrice['gift_id']][$goodsPrice['goods_id']])) {
                            if (isset($momHadGetGiftGoodsList[$goodsPrice['gift_id']][$goodsPrice['goods_id']][1])) {
                                $getState = 3;
                            } else {
                                $getState = 6;
                            }
                        } elseif ($nowTime > $endTime) {
                            $getState = 4;
                        } else {
                            $getState = 1;
                        }
                    }
                    unset($goodsPrice['age_group']);
                    if ($hadBuy) {
                        if ($getState == 1) {
                            $data[$goodsPrice['goods_id']] = $goodsPrice;
                        }
                    } else {
                        $allowGetState = array('1' => 1, '3' => 3);
                        if ($isShowPrice) {
                            $allowGetState[6] = 6;
                        }
                        if (isset($allowGetState[$getState])) {
                            $data[$goodsPrice['goods_id']] = $goodsPrice;
                        }
                    }
                }
            }
        }
        return $data;
    }

    /**
     * 获取商品标签价格
     *
     * @param int $userId 用户id
     * @param $sGoodsId 商品id列
     * @param null $price 价格
     * @return array
     */
    public function getGoodsTagPriceList($userId, $sGoodsId, $price=null)
    {
        $momTagId = $this->config['mom']['tag_id'];
        if (!$momTagId) {
            $momTagId = 1;
        }
        $goodsTagPriceList = $this->getGoodsPriceList($userId, $sGoodsId, $momTagId, $price);
        $goodsTagPriceList = $this->relationArray($goodsTagPriceList, 'goods_id');
        $userTagPriceList = $this->getUserTagPriceList($userId, $sGoodsId);
        $data = array();
        $goodsIdList = explode(',', $sGoodsId);
        foreach ($goodsIdList as $goodsId) {
            if (isset($userTagPriceList[$goodsId])) {
                $state = true;
                if (isset($goodsTagPriceList[$goodsId])) {
                    if (bccomp($goodsTagPriceList[$goodsId]['price'], $userTagPriceList[$goodsId]['price'], 2) <= 0) {
                        $state = false;
                    }
                }
                if ($state) {
                    if (!is_null($price) && $userTagPriceList[$goodsId]['price'] >= $price) {
                        $data[$goodsId] = array();
                    } else {
                        $data[$goodsId] = $userTagPriceList[$goodsId];
                    }
                }
            }
        }
        return $data;
    }
}