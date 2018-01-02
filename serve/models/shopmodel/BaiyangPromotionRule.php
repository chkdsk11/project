<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangPromotionRule extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $rule_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $promotion_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $member_tag;

    /**
     *
     * @var string
     * @Column(type="integer", length=11, nullable=false)
     */
    public $join_times;

    /**
     *
     * @var integer
     * @Column(type="integer", length=2, nullable=false)
     */
    public $offer_type;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $limit_unit;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $limit_number;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $condition;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $except_category_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $except_brand_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $except_good_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $rule_value;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_superimposed;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_promotion_rule';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangPromotionRule[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangPromotionRule
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
