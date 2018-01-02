<?php

namespace Shop\Models;

class BaiyangMomGoodsTrialReport extends BaseModel
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
     * @Column(type="integer", length=11, nullable=false)
     */
    public $report_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=20, nullable=false)
     */
    public $goods_id;

    /**
     *
     * @var string
     * @Column(type="string", length=250, nullable=false)
     */
    public $content;

    /**
     *
     * @var string
     * @Column(type="string", length=500, nullable=false)
     */
    public $images;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_mom_goods_trial_report';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangMomGoodsTrialReport[]|BaiyangMomGoodsTrialReport
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangMomGoodsTrialReport
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
