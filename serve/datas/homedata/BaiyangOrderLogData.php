<?php
/**
 * User: Chensonglu
 */
namespace Shop\Home\Datas;

use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class BaiyangOrderLogData extends BaseData
{
    protected static $instance=null;

    public function addOrderLog($param)
    {
        if(isset($param['order_sn']) && $param['order_sn']){
            $this->addData([
                'table'=>'\Shop\Models\BaiyangOrderLog',
                'bind'=>$param
            ],true);
            return true;
        }
        return false;
    }
}