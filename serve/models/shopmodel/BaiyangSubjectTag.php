<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangSubjectTag extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $tag_id;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $component_id;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $field_id;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    public $type;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    public $tag_name;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $status;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $start_time;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    public $end_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $background;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $product_ids;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $img_url;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $create_time;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    public $update_time;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_subject_tag';
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
