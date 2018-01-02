<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangSkuImages extends BaseModel
{

    /**
     *
     * @var string
     * @Primary
     * @Identity
     * @Column(type="string", length=20, nullable=false)
     */
    public $id;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $sku_id;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $sku_image;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $sku_middle_image;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $sku_big_image;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $is_default;

    /**
     *
     * @var integer
     * @Column(type="integer", length=3, nullable=true)
     */
    public $sort;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $spu_id;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_sku_images';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangSkuImages[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangSkuImages
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
