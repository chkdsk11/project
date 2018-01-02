<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangGoodsStockChangeLog extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=10, nullable=false)
     */
    public $id;

    /**
     *
     * @var string
     * @Column(type="string", length=32, nullable=false)
     */
    public $order_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $goods_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=9, nullable=false)
     */
    public $change_num;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $stock_type;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $change_reason;

    /**
     *
     * @var integer
     * @Column(type="integer", length=3, nullable=false)
     */
    public $sync;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $change_time;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $sync_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
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
        return 'baiyang_goods_stock_change_log';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangGoodsStockChangeLog[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangGoodsStockChangeLog
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
