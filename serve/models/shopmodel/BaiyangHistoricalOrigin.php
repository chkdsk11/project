<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangHistoricalOrigin extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=5, nullable=false)
     */
    public $id;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    public $keywords;

    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    public $platform_id;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $count;
    
    public $min_res;
    
    public $max_res;
    
    public $at;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_historical_origin';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangCountry[]
     */
//    public static function find($parameters = null)
//    {
//        return parent::find($parameters);
//    }
//
//    /**
//     * Allows to query the first record that match the specified conditions
//     *
//     * @param mixed $parameters
//     * @return BaiyangCountry
//     */
//    public static function findFirst($parameters = null)
//    {
//        return parent::findFirst($parameters);
//    }

}