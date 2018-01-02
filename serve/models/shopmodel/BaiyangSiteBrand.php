<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangSiteBrand extends BaseModel
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
     * @Column(type="string", length=255, nullable=false)
     */
    public $brand_logo;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $brand_describe;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $mon_title;

    /**
     *
     * @var integer
     * @Column(type="integer", length=5, nullable=false)
     */
    public $sort;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $list_image;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $status;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_hot;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $type;

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
        return 'baiyang_site_brand';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangSiteBrand[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangSiteBrand
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
