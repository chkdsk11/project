<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2017/1/25
 * Time: 14:03
 */

namespace Shop\Home\Listens;

use Shop\Home\Datas\BaseData;
use Shop\Models\HttpStatus;
use Shop\Home\Datas\BaiyangO2oData;
use Shop\Home\Datas\BaiyangFreghtTemplate;

class O2OFreightListener extends BaseListen {

    /**
     * @desc  查看O2O配送信息
     * @param $param array
     *   - $consigneeInfo 收货地址信息
     * @return array
     * @author 柯琼远
     */
    public function getO2OExpressInfo($event, $class, $param) {
        $consigneeInfo = isset($param['consigneeInfo']) ? (array)$param['consigneeInfo'] : array();
        if (empty($consigneeInfo)) {
            return $class->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param'=> $param]);
        }
        $region_id = $consigneeInfo['county'];
        $o2oDataInstance = BaiyangO2oData::getInstance();
        $type = $o2oDataInstance->getO2OType($region_id);
        // if ($type == 0 || $this->func->isZitiAddress($consigneeInfo)) {
        if ($type == 0) {
            return $class->uniteReturnResult(HttpStatus::O2O_REGION_NOT_EXIST, ['param'=> $param]);
        }
        // 获取O2O运费模板
        $tempInfo = $o2oDataInstance->getO2OTemplate($region_id, $type);
        // 获取时间信息
        $timeList = $o2oDataInstance->getO2OTime();
        // o2o时间
        $o2oTime = array();
        // 午休时间
        $wuxiuInfo = array();
        foreach ($timeList as $val) {
            if ($val['type'] == $type) {
                $o2oTime = $val;
            }elseif ($val['type'] == 3) {
                $wuxiuInfo = $val;
            }
        }
        $now_time = time();
        if ($type == 1) {// 两小时达
            // 开始时间
            $begin_time = strtotime(date('Y-m-d ') . $o2oTime['begin_time']);
            // 结束时间
            $end_time = strtotime(date('Y-m-d ') . $o2oTime['end_time']);
            // 当前时间取整
            $nowInt = strtotime(date('Y-m-d H', $now_time).':0');
            $nowInt = $nowInt == $now_time ? $nowInt : $nowInt + 3600;
            // 开始时间取整
            $beginInt = strtotime(date('Y-m-d H', $begin_time).':0');
            $beginInt = $beginInt == $begin_time ? $beginInt : $beginInt + 3600;
            // 结束时间取整
            $endInt = strtotime(date('Y-m-d H', $end_time).':0');
            $endInt = $endInt == $end_time ? $endInt : $endInt + 3600;
            // 时间间隔
            $interval = $this->func->getConfigValue('o2o_interval');
            $interval = (int)$interval * 60;
            // 取整后最快配送时间
            $fastest_time = $this->func->getConfigValue('o2o_fastest_time');
            $fastest_time = (int)$fastest_time * 60;
            $wuxiu_begin_time = strtotime(date('Y-m-d ') . $wuxiuInfo['begin_time']);
            $wuxiu_end_time = strtotime(date('Y-m-d ') . $wuxiuInfo['end_time']);
            // 午休结束时间取整
            $wuxiuEndInt = strtotime(date('Y-m-d H', $wuxiu_end_time).':0');
            $wuxiuEndInt = $wuxiuEndInt == $wuxiu_end_time ? $wuxiuEndInt : $wuxiuEndInt + 3600;

            $data = array(
                'type' 		 => 1,
                'interval' 	 => $interval,
                'free_price' => $tempInfo['free_price'],
                'list' 		 => array(),
            );
            // 第一个配送时间的起始时间戳
            if ($begin_time > $now_time) {
                $for_begin_i = $beginInt + $fastest_time;
            } elseif ($end_time >= $now_time) {
                $for_begin_i = $nowInt + $fastest_time;
            }
            // 今天
            if ($end_time >= $now_time) {
                for ($i = $for_begin_i; $i <= $endInt + $fastest_time; $i += $interval) {
                    if ($i > (strtotime(date('Y-m-d')) + 86400)) {
                        break;
                    }
                    if (($i > $wuxiu_begin_time - 3600 && $i < $wuxiu_begin_time)
                        || ($i >= $wuxiu_begin_time && $i < $wuxiu_end_time)) {
                        $i = $wuxiuEndInt + $fastest_time - $interval;
                        continue;
                    }
                    $remark = date('Y-m-d H:i', $i).'—'.date('Y-m-d H:i', ($i + $interval) > (strtotime(date('Y-m-d')) + 86400) ? (strtotime(date('Y-m-d')) + 86400) : ($i + $interval));
                    $dt = bcdiv($i + $interval - $nowInt, 3600, 2);
                    if ($dt <= 2) {
                        $fee = $tempInfo['col_0'];
                    } elseif ($dt <= 4) {
                        $fee = $tempInfo['col_1'];
                    } elseif ($dt <= 6) {
                        $fee = $tempInfo['col_2'];
                    } elseif ($dt <= 8) {
                        $fee = $tempInfo['col_3'];
                    } else {
                        $fee = $tempInfo['col_4'];
                    }
                    $data['list'][] = array(
                        'remark' => $remark,
                        'time'   => $i,
                        'fee'    => $fee,
                    );
                }
            }
            // 明天
            $beginInt += 86400;
            $endInt += 86400;
            $wuxiu_begin_time += 86400;
            $wuxiu_end_time += 86400;
            $wuxiuEndInt += 86400;
            for ($i = $beginInt + $fastest_time; $i <= $endInt + $fastest_time; $i += $interval) {
                if ($i > (strtotime(date('Y-m-d')) + 86400 * 2)) {
                    break;
                }
                if (($i > ($wuxiu_begin_time - 3600) && $i < $wuxiu_begin_time)
                    || ($i >= $wuxiu_begin_time && $i < $wuxiu_end_time)) {
                    $i = $wuxiuEndInt + $fastest_time - $interval;
                    continue;
                }
                $remark = date('Y-m-d H:i', $i).'—'.date('Y-m-d H:i', ($i + $interval) > (strtotime(date('Y-m-d')) + 86400 * 2) ? (strtotime(date('Y-m-d')) + 86400 * 2) : ($i + $interval));
                $data['list'][] = array(
                    'remark' => $remark,
                    'time'   => $i,
                    'fee'    => $tempInfo['col_4'],
                );

            }
            if (empty($data['list'])) {
                return $class->uniteReturnResult(HttpStatus::NOT_O2O_EXPRESS_TIME, ['param'=> $param]);
            }
        } else {
            // 当日达
            $end_time = strtotime(date('Y-m-d ') . $o2oTime['end_time']);
            $data = array(
                'type' 		 => 2,
                'interval' 	 => 86400,
                'free_price' => $tempInfo['free_price'],
                'list' 		 => array(),
            );
            if ($end_time >= $now_time) {
                $data['list'][] = array(
                    'remark' => date('Y-m-d'),
                    'time'   => strtotime(date('Y-m-d')),
                    'fee'    => $tempInfo['col_0'],
                );
            }
            $data['list'][] = array(
                'remark' => date('Y-m-d', $now_time + 24 * 3600),
                'time'   => (strtotime(date('Y-m-d')) + 24 * 3600),
                'fee'    => $tempInfo['col_1'],
            );
        }
        return $class->uniteReturnResult(HttpStatus::SUCCESS, $data);
    }

