<?php
/**
 * Created by PhpStorm.
 * User: 吴俊华
 * Date: 2016/9/5
 * Time: 上午 11:12
 * 后台促销活动类
 */

namespace Shop\Services;

use Shop\Datas\BaiyangSkuData;
use Shop\Models\BaiyangPromotionEnum;
use Shop\Models\BaiyangLimitPromotionEnum;
use Shop\Datas\BaseData;
use Shop\Datas\BaiyangPromotionData;
use Shop\Models\CacheKey;

class PromotionService extends BaseService
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance = null;

    /**
     * @desc 获取促销活动列表信息
     * @param array $param 查询条件
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getPromotionList($param)
    {
        //查询的基本条件
        $table = "\\Shop\\Models\\BaiyangPromotion as a";
        $join='';
        $selections = 'a.promotion_id,a.promotion_code,a.promotion_number,a.promotion_title,a.promotion_type,a.promotion_platform_pc,a.promotion_platform_app,a.promotion_platform_wap,a.promotion_platform_wechat,a.promotion_scope,a.promotion_start_time,a.promotion_end_time,a.promotion_status';
        //限购和限时优惠
        if(isset($param['promotion_type']) && !empty($param['promotion_type'])){
            $conditions = [
                'promotion_type' => $param['promotion_type']
            ];
            $whereStr = 'a.promotion_type = :promotion_type:';
        }else{
            //其他促销活动
            $conditions = [
                'limit_buy' => BaiyangPromotionEnum::LIMIT_BUY,
                'limit_time' => BaiyangPromotionEnum::LIMIT_TIME
            ];
            $whereStr = 'a.promotion_type not in(:limit_buy:,:limit_time:)';
            if(!empty($param['param']['promotion_type'])){
                $whereStr = 'a.promotion_type = :promotion_type:';
                unset($conditions);
                $conditions['promotion_type'] = $param['param']['promotion_type'];
            }

        }

        //输入的查询条件
        if(!empty($param['param']['promotion_code'])){
            $whereStr .= ' AND a.promotion_code like :promotion_code:';
            $conditions['promotion_code'] = '%'.$param['param']['promotion_code'].'%';
        }
        if(!empty($param['param']['promotion_title'])){
            $whereStr .= ' AND a.promotion_title like :promotion_title:';
            $conditions['promotion_title'] = '%'.$param['param']['promotion_title'].'%';
        }
        if(!empty($param['param']['promotion_status'])){
            $whereStr .= ' AND a.promotion_status = :promotion_status:';
            $conditions['promotion_status'] = $param['param']['promotion_status'];
        }
        if(!empty($param['param']['promotion_platform']) && $param['param']['promotion_platform'] == BaiyangPromotionEnum::SITE_PC){
            $whereStr .= ' AND a.promotion_platform_pc = :promotion_platform_pc:';
            $conditions['promotion_platform_pc'] = 1;
        }
        if(!empty($param['param']['promotion_platform']) && $param['param']['promotion_platform'] == BaiyangPromotionEnum::SITE_APP){
            $whereStr .= ' AND a.promotion_platform_app = :promotion_platform_app:';
            $conditions['promotion_platform_app'] = 1;
        }
        if(!empty($param['param']['promotion_platform']) && $param['param']['promotion_platform'] == BaiyangPromotionEnum::SITE_WAP){
            $whereStr .= ' AND a.promotion_platform_wap = :promotion_platform_wap:';
            $conditions['promotion_platform_wap'] = 1;
        }
        if(!empty($param['param']['promotion_platform']) && $param['param']['promotion_platform'] == BaiyangPromotionEnum::SITE_WeChat){
            $whereStr .= ' AND a.promotion_platform_wechat = :promotion_platform_wechat:';
            $conditions['promotion_platform_wechat'] = 1;
        }
        if(!empty($param['param']['promotion_scope'])){
            $whereStr .= ' AND a.promotion_scope = :promotion_scope:';
            $conditions['promotion_scope'] = $param['param']['promotion_scope'];
        }

        if(!empty($param['param']['single_search'])){
            if(isset($param['param']['promotion_scope']) && !empty($param['param']['promotion_scope'])){
                unset($param['param']['promotion_scope']);
                $conditions['promotion_scope'] = BaiyangPromotionEnum::SINGLE_RANGE;
            }else{
                $whereStr .= ' AND a.promotion_scope = :promotion_scope:';
                $conditions['promotion_scope'] = BaiyangPromotionEnum::SINGLE_RANGE;
            }
            $whereStr .= ' AND (FIND_IN_SET(:c_id:,b.condition) OR c.goods_name LIKE :c_goods_name:)';
            $conditions['c_id'] = $param['param']['single_search'];
            $conditions['c_goods_name'] = $param['param']['single_search'].'%';
            $join='LEFT JOIN \Shop\Models\BaiyangPromotionRule as b ON a.promotion_id = b.promotion_id LEFT JOIN \Shop\Models\BaiyangGoods as c ON c.id IN(b.condition)';
        }
        //总记录数
        $counts = count(BaseData::getInstance()->select($selections,$table,$conditions,$whereStr,$join));
        if(empty($counts)){
            return ['res' => 'error','list' => '','voltValue' => $param['param']];
        }
        //分页
        $pages['page'] = isset($param['page']) ? $param['page'] : 1;//当前页
        $pages['counts'] = $counts;
        $pages['psize'] = isset($param['psize'])? $param['psize'] : 15;
        $pages['url'] = $param['url'];
        $pages['url_back'] = isset($param['url_back']) ? $param['url_back'] : '';
        $pages['home_page'] = isset($param['home_page']) ? $param['home_page'] : '';
        $pages['size'] = isset($param['size'])?$param['size']:5;
        $page = $this->page->pageDetail($pages);
        $whereStr .= ' ORDER BY a.promotion_create_time DESC LIMIT '.$page['record'].','.$page['psize'];
        $result = BaseData::getInstance()->select($selections,$table,$conditions,$whereStr,$join);
        if(empty($result)){
            return ['res' => 'error'];
        }
        //数据库的值转化为实际意义上的值
        $result = $this->toActualValue($result);
        return ['res'  => 'succcess', 'list' => $result, 'page' => $page['page'], 'voltValue' => $param['param']];
    }

    /**
     * @desc 数据库的值转化为实际意义上的值
     * @param array $result 促销活动数据信息
     * @return array $result 转化后的结果信息
     * @author 吴俊华
     */
    private function toActualValue($result)
    {
        foreach($result as $key => $val){
            $result[$key]['en_promotion_type'] = $val['promotion_type'];
            if(!isset($param['promotion_type'])){
                $result[$key]['promotion_type'] = BaiyangPromotionEnum::$OfferType[$val['promotion_type']];
            }
            if($val['promotion_type'] == BaiyangPromotionEnum::LIMIT_BUY){
                $result[$key]['promotion_scope'] = BaiyangPromotionEnum::$LimitBuyForScope[$val['promotion_scope']];
            }else{
                $result[$key]['promotion_scope'] = BaiyangPromotionEnum::$ForScope[$val['promotion_scope']];
            }
            $result[$key]['promotion_status'] = BaiyangPromotionEnum::$PromotionStatus[$val['promotion_status']];
            $result[$key]['promotion_start_time'] = date('Y-m-d H:i:s',$val['promotion_start_time']);
            $result[$key]['promotion_end_time'] = date('Y-m-d H:i:s',$val['promotion_end_time']);
            $result[$key]['promotion_platform_pc'] = ($val['promotion_platform_pc'] == 1) ? 'PC、' : '';
            $result[$key]['promotion_platform_app'] = ($val['promotion_platform_app'] == 1) ? 'APP、' : '';
            $result[$key]['promotion_platform_wap'] = ($val['promotion_platform_wap'] == 1) ? 'WAP、' : '';
            $result[$key]['promotion_platform_wechat'] = ($val['promotion_platform_wechat'] == 1) ? '微商城、' : '';
            //活动平台
            $result[$key]['promotion_platform'] = ($result[$key]['promotion_platform_pc'].$result[$key]['promotion_platform_app'].$result[$key]['promotion_platform_wap']. $result[$key]['promotion_platform_wechat']) ? rtrim($result[$key]['promotion_platform_pc']. $result[$key]['promotion_platform_app']. $result[$key]['promotion_platform_wap']. $result[$key]['promotion_platform_wechat'],'、') : $result[$key]['promotion_platform_pc'].$result[$key]['promotion_platform_app'].$result[$key]['promotion_platform_wap']. $result[$key]['promotion_platform_wechat'];
        }
        return $result;
    }


    /**
     * @desc 添加促销活动
     * @param array $param 促销活动数据信息
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function addPromotion($param)
    {
        $promotion = []; //促销活动信息
        $promotionRule = []; //促销活动规则信息
        $promotion['promotion_type'] = (int)$param['promotion_type'];
        $promotion['promotion_create_time'] = time();
        $promotion['promotion_start_time'] = strtotime($param['promotion_start_time']);
        $promotion['promotion_end_time'] = strtotime($param['promotion_end_time']);
        if($promotion['promotion_start_time'] > $promotion['promotion_end_time']){
            return $this->arrayData('活动开始时间不能大于结束时间！', '', '', 'error');
        }
        if($promotion['promotion_end_time'] < $promotion['promotion_create_time']){
            return $this->arrayData('活动结束时间不能小于当前时间！', '', '', 'error');
        }
        $promotion['promotion_scope'] = $param['promotion_scope'];
        $promotion['promotion_title'] = $param['promotion_title'];
        $promotion['promotion_content'] = $param['promotion_content'];
        $promotion['promotion_copywriter'] = isset($param['promotion_copywriter']) ? $param['promotion_copywriter'] : '';
        $promotion['promotion_for_users'] = isset($param['promotion_for_users']) ? (int)$param['promotion_for_users'] : 10;

        //获取促销活动的活动状态
        $promotion['promotion_status'] = $this->getPromotionStatus($promotion);
        $promotion['promotion_create_user_id']  = $this->session->get('user_id');
        $promotion['promotion_create_username'] = $this->session->get('username');
        //会员等级
        $promotion['promotion_member_level'] = isset($param['promotion_member_level']) ? (int)$param['promotion_member_level'] : 0;
        //互斥活动
        $promotion['promotion_mutex'] = isset($param['promotion_mutex']) ? implode(',',$param['promotion_mutex']) : '';
        //活动平台
        $promotion['promotion_platform_pc'] = isset($param['promotion_platform_pc']) ? (int)$param['promotion_platform_pc'] : 0;
        $promotion['promotion_platform_app'] = isset($param['promotion_platform_app']) ? (int)$param['promotion_platform_app'] : 0;
        $promotion['promotion_platform_wap'] = isset($param['promotion_platform_wap']) ? (int)$param['promotion_platform_wap'] : 0;
        $promotion['promotion_platform_wechat'] = isset($param['promotion_platform_wechat']) ? (int)$param['promotion_platform_wechat'] : 0;
        //是否使用实付
        $promotion['promotion_is_real_pay'] = isset($param['promotion_is_real_pay']) ? (int)$param['promotion_is_real_pay'] : 0;

        //获取处理好的条件和规则
        $condition = $this->getRuleCondition($promotion,$param);
        $conditionArr['condition'] = $condition;
        $conditionArr['except_category_id'] = $promotionRule['except_category_id'] = isset($param['except_category_id']) ? $param['except_category_id'] : '';
        $conditionArr['except_brand_id'] = $promotionRule['except_brand_id'] = isset($param['except_brand_id']) ? $param['except_brand_id'] : '';
        $conditionArr['except_good_id'] = $promotionRule['except_good_id'] = isset($param['except_good_id']) ? $param['except_good_id'] : '';
        // 限购活动的限制和提示语都跟其他的不一样(限购活动不能交叉)
        if($promotion['promotion_type'] == BaiyangPromotionEnum::LIMIT_BUY) {
            $res = $this->verifyLimitBuy($promotion,$conditionArr);
            if($res['error'] == 1){
                return $this->arrayData($res['tips'], '', '', 'error');
            }
        }else{
            //检测促销活动在相同的时间里是否设置相同的使用范围
            $platformResult = $this->checkPromotionTimeRange($promotion,$param);
            if($platformResult){
                return $this->arrayData($this->getTimeRangeMsg($promotion,$platformResult), '', '', 'error');
            }
        }
        $promotionRule['rule_value'] = $this->getPromotionRuleValue($promotion,$param);
        if($promotion['promotion_scope'] != BaiyangPromotionEnum::ALL_RANGE && $promotionRule['rule_value'] == ''){
            return $this->arrayData('适用范围内容不能为空！', '', '', 'error');
        }
        if($promotion['promotion_type'] == BaiyangPromotionEnum::LIMIT_BUY || $promotion['promotion_type'] == BaiyangPromotionEnum::LIMIT_TIME){
            //获取规则生成的活动编号(限购、限时优惠)
            $promotion['promotion_number'] = $this->generatePromotionNumber($promotion);
        }else{
            //获取规则生成的活动编号(满减、满折、满赠、包邮)
            $promotion['promotion_code'] = $this->generatePromotionCode();
        }
        // 开启事务
        $this->dbWrite->begin();
        $promotionId = BaseData::getInstance()->insert('\Shop\Models\BaiyangPromotion',$promotion,true);
        if(empty($promotionId)){
            $this->dbWrite->rollback();
            return $this->arrayData('添加失败！', '', '', 'error');
        }

        //促销规则
        $promotionRule['promotion_id'] = $promotionId;
        $promotionRule['is_superimposed'] = isset($param['is_superimposed']) ? (int)$param['is_superimposed'] : 0;
        $promotionRule['limit_number'] = isset($param['limit_number'])? (int)$param['limit_number'] : 0;
        $promotionRule['limit_unit'] = isset($param['limit_unit'])? (int)$param['limit_unit'] : 0;
        // 新会员的只能限购1次
        if($promotion['promotion_for_users'] == BaiyangPromotionEnum::HAVE_NOT_SHOPPING && $promotionRule['limit_unit'] == BaiyangPromotionEnum::UNIT_TIME && $promotionRule['limit_number'] > 1){
            return $this->arrayData('新会员只能限购1次！', '', '', 'error');
        }
        $promotionRule['offer_type'] = isset($param['offer_type'])? (int)$param['offer_type'] : 0;
        $promotionRule['member_tag'] = isset($param['member_tag'])? (int)$param['member_tag'] : 0;
        $promotionRule['join_times'] = isset($param['join_times'])? (int)$param['join_times'] : 0;
        // 限时优惠、限购的新会员，只能参加一次促销活动
        if(($promotion['promotion_type'] == BaiyangPromotionEnum::LIMIT_BUY || $promotion['promotion_type'] == BaiyangPromotionEnum::LIMIT_TIME) && $promotion['promotion_for_users'] == BaiyangPromotionEnum::HAVE_NOT_SHOPPING){
            $promotionRule['join_times'] = 1;
        }
        if ($promotionRule['except_category_id']) {
            $result = $this->verify_except_list($promotionRule['except_category_id'], '分类');
            if ($result) {
                return $result;
            }
        }
        if ($promotionRule['except_brand_id']) {
            $result = $this->verify_except_list($promotionRule['except_brand_id'], '品牌');
            if ($result) {
                return $result;
            }
        }
        if ($promotionRule['except_good_id']) {
            $result = $this->verify_except_list($promotionRule['except_good_id'], '商品');
            if ($result) {
                return $result;
            }
        }
        $promotionRule['condition'] = $condition;
        if(!empty($promotionRule['condition'])){
            $conditionArray = explode(',', $promotionRule['condition']);
            if(count($conditionArray) != count(array_unique($conditionArray))){
                $this->dbWrite->rollback();
                return $this->arrayData('添加失败！不能添加相同数据', '', '', 'error');
            }
        }
        $promotionRule['rule_value'] = $this->getPromotionRuleValue($promotion,$param);
        $ruleResult = BaseData::getInstance()->insert('\Shop\Models\BaiyangPromotionRule',$promotionRule,true);
        if(empty($ruleResult)){
            $this->dbWrite->rollback();
            return $this->arrayData('添加失败！', '', '', 'error');
        }

        //成功后加载的url
        $url = '/promotion/list';
        if($promotion['promotion_type'] == BaiyangPromotionEnum::LIMIT_BUY){
            $url = '/limitbuy/list';
        }
        if($promotion['promotion_type'] == BaiyangPromotionEnum::LIMIT_TIME){
            $url = '/limittime/list';
        }
        $this->dbWrite->commit();
        BaiyangPromotionData::getInstance()->getPromotionsInfo('', true); // 缓存活动
        if($promotion['promotion_type'] != BaiyangPromotionEnum::LIMIT_BUY){
            $esUrl = $this->config->es->pcUrl. 'pces/uPromotionById.do';
            $requestData = http_build_query(['proId' => $promotionId]);
            $responseResult = json_decode($this->curl->sendPost($esUrl,$requestData),true);
        }
        return $this->arrayData('添加成功！', $url);
    }

    /**
     * @desc 生成促销活动的活动编号(满减、满折、满赠、包邮)
     * @return string $code 活动编号【10位数字】
     * @author 吴俊华
     */
    private function generatePromotionCode()
    {
        $baseData = BaseData::getInstance();
        $table = '\Shop\Models\BaiyangPromotion';
        $conditions = [
            'limit_buy' => BaiyangPromotionEnum::LIMIT_BUY,
            'limit_time' => BaiyangPromotionEnum::LIMIT_TIME
        ];
        $whereStr = 'promotion_type not in(:limit_buy:,:limit_time:) ORDER BY promotion_id DESC LIMIT 1';
        $maxCode = $baseData->select('promotion_code',$table,$conditions,$whereStr);
        //生成活动编号的规则： 年月日.'xx'  【共10位数字】
        if(!empty($maxCode)){
            $date = substr($maxCode[0]['promotion_code'],0,8);
            if($date == date('Ymd')){
                $id = substr($maxCode[0]['promotion_code'],-2);
                $id += 1;
                $newId = sprintf("%02d",$id);
                $code = date('Ymd').$newId;
            }else{
                $id = '1';
                $newId = sprintf("%02d",$id);
                $code = date('Ymd').$newId;
            }
        }else{
            $id = '1';
            $newId = sprintf("%02d",$id);
            $code = date('Ymd').$newId;
        }
        $count = $baseData->count($table, ['promotion_code' => $code], 'promotion_code = :promotion_code:');
        if($count > 0){
            $code = $this->generatePromotionCode();
        }
        return $code;
    }

    /**
     * @desc 生成促销活动的活动编号(限购、限时优惠)
     * @param array $promotion 促销活动数据信息
     * @return string $code 活动编号【10位数字】
     * @author 吴俊华
     */
    private function generatePromotionNumber($promotion)
    {
        $baseData = BaseData::getInstance();
        $table = '\Shop\Models\BaiyangPromotion';
        $conditions['promotion_type'] = $promotion['promotion_type'];
        $whereStr = 'promotion_type = :promotion_type: ORDER BY promotion_id DESC LIMIT 1';
        $maxCode = $baseData->select('promotion_number', $table, $conditions, $whereStr);
        //生成活动编号的规则：活动编号+1自增
        if(!empty($maxCode)){
            $code = $maxCode[0]['promotion_number'] + 1;
        }else{
            $code = 1;
        }
        $count = $baseData->count($table, ['promotion_type' => $promotion['promotion_type'], 'promotion_number' => $code], 'promotion_type = :promotion_type: and promotion_number = :promotion_number:');
        if($count > 0){
            $code = $this->generatePromotionNumber($promotion);
        }
        return $code;
    }

    /**
     * @desc 获取促销活动的活动状态
     * @param array $promotion 促销活动数据信息
     * @return int $promotionStatus 活动状态
     * @author 吴俊华
     */
    private function getPromotionStatus($promotion)
    {
        $promotionStatus = BaiyangPromotionEnum::PROMOTION_NOT_START;
        $nowTime = isset($promotion['promotion_id'])? $promotion['promotion_update_time'] : $promotion['promotion_create_time'];
        if($promotion['promotion_start_time'] <= $nowTime && $nowTime <= $promotion['promotion_end_time']){
            $promotionStatus = BaiyangPromotionEnum::PROMOTION_PROCESSING;
        }
        if($promotion['promotion_end_time'] < $nowTime){
            $promotionStatus = BaiyangPromotionEnum::PROMOTION_HAVE_ENDED;
        }
        return $promotionStatus;
    }

    /**
     * @desc 检验促销活动在相同的时间里是否设置相同的使用范围
     * @param array $promotion 促销活动数据信息
     * @param array $param 促销商品的goods_id或brand_id或category_id
     * @return string $result 结果信息:true|空字符串
     * @author 吴俊华
     */
    public function checkPromotionTimeRange($promotion,$param)
    {
        $tables = []; //操作的表
        $selections = 'bb.condition'; //品类、品牌、单品使用范围
        $allSelections = 'aa.promotion_id'; //全场使用范围
        $tables['promotionTable'] = '\Shop\Models\BaiyangPromotion as aa';
        $tables['promotionRuleTable'] = '\Shop\Models\BaiyangPromotionRule as bb';
        $result = '';
        //公共条件
        $conditions = [
            'promotion_type' => $promotion['promotion_type'],
            'promotion_scope' => $promotion['promotion_scope'],
            'promotion_start_time' => $promotion['promotion_start_time'],
            'promotion_end_time' => $promotion['promotion_end_time'],
            'haveEnded' => BaiyangPromotionEnum::PROMOTION_HAVE_ENDED,
            'haveCanceled' => BaiyangPromotionEnum::PROMOTION_CANCEL
        ];
        $whereStr = 'aa.promotion_type = :promotion_type: AND aa.promotion_scope = :promotion_scope: AND aa.promotion_status NOT IN (:haveEnded:,:haveCanceled:)';
        if($promotion['promotion_for_users'] == BaiyangPromotionEnum::HAVE_NOT_SHOPPING || $promotion['promotion_for_users'] == BaiyangPromotionEnum::HAVE_SHOPPING){
            // 添加新用户后只能添加老用户，反之同理。(但添加所有人后，新老用户都不能再添加)
            $conditions = array_merge($conditions, ['all_people' => BaiyangPromotionEnum::ALL_PEOPLE,'promotion_for_users' => $promotion['promotion_for_users']]);
            $whereStr .= ' AND aa.promotion_for_users in(:all_people:,:promotion_for_users:)';
        }

        //编辑时排除自身
        if(isset($promotion['promotion_id']) && !empty($promotion['promotion_id'])){
            $whereStr .= ' AND aa.promotion_id != :promotion_id:';
            $conditions['promotion_id'] = $promotion['promotion_id'];
        }
        //全场使用范围
        $allWhere =  $whereStr.' AND ((:promotion_start_time: <= aa.promotion_start_time AND :promotion_end_time: >= aa.promotion_end_time) OR (:promotion_start_time: <= aa.promotion_start_time AND aa.promotion_start_time <= :promotion_end_time: AND :promotion_end_time: <= aa.promotion_end_time) OR (aa.promotion_start_time <= :promotion_start_time: AND :promotion_start_time: <= aa.promotion_end_time AND aa.promotion_end_time <= :promotion_end_time:) OR (:promotion_start_time: >= aa.promotion_start_time AND :promotion_end_time: <= aa.promotion_end_time))';

        $shopId = 0;
        //验证全场的使用范围
        if($promotion['promotion_scope'] == BaiyangPromotionEnum::ALL_RANGE){
            $result = $this->getPlatformVerify($shopId,$promotion,$allSelections,$tables,$conditions,$allWhere);
        }else{
            //验证品类、品牌、单品的使用范围
            if($promotion['promotion_scope'] == BaiyangPromotionEnum::CATEGORY_RANGE){
                $shopId = $param['shop_category'][2];
            }
            if($promotion['promotion_scope'] == BaiyangPromotionEnum::BRAND_RANGE){
                $shopId = explode(',',$param['shop_brand']);
            }
            if($promotion['promotion_scope'] == BaiyangPromotionEnum::SINGLE_RANGE){
                $shopId = explode(',',$param['shop_single']);
            }
            if($promotion['promotion_scope'] == BaiyangPromotionEnum::MORE_RANGE){
                $shopId = explode(',',$param['shop_more']);
            }
            $conditions['promotion_end_time'] = time();
            unset($conditions['promotion_start_time']);
            $where = $whereStr.' AND aa.promotion_end_time >= :promotion_end_time:';
            $result = $this->getPlatformVerify($shopId,$promotion,$selections,$tables,$conditions,$where);
        }
        return $result;
    }

    /**
     * @desc 获取【****不可以在相同的时间设置相同的使用范围】提示信息
     * @param array $promotion 促销活动数据信息
     * @param string|array $platformResult 平台验证提示信息
     * @return string $message 提示信息
     * @author 吴俊华
     */
    private function getTimeRangeMsg($promotion,$platformResult)
    {
        $rangeMsg = ''; //使用范围
        $typeMsg  = ''; //活动类型
        $sameMsg  = '不可以在相同的时间设置相同的使用范围！'; //【全场、品类】相同提示信息
        $sameMsg1  = '已存在其他同类活动，请重新选择！'; //【品牌、单品、多单品】相同提示信息
        $message  = ''; //最后拼接完的提示信息

        //适用范围提示
        switch ($promotion['promotion_scope']) {
            case BaiyangPromotionEnum::ALL_RANGE:
                $rangeMsg = BaiyangPromotionEnum::$ForScope[BaiyangPromotionEnum::ALL_RANGE];
                break;
            case BaiyangPromotionEnum::CATEGORY_RANGE:
                $rangeMsg = BaiyangPromotionEnum::$ForScope[BaiyangPromotionEnum::CATEGORY_RANGE];
                break;
            case BaiyangPromotionEnum::BRAND_RANGE:
                $rangeMsg = BaiyangPromotionEnum::$ForScope[BaiyangPromotionEnum::BRAND_RANGE];
                break;
            case BaiyangPromotionEnum::SINGLE_RANGE:
                $rangeMsg = BaiyangPromotionEnum::$ForScope[BaiyangPromotionEnum::SINGLE_RANGE];
                break;
            case BaiyangPromotionEnum::MORE_RANGE:
                $rangeMsg = BaiyangPromotionEnum::$LimitBuyForScope[BaiyangPromotionEnum::MORE_RANGE];
                break;
        }

        //活动类型提示
        switch ($promotion['promotion_type']) {
            case BaiyangPromotionEnum::FULL_MINUS:
                $typeMsg = BaiyangPromotionEnum::$OfferType[BaiyangPromotionEnum::FULL_MINUS];
                break;
            case BaiyangPromotionEnum::FULL_OFF:
                $typeMsg = BaiyangPromotionEnum::$OfferType[BaiyangPromotionEnum::FULL_OFF];
                break;
            case BaiyangPromotionEnum::FULL_GIFT:
                $typeMsg = BaiyangPromotionEnum::$OfferType[BaiyangPromotionEnum::FULL_GIFT];
                break;
            case BaiyangPromotionEnum::EXPRESS_FREE:
                $typeMsg = BaiyangPromotionEnum::$OfferType[BaiyangPromotionEnum::EXPRESS_FREE];
                break;
            case BaiyangPromotionEnum::INCREASE_BUY:
                $typeMsg = BaiyangPromotionEnum::$OfferType[BaiyangPromotionEnum::INCREASE_BUY];
                break;
            case BaiyangPromotionEnum::LIMIT_BUY:
                $typeMsg = BaiyangPromotionEnum::$promotionType[BaiyangPromotionEnum::LIMIT_BUY];
                break;
            case BaiyangPromotionEnum::LIMIT_TIME:
                $typeMsg = BaiyangPromotionEnum::$promotionType[BaiyangPromotionEnum::LIMIT_TIME];
                break;
        }

        //使用范围是全场或品类的提示
        if($promotion['promotion_scope'] == BaiyangPromotionEnum::ALL_RANGE || $promotion['promotion_scope'] == BaiyangPromotionEnum::CATEGORY_RANGE){
            ($platformResult && $rangeMsg && $typeMsg) && $message .= $platformResult.'的'.$rangeMsg.$typeMsg.$sameMsg;
        }
        //使用范围是品牌的提示
        if($promotion['promotion_scope'] == BaiyangPromotionEnum::BRAND_RANGE){
            ($platformResult && $rangeMsg && $typeMsg) && $message .= '品牌：'.$platformResult[0].' 在'.$platformResult['platform'].$sameMsg1;
        }
        //使用范围是单品的提示
        if($promotion['promotion_scope'] == BaiyangPromotionEnum::SINGLE_RANGE || $promotion['promotion_scope'] == BaiyangPromotionEnum::MORE_RANGE){
            ($platformResult && $rangeMsg && $typeMsg) && $message .= '商品：'.$platformResult[0].' 在'.$platformResult['platform'].$sameMsg1;
        }
        return $message;
    }

    /**
     * @desc 获取促销活动的使用条件
     * @param array $promotion 促销活动数据信息【主要是使用范围】
     * @param array $param 促销商品的good_id或brand_id或category_id
     * @return string $condition 使用条件
     * @author 吴俊华
     */
    private function getRuleCondition($promotion,$param)
    {
        $condition = ''; //使用条件
        switch ($promotion['promotion_scope']) {
            //分类的使用范围
            case BaiyangPromotionEnum::CATEGORY_RANGE:
                $condition = $param['shop_category'][2];
                break;
            //品牌的使用范围
            case BaiyangPromotionEnum::BRAND_RANGE:
                $condition = $param['shop_brand'];
                break;
            //单品的使用范围
            case BaiyangPromotionEnum::SINGLE_RANGE:
                $condition = $param['shop_single'];
                break;
            //多单品的使用范围
            case BaiyangLimitPromotionEnum::MORE_RANGE:
                $condition = $param['shop_more'];
                break;
        }
        return $condition;
    }

    /**
     * @desc 获取促销活动的使用规则【处理后的】
     * @param array $promotion 促销活动数据信息【主要是优惠类型】
     * @param array $param 规则的full_price、reduce_price或discount_rate等
     * @return string $ruleValue 规则内容
     * @author 吴俊华
     */
    private function getPromotionRuleValue($promotion,$param)
    {
        $promotionRule = []; //促销活动规则
        //满赠规则
        if($promotion['promotion_type'] == BaiyangPromotionEnum::FULL_GIFT){
            if(isset($param['premiums_group'])){
                $promotionRule = json_decode(str_replace("&quot;","\"",$param['premiums_group']),true);
            }

        }elseif($promotion['promotion_type'] == BaiyangPromotionEnum::LIMIT_BUY){
            //限购规则
            if($promotion['promotion_type'] == BaiyangPromotionEnum::LIMIT_BUY){
                //品类的使用范围
                if($promotion['promotion_scope'] == BaiyangPromotionEnum::CATEGORY_RANGE){
                    $cat_id = $param['shop_category']['2'];
                    $limit_number = $param['limit_number'];
                    $promotionRule = [[
                        'id' => $cat_id,
                        'promotion_num' => $limit_number,
                    ]];
                }
                //品牌的使用范围
                if($promotion['promotion_scope'] == BaiyangPromotionEnum::BRAND_RANGE && isset($param['shop_brand_json'])){
                    $promotionRule = json_decode(str_replace("&quot;","\"",$param['shop_brand_json']),true);
                }
                //单品的使用范围
                if($promotion['promotion_scope'] == BaiyangPromotionEnum::SINGLE_RANGE && isset($param['shop_single_json'])){
                    $promotionRule = json_decode(str_replace("&quot;","\"",$param['shop_single_json']),true);
                }
                //多单品使用范围
                if($promotion['promotion_scope'] == BaiyangPromotionEnum::MORE_RANGE && isset($param['shop_more_json'])){
                    $promotionRule = json_decode(str_replace("&quot;","\"",$param['shop_more_json']),true);
                }
            }

        }elseif($promotion['promotion_type'] == BaiyangPromotionEnum::LIMIT_TIME){
            //限时优惠规则
            if($promotion['promotion_type'] == BaiyangPromotionEnum::LIMIT_TIME){
                if(isset($param['offer_goods'])){
                    $promotionRule = json_decode(str_replace("&quot;","\"",$param['offer_goods']),true);
                }
            }

        }else{
            //满减、满折、包邮、加价购规则
            if(isset($param['premiums_group']) && $promotion['promotion_type'] == BaiyangPromotionEnum::INCREASE_BUY){
                $promotionRule = json_decode(str_replace("&quot;","\"",$param['premiums_group']),true);
                $promotionRuleTmp = $promotionRule;
                $promotionRule = [];
                foreach ($promotionRuleTmp as $k => $rule){
                    $promotionRule[$k]['full_price'] = $rule['full_price'];
                    $promotionRule[$k]['unit'] = $rule['unit'];
                    foreach ($rule['premiums_group'] as $p_k => $premiums_group){
                        $promotionRule[$k]['reduce_group'][$p_k]['product_id'] = $premiums_group['premiums_id'];
                        $promotionRule[$k]['reduce_group'][$p_k]['reduce_price'] = $premiums_group['premiums_number'];
                    }
                }
            }
            for($i = 0;$i < count($param['full_price']); $i++){
                $promotionRule[$i]['full_price'] = $param['full_price'][$i];
                $promotionRule[$i]['unit'] = $param['unit'][$i];
                if($promotion['promotion_type'] == BaiyangPromotionEnum::FULL_MINUS){
                    $promotionRule[$i]['reduce_price'] =  $param['reduce_price'][$i];
                }
                if($promotion['promotion_type'] == BaiyangPromotionEnum::FULL_OFF){
                    $promotionRule[$i]['discount_rate'] =  $param['discount_rate'][$i];
                }
            }
        }
        $ruleValue = empty($promotionRule) ? '' : json_encode($promotionRule);
        return $ruleValue;
    }

    /**
     * @desc 对各个平台进行拼接组装验证条件
     * @param string|array $shopId 促销活动的goodId或brandId或categoryId
     * @param string $promotion 促销活动的数据信息【主要是各个平台信息】
     * @param string $selections 查询的字段【主要是规则表的condition】
     * @param array $tables 查询的表
     * @param array $conditions 查询的键值对
     * @param string|array $where 查询的条件
     * @return string $platformInfo 验证后的结果信息|空字符串
     * @author 吴俊华
     */
    private function getPlatformVerify($shopId,$promotion,$selections,$tables,$conditions,$where)
    {
        //平台提示
        $platformTips = [
            'pc'     => 'PC',
            'app'    => 'APP',
            'wap'    => 'WAP',
            'wechat' => '微商城',
            'symbol' => '或',  //间隔符号 [eg:PC或WAP]
            'word'   => '的'   //连接字 [eg:PC或APP的]
        ];
        $newPlatformTips = '';
        if($promotion['promotion_platform_pc'] == 1 || $promotion['promotion_platform_app'] == 1 || $promotion['promotion_platform_wap'] == 1 || $promotion['promotion_platform_wechat'] == 1){
            if($promotion['promotion_platform_pc'] == 1) {
                $conditions['promotion_platform_pc'] = $promotion['promotion_platform_pc'];
                $newPlatformTips .= $platformTips['pc'].$platformTips['symbol'];
                $where .= ' AND (aa.promotion_platform_pc = :promotion_platform_pc:';
            }
            if($promotion['promotion_platform_app'] == 1){
                $conditions['promotion_platform_app'] = $promotion['promotion_platform_app'];
                $joiner = !empty($newPlatformTips) ? ' OR ' : ' AND (';
                $newPlatformTips .= $platformTips['app'].$platformTips['symbol'];
                $where .= $joiner.'aa.promotion_platform_app = :promotion_platform_app:';
            }
            if($promotion['promotion_platform_wap'] == 1){
                $conditions['promotion_platform_wap'] = $promotion['promotion_platform_wap'];
                $joiner = !empty($newPlatformTips) ? ' OR ' : ' AND (';
                $newPlatformTips .= $platformTips['wap'].$platformTips['symbol'];
                $where .= $joiner.'aa.promotion_platform_wap = :promotion_platform_wap:';
            }
            if($promotion['promotion_platform_wechat'] == 1){
                $conditions['promotion_platform_wechat'] = $promotion['promotion_platform_wechat'];
                $joiner = !empty($newPlatformTips) ? ' OR ' : ' AND (';
                $newPlatformTips .= $platformTips['wechat'].$platformTips['symbol'];
                $where .= $joiner.'aa.promotion_platform_wechat = :promotion_platform_wechat:';
            }
            $where .= ')';
            $newPlatformTips = rtrim($newPlatformTips,'或');

            //全场、品类的使用范围
            if($promotion['promotion_scope'] == BaiyangPromotionEnum::ALL_RANGE ||
                $promotion['promotion_scope'] == BaiyangPromotionEnum::CATEGORY_RANGE){
                $platformInfo = $this->verifyPromotionPlatform($selections,$tables,$conditions,$where,$newPlatformTips,$promotion,$shopId);
                return $platformInfo;
            }else{
                //品牌、单品、多单品的使用范围
                foreach($shopId as $key => $val){
                    $platformInfo = $this->verifyPromotionPlatform($selections, $tables, $conditions, $where,$newPlatformTips,$promotion,$shopId[$key]);
                    if(!empty($platformInfo['platform'])){
                        return $platformInfo;
                    }
                }
                return '';
            }
        }
        return '';
    }

    /**
     * @desc 验证各个平台的使用范围
     * @param string $selections 查询的字段【主要是规则表的condition】
     * @param array $tables 查询的表
     * @param array $conditions 查询的键值对
     * @param array $where 查询的条件
     * @param array $platformTips 平台提示
     * @param array $promotion 促销活动信息
     * @param string $shopId 促销活动的goodId或brandId或categoryId
     * @return string $platform PC|APP|WAP PC|APP|WAP平台或空的字符串
     * @author 吴俊华
     */
    private function verifyPromotionPlatform($selections,$tables,$conditions,$where,$platformTips,$promotion,$shopId = '0')
    {
        //全场使用范围
        if($promotion['promotion_scope'] == BaiyangPromotionEnum::ALL_RANGE){
            $promotionList = BaiyangPromotionData::getInstance()->checkPromotionTimeAllRange($selections,$tables,$conditions,$where);
            if($promotionList){
                return $platformTips;
            }
            return '';
        }else{
            //品类、品牌、单品、多单品的使用范围
            $promotionList = BaiyangPromotionData::getInstance()->checkPromotionTimeRange($selections,$tables,$conditions,$where);
        }
        if(!empty($promotionList)){
            $condition = explode(',', $shopId);
            foreach($promotionList as $value){
                $result = array_intersect($condition, explode(',',$value['condition']));
                if($result){
                    // 时间上的交叉(4种情况)
                    if(($promotion['promotion_start_time'] <= $value['promotion_start_time'] && $promotion['promotion_end_time'] >= $value['promotion_end_time']) || ($promotion['promotion_start_time'] <= $value['promotion_start_time'] && $value['promotion_start_time'] <= $promotion['promotion_end_time']) || ($value['promotion_start_time'] <= $promotion['promotion_start_time'] && $promotion['promotion_start_time'] <= $value['promotion_end_time'] && $value['promotion_end_time'] <= $promotion['promotion_end_time']) || ($promotion['promotion_start_time'] >= $value['promotion_start_time'] && $promotion['promotion_end_time'] <= $value['promotion_end_time'])){
                        switch ($promotion['promotion_scope']) {
                            //分类的使用范围
                            case BaiyangPromotionEnum::CATEGORY_RANGE:
                                return $platformTips;
                                break;
                            //品牌的使用范围
                            case BaiyangPromotionEnum::BRAND_RANGE:
                                $result['platform'] = $platformTips;
                                return $result;
                                break;
                            //单品的使用范围
                            case BaiyangPromotionEnum::SINGLE_RANGE:
                                $goodName = BaseData::getInstance()->select('goods_name','\Shop\Models\BaiyangGoods',['id' => $result[0]],'id = :id:');
                                $result[0] =  $goodName[0]['goods_name'];
                                $result['platform'] = $platformTips;
                                return $result;
                                break;
                            //多单品的使用范围
                            case BaiyangPromotionEnum::MORE_RANGE:
                                $goodName = BaseData::getInstance()->select('goods_name','\Shop\Models\BaiyangGoods',['id' => $result[0]],'id = :id:');
                                $result[0] =  $goodName[0]['goods_name'];
                                $result['platform'] = $platformTips;
                                return $result;
                                break;
                        }
                    }

                }
            }
        }
        return '';
    }

    /**
     * @desc 获取促销活动的枚举项
     * @return array $promotionEnum 枚举信息
     * @author 吴俊华
     */
    public function getPromotionEnum()
    {
        $promotionEnum = []; //促销活动枚举项
        //优惠类型
        $promotionEnum['offerType'] = BaiyangPromotionEnum::$OfferType;
        //公共互斥活动
        $promotionEnum['mutexPromotion'] = BaiyangPromotionEnum::$MutexPromotion;
        //适用人群
        $promotionEnum['forPeople'] = BaiyangPromotionEnum::$ForPeople;
        //适用范围
        $promotionEnum['forScope'] = BaiyangPromotionEnum::$ForScope;
        //限购的适用范围
        $promotionEnum['limitBuyForScope'] = BaiyangPromotionEnum::$LimitBuyForScope;
        //适用平台
        $promotionEnum['forPlatform'] = BaiyangPromotionEnum::$ForPlatform;
        $configPlatform = (array)$this->config['shop_platform'];
        $configPlatform = $configPlatform ? array_values($configPlatform): ['WAP'];
        foreach ($promotionEnum['forPlatform'] as $k => $platform) {
            if (!in_array($platform, $configPlatform)) {
                unset($promotionEnum['forPlatform'][$k]);
            }
        }
        //活动状态
        $promotionEnum['promotionStatus'] = BaiyangPromotionEnum::$PromotionStatus;

        $promotionEnum['drugType']=BaiyangPromotionEnum::$CouponRx;
        //会员等级
        $promotionEnum['memberLevel'] = BaiyangPromotionEnum::$MemberLevel;
        //限购单位
        $promotionEnum['limitBuyUnit'] = BaiyangPromotionEnum::$LimitBuyUnit;
        //优惠类型
        $promotionEnum['limitTimeType'] = BaiyangPromotionEnum::$LimitTimeType;
        //促销活动的互斥活动
        $promotionEnum['mutexAlone'] = BaiyangPromotionEnum::$MutexAlone;
        //优惠券渠道
        $promotionEnum['channel'] =BaiyangPromotionEnum::$channelList;
        return $promotionEnum;
    }

    /**
     * @desc 根据id获取促销活动详情信息
     * @param int $promotionId 促销活动id
     * @return array|bool $promotionDetail[0]|false 详情信息
     * @author 吴俊华
     */
    public function getPromotionListById($promotionId)
    {
        $table = '\Shop\Models\BaiyangPromotion as aa LEFT JOIN \Shop\Models\BaiyangPromotionRule as bb on aa.promotion_id = bb.promotion_id';
        $selections = 'aa.promotion_id,aa.promotion_code,aa.promotion_title,aa.promotion_content,aa.promotion_type,aa.promotion_platform_pc,aa.promotion_platform_app,aa.promotion_platform_wap,aa.promotion_platform_wechat,aa.promotion_copywriter,aa.promotion_member_level,aa.promotion_is_real_pay,aa.promotion_mutex,aa.promotion_for_users,aa.promotion_scope,aa.promotion_start_time,aa.promotion_end_time,bb.condition,bb.except_category_id,bb.except_brand_id,bb.except_good_id,bb.rule_value,bb.is_superimposed,bb.offer_type,bb.limit_unit,bb.limit_number,bb.member_tag,bb.join_times';
        $conditions = [
            'promotion_id' => $promotionId,
        ];
        $whereStr = 'aa.promotion_id = :promotion_id:';
        $promotionDetail = BaseData::getInstance()->select($selections,$table,$conditions,$whereStr);
        if(!empty($promotionDetail)){
            if(isset($promotionDetail[0]['join_times']) && $promotionDetail[0]['join_times'] == 0 ){
                $promotionDetail[0]['join_times'] = '';
            }
            return $promotionDetail[0];
        }
        return false;
    }

    /**
     * @desc 编辑促销活动
     * @param array $param 促销活动数据信息
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function editPromotion($param)
    {
        $promotion = []; //促销活动信息
        $promotionRule = []; //促销活动规则信息
        $promotion['promotion_id'] = (int)$param['promotion_id'];
        $promotion['promotion_type'] = (int)$param['promotion_type'];
        $promotion['promotion_update_time'] = time();
        $promotion['promotion_start_time'] = strtotime($param['promotion_start_time']);
        $promotion['promotion_end_time'] = strtotime($param['promotion_end_time']);
        if($promotion['promotion_start_time'] > $promotion['promotion_end_time']){
            return $this->arrayData('活动开始时间不能大于结束时间！', '', '', 'error');
        }
        $promotion['promotion_scope'] = $param['promotion_scope'];
        $promotion['promotion_title'] = $param['promotion_title'];
        $promotion['promotion_content'] = $param['promotion_content'];
        $promotion['promotion_copywriter'] = isset($param['promotion_copywriter']) ? $param['promotion_copywriter'] : '';
        $promotion['promotion_for_users'] = isset($param['promotion_for_users']) ? (int)$param['promotion_for_users'] : 10;
        //会员等级
        $promotion['promotion_member_level'] = isset($param['promotion_member_level']) ? (int)$param['promotion_member_level'] : 0;

        //获取促销活动的活动状态
        $promotion['promotion_status'] = $this->getPromotionStatus($promotion);
        $promotion['promotion_edit_user_id']  = $this->session->get('user_id');
        $promotion['promotion_edit_username'] = $this->session->get('username');
        //互斥活动
        $promotion['promotion_mutex'] = isset($param['promotion_mutex']) ? implode(',',$param['promotion_mutex']) : '';
        //活动平台
        $promotion['promotion_platform_pc'] = isset($param['promotion_platform_pc']) ? (int)$param['promotion_platform_pc'] : 0;
        $promotion['promotion_platform_app'] = isset($param['promotion_platform_app']) ? (int)$param['promotion_platform_app'] : 0;
        $promotion['promotion_platform_wap'] = isset($param['promotion_platform_wap']) ? (int)$param['promotion_platform_wap'] : 0;
        $promotion['promotion_platform_wechat'] = isset($param['promotion_platform_wechat']) ? (int)$param['promotion_platform_wechat'] : 0;;
        //是否使用实付
        $promotion['promotion_is_real_pay'] = isset($param['promotion_is_real_pay']) ? (int)$param['promotion_is_real_pay'] : 0;

        //获取处理好的条件和规则
        $condition = $this->getRuleCondition($promotion,$param);
        $conditionArr['condition'] = $condition;
        $conditionArr['except_category_id'] = $promotionRule['except_category_id'] = isset($param['except_category_id']) ? $param['except_category_id'] : '';
        $conditionArr['except_brand_id'] = $promotionRule['except_brand_id'] = isset($param['except_brand_id']) ? $param['except_brand_id'] : '';
        $conditionArr['except_good_id'] = $promotionRule['except_good_id'] = isset($param['except_good_id']) ? $param['except_good_id'] : '';
        // 限购活动的限制和提示语都跟其他的不一样(限购活动不能交叉)
        if($promotion['promotion_type'] == BaiyangPromotionEnum::LIMIT_BUY) {
            $res = $this->verifyLimitBuy($promotion,$conditionArr);
            if($res['error'] == 1){
                return $this->arrayData($res['tips'], '', '', 'error');
            }
        }else{
            //检测促销活动在相同的时间里是否设置相同的使用范围
            $platformResult = $this->checkPromotionTimeRange($promotion,$param);
            if($platformResult){
                return $this->arrayData($this->getTimeRangeMsg($promotion,$platformResult), '', '', 'error');
            }
        }

        //促销活动规则
        $promotionRule['promotion_id'] = $promotion['promotion_id'];
        $promotionRule['is_superimposed'] = isset($param['is_superimposed']) ? (int)$param['is_superimposed'] : 0;
        $promotionRule['limit_number'] = isset($param['limit_number'])? (int)$param['limit_number'] : 0;
        $promotionRule['limit_unit'] = isset($param['limit_unit'])? (int)$param['limit_unit'] : 0;
        // 新会员的只能限购1次
        if($promotion['promotion_for_users'] == BaiyangPromotionEnum::HAVE_NOT_SHOPPING && $promotionRule['limit_unit'] == BaiyangPromotionEnum::UNIT_TIME && $promotionRule['limit_number'] > 1){
            return $this->arrayData('新会员只能限购1次！', '', '', 'error');
        }
        $promotionRule['offer_type'] = isset($param['offer_type'])? (int)$param['offer_type'] : 0;
        $promotionRule['member_tag'] = isset($param['member_tag'])? (int)$param['member_tag'] : 0;
        $promotionRule['join_times'] = isset($param['join_times'])? (int)$param['join_times'] : 0;
        // 限时优惠、限购的新会员，只能参加一次促销活动
        if($promotion['promotion_type'] == BaiyangPromotionEnum::LIMIT_BUY || $promotion['promotion_type'] == BaiyangPromotionEnum::LIMIT_TIME){
            $promotionRule['join_times'] = ($promotion['promotion_for_users'] == BaiyangPromotionEnum::HAVE_NOT_SHOPPING) ? 1 : 0;
        }
        $promotionRule['condition'] = $condition;
        if(!empty($promotionRule['condition'])){
            $conditionArray = explode(',', $promotionRule['condition']);
            if(count($conditionArray) != count(array_unique($conditionArray))){
                return $this->arrayData('编辑失败！不能添加相同数据', '', '', 'error');
            }
        }
        $promotionRule['rule_value'] = $this->getPromotionRuleValue($promotion,$param);
        if ($promotionRule['except_category_id']) {
            $result = $this->verify_except_list($promotionRule['except_category_id'], '分类');
            if ($result) {
                return $result;
            }
        }
        if ($promotionRule['except_brand_id']) {
            $result = $this->verify_except_list($promotionRule['except_brand_id'], '品牌');
            if ($result) {
                return $result;
            }
        }
        if ($promotionRule['except_good_id']) {
            $result = $this->verify_except_list($promotionRule['except_good_id'], '商品');
            if ($result) {
                return $result;
            }
        }

        //促销活动更新的字段
        $columStr1 = $this->jointString($promotion, array('promotion_id'));
        //促销活动规则更新的字段
        $columStr2 = $this->jointString($promotionRule, array('promotion_id'));
        $whereStr = 'promotion_id = :promotion_id:';
        // 开启事务
        $this->dbWrite->begin();
        //更新操作
        $promotionResult = BaseData::getInstance()->update($columStr1,'\Shop\Models\BaiyangPromotion',$promotion,$whereStr);
        $promotionRuleResult = BaseData::getInstance()->update($columStr2,'\Shop\Models\BaiyangPromotionRule',$promotionRule,$whereStr);
        if(empty($promotionResult) || empty($promotionRuleResult)){
            $this->dbWrite->rollback();
            return $this->arrayData('编辑失败！', '', '', 'error');
        }
        //成功后加载的url
        $url = '/promotion/list';
        if($promotion['promotion_type'] == BaiyangPromotionEnum::LIMIT_BUY){
            $url = '/limitbuy/list';
        }
        if($promotion['promotion_type'] == BaiyangPromotionEnum::LIMIT_TIME){
            $url = '/limittime/list';
        }
        $this->dbWrite->commit();
        BaiyangPromotionData::getInstance()->getPromotionsInfo('', true); // 缓存活动
        if($promotion['promotion_type'] != BaiyangPromotionEnum::LIMIT_BUY){
            $esUrl = $this->config->es->pcUrl. 'pces/uPromotionById.do';
            $requestData = http_build_query(['proId' => $promotion['promotion_id']]);
            $responseResult = json_decode($this->curl->sendPost($esUrl,$requestData),true);
        }
        return $this->arrayData('编辑成功！', $url);
    }

    /**
     * @desc 取消促销活动
     * @param int $promotionId 活动id
     * @param int $promotionType 活动类型
     * @param string $request 请求url参数
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function delPromotion($promotionId,$promotionType = 0,$request = '')
    {
        if(empty($promotionId)){
            return $this->arrayData('操作有误！', '', '', 'error');
        }
        $promotionType = trim($promotionType);
        $columStr = 'promotion_status = :promotion_status:,promotion_update_time = :promotion_update_time:,promotion_edit_user_id = :promotion_edit_user_id:,promotion_edit_username = :promotion_edit_username:';
        $conditions =[
            'promotion_id' => (int)$promotionId,
            'promotion_update_time' => time(),
            'promotion_edit_user_id' => $this->session->get('user_id'),
            'promotion_edit_username' => $this->session->get('username'),
            'promotion_status' => BaiyangPromotionEnum::PROMOTION_CANCEL,
        ];
        $whereStr = 'promotion_id = :promotion_id:';
        $result = BaseData::getInstance()->update($columStr,'\Shop\Models\BaiyangPromotion',$conditions,$whereStr);
        //成功后加载的url
        $url = $request ? '/promotion/list'.$request : '/promotion/list';
        if($promotionType == BaiyangPromotionEnum::LIMIT_BUY){
            $url = $request ? '/limitbuy/list'.$request : '/limitbuy/list';
        }
        if($promotionType == BaiyangPromotionEnum::LIMIT_TIME){
            $url = $request ? '/limittime/list'.$request : '/limittime/list';
        }
        BaiyangPromotionData::getInstance()->getPromotionsInfo('', true); // 缓存活动
        if($promotionType != BaiyangPromotionEnum::LIMIT_BUY){
            $esUrl = $this->config->es->pcUrl. 'pces/uPromotionById.do';
            $requestData = http_build_query(['proId' => $promotionId]);
            $responseResult = json_decode($this->curl->sendPost($esUrl,$requestData),true);
        }
        return $result ? $this->arrayData('取消成功！', $url) : $this->arrayData('取消失败！', '', '', 'error');
    }

    /**
     * @desc 检查改变促销活动的状态
     * @return int $count 改变活动状态的次数
     * @author 吴俊华
     */
    public function checkPromotionStatus()
    {
        $table = '\Shop\Models\BaiyangPromotion';
        $selections = 'promotion_id,promotion_start_time,promotion_end_time';
        $conditions = [
            'promotion_status' => BaiyangPromotionEnum::PROMOTION_CANCEL
        ];
        $whereStr = 'promotion_status != :promotion_status:';
        $promotionList = BaseData::getInstance()->select($selections,$table,$conditions,$whereStr);

        $nowTime = time(); //当前时间戳
        $count = 0; //记录更新次数
        $updateConditions = [];
        if(!empty($promotionList)){
            foreach($promotionList as $value){
                $updateConditions['promotion_id'] = $value['promotion_id'];
                if($value['promotion_start_time'] <= $nowTime && $nowTime <= $value['promotion_end_time']){
                    $updateConditions['promotion_status'] = BaiyangPromotionEnum::PROMOTION_PROCESSING;
                }
                if($value['promotion_end_time'] < $nowTime){
                    $updateConditions['promotion_status'] = BaiyangPromotionEnum::PROMOTION_HAVE_ENDED;
                }
                if($value['promotion_start_time'] > $nowTime){
                    $updateConditions['promotion_status'] = BaiyangPromotionEnum::PROMOTION_NOT_START;
                }
                $result = BaseData::getInstance()->update('promotion_status = :promotion_status:','\Shop\Models\BaiyangPromotion',$updateConditions,'promotion_id = :promotion_id:');
                if(!empty($result)){
                    $count++;
                }
            }
        }
        return $count;
    }

    /**
     * @desc 验证在相同活动、时间里是否设置相同的使用范围【主要针对品牌、单品、多单品】
     * @return array []
     * @author 吴俊华
     */
    public function verifyTimeRange($param)
    {
        // 限购活动的限制和提示语都跟其他的不一样(限购活动不能交叉)
        if($param['promotion_type'] == BaiyangPromotionEnum::LIMIT_BUY){
            $condition['condition'] = '';
            if($param['promotion_scope'] == BaiyangPromotionEnum::CATEGORY_RANGE){
                $condition['condition'] = $param['shop_category'][2];
            }
            if($param['promotion_scope'] == BaiyangPromotionEnum::BRAND_RANGE){
                $condition['condition'] = $param['shop_brand'];
            }
            if($param['promotion_scope'] == BaiyangPromotionEnum::SINGLE_RANGE || $param['promotion_scope'] == BaiyangPromotionEnum::MORE_RANGE){
                $condition['condition'] = $param['shop_single'];
            }
            $param['promotion_start_time'] = strtotime($param['promotion_start_time']);
            $param['promotion_end_time'] = strtotime($param['promotion_end_time']);
            $condition['except_category_id'] = '';
            $condition['except_brand_id'] = '';
            $condition['except_good_id'] = '';
            $res = $this->verifyLimitBuy($param, $condition);
            if($res['error'] == 1){
                return $this->arrayData($res['tips'], '', '', 'error');
            }
            return $this->arrayData('满足规则', '', '', 'success');
        }else{
            $tables = []; //操作的表
            $selections = 'bb.condition'; //品牌、单品使用范围
            $tables['promotionTable'] = '\Shop\Models\BaiyangPromotion as aa';
            $tables['promotionRuleTable'] = '\Shop\Models\BaiyangPromotionRule as bb';
            //公共条件
            $conditions = [
                'promotion_type' => $param['promotion_type'],
                'promotion_scope' => $param['promotion_scope'],
                'promotion_end_time' => time(),
                'haveEnded' => BaiyangPromotionEnum::PROMOTION_HAVE_ENDED,
                'haveCanceled' => BaiyangPromotionEnum::PROMOTION_CANCEL
            ];
            $whereStr = 'aa.promotion_type = :promotion_type: AND aa.promotion_scope = :promotion_scope: AND aa.promotion_status NOT IN (:haveEnded:,:haveCanceled:)';
            if(isset($param['promotion_for_users'])){
                if($param['promotion_for_users'] == BaiyangPromotionEnum::HAVE_NOT_SHOPPING || $param['promotion_for_users'] == BaiyangPromotionEnum::HAVE_SHOPPING){
                    // 添加新用户后只能添加老用户，反之同理。(但添加所有人后，新老用户都不能再添加)
                    $conditions = array_merge($conditions, ['all_people' => BaiyangPromotionEnum::ALL_PEOPLE,'promotion_for_users' => $param['promotion_for_users']]);
                    $whereStr .= ' AND aa.promotion_for_users in(:all_people:,:promotion_for_users:)';
                }
            }
            $param['promotion_start_time'] = strtotime($param['promotion_start_time']);
            $param['promotion_end_time'] = strtotime($param['promotion_end_time']);
            //编辑时排除自身
            if (isset($param['promotion_id']) && !empty($param['promotion_id'])) {
                $whereStr .= ' AND aa.promotion_id != :promotion_id:';
                $conditions['promotion_id'] = $param['promotion_id'];
            }

            $shopId = 0;
            //验证品牌、单品、多单品的使用范围
            if($param['promotion_scope'] == BaiyangPromotionEnum::BRAND_RANGE){
                $shopId = explode(',',$param['shop_brand']);
            }
            if($param['promotion_scope'] == BaiyangPromotionEnum::SINGLE_RANGE || $param['promotion_scope'] == BaiyangPromotionEnum::MORE_RANGE){
                $shopId = explode(',',$param['shop_single']);
            }
//        if($param['promotion_scope'] == BaiyangPromotionEnum::MORE_RANGE){
//            $shopId = explode(',',$param['shop_more']);
//        }
            $where = $whereStr.' AND aa.promotion_end_time >= :promotion_end_time:';
            $result = $this->getPlatformVerify($shopId, $param, $selections, $tables, $conditions, $where);
            if(!empty($result)){
                return $this->arrayData($this->getTimeRangeMsg($param,$result), '', '', 'error');
            }
            return $this->arrayData('满足规则', '', '', 'success');
        }
    }

   /**
    * @desc 验证限购活动
    * @param array $param 需要验证的必要参数
    * @param array $condition 商品id或品牌id或品类id
    * @return array [] 验证结果信息
    * @author 吴俊华
    */
    public function verifyLimitBuy($param, $condition)
    {
        $param['promotion_id'] = isset($param['promotion_id']) ? $param['promotion_id'] : 0;
        $param['condition'] = $condition['condition'];
        $param['except_category_id'] = $condition['except_category_id'];
        $param['except_brand_id'] = $condition['except_brand_id'];
        $param['except_good_id'] = $condition['except_good_id'];
        $limitBuyList = BaiyangPromotionData::getInstance()->getPromotionsInfo($param['promotion_type']);
        if(empty($limitBuyList)){
            return ['error' => 0,'tips' => ''];
        }

        foreach ($limitBuyList as $key => $value){
            $platformTips = ''; // 平台提示
            // 时间上的交叉(4种情况)
            if(($param['promotion_start_time'] <= $value['promotion_start_time'] && $param['promotion_end_time'] >= $value['promotion_end_time']) || ($param['promotion_start_time'] <= $value['promotion_start_time'] && $value['promotion_start_time'] <= $param['promotion_end_time']) || ($value['promotion_start_time'] <= $param['promotion_start_time'] && $param['promotion_start_time'] <= $value['promotion_end_time'] && $value['promotion_end_time'] <= $param['promotion_end_time']) || ($param['promotion_start_time'] >= $value['promotion_start_time'] && $param['promotion_end_time'] <= $value['promotion_end_time'])){

                // 平台交叉
                if(($param['promotion_platform_pc'] == 1 && $param['promotion_platform_pc'] == $value['promotion_platform_pc']) || ($param['promotion_platform_app'] == 1 && $param['promotion_platform_app'] == $value['promotion_platform_app']) || ($param['promotion_platform_wap'] == 1 && $param['promotion_platform_wap'] == $value['promotion_platform_wap']) || ($param['promotion_platform_wechat'] == 1 && $param['promotion_platform_wechat'] == $value['promotion_platform_wechat'])){
                    if($param['promotion_platform_pc'] == 1){
                        $platformTips .= 'PC或';
                    }
                    if($param['promotion_platform_app'] == 1){
                        $platformTips .= 'APP或';
                    }
                    if($param['promotion_platform_wap'] == 1){
                        $platformTips .= 'WAP或';
                    }
                    if($param['promotion_platform_wechat'] == 1){
                        $platformTips .= '微商城';
                    }
                    $platformTips = rtrim($platformTips,'或');
                    // 排除自身
                    if($param['promotion_id'] != $value['promotion_id']){
                        // 全场使用范围
                        if($param['promotion_scope'] == BaiyangPromotionEnum::ALL_RANGE && $param['promotion_scope'] == $value['promotion_scope']){
                            // 新老会员交叉
                            if($param['promotion_for_users'] == $value['promotion_for_users'] || $param['promotion_for_users']== BaiyangPromotionEnum::ALL_PEOPLE || $value['promotion_for_users'] == BaiyangPromotionEnum::ALL_PEOPLE){
                                return ['error' => 1,'tips' => '活动编号：'.$value['promotion_number'].'在'.$platformTips.'平台已设置为限购活动，不能重复设置'];
                            }
                        }
                        $result = $this->isRelatedGoods($value, $param, $platformTips);
                        if($result['error'] == 1){
                            return $result;
                        }
                    }
                }
            }
        }
        return ['error' => 0,'tips' => ''];
    }

    /**
     * @desc 判断商品是否满足限购活动的规则
     * @param array $promotion 数据库的限购活动信息 [一维数组]
     * @param array $param 准备新增/修改的限购活动信息 [一维数组]
     * @param string $platformTips 平台提示
     * @return array []  验证的结果信息
     * @author 吴俊华
     */
    public function isRelatedGoods($promotion, $param ,$platformTips)
    {
        $currentGoodsId = [];
        $currentBrandId = [];
        $currentCategoryId = [];
        // 准备添加或修改的限购活动信息
        $currentCondition = explode(',', $param['condition']);
        $currentExceptCategoryId = explode(',', $param['except_category_id']);
        $currentExceptBrandId = explode(',', $param['except_brand_id']);
        $currentExceptGoodId = explode(',', $param['except_good_id']);
        // 数据库的限购活动信息
        $condition = explode(',', $promotion['condition']);
        $exceptCategoryId = explode(',', $promotion['except_category_id']);
        $exceptBrandId = explode(',', $promotion['except_brand_id']);
        $exceptGoodId = explode(',', $promotion['except_good_id']);

        // 原有排除当前的品类id
        if(!empty($promotion['except_category_id']) && $param['promotion_scope'] == BaiyangPromotionEnum::CATEGORY_RANGE){
            $intersect = array_intersect($currentCondition, $exceptCategoryId);
            if (count($intersect) == count($currentCondition) && count($currentCondition) > 0) {
                return ['error' => 0,'tips' => ''];
            }
        }
        // 当前排除原有的品类id
        if(!empty($param['except_category_id']) && $promotion['promotion_scope'] == BaiyangPromotionEnum::CATEGORY_RANGE){
            $intersect = array_intersect($condition, $currentExceptCategoryId);
            if (count($intersect) == count($condition) && count($condition) > 0) {
                return ['error' => 0,'tips' => ''];
            }
        }
        // 原有排除当前的品牌id
        if(!empty($promotion['except_brand_id']) && $param['promotion_scope'] == BaiyangPromotionEnum::BRAND_RANGE){
            $intersect = array_intersect($currentCondition, $exceptBrandId);
            if (count($intersect) == count($currentCondition) && count($currentCondition) > 0) {
                return ['error' => 0,'tips' => ''];
            }
        }
        // 当前排除原有的品牌id
        if(!empty($param['except_brand_id']) && $promotion['promotion_scope'] == BaiyangPromotionEnum::BRAND_RANGE){
            $intersect = array_intersect($condition, $currentExceptBrandId);
            if (count($intersect) == count($condition) && count($condition) > 0) {
                return ['error' => 0,'tips' => ''];
            }
        }
        // 原有排除当前的商品id
        if(!empty($promotion['except_good_id']) && ($param['promotion_scope'] == BaiyangPromotionEnum::SINGLE_RANGE || $param['promotion_scope'] == BaiyangPromotionEnum::MORE_RANGE)){
            $intersect = array_intersect($currentCondition, $exceptGoodId);
            if (count($intersect) == count($currentCondition) && count($currentCondition) > 0) {
                return ['error' => 0,'tips' => ''];
            }
        }
        // 当前排除原有的商品id
        if(!empty($param['except_good_id']) && ($promotion['promotion_scope'] == BaiyangPromotionEnum::SINGLE_RANGE || $promotion['promotion_scope'] == BaiyangPromotionEnum::MORE_RANGE)){
            $intersect = array_intersect($condition, $currentExceptGoodId);
            if (count($intersect) == count($condition) && count($condition) > 0) {
                return ['error' => 0,'tips' => ''];
            }
        }
        $skuData = BaiyangSkuData::getInstance();
        // 非全场验证
        if($param['promotion_scope'] != BaiyangPromotionEnum::ALL_RANGE){
            // 使用范围：品类、品牌、单品、多单品
            switch ($param['promotion_scope']) {
                case BaiyangPromotionEnum::CATEGORY_RANGE:
                    $keyIdName = 'b.category_id';
                    $currentCategoryId = $currentCondition;
                    break;
                case BaiyangPromotionEnum::BRAND_RANGE:
                    $keyIdName = 'b.brand_id';
                    $currentBrandId = $currentCondition;
                    break;
                case BaiyangPromotionEnum::SINGLE_RANGE:
                    $currentGoodsId = $currentCondition;
                    $keyIdName = 'a.id';
                    break;
                case BaiyangPromotionEnum::MORE_RANGE:
                    $currentGoodsId = $currentCondition;
                    $keyIdName = 'a.id';
                    break;
                default: $keyIdName = ''; break;
            }
            if(!empty($keyIdName) && !empty($param['condition'])){
                $where = "{$keyIdName} in({$param['condition']})";
                $goodsInfo = $skuData->getSkuInfoBySalesId($where);
                if(!empty($goodsInfo)){
                    $currentGoodsId = $this->handleEmpty(array_unique(array_column($goodsInfo,'goods_id')));
                    $currentBrandId = $this->handleEmpty(array_unique(array_column($goodsInfo,'brand_id')));
                    $currentCategoryId = $this->handleEmpty(array_unique(array_column($goodsInfo,'category_id')));
                }
            }

            // 新老会员交叉
            if($param['promotion_for_users'] == $promotion['promotion_for_users'] || $param['promotion_for_users']== BaiyangPromotionEnum::ALL_PEOPLE || $promotion['promotion_for_users'] == BaiyangPromotionEnum::ALL_PEOPLE){
                // 商品id交叉
                if(!empty($currentGoodsId) && ($promotion['promotion_scope'] == BaiyangPromotionEnum::SINGLE_RANGE || $promotion['promotion_scope'] == BaiyangPromotionEnum::MORE_RANGE)){
                    $goodIntersect = array_intersect($currentGoodsId, $condition);
                    if($goodIntersect){
                        $goodsId = implode(',',$goodIntersect);
                        $where = "a.id in({$goodsId})";
                        $goodsInfo = $skuData->getSkuInfoBySalesId($where);
                        if($goodsInfo){
                            $goodsName = implode(',',array_column($goodsInfo,'goods_name'));
                            return ['error' => 1,'tips' => '活动编号：'.$promotion['promotion_number'].'的商品：'.$goodsName.'在'.$platformTips.'平台已设置为限购活动，不能重复设置'];
                        }
                    }
                }
                // 品牌id交叉
                if(!empty($currentBrandId) && $promotion['promotion_scope'] == BaiyangPromotionEnum::BRAND_RANGE){
                    $brandIntersect = array_intersect($currentBrandId, $condition);
                    if($brandIntersect){
                        $brandsId = implode(',',$brandIntersect);
                        $brandInfo = $skuData->getBrandInfoById($brandsId);
                        if($brandInfo){
                            $brandsName = implode(',',array_column($brandInfo,'brand_name'));
                            return ['error' => 1,'tips' => '活动编号：'.$promotion['promotion_number'].'的品牌：'.$brandsName.'在'.$platformTips.'平台已设置为限购活动，不能重复设置'];
                        }
                    }
                }
                // 品类id交叉
                if(!empty($currentCategoryId) && $promotion['promotion_scope'] == BaiyangPromotionEnum::CATEGORY_RANGE){
                    $categoryIntersect = array_intersect($currentCategoryId, $condition);
                    if($categoryIntersect){
                        $categorysId = implode(',',$categoryIntersect);
                        $categoryInfo = $skuData->getCategoryInfoById($categorysId);
                        if($categoryInfo){
                            $categorysName = implode(',',array_column($categoryInfo,'category_name'));
                            return ['error' => 1,'tips' => '活动编号：'.$promotion['promotion_number'].'的分类：'.$categorysName.'在'.$platformTips.'平台已设置为限购活动，不能重复设置'];
                        }
                    }
                }
            }
        }
        return ['error' => 0,'tips' => ''];
    }

    /**
     * @desc 处理空值
     * @param array $array 需要处理的数组 [一维数组]
     * @return array [] 处理后的数组
     * @author 吴俊华
     */
    private function handleEmpty($array)
    {
        foreach ($array as $key => $value){
            if(empty($value)){
                unset($array[$key]);
            }
        }
        $array = array_values($array);
        return $array;
    }

    private function verify_except_list($data, $msg)
    {
        $except_id_list = explode(',', $data);
        $old_except_count = count($except_id_list);
        $except_id_list = array_unique($except_id_list);
        $new_except_count = count($except_id_list);
        if ($old_except_count != $new_except_count) {
            return $this->arrayData("请勿重复输入不参加活动的{$msg}ID", '', '', 'error');
        }
        $except_id_list = array_filter($except_id_list,'ctype_digit');
        $new_except_count = count($except_id_list);
        if ($old_except_count != $new_except_count) {
            return $this->arrayData("请正确输入不参加活动的{$msg}ID", '', '', 'error');
        }
        return false;
    }

}
