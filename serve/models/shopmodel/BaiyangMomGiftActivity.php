<?php

namespace Shop\Models;

class BaiyangMomGiftActivity extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $gifts_id;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=false)
     */
    public $gifts_title;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $gifts_message;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $start_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $end_time;

    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    public $generation;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $gifts_image;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $sort;

    /**
     *
     * @var string
     * @Column(type="string", length=500, nullable=false)
     */
    public $binding_gift;

    /**
     *
     * @var string
     * @Column(type="string", length=500, nullable=false)
     */
    public $binding_coupon;

    /**
     *
     * @var string
     * @Column(type="string", length=1000, nullable=false)
     */
    public $relation_goods_id;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $tag_name;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $attribute;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $add_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $pregnant;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_mom_gift_activity';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangMomGiftActivity[]|BaiyangMomGiftActivity
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangMomGiftActivity
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
