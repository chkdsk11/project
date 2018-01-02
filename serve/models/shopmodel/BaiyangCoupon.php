<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangCoupon extends BaseModel
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
    public $coupon_sn;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $coupon_name;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $coupon_description;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $coupon_logo;

    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    public $coupon_value;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $coupon_number;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $limit_number;

    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    public $min_cost;

    /**
     *
     * @var integer
     * @Column(type="integer", length=2, nullable=false)
     */
    public $provide_type;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=true)
     */
    public $type;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=true)
     */
    public $medicine_type;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $condition;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $start_provide_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $end_provide_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $start_use_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $end_use_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $register_bonus;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_delete;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_cancel;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $add_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=20, nullable=false)
     */
    public $relative_validity;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $validitytype;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=true)
     */
    public $wap_platform;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=true)
     */
    public $app_platform;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=true)
     */
    public $pc_platform;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=true)
     */
    public $wechat_platform;

    /**
     *
     * @var integer
     * @Column(type="integer", length=2, nullable=false)
     */
    public $coupon_type;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=true)
     */
    public $discount_unit;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $channel_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $group_set;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $tels;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $use_range;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $brand_ids;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $category_ids;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $product_ids;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $ban_join_rule;

    /**
     *
     * @var integer
     * @Column(type="integer", length=20, nullable=true)
     */
    public $bring_number;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=true)
     */
    public $is_activecode;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $goods_tag_id;

    /**
     *
     * @var string
     * @Column(type="string", length=12, nullable=false)
     */
    public $drug_type;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    public $pc_url;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    public $wap_url;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    public $app_url;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    public $wechat_url;



    public $is_present;
    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_coupon';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangCoupon[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangCoupon
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
