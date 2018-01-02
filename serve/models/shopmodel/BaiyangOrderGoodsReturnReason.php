<?php

namespace Shop\Models;

class BaiyangOrderGoodsReturnReason extends BaseModel
{

    /**
     *
     * @var string
     * @Primary
     * @Identity
     * @Column(type="string", length=20, nullable=false)
     */
    public $id;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $order_sn;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $return_type;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $return_id;

    /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=false)
     */
    public $reason;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $explain;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $images;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $status;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $add_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $update_time;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $refund_amount;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $pay_fee;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=true)
     */
    public $serv_id;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=true)
     */
    public $serv_nickname;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $remark;

    /**
     *
     * @var integer
     * @Column(type="integer", length=2, nullable=true)
     */
    public $auto;


    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $service_sn;



    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_order_goods_return_reason';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangOrderGoodsReturnReason[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangOrderGoodsReturnReason
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
