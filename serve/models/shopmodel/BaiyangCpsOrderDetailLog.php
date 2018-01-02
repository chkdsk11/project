<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangCpsOrderDetailLog extends BaseModel
{

    /**
     *
     * @var string
     * @Primary
     * @Column(type="string", length=32, nullable=false)
     */
    public $id;

    /**
     *
     * @var string
     * @Column(type="string", length=32, nullable=false)
     */
    public $order_sn;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $brand_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $goods_id;

    /**
     *
     * @var string
     * @Column(type="string", length=1000, nullable=false)
     */
    public $invite_code;

    /**
     *
     * @var integer
     * @Column(type="integer", length=3, nullable=true)
     */
    public $qty;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $price;

    /**
     *
     * @var double
     * @Column(type="double", length=8, nullable=true)
     */
    public $market_price;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $platform_id;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $channel_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $act_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $back_amount;

    /**
     *
     * @var integer
     * @Column(type="integer", length=3, nullable=true)
     */
    public $back_percent;

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
    public $doctor_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=2, nullable=false)
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
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $order_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $cps_collar_pattern_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_cps_order_detail_log';
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
