<?php

namespace Shop\Models;

class BaiyangOrderPayDetail extends BaseModel
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
     * @var string
     * @Column(type="string", length=32, nullable=false)
     */
    public $order_sn;

    /**
     *
     * @var string
     * @Column(type="string", length=3, nullable=true)
     */
    public $order_channel;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=true)
     */
    public $payid;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=true)
     */
    public $pay_name;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=true)
     */
    public $pay_money;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $pay_time;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=true)
     */
    public $trade_no;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $pay_remark;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $create_time;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_order_pay_detail';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangOrderPayDetail[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangOrderPayDetail
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
