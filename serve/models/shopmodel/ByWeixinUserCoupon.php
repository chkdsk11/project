<?php

namespace Shop\Models;

class ByWeixinUserCoupon extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $user_coupon_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $coupon_id;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $user_id;

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
     * @Column(type="string", nullable=false)
     */
    public $start_time;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $end_time;

    /**
     *
     * @var string
     * @Column(type="string", length=11, nullable=false)
     */
    public $value;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $bind_time;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $status;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $used_time;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'by_weixin_user_coupon';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ByWeixinUserCoupon[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ByWeixinUserCoupon
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
