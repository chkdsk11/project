<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangConsigneeLimitBuy extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=10, nullable=false)
     */
    public $lb_id;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $card_sn;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $consignee;

    /**
     *
     * @var integer
     * @Column(type="integer", length=6, nullable=true)
     */
    public $bought_limit;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $update_time;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_consignee_limit_buy';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangConsigneeLimitBuy[]|BaiyangConsigneeLimitBuy
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangConsigneeLimitBuy
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
