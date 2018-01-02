<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangBrands extends BaseModel
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
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $brand_name;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $brand_desc;

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
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_brands';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangBrands[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangBrands
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
