<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangVersions extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $versions_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $versions;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $versions_description;

    /**
     *
     * @var string
     * @Column(type="string", length=125, nullable=false)
     */
    public $is_compulsive;

    /**
     *
     * @var integer
     * @Column(type="integer", length=5, nullable=false)
     */
    public $channel;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $add_time;

    public $edit_time;

    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_versions';
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
