<?php

namespace Shop\Models;

class BaiyangRefundReason extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=20, nullable=false)
     */
    public $reason_id;



    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $reason_desc;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $priority;



    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_refund_reason';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangGoodsPrice[]|BaiyangGoodsPrice
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangGoodsPrice
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