    /**
     * @desc 获取O2O配送运费
     * @param array $param
     *       - time      int 配送时间段起始时间（时间戳）（*）
     *       - total      float  订单金额（*）
     *       - consigneeInfo array 收货地址信息
     * @return array
     * @author 柯琼远
     */
    public function getO2OExpressFee($event, $class, $param) {
        $time = isset($param['time']) ? (int)$param['time'] : 0;
        $total = isset($param['total']) ? (string)$param['total'] : 0;
        $consigneeInfo = isset($param['consigneeInfo']) ? (array)$param['consigneeInfo'] : array();
        if ($time < 1 || empty($consigneeInfo)) {
            return $class->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param'=> $param]);
        }
        $expressInfo = $this->getO2OExpressInfo($event, $class, $param);
        if ($expressInfo['status'] != HttpStatus::SUCCESS) {
            return $expressInfo;
        }
        $result = array();
        foreach ($expressInfo['data']['list'] as $key => $value) {
            if ($value['time'] == $time) {
                $result = array(
                    'type' 	   	 => $expressInfo['data']['type'],
                    'interval' 	 => $expressInfo['data']['interval'],
                    'remark' 	 => $value['remark'],
                    'time' 		 => $value['time'],
                    'fee' 		 => $expressInfo['data']['free_price'] > $total ? $value['fee'] : "0.00",
                );
                break;
            }
        }
        if (empty($result)) {
            return $class->uniteReturnResult(HttpStatus::O2O_EXPRESS_TIME_INVALID, ['param'=> $param]);
        }
        return $class->uniteReturnResult(HttpStatus::SUCCESS, $result);
    }

