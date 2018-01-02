<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangGoodsCategory extends BaseModel
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
     * @Column(type="string", length=50, nullable=false)
     */
    public $category_name;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $alias;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $recommend_link;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $name_pinyin;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $pid;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $level;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $has_child;

    /**
     *
     * @var string
     * @Column(type="string", length=500, nullable=false)
     */
    public $filter_list;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_main;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_show;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $sort;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $category_path;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $meta_title;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $meta_keyword;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $meta_description;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=true)
     */
    public $add_time;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_goods_category';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangGoodsCategory[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangGoodsCategory
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
