<?php

/**
 * Created by PhpStorm.
 * @author yanbo
 * @date: 2026/8/16
 */

namespace Shop\Home\Services;

//use Phalcon\Annotations\Adapter\Base;
//use Shop\Home\Datas\BaiyangOrderData;
use Shop\Home\Datas\BaiyangSkuData;
//use Shop\Home\Datas\BaiyangUserData;
use Shop\Home\Datas\BaseData;
use Shop\Home\Services\BaseService;
//use Shop\Home\Listens\PromotionGoodsDetail;
//use Shop\Models\BaiyangGoodsPriceEnum;
//use Shop\Models\BaiyangPromotionEnum;
//use Shop\Home\Datas\BaiyangGoodsStockChangeLogData;
use Shop\Models\HttpStatus;
//use Shop\Home\Services\CouponService;
use Shop\Home\Listens\O2OPromotionGoodsDetail;
use Shop\Home\Listens\O2OPromotionShopping;
use Phalcon\Events\Manager as EventsManager;
use Shop\Home\Datas\BaiyangUserGoodsPriceTagData;

class O2OSkuService extends BaseService {

    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance = null;

    /**
     * @desc 重写实例化方法
     * @return class
     * @author 吴俊华
     */
    public static function getInstance() {
        if (empty(static::$instance)) {
            static::$instance = new O2OSkuService();
        }
        $eventsManager = new EventsManager();
        $eventsManager->attach('promotionInfo', new O2OPromotionGoodsDetail);
        static::$instance->setEventsManager($eventsManager);
        return static::$instance;
    }

