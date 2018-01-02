<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangVersionsDownUrl extends BaseModel
{

    public $id;

    public $versions_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */


    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $down_channel;

    /**
     *
     * @var string
     * @Column(type="string", length=125, nullable=false)
     */
    public $url;

    public $add_time;

    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);

        return 'baiyang_version_down_url';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangVideo[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangVideo
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
