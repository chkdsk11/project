<?php

namespace Shop\Models;

class BaiyangPayment extends BaseModel
{
    public $id;
    public $name;
    public $alias;
    public $other_guide;
    public $channel;
    public $image;
    public $sort;
    public $status;
    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_payment';
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
