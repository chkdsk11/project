<?php

namespace Shop\Models;

/**
 * ProductsTypes
 * 
 * @package Shop\Models
 * @autogenerated by Phalcon Developer Tools
 * @date 2017-05-26, 19:03:09
 */
class ProductsTypes extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $product_type_id;

    /**
     *
     * @var string
     * @Column(type="string", length=40, nullable=false)
     */
    public $type_name;

    /**
     *
     * @var string
     * @Column(type="string", length=40, nullable=true)
     */
    public $other_name;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    public $summary;

    /**
     *
     * @var integer
     * @Column(type="integer", length=2, nullable=true)
     */
    public $realpro_yes;

    /**
     *
     * @var integer
     * @Column(type="integer", length=2, nullable=true)
     */
    public $extension_yes;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $created_at;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $updated_at;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $created_by;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $updated_by;
	
	public function initialize ()
	{
		parent::initialize();
		$this->setConnectionService('dbWriteApp');
	}
    
    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'products_types';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ProductsTypes[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ProductsTypes
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    /**
     * Independent Column Mapping.
     * Keys are the real names in the table and the values their names in the application
     *
     * @return array
     */
    public function columnMap()
    {
        return array(
            'product_type_id' => 'product_type_id',
            'type_name' => 'type_name',
            'other_name' => 'other_name',
            'summary' => 'summary',
            'realpro_yes' => 'realpro_yes',
            'extension_yes' => 'extension_yes',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
            'created_by' => 'created_by',
            'updated_by' => 'updated_by'
        );
    }

}