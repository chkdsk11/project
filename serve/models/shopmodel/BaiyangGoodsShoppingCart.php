<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangGoodsShoppingCart extends BaseModel
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
     * @Column(type="string", length=20, nullable=false)
     */
    public $user_id;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $goods_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $group_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $goods_number;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_temp;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $add_time;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $brand_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_global;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $selected;

    /**
     *
     * @var integer
     * @Column(type="string", length=128, nullable=false)
     */
    public $selected_promotion;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $increase_buy;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_goods_shopping_cart';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangGoodsShoppingCart[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangGoodsShoppingCart
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
