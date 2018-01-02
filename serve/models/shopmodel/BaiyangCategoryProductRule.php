<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangCategoryProductRule extends BaseModel
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
    public $category_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $name_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $name_id2;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $name_id3;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=true)
     */
    public $add_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=true)
     */
    public $update_time;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_category_product_rule';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangCategoryProductRule[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangCategoryProductRule
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
