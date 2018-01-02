<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangVersionsDownChannel extends BaseModel
{

    public $id;

    public $name;

    public $simple_name;

    /**
     *$port
     * @var int 版本来源 89 IOS   90 安卓
     */
    public $port;

    public $add_time;

    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);

        return 'baiyang_version_down_channel';
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
