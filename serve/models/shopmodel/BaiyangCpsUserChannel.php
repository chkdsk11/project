<?php

namespace Shop\Models;

class BaiyangCpsUserChannel extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $user_id;

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
     * @Column(type="integer", length=10, nullable=false)
     */
    public $bind_time;

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
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $cps_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $user_add_time;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $user_phone;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_cps_user_channel';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangCpsUserChannel[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangCpsUserChannel
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
