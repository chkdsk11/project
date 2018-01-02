<?php

namespace Shop\Models;

class AppLimitTimeOffer extends BaseModel
{
    public function initialize()
    {
        parent::initialize();
        $this->setConnectionService('dbWriteApp');
    }

    public $limit_id;

    public $limit_name;

    public $content;

    public $user_level;

    public $limit_type;

    public $limit_condition;

    public $not_limit_condition;

    public $limit_num;

    public $price_type;

    public $show_page;

    public $is_cancel;

    public $mutex_coupon;

    public $mutex_gif;

    public $mutex_discount;

    public $start_time;

    public $end_time;

    public $author;

    public $create_time;

    public $update_time;

    public function getSource()
    {
        return 'limit_time_offer';
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
