<?php

namespace Shop\Models;

class BaiyangPrescription extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=10, nullable=false)
     */
    public $prescription_id;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $yfz_prescription_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $create_time;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $doctor_name;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $user_name;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $user_sex;

    /**
     *
     * @var integer
     * @Column(type="integer", length=5, nullable=false)
     */
    public $user_age;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $user_address;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $diagnose;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $diagnose_content;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=false)
     */
    public $doctor_sign;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $union_user_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $exp_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $status;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $type;

    /**
     *
     * @var string
     * @Column(type="string", length=32, nullable=false)
     */
    public $order_id;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $doctor_union_user_id;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_prescription';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangPrescription[]|BaiyangPrescription
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangPrescription
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
