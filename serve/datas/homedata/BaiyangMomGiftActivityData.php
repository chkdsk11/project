<?php
/**
 * 礼包活动
 *
 * Created by PhpStorm.
 * User: ZHQ
 * Date: 2016/12/27
 * Time: 16:39
 */
namespace Shop\Home\Datas;

class BaiyangMomGiftActivityData extends BaseData
{

    protected static $instance=null;

    /**
     * 获取礼包列表
     *
     * @return array|bool
     */
    public function getMomGiftList()
    {
        /*$param = array(
            'table' => 'Shop\Models\BaiyangMomGiftActivity',
            'column' => 'gifts_id gift_id,gifts_title title,gifts_message content,generation age_group,gifts_image gift_image,pregnant',
            'where' => 'WHERE attribute=1',
        );
        $ret = $this->getData($param);*/
        $stmt = $this->dbRead->prepare("select gifts_id gift_id,gifts_title title,gifts_message content,generation age_group,gifts_image gift_image,pregnant from baiyang_mom_gift_activity WHERE attribute=1 ORDER BY sort ASC, IF(pregnant = 1, LEFT(generation, LOCATE('-', generation) - 1) + 1, LEFT(generation, LOCATE('-', generation) - 1) + 12) ASC ");
        $stmt->execute();
        $ret = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $ret;
    }

    /**
     * 按怀孕情况获取礼包开始和结束时间
     *
     * @param array $ageGroup 年龄段
     * @param int $birthTime 出生时间
     * @param int $pregnant 怀孕情况（1孕中/2孕后）
     * @return array
     */
    public function getGiftStartEndTime(array $ageGroup, $birthTime, $pregnant) {
        $data = array();
        if ($pregnant == 1) {
            // 默认怀孕10个月
            $data[0] = strtotime('-' . (10 - $ageGroup[0]) . ' month', $birthTime);
            $data[1] = strtotime('-' . (10 - $ageGroup[1]) . ' month', $birthTime);
        } else {
            $data[0] = strtotime('+' . $ageGroup[0] . ' month', $birthTime);
            $data[1] = strtotime('+' . $ageGroup[1] . ' month', $birthTime);
        }
        return $data;
    }

    /**
     * 按礼包id获取礼包详情
     *
     * @param int $giftId
     * @return array|bool
     */
    public function getMomGiftByGiftId($giftId)
    {
        $param = array(
            'table' => 'Shop\Models\BaiyangMomGiftActivity',
            'column' => 'gifts_id gift_id,gifts_title title,gifts_message content,generation age_group,pregnant,gifts_image default_image,binding_gift,binding_coupon',
            'where' => 'WHERE gifts_id=:gift_id: AND attribute=1',
            'bind' => array(
                'gift_id' => $giftId
            )
        );
        $ret = $this->getData($param, true);
        return $ret;
    }

    /**
     * 按礼包id获取礼包标签
     *
     * @param int $giftId 礼包id
     * @param bool $isReturnArray 是否返回数组
     * @return array|bool
     */
    public function getGiftTagNameByGiftId($giftId, $isReturnArray=false)
    {
        $param = array(
            'table' => 'Shop\Models\BaiyangMomGiftActivity',
            'column' => 'tag_name',
            'where' => 'WHERE gifts_id=:gift_id: AND attribute=1',
            'bind' => array(
                'gift_id' => $giftId
            )
        );
        $ret = $this->getData($param, true);
        if ($isReturnArray && $ret) {
            return explode(';', $ret['tag_name']);
        }
        return $ret;
    }

    /**
     * 获取礼包标题列表，用于显示礼包
     *
     * @return array|bool
     */
    public function getAllGiftTitleList(){
        $param = array(
            'table' => 'Shop\Models\BaiyangMomGiftActivity',
            'column' => 'gifts_id gift_id,gifts_title title',
            'where' => 'WHERE attribute=1',
        );
        $ret = $this->getData($param);
        return $ret;
    }

    /**
     * 按礼包获取礼包id
     *
     * @param int $giftId 礼包id
     * @return array|bool\
     */
    public function getSingleGiftTagNameList($giftId)
    {
        $param = array(
            'table' => 'Shop\Models\BaiyangMomGiftActivity',
            'column' => 'tag_name',
            'where' => 'WHERE gifts_id=:gift_id:',
            'bind' => array('gift_id' => $giftId)
        );
        $ret = $this->getData($param, true);
        return $ret;
    }

    /**
     * 获取礼包关联商品ID列表
     *
     * @param $sGiftId
     * @return array|bool
     */
    public function getGiftRelationGoodsList($strGiftId)
    {
        $param = array(
            'table' => 'Shop\Models\BaiyangMomGiftActivity',
            'column' => 'gifts_id gift_id,relation_goods_id',
            'where' => "WHERE gifts_id IN({$strGiftId})",
        );
        $ret = $this->getData($param, true);
        return $ret;
    }
}