    /**
     * @desc 获取普通运费
     * @param array $param
     *       - goods_ids   string 商品ID字符串，用英文逗号隔开（*）
     *       - region_id   int    type=0 or 1时表示省ID，type=2时表示门店ID（*）
     *       - type        int    0-在线支付，1-货到付款，2-顾客自提，默认：0
     *       - total       float  订单总额（*）
     * @return array
     *       - freight     float  运费
     *       - tips        array  提示语需要的数据
     *              - free_price     float    包邮门槛
     *              - not_free_fee   float    不包邮运费
     *              - lack_price     float    还差多少钱包邮
     *              - promote_text   float    促销方案
     * @author 柯琼远
     */
    public function getFreightFee($event, $class, $param) {
        $goods_ids = isset($param['goods_ids']) ? (string)$param['goods_ids'] : '';
        $region_id = isset($param['region_id']) ? (int)$param['region_id'] : 0;
        $type = isset($param['type']) ? (int)$param['type'] : 0;
        $total = isset($param['total']) ? (float)$param['total'] : 0;
        if (empty($goods_ids) || $region_id < 0 || !in_array($type, array(0,1,2)) || $total < 0) {
            return $class->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param'=> $param]);
        }
        // 获取商品列表
        $idArr = explode(',', $goods_ids);
        foreach ($idArr as &$goods_id) $goods_id = (int)$goods_id;
        $goods_ids = implode(',', array_unique($idArr));
        $goodsList = BaseData::getInstance()->getData([
            'column' => 'id,pc_freight_temp_id as template_id,is_global',
            'table'  => '\Shop\Models\BaiyangGoods',
            'where'  => "where id in ({$goods_ids}) group by pc_freight_temp_id,is_global",
        ]);
        if (empty($goodsList)) {
            return $class->uniteReturnResult(HttpStatus::NOT_GOOD_INFO, ['param'=> $param]);
        }
        // 海外购只支持在线支付
        $is_global = $goodsList[0]['is_global'];
        if ($is_global) $type  = 0;
        // 获取商品对应的模板列表
        $templateInstance = BaiyangFreghtTemplate::getInstance();
        $defaultTemplate = $templateInstance->getFreightTemplate(0, $is_global);
        $templateList = array();
        foreach ($goodsList as $goodsInfo) {
            $temp = $defaultTemplate;
            if ($goodsInfo['template_id'] != 0) {
                $ret = $templateInstance->getFreightTemplate($goodsInfo['template_id'], $is_global);
                if (!empty($ret)) $temp = $ret;
            }
            $templateList[$temp['id']] = $temp;
        }
        // 获取各模板运费信息
        $list = array();
        foreach ($templateList as $key => $value) {
            $detailList = $templateInstance->getTemplateDetail($value['id'], $type);
            $defaultDetail = $detailInfo = array();
            foreach ($detailList as $k => $v) {
                if ($v['is_default'] == 0) {
                    if (strpos(','.$v['region_list'].',', ','.$region_id.',') !== false) {
                        $detailInfo = $v;
                    }
                } else {
                    $defaultDetail = $v;
                }
            }
            $list[] = empty($detailInfo) ? $defaultDetail : $detailInfo;
        }
        // 计算
        $carriage = 0;    // 实际运费
        $free_price = 0;  // 包邮金额
        $fee = 0;         // 不包邮时运费
        foreach ($list as $key => $value) {
            if ($total < $value['default_value']) {
                $carriage = $carriage > $value['default_fee'] ? $carriage : $value['default_fee'];
            }
            $free_price = $free_price > $value['default_value'] ? $free_price : $value['default_value'];
            $fee = $fee > $value['default_fee'] ? $fee : $value['default_fee'];
        }
        $lack_price = sprintf("%.2f", $carriage > 0 ? $free_price - $total : 0);
        $promote_text = $lack_price > 0 ? "再购买".$lack_price."元免邮" : '';
        $result = array(
            'freight' => sprintf("%.2f", $carriage),
            'tips'    => array(
                'free_price'   => sprintf("%.2f", $free_price),
                'not_free_fee' => sprintf("%.2f", $carriage > 0 ? $carriage : $fee),
                'lack_price'   => $lack_price,
                'promote_text' => $promote_text
            )
        );
        return $class->uniteReturnResult(HttpStatus::SUCCESS, $result);
    }

