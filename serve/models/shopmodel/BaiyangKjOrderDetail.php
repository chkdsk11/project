<?php

namespace Shop\Models;

class BaiyangKjOrderDetail extends BaseModel
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
     * @Column(type="string", length=20, nullable=false)
     */
    public $goods_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $goods_order;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $goods_name;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $goods_custom_name;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $code_ts;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $item_record_no;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $goods_model;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $goods_unit;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $goods_tax_amount;

    /**
     *
     * @var string
     * @Column(type="string", length=4, nullable=false)
     */
    public $tax_rate;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=false)
     */
    public $goods_image;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $price;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $unit_price;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $discount_price;

    /**
     *
     * @var string
     * @Column(type="string", length=300, nullable=false)
     */
    public $discount_remark;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $promotion_price;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $promotion_total;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $goods_number;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $goods_type;

    /**
     *
     * @var string
     * @Column(type="string", length=64, nullable=false)
     */
    public $specifications;

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
     * @Column(type="integer", length=11, nullable=false)
     */
    public $add_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $stock_type;

    /**
     *
     * @var string
     * @Column(type="string", length=32, nullable=false)
     */
    public $market_price;

    /**
     *
     * @var string
     * @Column(type="string", length=32, nullable=false)
     */
    public $original_price;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=true)
     */
    public $promotion_origin;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $promotion_code;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $push_host;

    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    public $invite_code;

    /**
     *
     * @var string
     * @Column(type="string", length=120, nullable=false)
     */
    public $code_bu;

    /**
     *
     * @var string
     * @Column(type="string", length=120, nullable=false)
     */
    public $code_region;

    /**
     *
     * @var string
     * @Column(type="string", length=120, nullable=false)
     */
    public $code_office;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $business_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_bigbrand_checked;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_kj_order_detail';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangKjOrderDetail[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangKjOrderDetail
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
