<?php

namespace Shop\Models;

class AppAdPosition extends BaseModel
{
    public function initialize()
    {
        parent::initialize();
        $this->setConnectionService('dbWriteApp');
    }
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
     * @Column(type="string", length=200, nullable=false)
     */
    public $adpositionid_name;

    /**
     *
     * @var string
     * @Column(type="string", length=200, nullable=true)
     */
    public $simple_name;

    /**
     *
     * @var integer
     * @Column(type="integer", length=2, nullable=true)
     */
    public $parent_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=true)
     */
    public $status;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $adposition_type;

    /**
     *
     * @var string
     * @Column(type="string", length=200, nullable=true)
     */
    public $image_size;

    /**
     *
     * @var string
     * @Column(type="string", length=500, nullable=true)
     */
    public $adpositionid_desc;

    /**
     *
     * @var string
     * @Column(type="string", length=200, nullable=true)
     */
    public $category_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_group;

    /**
     *
     * @var integer
     * @Column(type="integer", length=3, nullable=true)
     */
    public $order;

    /**
     *
     * @var string
     * @Column(type="string", length=200, nullable=false)
     */
    public $versions;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $channel;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $display_status;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'ad_position';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return AdPosition[]|AdPosition
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return AdPosition
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
