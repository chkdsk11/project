<?php
/**
 * @author 邓永军
 */
namespace Shop\Home\Datas;

/**
 * Class O2OBaiyangCouponData
 * @package Shop\Home\Datas
 * @param
 *      - platform 【pc、app、wap】 平台标识
 */
class O2OBaiyangCouponData extends BaseData
{
    protected static $instance=null;

    /**
     *
     * @param $param
     *          注意参数
     *          - mode 1 进行中（默认 不需要添加） mode 2 未开始 mode 3 过期
     * @return array|bool
     */
    public function getCouponList($param,$goods_id = 0)
    {
        $data=[];
        $where='';
        if(!isset($param['mode']) || empty($param['mode'])){
            $where.='start_provide_time < :start_provide_time: AND end_provide_time > :end_provide_time: ';
        }else{
            if($param['mode'] == '1'){
                $where.='start_provide_time < :start_provide_time: AND end_provide_time > :end_provide_time: ';
            }elseif ($param['mode'] == '2'){
                $where.='start_provide_time > :start_provide_time:';
            }else{
                $where.='end_provide_time < :end_provide_time: ';
            }
        }
        $data['start_provide_time'] = time();
        $data['end_provide_time'] = time();
        $data[$param['platform'].'_platform'] = 1 ;
        $where.='AND '.$param['platform'].'_platform = :'.$param['platform'].'_platform: ';
        $data['is_cancel'] = 1 ;
        $where.=' AND is_cancel <> :is_cancel: ';
      if($param['drug_type'] == 1){
            $data["medicine_type_all"] = 'all';
            $data["medicine_type_rx"]='rx';
            $where.='AND (drug_type = :medicine_type_all: OR drug_type = :medicine_type_rx:) ';
        }else{
            $data["medicine_type_all"] = 'all';
            $data["medicine_type_rx"]='non_rx';
            $where.='AND (drug_type = :medicine_type_all: OR drug_type = :medicine_type_rx:) ';
        }

        if(!isset($param['provide_type']) || empty($param['provide_type'])){
            $data['provide_type'] = 1;
            $where.='AND provide_type = :provide_type: ';
        }
        if(isset($param['channel_id']) && !empty($param['channel_id'])){
            $data['channel_id'] = $param['channel_id'];
            $where.='AND ( channel_id = 0 OR channel_id = :channel_id: )  ' ;
        }
                       $where.='AND ( bring_number <= coupon_number OR coupon_number = 0 ) ' ;
        
                                       if(isset($param['user_id']) && !empty($param['user_id']) && ( !isset($param['is_temp']) || empty($param['is_temp']) || $param['is_temp'] == 0 )){
                                        if($goods_id != 0){
                                            $tagInfo = $this->getData([
                                                'table' => '\Shop\Models\BaiyangGoodsPrice as a',
                                                'column' => 'b.tag_id,a.goods_id,a.platform_'.$param['platform'],
                                                'bind' => [
                                                    'user_id' => $param['user_id'],
                                                    'goods_id' => $goods_id,
                                                    'platform' => 1
                                                ],
                                                'where' => 'where a.goods_id = :goods_id: AND b.user_id = :user_id: AND a.platform_'.$param['platform'].' = :platform:',
                                                'join' => 'LEFT JOIN \Shop\Models\BaiyangUserGoodsPriceTag as b on b.tag_id = a.tag_id'
                                            ],1);
                                            if(isset($tagInfo['tag_id']) && !empty($tagInfo['tag_id']) && isset($tagInfo['goods_id']) && !empty($tagInfo['goods_id']) &&isset($tagInfo['platform_'.$param['platform']]) && !empty($tagInfo['platform_'.$param['platform']]) && $tagInfo['platform_'.$param['platform']] == 1){
                                                $where.='AND ( goods_tag_id = 0 OR goods_tag_id = :goods_tag_id: )' ;
                                                //$where.='AND goods_tag_id = :goods_tag_id: ';
                                                //$data['goods_tag_id_no_sign'] = 0;
                                                $data['goods_tag_id'] = $tagInfo['tag_id'];
                                            }else{
                                                $where.='AND goods_tag_id = 0 ';
                                            }
                                        }

                                           switch ($param['is_new_user']) {
                                               case '1':
                                                   $where.='AND ( group_set = :all_member: OR group_set = :new_member: OR FIND_IN_SET( :phone: ,tels) )';
                                                   $data["all_member"] =0;
                                                   $data["new_member"] =1;
                                                   $data["phone"] =$param['phone'];
                                                   break;
                                               case '0':
                                                   $where.='AND ( group_set = :all_member: OR group_set = :old_member: OR FIND_IN_SET( :phone: ,tels) )';
                                                   $data["all_member"] =0;
                                                   $data["old_member"] =2;
                                                   $data["phone"] =$param['phone'];
                                                   break;
                                           }
                                       }
        $result=$this->getData([
            'column'=>'id,coupon_sn,coupon_name,coupon_description,coupon_value,coupon_number,limit_number,min_cost,start_use_time,end_use_time,start_provide_time,end_provide_time,start_use_time,end_use_time,validitytype,relative_validity,coupon_type,discount_unit,ban_join_rule,bring_number,use_range,brand_ids,category_ids,product_ids,goods_tag_id,pc_url,app_url,wap_url,wechat_url',
            'table'=>'\Shop\Models\BaiyangCoupon',
            'where'=>'where '.$where,
            'bind'=>$data
        ]);
        return $result;
    }

