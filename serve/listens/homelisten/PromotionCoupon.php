<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/10/12 0012
 * Time: 上午 11:16
 */
namespace Shop\Home\Listens;
use Shop\Home\Datas\BaiyangCouponData;
use Shop\Models\BaiyangPromotionEnum;

class PromotionCoupon extends BaseListen
{

    /**
     * @desc 优惠券Listener
     * @param $event
     * @param $class
     * @param $data
     *         - goods_id ：Int 商品id | 必填
     *         - platform : String 平台类型 pc app wap | 必填
     *         - user_id ：String 用户id 用于区分权限和获取指定用户号码 | 可填
     *         - is_temp : Int 是否临时用户 | 1是 0 否 选填
     *         - channel_subid : int 渠道号
     *         - udid : string 手机唯一id(app only)
     * @return array
     *         - coupon_sn ：String 优惠券码
     *         - coupon_name ： String 优惠券名字
     *         - tips ：String 优惠信息提示（商品详情页面小标签）
     *         - start_provide_time : timestamp 优惠券开始领取时间
     *         - end_provide_time : timestamp 优惠券结束领取时间
     *         - coupon_type : Int 优惠券类型 1 满减券 2 折扣券 3 包邮券
     *         - coupon_value : String 优惠券优惠金额或折扣
     *         - min_cost : String 优惠券满足条件
     *         - coupon_number ：Int 优惠券数量
     *         - got_num ：Int 已经领取数量
     *         - is_over_bring_limit ：Int 是否超过可领取优惠券的数量 是 1 否 0
     *         - type ：Int 1 登陆 2 游客
     *         - use_range : Int 使用范围 all 全场 category 品类 brand 品牌 single 单品
     *         - 如果使用范围是all全场的 则为空Column 如果为single单品 brand品牌 category品类 则为对应的ids
     *         - validitytype 有效期类型 1 绝对有效期 2 相对有效期
     *         - relative_validity 相对有效天数
     *         - is_over 是否已领取完
     *         - expiration 有效期
     * @author 邓永军
     */
    public function getCouponList($event,$class,$data)
    {
        return $this->CouponList($data);
    }
    

}