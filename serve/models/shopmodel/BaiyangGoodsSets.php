<?php

namespace Shop\Models;

use Shop\Models\BaseModel;

class BaiyangGoodsSets extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=10, nullable=false)
     */
    public $id;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $name;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $update_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $sort;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $pc_platform;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $wap_platform;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $app_platform;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $wechat_platform;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_goods_sets';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangGoodsSets[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangGoodsSets
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
