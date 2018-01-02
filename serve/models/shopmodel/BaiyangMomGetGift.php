<?php

namespace Shop\Models;

class BaiyangMomGetGift extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=20, nullable=false)
     */
    public $user_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $gifts_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=2, nullable=false)
     */
    public $ascription;

    /**
     *
     * @var integer
     * @Column(type="integer", length=50, nullable=false)
     */
    public $goods_pty;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $price;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $goods_id;

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
        return 'baiyang_mom_get_gift';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangMomGetGift[]|BaiyangMomGetGift
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangMomGetGift
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
