<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangAttrValue extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=8, nullable=false)
     */
    public $id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=8, nullable=false)
     */
    public $attr_name_id;

    /**
     *
     * @var string
     * @Column(type="string", length=512, nullable=false)
     */
    public $attr_value;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations']);
        return 'baiyang_attr_value';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangAttrValue[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangAttrValue
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
