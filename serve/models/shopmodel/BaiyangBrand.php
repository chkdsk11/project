<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangBrand extends BaseModel
{

    /**
     *
     * @var string
     * @Primary
     * @Identity
     * @Column(type="string", length=10, nullable=false)
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
     * @Column(type="string", length=30, nullable=false)
     */
    public $account;

    /**
     *
     * @var string
     * @Column(type="string", length=32, nullable=false)
     */
    public $password;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $username;

    /**
     *
     * @var string
     * @Column(type="string", length=15, nullable=false)
     */
    public $phone;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $sex;

    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    public $province;

    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    public $city;

    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    public $zone;

    /**
     *
     * @var string
     * @Column(type="string", length=300, nullable=false)
     */
    public $address;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=false)
     */
    public $alipayaccount;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=false)
     */
    public $alipayname;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $brand_logo;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $list_image;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $banner;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=true)
     */
    public $summary;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $brand_desc;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $site_url;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_recommended;

    /**
     *
     * @var integer
     * @Column(type="integer", length=5, nullable=false)
     */
    public $sort;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_delete;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $add_time;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    public $website;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $status;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $created_at;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $updated_at;

    /**
     *
     * @var string
     * @Column(type="string", length=32, nullable=false)
     */
    public $created_by;

    /**
     *
     * @var string
     * @Column(type="string", length=32, nullable=false)
     */
    public $updated_by;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    public $show_title;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_brand';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangBrand[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangBrand
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
