<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangSpu extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=10, nullable=false)
     */
    public $spu_id;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    public $spu_name;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $brand_id;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    public $brand_name;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    public $spu_tag;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $drug_type;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $category_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $add_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $update_time;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $goods_image;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $big_path;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $small_path;

    /**
     *
     * @var integer
     * @Column(type="integer", length=8, nullable=false)
     */
    public $freight_temp_id;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    public $category_path;

    /**
     *
     * @var string
     * @Column(type="string", length=16, nullable=false)
     */
    public $code;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_spu';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangSpu[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangSpu
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
