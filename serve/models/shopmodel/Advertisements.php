<?php

namespace Shop\Models;

class Advertisements extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $advertisement_id;

    /**
     *
     * @var string
     * @Column(type="string", length=200, nullable=false)
     */
    public $advertisement;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $adp_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $start_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $end_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $advertisement_type;

    /**
     *
     * @var string
     * @Column(type="string", length=500, nullable=true)
     */
    public $image_url;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=true)
     */
    public $backgroud;

    /**
     *
     * @var string
     * @Column(type="string", length=500, nullable=true)
     */
    public $slogan;

    /**
     *
     * @var string
     * @Column(type="string", length=500, nullable=true)
     */
    public $location;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $order;

    /**
     *
     * @var string
     * @Column(type="string", length=800, nullable=true)
     */
    public $advertisement_desc;

    /**
     *
     * @var string
     * @Column(type="string", length=5000, nullable=true)
     */
    public $products;

    /**
     *
     * @var string
     * @Column(type="string", length=32, nullable=true)
     */
    public $author;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $create_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    public $update_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=true)
     */
    public $is_default;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'advertisements';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Advertisements[]|Advertisements
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Advertisements
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
