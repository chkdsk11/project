<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2017/1/5 0005
 * Time: 9:47
 */

namespace Shop\Models;


use Phalcon\Http\Request;

class BaiyangSmsLog extends BaseModel
{
    public $log_id;
    public $log_type;
    public $description;
    public $original_data;
    public $phone;
    public $sms_content;
    public $client_code;
    public $ip_address;
    public $session_id;
    public $user_agent;
    public $create_time;
    public $template_code;
    public $client_ip_address;

    public function getSource()
    {
        return 'baiyang_sms_log';
    }
    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangSmsList[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangSmsList
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }
}