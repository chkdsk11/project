<?php

namespace Shop\Models;

class BaiyangHotSearch extends BaseModel
{

    /**
     *
     * @var int
     * @Primary
     * @Identity
     * @Column(type="int", nullable=false)
     */
    public $id;

    /**
     *
     * @var char
     * @Primary
     * @Identity
     * @Column(type="char", length=20, nullable=false)
     */
    public $platform;

    /**
     *
     * @var char
     * @Column(type="char", length=128, nullable=false)
     */
    public $keys;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_hot_search';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
