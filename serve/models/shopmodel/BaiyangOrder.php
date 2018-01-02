<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangOrder extends BaseModel
{

    /**
     *
     * @var string
     * @Primary
     * @Identity
     * @Column(type="string", length=20, nullable=false)
     */
    public $id;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $agent_id;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $user_id;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $total_sn;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $order_sn;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $tmall_order_sn;

    /**
     *
     * @var integer
     * @Column(type="integer", length=6, nullable=true)
     */
    public $admin_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $delivery_status;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $consignee;

    /**
     *
     * @var string
     * @Column(type="string", length=15, nullable=false)
     */
    public $telephone;

    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    public $zipcode;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $province;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $city;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $county;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $address;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $express;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $express_sn;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $total;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $pay_remark;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $real_pay;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $carriage;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_pay;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $pay_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $pay_type;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $pay_total;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $delivery_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $received_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $received;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $express_type;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $o2o_remark;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $shop_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $invoice_type;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $invoice_info;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $e_invoice_url;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $buyer_message;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=false)
     */
    public $cancel_reason;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_remind;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_out_date;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_comment;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_return;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_delete;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $add_time;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $order_discount_money;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $discount_remark;

    /**
     *
     * @var integer
     * @Column(type="integer", length=8, nullable=false)
     */
    public $ad_source_id;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $ad_web_id;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $ad_by_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=true)
     */
    public $ad_click_time;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $ad_dm_referer;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=true)
     */
    public $detail_discount_money;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $addr_id;

    /**
     *
     * @var string
     * @Column(type="string", length=32, nullable=true)
     */
    public $call_tel;

    /**
     *
     * @var string
     * @Column(type="string", length=200, nullable=true)
     */
    public $email;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $send_time;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=true)
     */
    public $goods_price;

    /**
     *
     * @var string
     * @Column(type="string", length=2000, nullable=true)
     */
    public $remark;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $status;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=true)
     */
    public $last_status;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $audit_state;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $audit_time;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=true)
     */
    public $user_coupon_id;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=true)
     */
    public $user_coupon_price;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=true)
     */
    public $youhui_price;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=true)
     */
    public $balance_price;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=true)
     */
    public $payment_name;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $payment_id;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=true)
     */
    public $payment_code;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $channel_subid;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $channel_name;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=true)
     */
    public $trade_no;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=true)
     */
    public $invoice_money;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $express_status;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $express_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=3, nullable=true)
     */
    public $allow_comment;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=false)
     */
    public $callback_phone;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $ordonnance_photo;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_dummy;

    /**
     *
     * @var string
     * @Column(type="string", length=150, nullable=false)
     */
    public $audit_reason;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $update_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=3, nullable=true)
     */
    public $order_type;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=true)
     */
    public $admin_account;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=true)
     */
    public $apothecary_message;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $o2o_syn;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_order';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangOrder[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangOrder
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
