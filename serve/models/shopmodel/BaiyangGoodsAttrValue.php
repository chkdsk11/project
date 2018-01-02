<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangGoodsAttrValue extends BaseModel
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
     * @var integer
     * @Column(type="integer", length=8, nullable=false)
     */
    public $attr_name_id;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=false)
     */
    public $attr_value;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_goods_attr_value';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangGoodsAttrValue[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangGoodsAttrValue
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