    /**
     * 按优惠券Id获取app优惠券列表
     *
     * @param string $strCouponId 优惠券id列
     * @return array|bool
     */
    public function getCouponListByCouponId($strCouponId)
    {
        $nowTime = time();
        $param = array(
            'table' => 'Shop\Models\BaiyangCoupon',
            'column' => 'coupon_sn,coupon_name,coupon_description,start_provide_time,
end_provide_time,coupon_value,min_cost,coupon_type,bring_number,coupon_number,relative_validity',
            'where' => "WHERE coupon_sn IN({$strCouponId}) AND (({$nowTime} BETWEEN start_provide_time AND end_provide_time AND relative_validity=0) OR relative_validity<>0) AND group_set = 0 AND is_cancel=0 AND app_platform=1",
            'order' => 'ORDER BY end_provide_time ASC'
        );
        $ret = $this->getData($param);
        return $ret;
    }

    /**
     * 按用户已经领取的app优惠券列表
     *
     * @param string $phone 手机号码
     * @param $strCouponId 优惠券id列
     * @return array|bool
     */
    public function getUserHadCouponList($userId, $strCouponId)
    {
        $param = array(
            'table' => 'Shop\Models\BaiyangCouponRecord',
            'column' => 'id,coupon_sn,start_use_time,end_use_time',
            'where' => "WHERE user_id = :user_id: AND coupon_sn IN({$strCouponId})",
            'bind' => array('user_id'=>$userId)
        );
        $ret = $this->getData($param);
        if ($ret) {
            $ret = $this->relationArray($ret, 'coupon_sn');
        }
        return $ret;
    }

    /**
     * @desc 判断品牌是否存在优惠券
     * @param $brand_id
     * @return bool|int
     * @author 邓永军
     */
    public function IsExistCouponInBrandData($brand_id)
    {
        $nowTime = time();
        $brand_arr = explode(',',$brand_id);
        $num = 0;
        foreach ($brand_arr as $new_brand_id){
            $param = [
                'table' => 'Shop\Models\BaiyangCoupon',
                'where' => 'WHERE ((FIND_IN_SET(:brand_ids:,brand_ids) OR use_range = :use_range:) AND ( :nowTime: BETWEEN start_provide_time AND end_provide_time ))',
                'bind' => [
                    'brand_ids' => $new_brand_id ,
                    'nowTime' => $nowTime ,
                    'use_range' => 'all'
                ]
            ];
            $num += $this->countData($param);
        }
        return $num;
    }
    /**
     * 根据优惠券编号返回单条优惠券信息
     * @param string couponSn 优惠券编号
     * @return array
     */
    public function getOneCouponInfoBySn($couponSn) {
        // 读写切换
        $this->switchRwDb('read');
        $param = array(
            'table' => 'Shop\Models\BaiyangCoupon',
            'column' => '*',
            'where' => "WHERE coupon_sn = :coupon_sn:",
            'bind' => array(
                'coupon_sn' => $couponSn
            )
        );
        $result = $this->getData($param, true);
        return $result;
    }

    /**
     * @desc 获取当前用户领取的所有优惠券
     * @param $user_id
     * @param $platform
     * @return mixed
     */
    public function getCurrentUserCoupon($user_id,$platform)
    {
        $UserAllCoupon = BaseData::getInstance()->getData([
            'column' => 'c.id as coupon_id,r.id as record_id,c.coupon_sn,c.coupon_name,c.coupon_description,c.coupon_value,c.coupon_number,c.limit_number,c.min_cost,c.start_use_time,c.end_use_time,c.start_provide_time,c.end_provide_time,c.start_use_time,c.end_use_time,c.validitytype,c.relative_validity,c.coupon_type,c.discount_unit,c.ban_join_rule,c.bring_number,c.use_range,c.brand_ids,c.category_ids,c.product_ids,c.goods_tag_id,c.pc_url,c.app_url,c.wap_url,c.wechat_url,r.add_time as opposite_add_time,c.drug_type',
            'table' => '\Shop\Models\BaiyangCouponRecord as r',
            'where' => 'where c.is_cancel = :is_cancel: AND r.user_id = :user_id: AND r.is_used = :is_used: AND c.'.$platform.'_platform = :platform:',
            'bind' => [
                'is_cancel' => 0,
                'user_id' => $user_id,
                'is_used' => 0,
                'platform' => 1
            ],
            'join' => 'LEFT JOIN \Shop\Models\BaiyangCoupon as c on r.coupon_sn = c.coupon_sn '
        ]);
        return $UserAllCoupon;
    }

    /**
     * @desc 绝对相对优惠券
     * @param $UserAllCoupon
     * @param $nowTime
     * @return array
     */
    public function getCurrentUserCouponInTime($UserAllCoupon,$nowTime)
    {
        $couponListInTime = [];
        if(!empty($UserAllCoupon)){
            foreach ($UserAllCoupon as $couponList){
                if ($couponList['validitytype'] == 1){
                    $over_time = $couponList['end_use_time'];
                }else{
                    $over_time = $couponList['opposite_add_time'] + $couponList['relative_validity'] * 86400;
                }
                if($over_time > $nowTime){
                    $couponListInTime[] = $couponList;
                }
            }
        }
        return $couponListInTime;
    }
}