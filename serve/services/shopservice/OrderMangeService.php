<?php
/**
 * Created by PhpStorm.
 * User: 杨先生
 * Date: 2017/4/26
 * Time: 15:02
 */

namespace Shop\Services;
use Shop\Datas\AdvertisementsData;

class OrderMangeService extends BaseService
{
    public $data;

    public function __construct()
    {
        $this->data =AdvertisementsData::getInstance();
        $this->table ='\Shop\Models\AppOrderMaster' ;
    }

    public function getList($param)
    {
        $data = array();
        $where = 'WHERE 1=1 ';
        if(isset($param['param']['start_time']) && $param['param']['start_time']!='' && isset($param['param']['end_time']) && $param['param']['end_time']!=''){
           $start_time =strtotime($param['param']['start_time']);
            $end_time =strtotime($param['param']['end_time']);
            $where .= " AND order_time BETWEEN {$start_time} AND {$end_time}";
        }else{
            //默认前一天
            $time = $this->default_time();
            $where .= " AND order_time BETWEEN {$time['start']} AND {$time['end']}";
        }
        $field = "order_id,user_id,gathering,status,payment_time";
        $result = $this->data->getData(array(
            'column'=>$field,
            'where'=>$where,
            'table'=> $this->table
        ));
        $return = [
            'res' => 'success',
            'list' => $result,
        ];
        return $return;
    }

    /**
     * 获取时间段之前购买过的用户ID
     * @param $search
     */
    public function get_old_user_all($search, $id = '')
    {
        if (!$id) return false;
        //组装sql
        if (isset($search['start_time']) && $search['start_time']) {
            $sql_time = "AND `order_time` < " .strtotime($search['start_time']). " ";
        } else {
            //默认前一天
            $time = $this->default_time();
            $sql_time = "AND order_time < '{$time['start']}' ";
        }
        $where=" WHERE user_id in('{$id}') {$sql_time} GROUP BY user_id";
        $field='user_id';
        $result = $this->data->getData([
                'column'=>$field,
                'table'=>$this->table,
                'where'=>$where,
         ]);
        $data = array();
        if ($result) {
            foreach ($result as $item) {
                $data[] = $item['user_id'];
            }
        }
        return $data;
    }
    /**
     * 获取APP首次登录的用户
     * @param $search array
     * @return int
     */
    public function get_first_login_count($search)
    {
        //组装sql
        if (isset($search['start_time']) && $search['start_time'] && isset($search['end_time']) && $search['end_time']) {
            $sql_time = "AND UNIX_TIMESTAMP(app_first_login_time) BETWEEN " .strtotime($search['start_time']). " AND ".strtotime($search['end_time'])." ";
        } else {
            //默认前一天
            $time = $this->default_time();
            $sql_time = "AND UNIX_TIMESTAMP(app_first_login_time) BETWEEN {$time['start']} AND {$time['end']} ";
        }
        $field = 'id';
        $where = 'WHERE 1=1 '.$sql_time;
        return $this->data->countData([
            'column'=>$field,
            'table'=>'\Shop\Models\BaiyangUser',
            'where'=>$where,
        ]);

        //return $result->num_rows();
    }
    /**
     * 默认获取前一天、前一周的时间
     * @param $where string
     * @return array
     */
    public function default_time($where = '')
    {
        switch ($where) {
            case 'week' :
                $start = strtotime(date("Y-m-d 00:00:00",strtotime("-1 week last monday")));
                $end = strtotime(date("Y-m-d 24:00:00",strtotime("last sunday")));
                break;
            default :
                $start = strtotime(date("Y-m-d 00:00:00",strtotime("-1 day")));
                $end = strtotime(date("Y-m-d 24:00:00",strtotime("-1 day")));
                break;
        }
        return array(
            'start' => $start,
            'end' => $end
        );
    }

}