<?php

namespace Shop\Models;

class ByWeixinCoupon extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    public $coupon_id;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $name;

    /**
     *
     * @var string
     * @Column(type="string", length=300, nullable=false)
     */
    public $description;

    /**
     *
     * @var string
     * @Column(type="string", length=11, nullable=false)
     */
    public $value;

    /**
     *
     * @var string
     * @Column(type="string", length=11, nullable=false)
     */
    public $order_amount;

    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    public $user_level;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $user_limit;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $get_num;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $qty;

    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    public $type;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $condition;

    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    public $condition_type;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $start_time;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $end_time;

    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    public $for_users;

    /**
     *
     * @var string
     * @Column(type="string", length=500, nullable=true)
     */
    public $commont;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $create_time;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $creater;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $update_time;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $updater;

    /**
     *
     * @var integer
     * @Column(type="integer", length=3, nullable=false)
     */
    public $channel;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $no_include;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_cancel;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $subject;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $is_created_code;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $location;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $wap_location;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $indate_day;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'by_weixin_coupon';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ByWeixinCoupon[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ByWeixinCoupon
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
