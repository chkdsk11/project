<?php

namespace Shop\Models;

class AppOrderMaster extends BaseModel
{
    public function initialize()
    {
        parent::initialize();
        $this->setConnectionService('dbWriteApp');
    }

    public $order_id;

    public $company_id;

    public $user_id;

    public $receiver_name;

    public $post;

    public $addr_id;

    public $address_districtid;

    public $address_cityid;

    public $address_areaid;

    public $address_info;

    public $call_mobile;

    public $call_tel;

    public $email;

    public $delvery_type;

    public $affix_money;

    public $send_time;

    public $order_time;

    public $gathering;

    public $goods_price;

    public $paid;

    public $remark;

    public $leave_word;

    public $status;

    public $send_result;

    public $last_status;

    public $audit_state;

    public $callid;

    public $logistics_id;

    public $logistics_com;

    public $logistics_msg;

    public $balance;

    public $x_transid;

    public $user_coupon_id;

    public $user_coupon_price;

    public $youhui_price;

    public $discount_remark;

    public $balance_price;

    public $payment_name;

    public $payment_time;

    public $payment_id;

    public $payment_code;

    public $is_receipt;

    public $receipt_type;

    public $receive_header;

    public $receive_content;

    public $order_type;

    public $channel_id;

    public $channel_subid;

    public $channel_name;

    public $trade_no;

    public $payables;

    public $pay_remark;

    public $invoice_money;

    public $flag;

    public $is_del;

    public $is_dummy;

    public $express_status;

    public $express_time;

    public $allow_comment;

    public $callback_phone;

    public $ordonnance_photo;

    public function getSource()
    {
        return 'by_order_master';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return AdPosition[]|AdPosition
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return AdPosition
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
