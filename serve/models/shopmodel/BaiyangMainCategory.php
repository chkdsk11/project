<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangMainCategory extends BaseModel
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
    public $pid;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $category_name;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $category_link;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $is_recommended;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $category_logo;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $level;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $thecoverwap;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $sort;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_main_category';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangMainCategory[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangMainCategory
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
