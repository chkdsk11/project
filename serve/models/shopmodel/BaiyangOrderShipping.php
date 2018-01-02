<?php

namespace Shop\Models;

use Shop\Models\BaseModel;

class BaiyangOrderShipping extends BaseModel
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
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $express_sn;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $shipping_detail;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_order_shipping';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangOrderShipping[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangOrderShipping
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
