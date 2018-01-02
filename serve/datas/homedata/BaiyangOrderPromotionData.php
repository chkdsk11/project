<?php
/**
 * @author 吴俊华
 */
namespace Shop\Home\Datas;
class BaiyangOrderPromotionData extends BaseData
{
    protected static $instance=null;

    /**
     * @desc 获取用户参加过的促销活动
     * @param array $param
     *       -int user_id 用户id
     * @param string $promotionIds 促销活动id
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getUserPromotions($param,$promotionIds)
    {
        $condition = [
            'table' => '\Shop\Models\BaiyangOrderPromotionDetail',
            'column' => 'promotion_id,count(1) as counts',
            'bind' => [
                'user_id' => $param['user_id'],
            ],
            'where' => "where user_id = :user_id: and is_delete = 0 and pid = 0 and promotion_id in($promotionIds) group by promotion_id"
        ];
        return $this->getData($condition);
    }
}