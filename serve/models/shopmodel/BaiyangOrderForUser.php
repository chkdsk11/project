<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangOrderForUser extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=8, nullable=false)
     */
    public $id;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $uid;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=true)
     */
    public $nickname;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_temp;

    /**
     *
     * @var string
     * @Column(type="string", length=15, nullable=false)
     */
    public $recall_phone;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $order_type;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $gid;

    /**
     *
     * @var string
     * @Column(type="string", length=200, nullable=false)
     */
    public $goods_name;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $goods_price;

    /**
     *
     * @var string
     * @Column(type="string", length=64, nullable=false)
     */
    public $specifications;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $add_time;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=true)
     */
    public $order_sn;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $order_price;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_deal;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $deal_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=3, nullable=false)
     */
    public $no_od_reason;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $group_id;

    /**
     *
     * @var string
     * @Column(type="string", length=550, nullable=false)
     */
    public $remarks;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $channel;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_order_for_user';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangOrderForUser[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangOrderForUser
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
