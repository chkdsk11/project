<?php

namespace Shop\Models;

class BaiyangCpsInviteLog extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=10, nullable=false)
     */
    public $log_id;

    /**
     *
     * @var string
     * @Column(type="string", length=32, nullable=false)
     */
    public $user_id;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $udid;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $cps_id;

    /**
     *
     * @var string
     * @Column(type="string", length=32, nullable=false)
     */
    public $cps_user_id;

    /**
     *
     * @var string
     * @Column(type="string", length=15, nullable=false)
     */
    public $invite_code;

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
    public $add_time;

    /**
     *
     * @var double
     * @Column(type="double", length=5, nullable=false)
     */
    public $back_amount;

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
        return 'baiyang_cps_invite_log';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangCpsInviteLog[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangCpsInviteLog
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
