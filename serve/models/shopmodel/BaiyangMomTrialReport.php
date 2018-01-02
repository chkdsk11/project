<?php

namespace Shop\Models;

class BaiyangMomTrialReport extends BaseModel
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
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $title;

    /**
     *
     * @var string
     * @Column(type="string", length=250, nullable=false)
     */
    public $content;

    /**
     *
     * @var string
     * @Column(type="string", length=800, nullable=false)
     */
    public $images;

    /**
     *
     * @var string
     * @Column(type="string", length=80, nullable=false)
     */
    public $tag_name;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $star;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_good;

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
        return 'baiyang_mom_trial_report';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangMomTrialReport[]|BaiyangMomTrialReport
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangMomTrialReport
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
