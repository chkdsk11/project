<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangAdminMenus extends BaseModel
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
     * @Column(type="string", length=64, nullable=false)
     */
    public $menu_path;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    public $menu_title;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $parent_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $has_child;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $menu_level;

    /**
     * @var integer
     */
    public $is_show_left;

    /**
     * @var integer
     */
    public $is_show_top;

    /**
     *  初始方法
     */
    public function initialize()
    {
        parent::initialize();
        $this->setup([
            'notNullValidations'=>false
        ]);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_admin_menus';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangAdminMenus[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangAdminMenus
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
