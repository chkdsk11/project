<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangSubjectComponent extends BaseModel
{

    /**
     *
     * @var integer
     * @Column(type="integer", length=30, nullable=false)
     */
    public $component_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=30, nullable=false)
     */
    public $subject_id;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    public $component_name;

    /**
     *
     * @var integer
     * @Column(type="integer", length=256, nullable=false)
     */
    public $state;

    /**
     *
     * @var integer
     * @Column(type="integer", nullable=false)
     */
    public $channel;

    /**
     *
     * @var string
     * @Column(type="string", length=4, nullable=false)
     */
    public $html_value;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    public $css_value;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $javascript_value;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_subject_component';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangPromotion[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangPromotion
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
