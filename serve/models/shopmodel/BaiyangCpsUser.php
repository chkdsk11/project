<?php

namespace Shop\Models;

class BaiyangCpsUser extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=10, nullable=false)
     */
    public $cps_id;

    /**
     *
     * @var string
     * @Column(type="string", length=32, nullable=false)
     */
    public $user_id;

    /**
     *
     * @var string
     * @Column(type="string", length=16, nullable=false)
     */
    public $user_name;

    /**
     *
     * @var string
     * @Column(type="string", length=32, nullable=false)
     */
    public $employee_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $channel_id;

    /**
     *
     * @var string
     * @Column(type="string", length=15, nullable=false)
     */
    public $invite_code;

    /**
     *
     * @var integer
     * @Column(type="integer", length=8, nullable=false)
     */
    public $invite_num;

    /**
     *
     * @var integer
     * @Column(type="integer", length=8, nullable=false)
     */
    public $order_num;

    /**
     *
     * @var double
     * @Column(type="double", length=15, nullable=false)
     */
    public $reward_amount;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $add_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $cps_status;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $area;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $clerk;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $short_code_office;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $province;

    /**
     *
     * @var integer
     * @Column(type="integer", length=5, nullable=true)
     */
    public $city;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $hospital;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $department;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_cps_user';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangCpsUser[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangCpsUser
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
