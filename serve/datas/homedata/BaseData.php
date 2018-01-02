<?php
/**
 * Created by PhpStorm.
 * User: 吴俊华
 * Date: 2016/10/11 0011
 * Time: 上午 10:06
 */

namespace Shop\Home\Datas;

use Phalcon\Mvc\User\Component;
use Shop\Models\BaseModel;

class BaseData extends Component
{

    protected static $instance=null;

    /**
     * 单例
     * @return static
     */
    public static function getInstance()
    {
        if(empty(static::$instance)){
            static::$instance=new static();
        }
        return static::$instance;
    }

    /**
     * @desc 根据条件查找单条或多条记录
     * @param array $param 查找的条件信息
     * @param bool $returnOne 获取多条或单条开关，true为获取单条，false为获取多条。
     * @return array|bool $result|[] 查找到的记录或[]
     * @author 吴俊华
     * @date 2016-10-17
     */
    public function getData($param, $returnOne = false)
    {

        if (is_array($param) && !empty($param)) {
            $phql = "select {$param['column']} from {$param['table']}";

            //连表查询
            if (isset($param['join']) && !empty($param['join'])) {
                $phql .= " {$param['join']}";
            }

            //条件查询
            if (isset($param['where']) && !empty($param['where'])) {
                $phql .= " {$param['where']}";
            }
			
			//排序条件
            if (isset($param['order']) && !empty($param['order'])) {
                $phql .= " {$param['order']}";
            }
			
			//分页条件
            if (isset($param['limit']) && !empty($param['limit'])) {
                $phql .= " {$param['limit']}";
            }

            //数据绑定
            if (isset($param['bind']) && !empty($param['bind'])) {
                $ret = $this->modelsManager->executeQuery($phql, $param['bind']);
            } else {
                $ret = $this->modelsManager->executeQuery($phql);
            }
            //判断查询结果
            if (count($ret)) {
                if($returnOne){
                    $ret = $ret->getFirst();
                }
                $data = $ret->toArray();
                unset($ret);
                return $data;
            }
            return [];
        }
    }

    /**
     * @desc 根据条件更新记录
     * @param array $param 更新的条件信息
     * @return bool true|false 更新的结果信息
     * @author 吴俊华
     * @date 2016-10-17
     */
    public function updateData($param)
    {
        if (is_array($param) && !empty($param)) {
            $phql = "update {$param['table']} set {$param['column']}";

            //条件
            if (isset($param['where']) && !empty($param['where'])) {
                $phql .= " {$param['where']}";
            }

            //参数绑定
            if (isset($param['bind']) && !empty($param['bind'])) {
                $ret = $this->modelsManager->executeQuery($phql, $param['bind']);
            } else {
                //无参数绑定
                $ret = $this->modelsManager->executeQuery($phql);
            }
            if (is_object($ret)) {
                return $ret->success();
            }
        }
        return false;
    }

    public function updateDataV2($table, $set, $where) {
        $bind = [];
        // 设置
        if (is_array($set)) {
            $setStr = [];
            foreach ($set as $key => $value) {
                $tempKey = $key . '_set';
                $setStr[] = "{$key}=:{$tempKey}:";
                $bind[$tempKey] = $value;
            }
            $setStr = implode(',', $setStr);
        } else {
            $setStr = $set;
        }
        // 条件
        if (is_array($where)) {
            $whereStr = [];
            foreach ($where as $key => $value) {
                $tempKey = $key.'_where';
                $whereStr[] = "{$key}=:{$tempKey}:";
                $bind[$tempKey] = $value;
            }
            $whereStr = implode(',', $whereStr);
        } else {
            $whereStr = $where;
        }
        // 更新
        $phql = "update {$table} set {$setStr} where {$whereStr}";
        $ret = $this->modelsManager->executeQuery($phql, $bind);
        if (is_object($ret)) {
            return $ret->success();
        }
        return false;
    }

    /**
     * @desc 根据条件删除记录
     * @param array $param 删除的条件信息
     * @return bool true|false 删除的结果信息
     * @author 吴俊华
     * @date 2016-10-17
     */
    public function deleteData($param)
    {
        if (is_array($param) && !empty($param)) {
            $phql = "delete from {$param['table']}";

            //条件
            if (isset($param['where']) && !empty($param['where'])) {
                $phql .= " {$param['where']}";
            }

            //参数绑定
            if (isset($param['bind']) && !empty($param['bind'])) {
                $ret = $this->modelsManager->executeQuery($phql, $param['bind']);
            } else {
                $ret = $this->modelsManager->executeQuery($phql);
            }
            if (is_object($ret)) {
                return $ret->success();
            }
        }
    }

    /**
     * @desc 插入一条记录
     * @param array $param 添加的数据信息
     * @return bool|string true|false|插入数据后的id
     * @author  吴俊华
     * @date    2016-10-17
     *
     */
    public function addData($param, $returnRow = false)
    {
        if (is_array($param) && !empty($param)) {
            $table = new $param['table'];
            $ret = $table->save($param['bind']);
            if (!empty($ret)) {
                if ($returnRow) {
                    $lastInsertId = $table->getWriteConnection()->lastInsertId($table->getSource());
                    return $lastInsertId;
                }
                return $ret;
            }
            return false;
        }
    }