    /**
     * 根据sku id 获取sku详细信息（已用）
     * @param array $param [一维数组]
     *          -int        sku_id      商品id
     *          -string     platform    平台【pc、app、wap】
     *          -int        user_id     用户id (临时用户或真实用户id)
     *          -int        is_temp     是否为临时用户 (1为临时用户、0为真实用户)
     *          -int        channel_subid  渠道号，微商场：85 IOS：89 安卓：90 WAP：91 PC：95（*）
     *          -string     udid        手机唯一id(app端必填)
     * @return array [] 结果信息
     * @author 梁伟
     */
    public function getSku($param) {
        if (!isset($param['sku_id']) || empty($param['sku_id']) || !isset($param['platform']) || empty($param['platform']) || !isset($param['user_id']) || !isset($param['is_temp']) || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $BaiyangSkuData = BaiyangSkuData::getInstance();

        $res = $BaiyangSkuData->getSkuInfo($param['sku_id'], $param['platform']);

        if (!$res) {
            return $this->uniteReturnResult(HttpStatus::NOT_GOOD_INFO);
        }
        //不能为海外购商品、第三方发货商品,返回不能O2O配送
        $goods = $BaiyangSkuData->getData(array(
            'table' => '\Shop\Models\BaiyangGoods',
            'column' => 'id,is_global,behalf_of_delivery',
            'where' => 'where id = :id:',
            'bind' => array(
                'id' => $param['sku_id']
            )
                ), true);
        if ($res['is_global'] == 1 || $goods['behalf_of_delivery'] == 1) {
            return $this->uniteReturnResult(HttpStatus::NOT_SUPPORT_O2O);
        }
        //品规信息(无品规)
//        if($res['spu_id'] > 1){
//            $param['spu_id'] = $res['spu_id'];
//            $ruleTmp = $this->getSkuRuleAll($param);
//            if($ruleTmp['status'] == 200){
//                $res['ruleApp'] = $ruleTmp['data'];
//            }else{
//                $res['ruleApp'] = array();
//            }
//        }else{
//            $res['ruleApp'] = array();
//        }
        $res['ruleApp'] = array();
        //商品药品标签
        $res['serviceInfoList'] = $BaiyangSkuData->getMedicineTag($res['is_global'] ? 0 : $res['drug_type'], $param['platform']);

        //海外购商品逻辑
//        if ($res['is_global']) {
//            //商品图片信息
//            $res['sku_img'] = $this->filterData('sku_image,sku_big_image', $BaiyangSkuData->getGoodsImg($res['id']));
//            $sku_desc = $BaiyangSkuData->getGoodsExtension($res['id']);
//            $res['sku_desc'] = ($param['platform'] == 'pc') ? $sku_desc['goods_desc'] : $sku_desc['body'];
//            //获取品牌名
//            $res['brand_name'] = '';
//            if ($res['brand_id'] > 0) {
//                $brand = $BaiyangSkuData->getGoodsBrand($res['brand_id']);
//                $res['brand_name'] = isset($brand['brand_name']) ? $brand['brand_name'] : '';
//                $res['brand_logo'] = isset($brand['brand_logo']) ? $brand['brand_logo'] : '';
//            }
//            $goodsStockChange = BaiyangGoodsStockChangeLogData::getInstance()->getGoodsStockChange(['goods_id' => $param['sku_id']]);
//            foreach ($goodsStockChange as $v) {
//                if ($res['sku_stock'] <= 0)
//                    break;
//                $res['sku_stock'] = $res['sku_stock'] + $v['change_num'];
//            }
//            $res['kindly_reminder'] = '海外购商品暂不支持开发票与换货操作';
//            //处方药商品是否显示"提交需求"按钮 (1:显示 0:不显示)
//            $res['display_add_cart'] = 1;
//            return $this->uniteReturnResult(HttpStatus::SUCCESS, $res);
//        }
        //获取视频信息
        if (isset($res['video']) && $res['video'] > 0) {
            $video = $BaiyangSkuData->getSkuVideo($res['video']);
            $res['video'] = $this->filterData('video_id,extend_images,video_unique,video_duration,video_desc', $video);
        }

        //商品图片信息
        $sku_img = $this->filterData('sku_image,sku_big_image', $BaiyangSkuData->getSkuImg($res['id'], $res['spu_id']));
        //判断主图图片是否重复
        if ($sku_img) {
            $tmpAct = false;
            foreach ($sku_img as $v) {
                if ($v['sku_image'] == $res['small_path']) {
                    $tmpAct = true;
                    break;
                }
            }
            if ($tmpAct) {
                $res['sku_img'] = $sku_img;
            } else {
                $res['sku_img'] = array_merge([0 => array('sku_image' => $res['small_path'], 'sku_big_image' => $res['big_path'])], is_array($sku_img) ? $sku_img : array());
            }
        } else {
            $res['sku_img'] = [0 => array('sku_image' => $res['small_path'], 'sku_big_image' => $res['big_path'])];
        }

        //获取品牌名
        $res['brand_name'] = '';
        $res['brand_logo'] = '';
        if ($res['brand_id'] > 0) {
            $brand = $BaiyangSkuData->getSkuBrand($res['brand_id']);
            $res['brand_name'] = isset($brand['brand_name']) ? $brand['brand_name'] : '';
            $res['brand_logo'] = isset($brand['brand_logo']) ? $brand['brand_logo'] : '';
        }
        //说明书
        $res['instruction'] = array();
        $instruction = $BaiyangSkuData->getSkuInstruction($param['sku_id']);
        if ($instruction) {
            foreach ($instruction as $k => $v) {
                if ($k != 'id' and $k != 'sku_id') {
                    if (!empty($v)) {
                        $res['instruction'] = $instruction;
                    }
                }
            }
        }

        //是否显示加入购物车
        $res['is_show_button'] = ($res['drug_type'] == 1) ? 0 : 1;

        //判断是否已收藏
        if ($param['is_temp'] == 0 && $param['user_id']) {
            $isCollect = $BaiyangSkuData->getData(array(
                'table' => '\Shop\Models\BaiyangUserCollect',
                'column' => 'id',
                'where' => 'where user_id = :user_id: and goods_id = :goods_id:',
                'bind' => array(
                    'user_id' => $param['user_id'],
                    'goods_id' => $param['sku_id']
                )
            ));
            $res['isCollect'] = ($isCollect) ? 1 : 0;
        } else {
            $res['isCollect'] = 0;
        }

        //特色服务
        $res['serviceInfoList'] = $BaiyangSkuData->getMedicineTag($res['drug_type'], $param['platform']);

        //处理APP信息，只能为APP
        //处方药提示
        $res['kindly_reminder'] = '';
        if ($res['drug_type'] == 1) {
            $res['kindly_reminder'] = '本品为处方药。购买需凭医生有效处方，服用请遵医嘱，有关用药信息请咨询药师。';
        }

        //促销信息
        $params['goods_id'] = $param['sku_id'];
        $params['platform'] = $param['platform'];
        $params['user_id'] = $param['user_id'];
        $params['is_temp'] = $param['is_temp'];
        $params['channel_subid'] = $param['channel_subid'];
        $params['udid'] = isset($param['udid']) ? $param['udid'] : '';
        //只拿取限时优惠促销信息
        $promotion = $this->_eventsManager->fire('promotionInfo:getPromotionInfoByGoodsId', $this, $params);
        $res['promotion'] = ($promotion['error']) ? array() : $promotion['data'];
        //处方药商品是否显示"提交需求"按钮 (1:显示 0:不显示)
        $res['display_add_cart'] = isset($res['promotion']['discountInfo']['display_add_cart']) ? $res['promotion']['discountInfo']['display_add_cart'] : 0;

        //获取附属赠品
        $O2OPromotionShopping = new O2OPromotionShopping();
        $goodsinfo = $O2OPromotionShopping->getCartGoodsInfo($res['id']);
        $res['bind_gift'] = isset($goodsinfo['bind_gift']) ? $goodsinfo['bind_gift'] : array();

        unset($res['is_use_stock']);
        unset($res['packaging_type']);
        unset($res['attribute_value_id']);
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $res);
    }

