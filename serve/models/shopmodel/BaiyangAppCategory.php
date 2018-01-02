<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangAppCategory extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $category_id;

    /**
     *
     * @var string
     * @Column(type="string", length=80, nullable=false)
     */
    public $category_name;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=true)
     */
    public $nickname;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $parent_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $product_type_id;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $category_path;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $sort;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $level;

    /**
     *
     * @var string
     * @Column(type="string", length=80, nullable=true)
     */
    public $class_icon;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $picture;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $image;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=true)
     */
    public $seo_title;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=true)
     */
    public $seo_keywords;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=true)
     */
    public $seo_description;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $sort_by;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $sort_rule;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=true)
     */
    public $enable;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=true)
     */
    public $main_category;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $num_products;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $created_at;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $updated_at;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $created_by;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $updated_by;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=true)
     */
    public $can_comment;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=true)
     */
    public $show_comments;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_app_category';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangAppCategory[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangAppCategory
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
