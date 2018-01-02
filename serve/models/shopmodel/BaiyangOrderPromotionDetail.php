<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangOrderPromotionDetail extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $promotion_detail_id;

    /**
     *
     * @var string
     * @Column(type="string", length=32, nullable=false)
     */
    public $order_sn;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $user_id;

    /**
     *
     * @var string
     * @Column(type="string", length=2, nullable=true)
     */
    public $discount_type;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=true)
     */
    public $promotion_id;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=true)
     */
    public $promotion_name;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $promotion_remark;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=true)
     */
    public $discount_money;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=true)
     */
    public $promotion_range;

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
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_order_promotion_detail';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangOrderPromotionDetail[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangOrderPromotionDetail
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
