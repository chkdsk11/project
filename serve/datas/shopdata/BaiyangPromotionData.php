<?php
/**
 * Created by PhpStorm.
 * User: 吴俊华
 * Date: 2016/9/1
 * Time: 14:44
 */

namespace Shop\Datas;

use Shop\Datas\BaseData;
use Shop\Models\BaiyangPromotion;
use Shop\Models\BaiyangPromotionRule;
use Shop\Models\BaiyangPromotionEnum;
use Shop\Models\CacheKey;

class BaiyangPromotionData extends BaseData
{
    protected static $instance = null;

    /**
     * @desc 各个平台检验促销活动在相同的时间里是否设置相同的使用范围【针对全场的使用范围】
     * @param string $selections 需要查询的字段
     * @param array $tables 检验的表名
     * @param array $conditions 检验条件值【键值对】
     * @param string $where 检验条件
     * @return bool true|false 结果信息
     * @author 吴俊华
     */
    public function checkPromotionTimeAllRange($selections,$tables,$conditions,$where)
    {
        $selections .= ',aa.promotion_start_time,aa.promotion_end_time';
        $phql = "SELECT {$selections} FROM {$tables['promotionTable']} WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if(count($result) > 0){
            $result = $result->toArray();
            return $result;
        }
        return false;
    }

    /**
     * @desc 各个平台检验促销活动在相同的时间里是否设置相同的使用范围【针对品类、品牌、单品的使用范围】
     * @param string $selections 需要查询的字段
     * @param array $tables 检验的表名
     * @param array $conditions 检验条件值【键值对】
     * @param string $where 检验条件
     * @return bool true|false 结果信息
     * @author 吴俊华
     */
    public function checkPromotionTimeRange($selections,$tables,$conditions,$where)
    {
        $selections .= ',aa.promotion_start_time,aa.promotion_end_time';
        $phql = "SELECT {$selections} FROM {$tables['promotionRuleTable']} LEFT JOIN {$tables['promotionTable']} ON bb.promotion_id = aa.promotion_id WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if(count($result) > 0){
            $result = $result->toArray();
            return $result;
        }
        return false;
    }

    /**
     * @desc 根据活动类型获取进行中、未开始的促销活动
     * @param string $promotionType 活动类型 (多个以逗号隔开)
     * @param bool $cache 是否更新缓存 (true:更新 false:不更新)
     * @return array $processingPromotion  进行中的促销活动|[]
     * @author 吴俊华
     */
    public function getPromotionsInfo($promotionType = '', $cache = false)
    {
        $promotionCondition = [
            'table' => '\Shop\Models\BaiyangPromotion as pro',
            'join' => 'left join \Shop\Models\BaiyangPromotionRule as rules on pro.promotion_id = rules.promotion_id',
            'column' => 'pro.promotion_id,pro.promotion_code,pro.promotion_number,pro.promotion_title,pro.promotion_copywriter,pro.promotion_type,pro.promotion_scope,pro.promotion_mutex,pro.promotion_start_time,pro.promotion_end_time,pro.promotion_for_users,pro.promotion_is_real_pay,rules.condition,rules.rule_value,rules.except_category_id,rules.except_brand_id,rules.except_good_id,rules.join_times,rules.is_superimposed,rules.offer_type,rules.limit_unit,rules.limit_number,pro.promotion_platform_pc,pro.promotion_platform_app,pro.promotion_platform_wap,pro.promotion_platform_wechat,rules.member_tag,pro.promotion_member_level,pro.promotion_status,pro.promotion_create_user_id,pro.promotion_create_username,pro.promotion_edit_user_id,pro.promotion_edit_username,pro.promotion_create_time,pro.promotion_update_time',
            'bind' => [
                'nowTime' => time(),
                'promotion_status' => BaiyangPromotionEnum::PROMOTION_CANCEL,
            ],
            'where' => 'where pro.promotion_status != :promotion_status: and pro.promotion_end_time > :nowTime:'
        ];
        //获取指定类型的促销活动
        if(!empty($promotionType)){
            $promotionCondition['where'] .= " and pro.promotion_type in({$promotionType})";
        }
        $processingPromotion = $this->getData($promotionCondition);
        if($cache && $processingPromotion){
            $redis = $this->cache;
            $redis->selectDb(5);
            $redis->setValue(CacheKey::EFFECTIVE_PROMOTION, $processingPromotion);
        }
        return $processingPromotion;
    }

}