    /**
     * @desc O2O数据重构
     * @param array $param
     *       - o2oInfo
     * @return array
     * @author 柯琼远
     */
    public function remakeExpress($event, $class, $param) {
        $expressIds = [];
        // 极速配送
        if (isset($param['o2oInfo']) && !empty($param['o2oInfo'])) {
            $expressInfo = [
                'express_type' => $param['o2oInfo']['type'] == 1 ? 2 : 3,
                'express_name' => '极速配送',
                'o2o_tips'     => '崂山区的部分地区不支持当日送达',
                'prompt'       => '',
                'selected'     => $param['expressType'] > 1 ? 1 : 0,
                'dates'        => [],
            ];
            foreach ($param['o2oInfo']['list'] as $key => $value) {
                // 两小时达
                if ($param['o2oInfo']['type'] == 1) {
                    $begin_time = date('H:i', $value['time']);
                    $end_time = date('H:i', $value['time'] + $param['o2oInfo']['interval']);
                    if (isset($datesInfo) && $datesInfo['date'] == date('Y-m-d', $value['time'])) {
                        if ($value['selected']) {
                            $datesInfo['selected'] = 1;
                        }
                        $datesInfo['times'][] = [
                            'time' => $value['time'],
                            'time_slot' => $begin_time . '-' . $end_time,
                            'begin_time' => $begin_time,
                            'end_time' => $end_time,
                            'fee' => $value['fee'],
                            'selected' => $value['selected'],
                        ];
                    } else {
                        if (isset($datesInfo)) {
                            $expressInfo['dates'][] = $datesInfo;
                        }
                        $datesInfo = [
                            'date' => date('Y-m-d', $value['time']),
                            'day'  => date('Y-m-d') == date('Y-m-d', $value['time']) ? '今天' : '明天',
                            'selected' => $value['selected'] ? 1 : $value['selected'],
                            'times' => []
                        ];
                        $datesInfo['times'][] = [
                            'time' => $value['time'],
                            'time_slot' => $begin_time . '-' . $end_time,
                            'begin_time' => $begin_time,
                            'end_time' => $end_time,
                            'fee' => $value['fee'],
                            'selected' => $value['selected'],
                        ];
                    }
                    if ($value['selected'] == 1) {
                        $param['expressText'] = $datesInfo['date']."[{$datesInfo['day']}] ".$begin_time.'-'.$end_time;
                    }
                    if ($key >= count($param['o2oInfo']['list']) - 1) {
                        $expressInfo['dates'][] = $datesInfo;
                    }
                } else {
                    // 当日达
                    $date = date('Y-m-d', $value['time']);
                    $day = date('Y-m-d') == date('Y-m-d', $value['time']) ? '今天' : '明天';
                    $expressInfo['dates'][] = [
                        'date' => $date,
                        'day'  => $day,
                        'selected' => $value['selected'],
                        'times' => [
                            [
                                'time' => $value['time'],
                                'time_slot' => '',
                                'begin_time' => "",
                                'end_time' => "",
                                'fee' => $value['fee'],
                                'selected' => $value['selected'],
                            ]
                        ]
                    ];
                    if ($value['selected'] == 1) {
                        $param['expressText'] = $date."[{$day}]";
                    }
                }
            }
            $param['expressList'][] = $expressInfo;
            $expressIds[] = $expressInfo['express_type'];
        }
        // 普通配送
        $globalText = "快递将由诚仁堂选合作方送达";
        $baiyangText = "快递将由诚仁堂合作方送达";
        $param['expressList'][] = [
            'express_type' => 0,
            'express_name' => '普通配送',
            'o2o_tips'     => '',
            'prompt'       => $param['isGlobal'] == 1 ? $globalText : $baiyangText,
            'selected'     => $param['expressType'] <= 1 ? 1 : 0,
            'dates'        => [],
        ];
        $expressIds[] = 0;
        if ($param['expressType'] <= 1) {
            $param['expressText'] = $param['isGlobal'] == 1 ? $globalText : $baiyangText;
        }
        // 在线支付
        $param['paymentList'][] = [
            'payment_id' => 0,
            'payment_name' => "在线支付",
            'support_express' => $expressIds,
            'selected' => $param['paymentId'] == 0 ? 1 : 0,
        ];
        // 货到付款
        if (!$param['isGlobal'] && $param['expressType'] != 1) {
            $param['paymentList'][] = [
                'payment_id' => 3,
                'payment_name' => "货到付款",
                'support_express' => $param['facePayIfO2o'] ? $expressIds : [0],
                'selected' => $param['paymentId'] == 3 ? 1 : 0,
            ];
        }

        return $param;
    }
}