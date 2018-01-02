<?php

namespace Shop\Models;
use Shop\Models\BaseModel;

class BaiyangVideo extends BaseModel
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
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $video_id;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $video_unique;

    /**
     *
     * @var string
     * @Column(type="string", length=125, nullable=false)
     */
    public $video_name;

    /**
     *
     * @var integer
     * @Column(type="integer", length=5, nullable=false)
     */
    public $status;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $video_desc;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $tag;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $img;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $video_duration;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $error_desc;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $isdownload;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_pay;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $video_url;

    /**
     *
     * @var string
     * @Column(type="string", length=1500, nullable=false)
     */
    public $extend_images;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $add_time;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        $this->setup(['notNullValidations'=>false]);
        return 'baiyang_video';
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
