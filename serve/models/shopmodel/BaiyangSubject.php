<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangSubject extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $id;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $title;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    public $keywords;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    public $description;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    public $share_title;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $shareUrl;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $share_img;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    public $background;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $create_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    public $update_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $channel;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $status;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    public $order;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $start_time;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    public $end_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $link;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_subject';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Baiyangsubject[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Baiyangsubject
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