    /**
     * @desc 统计满足条件的记录条数
     * @param array $param 统计的条件信息
     * @return int|bool $count|false 满足条件的记录条数或false
     * @author 吴俊华
     * @date   2016-10-17
     */
    public function countData($param)
    {
        if (is_array($param) && !empty($param)) {
            $phql = "select count(1) as counts from {$param['table']}";
            if (isset($param['column']) && !empty($param['column']) && strpos($param['column'], ',') === false) {
                $phql = "select count({$param['column']}) as counts from {$param['table']}";
            }

            //连表查询
            if (isset($param['join']) && !empty($param['join'])) {
                $phql .= " {$param['join']}";
            }

            //条件查询
            if (isset($param['where']) && !empty($param['where'])) {
                $phql .= " {$param['where']}";
            }

            //数据绑定
            if (isset($param['bind']) && !empty($param['bind'])) {
                $ret = $this->modelsManager->executeQuery($phql, $param['bind']);
            } else {
                $ret = $this->modelsManager->executeQuery($phql);
            }

            //判断查询结果
            if (count($ret)) {
                $data = $ret->toArray();
                unset($ret);
                return (int)$data[0]['counts'];
            }
            return false;
        }
    }

    /**
     * @desc 强制都主库
     * @param $name 锁名前缀
     */
    public function setLockDB($name)
    {
        if($this->lock->getLock($name)){
            $model = new BaseModel;
            $model -> isLock();
        }
    }

    /**
     * 数据库连接切换
     * @param $rw string
     * return class
     * @author  康涛
     */
    protected function switchRwDb($rw)
    {
        if($rw===\Shop\Models\BaseModelEnum::DB_WRITE){
            return $this->model->getWriteConnection();
        }
        return $this->model->getReadConnection();
    }

    /**
     * @desc 悲观锁
     * @param $param
     */
    public function PLockUpdate($param)
    {
        $this->dbWrite->begin();
        try{
            $oneRes = $this->getData([
                'column' => $param['query']['column'],
                'table'=> $param['table'],
                'where' => 'where '.$param['query']['where'].' FOR UPDATE',
                'bind' => $param['query']['bind']
            ],1)[$param['query']['column']];
            if($oneRes){
                $is_update = $this->updateData([
                   'column' => $param['query']['column']." = :ind_num:",
                    'table' => $param['table'],
                    'where' => 'where '.$param['query']['column'].' = :column:',
                    'bind' => [
                        'ind_num' => $param['update']['ind_num'],
                        $param['query']['where']
                    ]
                ]);
                if($is_update == false){
                    throw new \Exception('更新失败');
                }
            }else{
                throw new \Exception('查询为空');
            }
        }catch (\Exception $e){
            $this->dbWrite->rollback();
        }
            $this->dbWrite->commit();

    }

    /**
     * @desc 数字型字段更新方法 预防超卖等高频安全性数字型数据更新
     * @param $param
     *          查询sql特定条件数量
     * @package redis
     * @return array
     * @author 邓永军
     */
    public function columnUpdate($param)
    {
        $redis = $this->cache;
        $redis->selectDb(11);
        $cache_prefix_key = $param['prefix'].'_counter';
        $column = $param['column']; //单个列
        $cache_value = $redis->getValue($cache_prefix_key);
        $column_info = $this->getData([
            'column'=>$column,
            'table'=>$param['table'],
            'where'=>'where '.$param['where'],
            'bind'=>$param['bind']
        ],1)[$column];
        if($cache_value != $column_info){
            $redis->setValue($cache_prefix_key,$column_info);
            return $this->columnUpdate($param);
        }
        if($cache_value !== false && $cache_value >= 0){
            //有缓存数量
            switch ($param['cache_ctrl']){
                case 'inc':
                    $res = $redis->setValue($cache_prefix_key,$redis->getValue($cache_prefix_key) + $param['order_num']);
                    break;
                case 'dec':
                    $res = $redis->setValue($cache_prefix_key,$redis->getValue($cache_prefix_key) - $param['order_num']);
                    break;
            }
            if($res !== false ){
                $update_num = $redis->getValue($cache_prefix_key);
                $param['bind'][$column] = $update_num;
                $this->dbWrite->begin();
                $update_res = $this->updateData([
                    'column' => $column.' = :'.$column.':',
                    'table' => $param['table'],
                    'where' => 'where '.$param['where'],
                    'bind' => $param['bind']
                ]);
                if($update_res !== false){
                    $this->dbWrite->commit();
                    return ['code'=>200,"msg"=>'更新成功','value'=> $update_num];
                }else{
                    $this->dbWrite->rollback();
                    switch ($param['cache_ctrl']){
                        case 'inc':
                            $redis->setValue($cache_prefix_key,$update_num - $param['order_num']);
                            break;
                        case 'dec':
                            $redis->setValue($cache_prefix_key,$update_num + $param['order_num']);
                            break;
                    }
                    return ['code'=>400,"msg"=>'更新失败'];
                }
            }else{
                return ['code'=>400,"msg"=>'超出限制'];
            }
        }

    }

    /**
     * 按数据对应key的值返回关联数组
     *
     * @param array $data 数据
     * @param $relationKey 数组对应的索引
     * @return array 关联数组
     * @autor ZHQ
     */
    public function relationArray($data, $relationKey) {
        $result = array();
        if ($data && $relationKey) {
            foreach ($data as $item) {
                if (isset($item[$relationKey])) {
                    $result[$item[$relationKey]] = $item;
                }
            }
            return $result;
        }
    }
}