<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangCpsOrderLog extends BaseModel
{

    /**
     *
     * @var string
     * @Primary
     * @Column(type="string", length=32, nullable=false)
     */
    public $order_sn;

    /**
     *
     * @var string
     * @Column(type="string", length=32, nullable=false)
     */
    public $user_id;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $order_status;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $order_time;

    /**
     *
     * @var string
     * @Column(type="string", length=1000, nullable=false)
     */
    public $discount_data;

    /**
     *
     * @var integer
     * @Column(type="integer", length=3, nullable=true)
     */
    public $pay_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $pay_time;

    /**
     *
     * @var double
     * @Column(type="double", length=8, nullable=true)
     */
    public $freight;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $balance;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $real_pay;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $platform_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $channel_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=3, nullable=true)
     */
    public $m_channel_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $cps_id;

    /**
     *
     * @var string
     * @Column(type="string", length=15, nullable=false)
     */
    public $invite_code;

    /**
     *
     * @var integer
     * @Column(type="integer", length=2, nullable=false)
     */
    public $clearing;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $clearing_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $doctor_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $manager_business_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $director_business_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $business_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $business_division_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $office_id;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_cps_order_log';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangCpsOrderLog[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangCpsOrderLog
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
