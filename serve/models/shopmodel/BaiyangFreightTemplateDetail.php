<?php

namespace Shop\Models;
use Shop\Models\BaseModel;
class BaiyangFreightTemplateDetail extends BaseModel
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
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $template_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $type;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=true)
     */
    public $default_value;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=true)
     */
    public $default_fee;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=true)
     */
    public $add_value;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=true)
     */
    public $add_fee;

    /**
     *
     * @var string
     * @Column(type="string", length=10000, nullable=false)
     */
    public $region_list;

    /**
     *
     * @var integer
     * @Column(type="integer", length=3, nullable=false)
     */
    public $is_default;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_freight_template_detail';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangFreightTemplateDetail[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangFreightTemplateDetail
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
