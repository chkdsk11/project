<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangOrderPromotionDetailGift extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $promotion_gift_id;

    /**
     *
     * @var string
     * @Column(type="string", length=32, nullable=false)
     */
    public $order_sn;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $promotion_id;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=false)
     */
    public $promotion_name;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $gift_code;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $gift_name;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $gift_quantity;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $gift_value;

    /**
     *
     * @var string
     * @Column(type="string", length=2, nullable=false)
     */
    public $gift_type;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
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
        return 'baiyang_order_promotion_detail_gift';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangOrderPromotionDetailGift[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangOrderPromotionDetailGift
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
