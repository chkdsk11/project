<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangGoodsExtension extends BaseModel
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
    public $goods_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $goods_desc;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $body;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $add_time;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_goods_extension';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangGoodsExtension[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangGoodsExtension
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