    /**
     * 根据spu id获取全部品规信息
     * @param array $param [一维数组]
     *          -int        spu_id    spu id
     *          -int        sku_id    商品ID
     *          -string     platform  平台【pc、app、wap】
     *          -int        user_id   用户id (临时用户或真实用户id)
     *          -int        is_temp   是否为临时用户 (1为临时用户、0为真实用户)
     *          -int        channel_subid  渠道号，微商场：85 IOS：89 安卓：90 WAP：91 PC：95（*）
     *          -string     udid        手机唯一id(app端必填)
     * @return array
     * @author 梁伟
     */
    public function getSkuRuleAll($param) {
        if (!isset($param['is_temp']) || !isset($param['user_id']) || !$this->verifyRequiredParam($param) || empty($param['spu_id'])) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }

        $BaiyangSkuData = BaiyangSkuData::getInstance();
        $spuAll = $BaiyangSkuData->getSkuRules($param['spu_id'], $param['platform'], false);
        $spu = $BaiyangSkuData->getSkuSpu($param['spu_id']);
        $rule = $BaiyangSkuData->getSkuRule($spu['category_id']);
        $ruleName1 = $BaiyangSkuData->getSkuRuleName($rule['name_id']);
        $ruleName2 = $BaiyangSkuData->getSkuRuleName($rule['name_id2']);
        $ruleName3 = $BaiyangSkuData->getSkuRuleName($rule['name_id3']);
        $array = array();
        $ruels = array();
        $ruels[0]['className'] = $ruleName1;
        $ruels[0]['classId'] = $rule['name_id'];
        $ruels[0]['valueList'] = array();
        $ruels[1]['className'] = $ruleName2;
        $ruels[1]['classId'] = $rule['name_id2'];
        $ruels[1]['valueList'] = array();
        $ruels[2]['className'] = $ruleName3;
        $ruels[2]['classId'] = $rule['name_id3'];
        $ruels[2]['valueList'] = array();
        $i = 0;
        foreach ($spuAll as $k => $v) {
            if ($param['platform'] != 'pc') {
                $key = '';
                if ($v['rule_value0']) {
                    $key .= $v['rule_value0'] . ';';
                }
                if ($v['rule_value1']) {
                    $key .= $v['rule_value1'] . ';';
                }
                if ($v['rule_value2']) {
                    $key .= $v['rule_value2'];
                }
                $key = trim($key, ';');
            } else {
                $key = $v['rule_value0'] . ';' . $v['rule_value1'] . ';' . $v['rule_value2'];
            }

            //获取品规值信息
            $ruleValue1 = $BaiyangSkuData->getSkuRuleName($v['rule_value0'], empty($rule['name_id']) ? -1 : $rule['name_id']);
            $ruleValue2 = $BaiyangSkuData->getSkuRuleName($v['rule_value1'], empty($rule['name_id2']) ? -1 : $rule['name_id2']);
            $ruleValue3 = $BaiyangSkuData->getSkuRuleName($v['rule_value2'], empty($rule['name_id3']) ? -1 : $rule['name_id3']);

            if (!(empty($ruleValue1) && empty($ruleValue2) && empty($ruleValue3))) {//获得商品信息
                $array[$key] = $this->filterData('small_path', $BaiyangSkuData->getSkuInfo($v['id'], $param['platform']));

                //促销信息
                $params['goods_id'] = $v['id'];
                $params['platform'] = $param['platform'];
                $params['user_id'] = $param['user_id'];
                $params['is_temp'] = $param['is_temp'];
                $params['channel_subid'] = $param['channel_subid'];
                $params['udid'] = isset($param['udid']) ? $param['udid'] : '';
                $promotion = $this->_eventsManager->fire('promotionInfo:getPromotionInfoByGoodsId', $this, $params);
                $promotionTmp = ($promotion['error']) ? array() : $promotion['data'];
                $array[$key]['sku_id'] = $v['id'];
                $array[$key]['goods_price'] = $promotionTmp['discountInfo']['goods_price'];
                $array[$key]['stock'] = $promotionTmp['discountInfo']['stock'];
                $array[$key]['goods_status'] = $promotionTmp['discountInfo']['goods_status'];
                $array[$key]['rules'] = array(
                    ['name' => $ruleName1, 'value' => $ruleValue1],
                    ['name' => $ruleName2, 'value' => $ruleValue2],
                    ['name' => $ruleName3, 'value' => $ruleValue3],
                );
            }
            $ruels[0]['valueList'] = $this->handleRule($ruels[0]['valueList'], $v, $ruleValue1, $param['sku_id']);
            $ruels[1]['valueList'] = $this->handleRule($ruels[1]['valueList'], $v, $ruleValue2, $param['sku_id'], 1);
            $ruels[2]['valueList'] = $this->handleRule($ruels[2]['valueList'], $v, $ruleValue3, $param['sku_id'], 2);

            $i++;
        }
        //处理APP格式
        if ($param['platform'] != 'pc') {
            if (!empty($ruels[0]['className']) && !empty($ruels[0]['valueList'][0]['name']))
                $res['gaugeList'][] = $ruels[0];
            if (!empty($ruels[1]['className']) && !empty($ruels[1]['valueList'][0]['name']))
                $res['gaugeList'][] = $ruels[1];
            if (!empty($ruels[2]['className']) && !empty($ruels[2]['valueList'][0]['name']))
                $res['gaugeList'][] = $ruels[2];
        }else {
            $res['gaugeList'] = $ruels;
        }
        $res['skuData'] = empty($array) ? null : $array;
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $res);
    }

    /**
     * 处理品规信息
     * @param $param
     * @return bool
     * @author 梁伟
     */
    private function handleRule($rules, $v, $ruleValue, $sku_id, $key = 0) {
        $tmp = false;
        foreach ($rules as $k1 => $v1) {
            if ($v1['name'] == $ruleValue) {
                $tmp = $k1;
            }
        }
        if ($tmp !== false) {
            if ($sku_id == $v['id'])
                $rules[$tmp]['isSelected'] = 1;
        }else {
            if (!empty($ruleValue)) {
                $rules[] = ['name' => $ruleValue, 'valueId' => $v['rule_value' . $key], 'isSelected' => ($sku_id == $v['id']) ? 1 : 0];
            }
        }
        return $rules;
    }

    /**
     * @remark app根据分类id搜索
     * @param array $param
     * @return \array[]
     * @author 杨永坚
     */
    public function getCategoryList($param = array()) {
        //格式化参数
        $param['platform'] = isset($param['platform']) ? (string) $param['platform'] : '';
        $param['categoryId'] = isset($param['categoryId']) ? (int) $param['categoryId'] : 0;
        $param['searchAttr'] = isset($param['searchAttr']) ? (string) $param['searchAttr'] : '';
        $param['type'] = isset($param['type']) ? (string) $param['type'] : null;
        $param['typeStatus'] = isset($param['typeStatus']) ? (int) $param['typeStatus'] : 0;
        $param['downPrice'] = isset($param['downPrice']) && !empty($param['downPrice']) ? (int) $param['downPrice'] : null;
        $param['upPrice'] = isset($param['upPrice']) && !empty($param['upPrice']) ? (int) $param['upPrice'] : null;
        $param['pageStart'] = isset($param['pageStart']) ? (int) $param['pageStart'] : 0;
        $param['pageSize'] = isset($param['pageSize']) ? (int) $param['pageSize'] : 0;
        $param['userId'] = isset($param['userId']) ? (int) $param['userId'] : null;
        $param['isTemp'] = isset($param['isTemp']) ? (int) $param['isTemp'] : null;
        //参数错误
        if (!$this->verifyRequiredParam($param) || $param['categoryId'] == 0 || $param['userId'] === null || $param['isTemp'] === null) {
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        switch ($param['platform']) {
            case 'app':
                $esUrl = $this->config['es']['appUrl'] . 'appes/getAppDataByCategory.do';
                break;
            case 'wap':
                $esUrl = $this->config['es']['wapUrl'] . 'wapes/getWapDataByCategory.do';
                break;
            case 'wechat':
                $esUrl = $this->config['es']['wechatUrl'] . 'wapes/getWapDataByCategory.do';
                break;
        }
        //请求es接口查询
        return $this->getEsApi($esUrl, $param);
    }

    /**
     * @remark app关键词搜索
     * @param array $param
     * @return \array[]
     * @author 杨永坚
     */
    public function getKeywordList($param = array()) {
        //格式化参数
        $param['platform'] = isset($param['platform']) ? (string) $param['platform'] : '';
        $param['searchName'] = isset($param['searchName']) ? (string) $param['searchName'] : null;
        $param['searchAttr'] = isset($param['searchAttr']) ? (string) $param['searchAttr'] : null;
        $param['type'] = isset($param['type']) ? (string) $param['type'] : null;
        $param['typeStatus'] = isset($param['typeStatus']) ? (int) $param['typeStatus'] : null;
        $param['downPrice'] = isset($param['downPrice']) && !empty($param['downPrice']) ? (int) $param['downPrice'] : null;
        $param['upPrice'] = isset($param['upPrice']) && !empty($param['upPrice']) ? (int) $param['upPrice'] : null;
        $param['pageStart'] = isset($param['pageStart']) ? (int) $param['pageStart'] : 0;
        $param['pageSize'] = isset($param['pageSize']) ? (int) $param['pageSize'] : 0;
        $param['userId'] = isset($param['userId']) ? (int) $param['userId'] : null;
        $param['isTemp'] = isset($param['isTemp']) ? (int) $param['isTemp'] : null;
        //参数错误
        if (!$this->verifyRequiredParam($param) || $param['searchName'] === null || $param['userId'] === null || $param['isTemp'] === null) {
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        switch ($param['platform']) {
            case 'app':
                $esUrl = $this->config['es']['appUrl'] . 'appes/getAppData.do';
                break;
            case 'wap':
                $esUrl = $this->config['es']['wapUrl'] . 'wapes/getWapData.do';
                break;
            case 'wechat':
                $esUrl = $this->config['es']['wechatUrl'] . 'wapes/getWeChatData.do';
                break;
        }
        //请求es接口查询
        return $this->getEsApi($esUrl, $param);
    }

    /**
     * @remark app联想词搜索
     * @param array $param
     * @return \array[]
     * @author 杨永坚
     */
    public function getNumList($param = array()) {
        //格式化参数
        $param['platform'] = isset($param['platform']) ? (string) $param['platform'] : '';
        $param['searchName'] = isset($param['searchName']) ? (string) $param['searchName'] : null;
        //参数错误
        if (!$this->verifyRequiredParam($param) || $param['searchName'] === null) {
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        switch ($param['platform']) {
            case 'app':
                $esUrl = $this->config['es']['appUrl'] . 'appes/getAppNum.do';
                break;
            case 'wap':
                $esUrl = $this->config['es']['wapUrl'] . 'wapes/getWapNum.do';
                break;
            case 'wechat':
                $esUrl = $this->config['es']['wechatUrl'] . 'wapes/getWeChatNum.do';
                break;
        }
        //请求es接口查询
        return $this->getEsApi($esUrl, $param, 2);
    }

    /**
     * @remark 发送es请求
     * @param $esUrl = string es地址
     * @param $param = array 请求参数
     * @param bool $isArr 是否创建新数组
     * @return \array[]
     * @author 杨永坚
     */
    private function getEsApi($esUrl, $param, $isArr = false) {
        $param['isGlobal'] = 0;
        $param['behalf'] = 0; //待发货
        //筛选促销活动
//        if (!empty($param['promotionType'])) {
//            $activity = $this->_eventsManager->fire('promotionInfo:getPromotionGoodsInfoByType', $this, array(
//                'platform' => $param['platform'],
//                'promotion_type' => $param['promotionType'],
//                'user_id' => 0,
//                'is_temp' => 0
//            ));
//            $param['activity'] = json_encode($activity['data']);
//        }
        //活动凑单
//        if (!empty($param['promotionId'])) {
//            $activity = $this->_eventsManager->fire('promotionInfo:getPromotionGoodsInfoById', $this, array(
//                'platform' => $param['platform'],
//                'promotion_id' => $param['promotionId'],
//                'user_id' => isset($param['userId']) ? $param['userId'] : 0,
//                'is_temp' => isset($param['isTemp']) ? $param['isTemp'] : 0,
//                'channel_subid' => isset($param['channel_subid']) ? $param['channel_subid'] : '',
//                'udid' => isset($param['udid']) ? $param['udid'] : ''
//            ));
//            if ($activity['error'] === 1) {
//                return $this->uniteReturnResult($activity['code']);
//            }
//            $data['promotionInfo'] = $activity['data']['promotionInfo'];
//            $data['changeGroup'] = $activity['data']['changeGroup'];
//            $param['activity'] = json_encode($activity['data']['goodsInfo']);
//        }
        //请求es接口查询
        $requestData = http_build_query($param);
        $responseResult = json_decode($this->curl->sendPost($esUrl, $requestData), true);
        if ($responseResult['code'] == 200) {
            if ($isArr) {
                switch ($isArr) {
                    case 1://同类推荐
                        $data = $responseResult['listData'];
                        break;
                    case 2://app联词搜索
                        $data = [];
                        foreach ($responseResult['dataNum'] as $k => $v) {
                            $data[] = array(
                                'name' => $k,
                                'result_count' => $v
                            );
                        }
                }
                $status = empty($data) ? \Shop\Models\HttpStatus::NO_DATA : \Shop\Models\HttpStatus::SUCCESS;
            } else {
                $data['pageCount'] = !empty($responseResult['pageCount']) ? $responseResult['pageCount'] : 0;
                $param['pageSize'] = $param['pageSize'] > 0 ? $param['pageSize'] : 1;
                $pageCount = $pageNum = 0;
                if (!empty($responseResult['pageCount'])) {
                    $pageNum = $responseResult['pageCount'];
                    $pageCount = ceil($pageNum / $param['pageSize']);
                }
                $data['param'] = $param;
                $data['pageCount'] = $pageCount;
                $data['pageNum'] = $pageNum;
                $data['pageStart'] = $param['pageStart'];
                $data['pageSize'] = $param['pageSize'];
                $userId = isset($param['userId']) ? $param['userId'] : 0;
                $isTemp = isset($param['isTemp']) ? $param['isTemp'] : 0;
                // 判断用户是否绑定标签
                $tagSign = BaiyangUserGoodsPriceTagData::getInstance()->isUserPriceTag(['user_id' => $userId, 'is_temp' => $isTemp]);
                //是否有优惠价格跟库存
                $resData = $this->_eventsManager->fire('promotionInfo:getPromotionGoodsPrice', $this, array(
                    'platform' => $param['platform'],
                    'goodsList' => $responseResult['listData'],
                    'user_id' => $userId,
                    'is_temp' => $isTemp,
                    'tag_sign' => $tagSign,
                ));

                //促销活动
//                if (in_array($param['platform'], array('pc', 'app', 'wap', 'wechat'))) {
//                    $result = $this->_eventsManager->fire('promotionInfo:getGoodsPromotionSign', $this, array(
//                        'platform' => $param['platform'],
//                        'goods_id' => implode(',', array_column($resData, 'goodsId')),
//                        'user_id' => 0,
//                        'is_temp' => 0
//                    ));
//                    //取出筛选的促销活动
//                    foreach ($resData as $k => $v) {
//                        $v['promotionData'] = array();
//                        $v['promotionData']['fullMinus'] = in_array($v['goodsId'], $result['data']['fullMinus']) ? 1 : 0;
//                        $v['promotionData']['fullGift'] = in_array($v['goodsId'], $result['data']['fullGift']) ? 1 : 0;
//                        $v['promotionData']['expressFree'] = in_array($v['goodsId'], $result['data']['expressFree']) ? 1 : 0;
//                        $v['promotionData']['coupon'] = in_array($v['goodsId'], $result['data']['coupon']) ? 1 : 0;
//                        $v['promotionData']['fullDiscount'] = in_array($v['goodsId'], $result['data']['fullOff']) ? 1 : 0;
//                        $v['promotionData']['limited'] = in_array($v['goodsId'], $result['data']['limitBuy']) ? 1 : 0;
//                        $v['promotionData']['farePurchase'] = in_array($v['goodsId'], $result['data']['increaseBuy']) ? 1 : 0;
//                        $resData[$k] = $v;
//                    }
//                }
                //促销价格有变，影响es价格排序问题，重置排序
                if ($param['type'] == 'price' && !empty($responseResult['listData'])) {
                    $resData = $this->sortArr($resData, array(
                        'direction' => $param['typeStatus'] == 1 ? 'SORT_ASC' : 'SORT_DESC',
                        'field' => 'price'
                    ));
                }
                $data['listData'] = $resData;
                $status = empty($data['listData']) ? \Shop\Models\HttpStatus::NO_DATA : \Shop\Models\HttpStatus::SUCCESS;
            }
            if (isset($responseResult['brandMap'])) {
                $data['brandMap'] = $responseResult['brandMap'];
            }
            if (isset($responseResult['attrName'])) {
                $attrName = array();
                $attrNumber = 0;
                foreach ($responseResult['attrName'] as $k => $v) {
                    $attrName[$attrNumber]['attrNames'] = $k;
                    $attrName[$attrNumber]['attrValue'] = $v;
                    $attrNumber++;
                }
                $data['attrName'] = $attrName;
            }
            return $this->uniteReturnResult($status, $data);
        } else {
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::FAILED);
        }
    }

    /**
     * @remark 多维数组某个字段排序
     * @param $data = array() 数组数据
     * @param $sort = array(
     *          'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
     *          'field'     => 'price',       //排序字段
     *        );
     * @return mixed
     * @author 杨永坚
     */
    public function sortArr($data, $sort) {
        $arrSort = array();
        foreach ($data AS $uniqid => $row) {
            foreach ($row AS $key => $value) {
                $arrSort[$key][$uniqid] = $value;
            }
        }
        if ($sort['direction']) {
            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $data);
        }
        return $data;
    }

    /**
     * -----------------------------------------------------------------------
     * 分类数据
     * -----------------------------------------------------------------------
     */

    /**
     * 获取一级分类数据
     * @param $condition 
     * return array
     */
    public function getFirstCategory($param) {
        if (!$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $bind = array(
            'pid' => 0,
            'enable' => 1,
            'main_category' => 1,
        );
        $condition['column'] = 'category_id id,category_name,picture category_logo,product_category_id';
        $condition['where'] = 'where parent_id=:pid: and enable=:enable: and main_category=:main_category: order by sort asc';
        $condition['table'] = '\Shop\Models\BaiyangAppCategory';
        $condition['bind'] = $bind;
        $result = BaseData::getInstance()->getData($condition);
        if (!count($result)) {
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
        if (!empty($result)) {
            foreach ($result as $k => $v) {
                if (!empty($v['category_logo']) && strstr($v['category_logo'], 'http://') === false) {
                    $result[$k]['category_logo'] = $this->config['domain']['appImg'] . $v['category_logo'];
                }
            }
        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $result);
    }

    /**
     * 分类列表
     * @param array $param [一维数组]
     *          -string     platform  平台【pc、app、wap】
     *          -int        channel_subid  渠道号，微商城：85 IOS：89 安卓：90 WAP：91 PC：95（*）
     *          -string     udid        手机唯一id(app端必填)
     */
    public function getMainCategory($param) {
        //格式化参数
        $param['platform'] = isset($param['platform']) ? (string) $param['platform'] : '';
        $param['categoryId'] = isset($param['categoryId']) ? (int) $param['categoryId'] : 0;
        if (!$this->verifyRequiredParam($param))
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        $category_id = $param['categoryId'];
        //获取该分类及其以下的所有子级分类
        $condition = array(
            'column' => 'product_category_id id,category_name,parent_id,picture category_logo,category_id',
            'table' => '\Shop\Models\BaiyangAppCategory',
            'where' => "where category_id = {$category_id} and enable = 1"
        );
        $listArr = BaseData::getInstance()->getData($condition);
        if (!$listArr) {
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
        if (!empty($listArr[0]['category_logo']) && strstr($listArr[0]['category_logo'], 'http://') === false) {
            $listArr[0]['category_logo'] = $this->config['domain']['appImg'] . $listArr[0]['category_logo'];
        }
        $listArr = $this->getNextChild($category_id, $listArr);
        //生成tree
        $categoryTree = $this->tree->structureTree($listArr, 'category_id', 'parent_id', 'son');
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $categoryTree);
    }

    /**
     * 递归方法 获取某分类下所有的子分类
     * @param type $id_str 分类ID字符串，以逗号隔开
     * @param type $listArr 返回的数组
     * * @param type $num 循环次数
     * @return type array
     */
    private function getNextChild($id_str, &$listArr, $num = 0) {
        if ($num >= 5 || strlen($id_str) <= 0) {
            return $listArr;
        }
        $num++;
        $param = array(
            'column' => 'product_category_id id,category_name,parent_id,picture category_logo,category_id',
            'table' => '\Shop\Models\BaiyangAppCategory',
            'where' => "where parent_id in ({$id_str}) and enable = 1"
        );
        $nextList = BaseData::getInstance()->getData($param);
        if (empty($nextList) == false && is_array($nextList)) {
            $ids = array();
            foreach ($nextList as $k => $v) {
                //处理图片地址
                if (!empty($v['category_logo']) && strstr($v['category_logo'], 'http://') === false) {
                    $nextList[$k]['category_logo'] = $this->config['domain']['appImg'] . $v['category_logo'];
                }
                $listArr[] = $nextList[$k];
                $ids[] = $v['category_id'];
            }
            $nextstr = implode(',', $ids);
            $this->getNextChild($nextstr, $listArr, $num);
        }
        return $listArr;
    }

}
