<?php

namespace Shop\Models;

use Shop\Models\BaseModel;

class BaiyangOrderStatusHistory extends BaseModel
{

    /**
     *
     * @var int
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
    public $service_sn;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $status;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $date_updated;
    
    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $date_created;
    

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_order_status_history';
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
