<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangCpsChannelRebate extends BaseModel
{

    /**
     *
     * @var string
     * @Primary
     * @Column(type="string", length=32, nullable=false)
     */
    public $id;

    /**
     *
     * @var string
     * @Column(type="string", length=32, nullable=false)
     */
    public $channel_id;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $back_percent;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $first_rebate;

    /**
     *
     * @var string
     * @Column(type="string", length=1000, nullable=false)
     */
    public $add_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=3, nullable=true)
     */
    
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_cps_channel_rebate';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangCpsOrderLog[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangCpsOrderLog
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
