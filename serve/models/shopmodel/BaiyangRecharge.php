<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangRecharge extends BaseModel
{

    /**
     *
     * @var string
     * @Primary
     * @Identity
     * @Column(type="string", length=10, nullable=false)
     */
    public $re_id;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $re_no;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $user_id;

    /**
     *
     * @var string
     * @Column(type="string", length=32, nullable=false)
     */
    public $create_time;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $money;

    /**
     *
     * @var string
     * @Column(type="string", length=15, nullable=false)
     */
    public $payment_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $payment_code;

    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    public $channel_subid;

    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    public $channel_name;

    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    public $status;

    /**
     *
     * @var string
     * @Column(type="string", length=300, nullable=false)
     */
    public $exception_reason;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=false)
     */
    public $is_send_message;




    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_recharge';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangBrand[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangBrand
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
