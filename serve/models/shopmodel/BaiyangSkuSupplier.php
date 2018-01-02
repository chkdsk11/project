<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangSkuSupplier extends BaseModel
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
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    public $name;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    public $user_name;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    public $address;

    /**
     *
     * @var string
     * @Column(type="string", length=8, nullable=false)
     */
    public $code;

    /**
     *
     * @var string
     * @Column(type="string", length=16, nullable=false)
     */
    public $phone;

    /**
     *
     * @var string
     * @Column(type="string", length=16, nullable=false)
     */
    public $telephone;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $updatetime;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_sku_supplier';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangSkuSupplier[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangSkuSupplier
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
