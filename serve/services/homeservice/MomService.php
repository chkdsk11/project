<?php
/**
 * Created by PhpStorm.
 * User: ZHQ
 * Date: 2016/12/27
 * Time: 15:50
 */

namespace Shop\Home\Services;

use Shop\Home\Datas\BaiyangAdData;
use Shop\Home\Datas\BaiyangCouponData;
use Shop\Home\Datas\BaiyangGoods;
use Shop\Home\Datas\BaiyangGoodsPrice;
use Shop\Home\Datas\BaiyangMomApplyData;
use Shop\Home\Datas\BaiyangMomGetGiftData;
use Shop\Home\Datas\BaiyangMomGiftActivityData;
use Shop\Home\Datas\BaiyangMomGiftReportData;
use Shop\Home\Listens\AuthListener;
use Shop\Home\Listens\BaseListen;
use Shop\Home\Services\BaseService;
use Shop\Libs\Base64Upload;
use Shop\Libs\Func;
use Shop\Models\HttpStatus;

class MomService extends BaseService
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance = null;

    private $momTagId = 1;

    /**
     * 妈妈申请
     *
     * @param array $param
     * @return \array[]
     */
    public function momApply($param)
    {
        $momApplyData = BaiyangMomApplyData::getInstance();
        if ($momApplyData->checkMomApplyData($param)) {
            $momHadApply = $momApplyData->getMomApplyState($param['user_id']);
            if ($momHadApply && $momHadApply['state'] != 2) {
                return $this->uniteReturnResult(HttpStatus::MOM_APPLY_NOT_UNIQUE);
            }
            $param['idcard'] = strtoupper($param['idcard']);
            $this->eventsManager->attach('authListener', new AuthListener());
            $authResult = $this->eventsManager->fire('authListener:idCardVerify', $this, array(
                'user_id' => $param['user_id'],
                'platform' => $param['platform'],
                'idCard' => $param['idcard'],
                'username' => $param['user_name']
            ));
            if ($authResult['code'] != HttpStatus::SUCCESS) {
                return $this->uniteReturnResult(HttpStatus::IDCARD_NAME_ERROR);
            }
            $momApplyNotUniqueData = $momApplyData->getMomApplyNotUniqueData($param['idcard'], $param['udid']);
            if ($momApplyNotUniqueData) {
                if ($momApplyNotUniqueData['idcard']) {
                    return $this->uniteReturnResult(HttpStatus::MOM_APPLY_NOT_UNIQUE, array('idcard' => $momApplyNotUniqueData['idcard']));
                }
                if ($momApplyNotUniqueData['udid']) {
                    return $this->uniteReturnResult(HttpStatus::MOM_APPLY_NOT_UNIQUE, array('udid' => $momApplyNotUniqueData['udid']));
                }
            }
            //上传证件图片
            $config = array(
                'base64File' => $param['upload_image'],
                'maxSize' => 2048, //KB
                'uploadPath' => '/tmp/mom_apply',
                'fileName' => md5('mom' . uniqid($param['user_id'])) . '.jpg'
            );
            $upload = new Base64Upload();
            $upload->setConfig($config);
            if ($upload->save()) {
                $param['upload_image'] = $upload->getFileRealPath();
                $param['upload_image'] = $this->FastDfs->uploadByFilename($param['upload_image'],2,'G1');
                if ($param['upload_image']) {
                    $param['upload_image'] = $this->config['domain']['img'] . $param['upload_image'];
                    $upload->deleteFile($upload->getFileRealPath());
                    $ret = $momApplyData->addMomApply($param);
                    if ($ret) {
                        return $this->uniteReturnResult(HttpStatus::SUCCESS, array('apply_tip' => '提交成功！资料将在48小时内审核，可在个人中心我的礼包查看审核状态'));
                    }
                }
            }
            return $this->uniteReturnResult(HttpStatus::FAILED);

        }
        return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
    }

    /**
     * 妈妈礼包列表
     *
     * @param array $param
     * @return \array[]
     */
    public function getMomGiftList($param)
    {
        if (!isset($param['user_id']) || !ctype_digit($param['user_id']) || (!isset($param['platform']) && $param['platform'] != 'app')) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        //妈妈申请状态
        $momApplyData = BaiyangMomApplyData::getInstance();
        $momApplyState = $momApplyData->getMomApplyState($param['user_id']);
        $data = $momApplyData->getMomApplyStateTip($momApplyState);
        //最新五条使用报告
        $momGiftReportData = BaiyangMomGiftReportData::getInstance();
        $newestFiveMomTrailReportId = $momGiftReportData->getNewestMomTrailStrReportId(5);
        $data['report_list'] = array();
        if ($newestFiveMomTrailReportId) {
            $momGiftNewestFiveReportList = $momGiftReportData->getNewestMomTrailReportList($newestFiveMomTrailReportId);
            if ($momGiftNewestFiveReportList) {
                foreach ($momGiftNewestFiveReportList as $report) {
                    $report['add_time'] = date('Y-m-d', $report['add_time']);
                    $report['images'] = explode(';', $report['images']);
                    $report['default_image'] = $report['images'][0];
                    if (empty($report['nickname'])) {
                        $report['nickname'] = Func::getInstance()->getNickName($report['phone']);
                    }
                    unset($report['images'], $report['phone']);
                    $data['report_list'][] = $report;
                }
            }
            unset($momGiftNewestFiveReportList, $newestFiveMomTrailReportId);
        }
        //礼包列表
        $momGiftActivityData = BaiyangMomGiftActivityData::getInstance();
        $momGiftList = $momGiftActivityData->getMomGiftList();
        $result = $this->momGiftGetAndReportList($momApplyState, $momGiftList);
        $data['gift_list'] = $result['gift_list'];
        if ($data['apply_state'] == 3) {
            $data['apply_state'] = $result['apply_state'];
            $data['apply_tip'] = $result['apply_tip'];
        }
        unset($result, $momApplyState, $momGiftList);
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $data);
    }

    /**
     * 妈妈礼包领取和报告情况列表
     *
     * @param array $momApplyState 妈妈申请状态
     * @param array $momGiftList 妈妈礼包列表
     * @return mixed
     */
    private function momGiftGetAndReportList($momApplyState, $momGiftList)
    {
        $userGetEachGiftStateList = array();
        $userHasReportGiftList = array();
        $babyBirthTime = 0;
        $momApplyData = BaiyangMomApplyData::getInstance();
        if ($momApplyState && $momApplyState['state'] == 1) {
            $babyBirthTime = $momApplyData->getCorrectBabyBirthTime($momApplyState['birth_time']);
            //用户每个礼包领取情况
            $momGetGiftData = BaiyangMomGetGiftData::getInstance();
            $userGetEachGiftStateList = $momGetGiftData->getMomGetEachGiftStateList($momApplyState['user_id'], 'gift_id');
            //用户已经填写试用报告的礼包列表
            $momGiftReportData = BaiyangMomGiftReportData::getInstance();
            $userHasReportGiftList = $momGiftReportData->getUserHadReportGiftList($momApplyState['user_id'], 'gift_id');
        }
        $nowTime = time();
        //按年月妈妈申请的数量
        $momYearMonthApplyList = $momApplyData->getMomYearMonthApplyList();
        //get_state 1:待领取、2：不能领取、3：已领取、4：错过领取、5：xx时间可领取、6：已领取，并可填写体验报告、 7已经填写报告
        $allowGetNumber = $futureAllowGetNumber = 0;
        $getGiftTimeTip = '';
        foreach ($momGiftList as $key => $gift) {
            $ageGroup = explode('-', $gift['age_group']);
            $momGiftList[$key]['default_image'] = $gift['gift_image'];
            $momGiftList[$key]['get_number'] = 0;
            $momGiftList[$key]['get_tip'] = '';
            $momGiftList[$key]['get_number'] = 0;
            $pregnant = ($gift['pregnant'] == 1) ? 1 : 2;
            //妈妈礼包领取数量
            foreach ($momYearMonthApplyList as $mom) {
                $yearMonthTime = strtotime($mom['birth_date']);
                //默认怀孕10个月
                list($startTime, $endTime) = BaiyangMomGiftActivityData::getInstance()->getGiftStartEndTime($ageGroup, $yearMonthTime, $pregnant);
                if ($gift['pregnant'] == 1) {
                    if ($nowTime >= $endTime) {
                        $momGiftList[$key]['get_number'] += $mom['birth_number'];
                    }
                } else {
                    if ($startTime <= $nowTime) {
                        $momGiftList[$key]['get_number'] += $mom['birth_number'];
                    }
                }
            }
            //用户礼包领取状态
            if (isset($userGetEachGiftStateList[$gift['gift_id']])) {
                if ($userGetEachGiftStateList[$gift['gift_id']]['position'] == 2) {
                    $momGiftList[$key]['get_state'] = isset($userHasReportGiftList[$gift['gift_id']]) ? 7 : 6;
                } else {
                    $momGiftList[$key]['get_state'] = 3;
                }
            } elseif ($babyBirthTime) {
                // 默认怀孕10个月
                list($startTime, $endTime) = BaiyangMomGiftActivityData::getInstance()->getGiftStartEndTime($ageGroup, $babyBirthTime, $pregnant);
                if ($nowTime <= $startTime) {
                    $getDate = date('Y-m-d', $startTime);
                    $futureAllowGetNumber++;
                    $momGiftList[$key]['get_state'] = 5;
                    if (!$getGiftTimeTip) {
                        $getGiftTimeTip = $getDate;
                    }
                    $momGiftList[$key]['get_tip'] = $getDate . '可领取';
                } else {
                    if ($nowTime >= $endTime) {
                        $momGiftList[$key]['get_state'] = 4;
                        $momGiftList[$key]['get_tip'] = '错过了';
                    } else {
                        $momGiftList[$key]['get_state'] = 1;
                        $momGiftList[$key]['get_tip'] = '点击领取';
                        $allowGetNumber++;
                    }
                }
            } else {
                $momGiftList[$key]['get_state'] = 2;
            }
            unset($momGiftList[$key]['pregnant'], $momGiftList[$key]['age_group'], $momGiftList[$key]['gift_image']);
        }
        $data['gift_list'] = $momGiftList;
        if ($babyBirthTime) {
            if ($allowGetNumber) {
                $data['apply_state'] = 5;  //可领取
                $data['apply_tip'] = $allowGetNumber;
            } elseif ($futureAllowGetNumber) {
                $data['apply_state'] = 6; //待领取
                $data['apply_tip'] = $getGiftTimeTip;
            } else {
                $data['apply_state'] = 7; //领取完
                $data['apply_tip'] = '';
            }
        }
        return $data;
    }

    /**
     * 礼包详情
     *
     * @param array $param
     * @return \array[]
     */
    public function getMomGiftDetail($param)
    {
        if (!isset($param['user_id']) || !ctype_digit($param['user_id']) || !isset($param['platform']) || $param['platform'] != 'app'
            || !isset($param['gift_id']) || !ctype_digit($param['gift_id'])
        ) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $momApplyData = BaiyangMomApplyData::getInstance();
        $momApplyState = $momApplyData->getMomApplyState($param['user_id']);
        $momGiftActivityData = BaiyangMomGiftActivityData::getInstance();
        $momGiftDetail = $momGiftActivityData->getMomGiftByGiftId($param['gift_id']);
        if ($momGiftDetail) {
            $momHadGetSingleGift = array();
            if ($momApplyState && $momApplyState['state'] == 1) {
                //get_state 1:待领取、2：不能领取、3：已领取、4：错过领取、5：xx时间可领取、6：已领取，并可填写体验报告、 7已经填写报告
                $momHadGetSingleGift = BaiyangMomGetGiftData::getInstance()->getMomHadGetSingleGift($param['user_id'], $param['gift_id']);
                if ($momHadGetSingleGift) {
                    $momGiftDetail['apply_state'] = 7;
                } else {
                    $momGiftDetail['apply_state'] = 5;
                }
                $momHadGetSingleGift = $momApplyData->relationArray($momHadGetSingleGift, 'goods_id');
            }
            //todo app 广告表，合并要修改
            $adList = BaiyangAdData::getInstance()->getAdByNameList('百万孕妈广告位', 20);
            $giftAppCouponList = array();
            if ($momGiftDetail['binding_coupon']) {
                $momGiftDetail['binding_coupon'] = json_decode($momGiftDetail['binding_coupon'], true);
                $strCouponId = implode(',', $momGiftDetail['binding_coupon']);
                $giftAppCouponList = $this->giftDetailCouponList($param['user_id'], $strCouponId);
            }
            //todo 关联的商品
            $bindGiftGoodsList = json_decode($momGiftDetail['binding_gift'], true);
            $subjectList = array();
            $subjectIndex = 0;
            if ($bindGiftGoodsList) {
                foreach ($bindGiftGoodsList as $key => $gift) {
                    if (trim($gift['category_name']) == "0元免费领") {
                        $subjectList[$subjectIndex]['description'] = "把您想要的礼品加入购物车 每人仅限一次哦";
                    }
                    if (trim($gift['category_name']) == "自选区") {
                        $subjectList[$subjectIndex]['description'] = "需要跟0元免费专区的商品一起拍下，每人一次机会哦";
                    }
                    $subjectList[$subjectIndex]['subject_name'] = $gift['category_name'];
                    $goodsTagPriceList = array();
                    if ($gift['product_list']) {
                        $strGoodsId = implode(',', $gift['product_list']);
                        if ($momApplyState && $momApplyState['state'] == 1) {
                            $goodsTagPriceList = BaiyangGoodsPrice::getInstance()->getUserTagPriceList($param['user_id'], $strGoodsId);
                        }
                        $func = Func::getInstance();
                        $goodsStockList = $func->getCanSaleStock(array('goods_id'=>$strGoodsId, 'platform'=>'app'));
                        $goodsList = BaiyangGoods::getInstance()->getGoodsList($strGoodsId, $this->momTagId);
                        if ($goodsList) {
                            foreach ($goodsList as $k => $goods) {
                                //get_state 1:待领取、2：不能领取、3：已领取
                                if (isset($momHadGetSingleGift[$goods['goods_id']])) {
                                    $goods['get_state'] = 3;
                                } else {
                                    if (isset($goodsTagPriceList[$goods['goods_id']])) {
                                        $goods['get_state'] = 1;
                                    } else {
                                        $goods['get_state'] = 2;
                                    }
                                }
                                $goods['stock'] = $goodsStockList[$goods['goods_id']] ?? 0;
                                $goods['stock_out'] = $goods['stock'] ? 0 : 1;
                                $subjectList[$subjectIndex]['goods_list'][] = array(
                                    'goods_id' => $goods['goods_id'],
                                    'goods_name' => $goods['goods_name'],
                                    'goods_image' => $goods['goods_image'],
                                    'market_price' => $goods['market_price'],
                                    'price' => $goods['price'],
                                    'stock' => $goods['stock'],
                                    'stock_out' => $goods['stock_out'],
                                    'comment_number' => $goods['comment_number'],
                                    'get_state' => $goods['get_state']
                                );
                                unset($goods);
                            }
                        }
                    }
                    $subjectIndex++;
                }
            }
            $momApplyState = $momApplyData->getMomApplyStateTip($momApplyState);
            $momApplyState['apply_state'] = $momGiftDetail['apply_state'] ?? $momApplyState['apply_state'];
            $data = array(
                'gift_id' => $momGiftDetail['gift_id'],
                'title' => $momGiftDetail['title'],
                'content' => $momGiftDetail['content'],
                'default_image' => $momGiftDetail['default_image'],
                'apply_state' => $momApplyState['apply_state']
            );
            $data['ad_list'] = $adList;
            $data['coupon_list'] = $giftAppCouponList;
            $data['subject_list'] = $subjectList;
            unset($momApplyState, $momGiftDetail, $adList, $giftAppCouponList, $subjectList);
            return $this->uniteReturnResult(HttpStatus::SUCCESS, $data);
        } else {
            return $this->uniteReturnResult(HttpStatus::NOT_FOUND);
        }
    }

    /**
     * 获取礼包详情优惠券列表
     *
     * @param string $phone 手机号码
     * @param string $strCouponId 优惠券id列
     */
    private function giftDetailCouponList($userId, $strCouponId)
    {
        $couponList = BaiyangCouponData::getInstance()->getCouponListByCouponId($strCouponId);
        if ($couponList) {
            $momHadCouponList = BaiyangCouponData::getInstance()->getUserHadCouponList($userId, $strCouponId);
            foreach ($couponList as $key => $coupon) {
                $userCountCouponQty = 0;
                if (isset($momHadCouponList[$coupon['coupon_sn']])) {
                    // 统计用户已领取优惠券张数
                    $userCountCouponQty = BaiyangCouponData::getInstance()->getUserCountCouponList($userId, $momHadCouponList[$coupon['coupon_sn']]['coupon_sn']);
                    $coupon['start_provide_time'] = date('Y-m-d H:i:s', $momHadCouponList[$coupon['coupon_sn']]['start_use_time']);
                    $coupon['end_provide_time'] = '有效期至' . date('Y-m-d', $momHadCouponList[$coupon['coupon_sn']]['end_use_time']);
                } else {
                    if ($coupon['relative_validity']) {
                        $coupon['start_provide_time'] = date('Y-m-d H:i:s', time());
                        $coupon['end_provide_time'] = '有效期自领取' . $coupon['relative_validity'] . '天有效';
                    } else {
                        $coupon['start_provide_time'] = date('Y-m-d H:i:s', $coupon['start_provide_time']);
                        $coupon['end_provide_time'] = '有效期至' . date('Y-m-d', $coupon['end_provide_time']);
                    }
                }
                $limit_number = intval($coupon['limit_number']);
                $coupon['is_over'] = ($coupon['bring_number'] == $coupon['coupon_number'] && $coupon['coupon_number']) ? 1 : 0;
                $couponList[$key] = array(
                    'coupon_id'   => $coupon['coupon_sn'],
                    'coupon_name' => $coupon['coupon_name'],
                    'description' => $coupon['coupon_description'],
                    'coupon_type' => $coupon['coupon_type'],
                    'amount'      => (string)$coupon['coupon_value'],
                    'start_time'  => $coupon['start_provide_time'],
                    'end_time'    => $coupon['end_provide_time'],
                    'get_state'   => ($limit_number>0 && ($limit_number-$userCountCouponQty) > 0 ) ? 0 : 1,
                    'is_over'     => $coupon['is_over'],
                    'coupon_qty'  => ($userCountCouponQty > 0 && $limit_number > 0) ? $limit_number-$userCountCouponQty : $limit_number,
                );
            }
        }
        return $couponList;
    }

    /**
     * 获取妈妈已经购买领取，待填写使用报告的商品列表
     *
     * @param array $param
     * @return \array[]
     */
    public function waitReportGiftGoodsList($param)
    {
        if (!isset($param['user_id']) || !ctype_digit($param['user_id']) || !isset($param['platform']) || $param['platform'] != 'app'
            || !isset($param['gift_id']) || !ctype_digit($param['gift_id'])) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $giftTagNameList = BaiyangMomGiftActivityData::getInstance()->getGiftTagNameByGiftId($param['gift_id'], true);
        $momHadGetGiftGoodsList = BaiyangMomGetGiftData::getInstance()->getMomHadGetGiftGoodsList($param['user_id'], $param['gift_id']);
        if ($momHadGetGiftGoodsList && !$momHadGetGiftGoodsList[0]['report_id']) {
            foreach ($momHadGetGiftGoodsList as $key => $goods) {
                unset($momHadGetGiftGoodsList[$key]['report_id']);
            }
            $data = array(
                'gift_id' => $param['gift_id'],
                'tag_name_list' => $giftTagNameList,
                'goods_list' => $momHadGetGiftGoodsList
            );
            return $this->uniteReturnResult(HttpStatus::SUCCESS, $data);
        } else {
            return $this->uniteReturnResult(HttpStatus::NO_DATA, $param);
        }
    }

    /**
     * 获取所有礼包列表
     *
     * @return \array[]
     */
    public function getGiftTitleList($param)
    {
        if (!isset($param['user_id']) || !ctype_digit($param['user_id']) || !isset($param['platform']) || $param['platform'] != 'app') {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $allGiftTitleList = BaiyangMomGiftActivityData::getInstance()->getAllGiftTitleList();
        if ($allGiftTitleList) {
            return $this->uniteReturnResult(HttpStatus::SUCCESS, array('gift_title_list' => $allGiftTitleList));
        }
        return $this->uniteReturnResult(HttpStatus::NO_DATA);

    }

    /**
     * 礼包报告首页列表
     *
     * @param array $param
     */
    public function getGiftReportHomeList($param)
    {
        if (!isset($param['user_id']) || !ctype_digit($param['user_id']) || !isset($param['platform']) || $param['platform'] != 'app') {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $data = array(
            'apply_state' => 0,
            'good_report_list' => array(),
            'goods_report_list' => array(),
            'report_list' => array()
        );
        $momApplyState = BaiyangMomApplyData::getInstance()->getMomApplyState($param['user_id']);
        $momApplyState = BaiyangMomApplyData::getInstance()->getMomApplyStateTip($momApplyState);
        $data['apply_state'] = $momApplyState['apply_state'];
        unset($momApplyState);
        $momGiftReportData = BaiyangMomGiftReportData::getInstance();
        //精品报告
        $giftBestReportList = $momGiftReportData->getGiftReportLimitList(0, 0, 3, true);
        if ($giftBestReportList) {
            $data['good_report_list'] = $this->giftReportListFormat($giftBestReportList);
            unset($giftBestReportList);
        }
        //非精品报告
        $giftReportList = $momGiftReportData->getGiftReportLimitList(0, 0, 3, false);
        if ($giftReportList) {
            $data['report_list'] = $this->giftReportListFormat($giftReportList, 'is_good');
            unset($giftReportList);
        }
        //礼包商品报告列表
        $giftGoodsReportIdList = $momGiftReportData->getGiftGoodsReportIdLimit(3);
        if ($giftGoodsReportIdList) {
            $strReportId = '';
            foreach ($giftGoodsReportIdList as $report) {
                $strReportId .= "{$report['id']},";
            }
            $strReportId = trim($strReportId, ',');
            $giftGoodsReportList = $momGiftReportData->getGiftGoodsReportByIdList($strReportId);
            $giftGoodsReportList = $this->giftReportListFormat($giftGoodsReportList, 'is_good');
            $data['goods_report_list'] = $giftGoodsReportList;
            unset($strReportId, $giftGoodsReportIdList, $giftGoodsReportList);
        }
        $data['ad_list'] = BaiyangAdData::getInstance()->getAdByNameList('体验报告首页广告位', 1);
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $data);
    }

    /**
     * 报告数据格式化
     *
     * @param array $giftReportList
     * @return array
     */
    private function giftReportListFormat(array $giftReportList, $deleteKey=null)
    {
        foreach ($giftReportList as $key => $report) {
            $report['add_time'] = date('Y-m-d', $report['add_time']);
            $report['image_list'] = explode(';', $report['images']);
            if (empty($report['nickname'])) {
                $report['nickname'] = Func::getInstance()->getNickName($report['phone']);
            }
            if (!is_null($deleteKey) || isset($report[$deleteKey])) {
                unset($report[$deleteKey]);
            }
            unset($report['images'], $report['user_id'], $report['phone']);
            $giftReportList[$key] = $report;
        }
        return $giftReportList;
    }

    /**
     * 获取所有或单个礼包报告列表
     *
     * @param array $param
     * @return \array[]
     */
    public function getGiftReportList($param)
    {
        if (!isset($param['user_id']) || !isset($param['platform']) || $param['platform'] != 'app' || !isset($param['class'])) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        if (!isset($param['size']) || $param['size']<1)
        {
            $param['size'] = 10;
        }
        if (!isset($param['page']) || $param['page']<1)
        {
            $param['page'] = 1;
        }
        $param['page'] = ($param['page']-1) * $param['size'];
        $param['gift_id'] = (isset($param['gift_id']) && $param['class'] === '2') ? intval($param['gift_id']) : 0;
        $momGiftReportData = BaiyangMomGiftReportData::getInstance();
        if ($param['class'] === '1') {
            $reportCount = $momGiftReportData->getBestGiftReportCount();
            $isGood = true;
            $param['gift_id'] = 0;
        } else {
            $reportCount = $momGiftReportData->getGiftReportCount($param['gift_id']);
            $isGood = false;
        }
        $momApplyState = BaiyangMomApplyData::getInstance()->getMomApplyState($param['user_id']);
        $momApplyState = BaiyangMomApplyData::getInstance()->getMomApplyStateTip($momApplyState);
        if ($reportCount) {
            $momGiftReportList = $momGiftReportData->getGiftReportLimitList($param['gift_id'], $param['page'], $param['size'], $isGood);
            $momGiftReportList = $this->giftReportListFormat($momGiftReportList);
            return $this->uniteReturnResult(HttpStatus::SUCCESS, array(
                'apply_state' => $momApplyState['apply_state'],
                'total' => $reportCount,
                'report_list' => $momGiftReportList
            ));
        }
        $param['apply_state'] = $momApplyState['apply_state'];
        $param['total'] = $reportCount;
        return $this->uniteReturnResult(HttpStatus::NO_DATA, $param);
    }

    /**
     * 获取礼包报告详情
     *
     * @param array $param
     * @return \array[]
     */
    public function getGiftReportDetail($param)
    {
        if (!isset($param['user_id']) || !isset($param['platform']) || $param['platform'] != 'app'
            || !isset($param['report_id']) || !ctype_digit($param['report_id']) || !$param['report_id']) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $momGiftReportData = BaiyangMomGiftReportData::getInstance();
        $giftReportDetail = $momGiftReportData->getGiftReportDetailByReportId($param['report_id']);
        if ($giftReportDetail) {
            $giftRelationGoods = BaiyangMomGiftActivityData::getInstance()->getGiftRelationGoodsList($giftReportDetail[0]['gift_id']);
            $relationGoodsIdList = array();
            if ($giftRelationGoods && $giftRelationGoods['relation_goods_id']) {
                $relationGoodsIdList = json_decode($giftRelationGoods['relation_goods_id'], true);
            }
            $data = array();
            $strGoodsId = '';
            foreach ($giftReportDetail as $report) {
                if (!isset($data['report_id'])) {
                    $data = array(
                        'report_id' => $report['id'],
                        'title' => $report['title'],
                        'content' => $report['content'],
                        'star' => $report['star'],
                        'image_list' => explode(';', $report['images']),
                        'add_time' => date('Y-m-d', $report['add_time']),
                        'tag_name_list' => explode(';', $report['tag_name']),
                        'headimgurl' => $report['headimgurl'],
                        'nickname' => $report['nickname'],
                        'is_good' => $report['is_good'],
                        'goods_report_list' => array()
                    );
                }
                //一些旧数据存在没有商品报告
                if ($report['goods_id']) {
                    $data['goods_report_list'][] = array(
                        'goods_id' => $report['goods_id'],
                        'goods_name' => $report['goods_name'],
                        'content' => $report['g_content'],
                        'image_list' => explode(';', $report['g_images']),
                    );
                    if (isset($relationGoodsIdList[$report['goods_id']])) {
                        $strGoodsId .= "{$relationGoodsIdList[$report['goods_id']]},";
                    }
                } else {
                    break;
                }
            }
            unset($giftReportDetail, $report);
            if($strGoodsId) {
                $strGoodsId = trim($strGoodsId, ',');
                $data['goods_list'] = $this->getGoodsPrice($param['user_id'], $strGoodsId);
            }
            return $this->uniteReturnResult(HttpStatus::SUCCESS, $data);
        }
        return $this->uniteReturnResult(HttpStatus::NO_DATA);
    }

    /**
     * 礼包商品报告详情
     *
     * @param $param
     * @return \array[]
     */
    public function getGiftGoodsReportDetail($param)
    {
        if (!isset($param['user_id']) || !isset($param['platform']) || $param['platform'] != 'app'
            || !isset($param['report_id']) || !$param['report_id'] || !isset($param['goods_id'])) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $momGiftReportData = BaiyangMomGiftReportData::getInstance();
        $giftGoodsReportDetail = $momGiftReportData->getGiftGoodsReportDetail($param['report_id'], $param['goods_id']);
        if ($giftGoodsReportDetail) {
            $giftGoodsReportDetail['add_time'] = date('Y-m-d', $giftGoodsReportDetail['add_time']);
            $giftGoodsReportDetail['image_list'] = explode(';', $giftGoodsReportDetail['images']);
            $giftGoodsReportDetail['goods_list'] = array();
            $giftRelationGoods = BaiyangMomGiftActivityData::getInstance()->getGiftRelationGoodsList($giftGoodsReportDetail['gift_id']);
            if ($giftRelationGoods && $giftRelationGoods['relation_goods_id']) {
                $relationGoodsIdList = json_decode($giftRelationGoods['relation_goods_id'], true);
                if (isset($relationGoodsIdList[$giftGoodsReportDetail['goods_id']])) {
                    $strGoodsId = $relationGoodsIdList[$giftGoodsReportDetail['goods_id']];
                    $strGoodsId = trim($strGoodsId, ',');
                    $giftGoodsReportDetail['goods_list'] = $this->getGoodsPrice($param['user_id'], $strGoodsId);
                }
            }
            unset($giftGoodsReportDetail['images'], $giftGoodsReportDetail['gift_id'], $giftRelationGoods);
            return $this->uniteReturnResult(HttpStatus::SUCCESS, $giftGoodsReportDetail);
        }
        return $this->uniteReturnResult(HttpStatus::NO_DATA, $param);
    }

    /**
     * 获取礼包商品报告列表
     *
     * @param array $param
     * @return \array[]
     */
    public function getGiftGoodsReportList($param)
    {
        if (!isset($param['user_id']) || !ctype_digit($param['user_id']) || !isset($param['platform']) || $param['platform'] != 'app') {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        if (!isset($param['size']) || $param['size']<1)
        {
            $param['size'] = 10;
        }
        if (!isset($param['page']) || $param['page']<1)
        {
            $param['page'] = 1;
        }
        $param['page'] = ($param['page'] - 1) * $param['size'];
        $momGiftReportData = BaiyangMomGiftReportData::getInstance();
        $goodsReportCount = $momGiftReportData->getGiftGoodsReportCount();
        if ($goodsReportCount) {
            $momGiftGoodsReportList = $momGiftReportData->getGiftGoodsReportLimitList($param['page'], $param['size']);
            $momGiftGoodsReportList = $this->giftReportListFormat($momGiftGoodsReportList);
            $momApplyState = BaiyangMomApplyData::getInstance()->getMomApplyState($param['user_id']);
            $momApplyState = BaiyangMomApplyData::getInstance()->getMomApplyStateTip($momApplyState);
            return $this->uniteReturnResult(HttpStatus::SUCCESS, array(
                'apply_state' => $momApplyState['apply_state'],
                'total' => $goodsReportCount,
                'goods_report_list' => $momGiftGoodsReportList
            ));
        }
        return $this->uniteReturnResult(HttpStatus::NO_DATA, $param);
    }

    /**
     * 添加辣妈报告
     *
     * @param array $param
     * @return \array[]
     */
    public function addGiftReport($param)
    {
        if (!isset($param['user_id']) || !isset($param['platform']) || $param['platform'] != 'app') {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        //todo 验证不完全
        $isValid = $this->checkGiftReportData($param);
        if (!$isValid) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        //标签比较
        $momGiftActivityData = BaiyangMomGiftActivityData::getInstance();
        $giftTagNameList = $momGiftActivityData->getSingleGiftTagNameList($param['gift_id']);
        if ($giftTagNameList) {
            $giftTagNameList = explode(';', $giftTagNameList['tag_name']);
            foreach ($param['tag_name_list'] as $tagName) {
                if (!in_array($tagName, $giftTagNameList)) {
                    return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
                }
            }
        }
        $giftHadGetAndReport = BaiyangMomGetGiftData::getInstance()->getGiftHadGetAndReport($param['user_id'], $param['gift_id']);
        if ($giftHadGetAndReport) {
            if ($giftHadGetAndReport['report_id']) {
                return $this->uniteReturnResult(HttpStatus::REPORT_CANNOT_REPEAT);
            }
            $param = $this->uploadReportImage($param);
            if (!$param) {
                return $this->uniteReturnResult(HttpStatus::FAILED);
            }
            $param['add_time'] = time();
            $param['tmpImageList'] = implode(';', $param['tmpImageList']);
            $param['tag_name'] = implode(';', $param['tag_name_list']);
            $momGiftReportData = BaiyangMomGiftReportData::getInstance();
            $reportId = $momGiftReportData->addGiftReport($param);
            if ($reportId) {
                foreach ($param['tmpGoodsImageList'] as $key => $imageList) {
                    $param['tmpGoodsImageList'][$key] = implode(';', $imageList);
                }
                $reportId = $momGiftReportData->addGiftGoodsReport($reportId, $param);
                if ($reportId) {
                    return $this->uniteReturnResult(HttpStatus::SUCCESS);
                } else {
                    return $this->uniteReturnResult(HttpStatus::FAILED);
                }
            }
            return $this->uniteReturnResult(HttpStatus::FAILED);
        }
        return $this->uniteReturnResult(HttpStatus::GIFT_NOT_GET);
    }

    /**
     * 辣妈数据验证
     *
     * @param array $param
     * @return bool
     */
    public function checkGiftReportData($param)
    {
        if (!isset($param['gift_id']) || !ctype_digit($param['gift_id'])) {
            return false;
        }
        if (!isset($param['title'])) {
            return false;
        }
        if (!isset($param['content'])) {
            return false;
        }
        if (!isset($param['star'])) {
            return false;
        }
        if (!isset($param['image_list']) || !is_array($param['image_list'])) {
            return false;
        }
        if (!isset($param['tag_name_list']) || !is_array($param['tag_name_list']) ) {
            return false;
        }
        if (!isset($param['goods_id_list']) || !is_array($param['goods_id_list'])) {
            return false;
        }
        if (!isset($param['goods_content_list']) || !is_array($param['goods_content_list'])) {
            return false;
        }
        if (!isset($param['goods_image_list']) || !is_array($param['goods_image_list'])) {
            return false;
        }
        return true;
    }

    /**
     * 上传报告图片
     * @param array $param
     * @return bool
     */
    private function uploadReportImage($param)
    {
        $upload = new Base64Upload();
        foreach ($param['image_list'] as $key => $image) {
            $config = array(
                'base64File' => $image,
                'maxSize' => 2048, //KB
                'uploadPath' => '/tmp/mom_report',
                'fileName' => md5(uniqid($param['user_id'] . $param['gift_id'])) . '.jpg'
            );
            $upload->setConfig($config);
            $isSuccess = false;
            if ($upload->save()) {
                $image = $upload->getFileRealPath();
                $fastDfsFile = $this->FastDfs->uploadByFilename($image,2,'G1');
                if ($fastDfsFile) {
                    $isSuccess = true;
                    $param['tmpImageList'][] = $this->config['domain']['img'] . $fastDfsFile;
                }
                $upload->deleteFile($image);
            }
            if (!$isSuccess) {
                if (isset($param['tmpImageList'])) {
                    foreach ($param['tmpImageList'] as $val) {
                        $val = str_replace($this->config['domain']['img'], '', $val);
                        $this->FastDfs->deleteFile($val, 'G1');
                    }
                }
                return false;
            }
        }

        //商品图片
        foreach ($param['goods_image_list'] as $key => $imageList) {
            foreach ($imageList as $k => $image) {
                //上传证件图片
                $config = array(
                    'base64File' => $image,
                    'maxSize' => 2048, //KB
                    'uploadPath' => '/tmp/mom_goods_report',
                    'fileName' => md5(uniqid($param['user_id'] . $param['gift_id'])) . '.jpg'
                );
                $upload->setConfig($config);
                $isSuccess = false;
                if ($upload->save()) {
                    $image = $upload->getFileRealPath();
                    $fastDfsFile = $this->FastDfs->uploadByFilename($image,2,'G1');
                    if ($fastDfsFile) {
                        $isSuccess = true;
                        $param['tmpGoodsImageList'][$key][] = $this->config['domain']['img'] . $fastDfsFile;
                    }
                    $upload->deleteFile($image);
                }
                if (!$isSuccess) {
                    if (isset($param['tmpGoodsImageList'])) {
                        foreach ($param['tmpGoodsImageList'] as $tmpImageList) {
                            foreach ($tmpImageList as $tmp) {
                                $tmp = str_replace($this->config['domain']['img'], '', $tmp);
                                $this->FastDfs->deleteFile($tmp, 'G1');
                            }
                        }
                    }
                    return false;
                }
            }
        }
        unset($param['image_list'], $param['goods_image_list']);
        return $param;
    }

    /**
     * 获取商品标签列表
     *
     * @param $param
     * @return \array[]
     */
    public function getGoodsTagPriceList($param)
    {
        if (!isset($param['user_id']) || !ctype_digit($param['user_id']) || !isset($param['platform'])
            || !isset($param['goods_id_list']) || !is_array($param['goods_id_list'])) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $param['tag_id'] = isset($param['tag_id']) ? $param['tag_id'] : 1;
        $param['goods_id_list'] = array_filter($param['goods_id_list'], 'intval');
        if (empty($param['goods_id_list'])) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $strGoodsId = implode(',', $param['goods_id_list']);
        $goodsPriceList = BaiyangGoodsPrice::getInstance()->getGoodsPriceList($param['user_id'], $strGoodsId, $param['tag_id']);
        if (!$goodsPriceList) {
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
        $goodsLowPriceList = array();
        foreach ($goodsPriceList as $item) {
            if (isset($goodsLowPriceList[$item['goods_id']])) {
                continue;
            }
            $goodsLowPriceList[$item['goods_id']] = $item;
        }
        unset($goodsPriceList);
        $momApplyData = BaiyangMomApplyData::getInstance();
        $momApplyState = $momApplyData->getMomApplyState($param['user_id']);
        if ($momApplyState && $momApplyState['state'] == 1) {
            $goodsTagPriceList = $this->userGoodsTagPriceList($param['user_id'], $strGoodsId, $momApplyState);
            $allowGetState = array(
                '1' => 1,
                '3' => 3,
                '6' => 6
            );
            if ($goodsTagPriceList) {
                foreach ($goodsTagPriceList as $key => $goods) {
                    if (isset($goodsLowPriceList[$goods['goods_id']])
                        && isset($allowGetState[$goods['get_state']])) {
                        if (bccomp($goodsLowPriceList[$goods['goods_id']]['price'], $goods['price'], 2) > 0) {
                            continue;
                        }
                    }
                    unset($goodsTagPriceList[$key]);
                }
                return $this->uniteReturnResult(HttpStatus::SUCCESS, $goodsTagPriceList);
            }
        }
        return $this->uniteReturnResult(HttpStatus::NO_DATA);
    }

    /**
     * 用户商品标签价列表
     *
     * @param int $userId 用户id
     * @param string $strGoodsId 商品列
     * @param array $momApplyState 妈妈申请状态(必须审核通过)
     * @return mixed
     */
    private function userGoodsTagPriceList($userId, $strGoodsId, $momApplyState)
    {
        //1孕中/2孕后
        if ($momApplyState['birth_time'] > (strtotime('+1 day', $momApplyState['birth_time']) - 1)) {
            $pregnant = 2;
        } else {
            $pregnant = 1;
        }
        $goodsPriceData = BaiyangGoodsPrice::getInstance();
        $goodsTagPriceList = $goodsPriceData->getGoodsMomTagPriceList($userId, $strGoodsId, $pregnant);
        if ($goodsTagPriceList) {
            $momGetGiftData = BaiyangMomGetGiftData::getInstance();
            $momGetEachGiftStateList = $momGetGiftData->getMomGetEachGiftStateList($userId, 'gift_id');
            $momGiftActivityData = BaiyangMomGiftActivityData::getInstance();
            $babyBirthTime = BaiyangMomApplyData::getInstance()->getCorrectBabyBirthTime($momApplyState['birth_time']);
            $nowTime = time();

            foreach ($goodsTagPriceList as $key => $goods) {
                if (isset($momGetEachGiftStateList[$goods['gift_id']])) {
                    if ($momGetEachGiftStateList[$goods['gift_id']]['position'] == 2) {
                        $goods['get_state'] = 6;
                    } else {
                        $goods['get_state'] = 3;
                    }
                } else {
                    $ageGroup = explode('-', $goods['age_group']);
                    list($startTime, $endTime) = $momGiftActivityData->getGiftStartEndTime($ageGroup, $babyBirthTime, $pregnant);
                    if ($nowTime <= $startTime) {
                        $goods['get_state'] = 5;
                    } else {
                        if ($nowTime > $endTime) {
                            $goods['get_state'] = 4;
                        } else {
                            $goods['get_state'] = 1;
                        }
                    }
                }
                unset($goods['age_group']);
                $goodsTagPriceList[$key] = $goods;
            }
        }
        return $goodsTagPriceList;
    }

    /**
     *按商品id获取商品最优价格
     *
     * @param int $user_id 用户自增id
     * @param $strGoodsId 商品id列表
     * @return array
     */
    private function getGoodsPrice($user_id,$strGoodsId)
    {
        $goodsList = $this->getGoodsDetail(array(
            'goods_id' => $strGoodsId,
            'platform' => 'app'
        ));
        $this->eventsManager->attach('baseListen', new BaseListen());
        $data = array();
        $temp = array();
        foreach ($goodsList as $key => $goods) {
            if (isset($temp[$goods['id']])) {
                continue;
            }
            $data[$key] = array(
                'goods_id' => $goods['id'],
                'goods_name' => $goods['name'],
                'goods_image' => $goods['goods_image'],
                'market_price' => $goods['sku_market_price'],
                'price' => $goods['sku_price']
            );
            $param = array(
                'goodsInfo' => array(
                    'goods_id' => $goods['id'],
                    'sku_price' => $goods['sku_price'],
                    'goods_number' => $goods['sku_stock']
                ),
                'platform' => 'app',
                'user_id' => $user_id,
                'is_temp' => 0
            );
            $temp[$goods['id']] = 1;
            $goods = $this->eventsManager->fire('baseListen:getGoodsDiscountPrice', $this, $param);
            $price = isset($goods['discountPromotion']) ? $goods['discountPromotion']['price'] : $goods['sku_price'];
            $data[$key]['price'] = $price;
        }
        return $data;
    }
}