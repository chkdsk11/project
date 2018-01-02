<?php

namespace Shop\Models;

class Apptheme extends BaseModel
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
    public $theme_id;

    /**
     *
     * @var string
     * @Column(type="string", length=200, nullable=false)
     */
    public $channel;

    /**
     *
     * @var string
     * @Column(type="string", length=200, nullable=true)
     */
    public $scale;

    /**
     *
     * @var integer
     * @Column(type="integer", length=2, nullable=true)
     */
    public $path;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=true)
     */
    public $start_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $end_time;

    /**
     *
     * @var string
     * @Column(type="string", length=200, nullable=true)
     */
    public $creater;

    /**
     *
     * @var string
     * @Column(type="string", length=500, nullable=true)
     */
    public $is_show_local;

    /**
     *
     * @var string
     * @Column(type="string", length=200, nullable=true)
     */
    public $local_url;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $create_time;
    /**
     *
     * @var integer
     * @Column(type="integer", length=3, nullable=true)
     */
    public $update_time;
    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'app_theme';
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
