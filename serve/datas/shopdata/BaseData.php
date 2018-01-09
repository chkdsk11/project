<?php
/**
 * Created by PhpStorm.
 * User: 吴俊华
 * Date: 2016/8/8
 * Time: 下午 4:45
 */

namespace Shop\Datas;
use Phalcon\Mvc\User\Component;


class BaseData extends Component
{
    protected static $instance = null;

    /**
     * 单例
     * @return class
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
     * @return array|bool $result|false 查找到的记录或false
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

            //分组
            if (isset($param['group']) && !empty($param['group'])) {
                $phql .= " {$param['group']}";
            }

            //排序条件
            if (isset($param['order']) && !empty($param['order'])) {
                $phql .= " {$param['order']}";
            }

            //分页条件
            if (isset($param['limit']) && !empty($param['limit'])) {
                $phql .= " {$param['limit']}";
            }
            //test
            if($_SESSION['admin_account'] == 'zhengwenzhong'){
                var_dump($phql);
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
                if($returnOne == true){
                    return $data[0];
                }
                return $data;
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
     * @desc 根据条件查找记录
     * @param string $selections 查找需要返回的字段名
     * @param string $table 表名的ORM映射，如：'\Shop\Models\BaiyangGoods'
     * @param array $conditions 键值对数组，如：['id'=>1,'name'=>'test1']
     * @param string $whereStr 查询的条件，如'id=:id: and name=:name:' (字段跟上面的键值对数组要对应)
     * @return array|bool $result|false 查找到的记录或false
     * @author 吴俊华
     * @date 2016-08-15
     * @param string $joinStr Join语句,用于联查
     * @modify 邓永军
     * @date 2016-09-19
     */
    public function select($selections = '',$table = '',$conditions = [],$whereStr = '',$joinStr='')
    {
        if(empty($selections) || empty($table)){
            return false;
        }

        //连表
        if(empty($joinStr)){
            $phql ="select {$selections} from {$table} where {$whereStr}";
        }else{
            $phql ="select {$selections} from {$table} {$joinStr} where {$whereStr}";
        }

        //where条件
        if(empty($whereStr)){
            if(empty($joinStr)){
                $phql ="select {$selections} from {$table}";
            }else{
                $phql ="select {$selections} from {$table} {$joinStr}";
            }
        }

        //数据绑定
        if (!empty($conditions)) {
            $result = $this->modelsManager->executeQuery($phql,$conditions);
        } else {
            $result = $this->modelsManager->executeQuery($phql);
        }

        if(count($result) > 0){
            $result = $result->toArray();
            return $result;
        }
        return false;
    }

    /**
     * @desc 插入一条记录
     * @param string $table 表名的ORM映射，如：'\Shop\Models\BaiyangGoods'
     * @param array $param 插入的数据，键值对数组，如：['id'=>1,'name'=>'test1']
     * @param bool $returnRow 是否开启插入数据后的id(true为开启，false为不开启)
     * @return bool|string true|false|插入数据后的id
     * @author 吴俊华
     * @date 2016-08-15
     */
    public function insert($table = '',$param = [],$returnRow = false)
    {
        if(empty($table) || empty($param)){
            return false;
        }
        $mappingTable = new $table();
        $result = $mappingTable->save($param);
        if(!empty($result)){
            if($returnRow){
                $insertId = $mappingTable->getWriteConnection()->lastInsertId($mappingTable -> getSource());
                return $insertId;
            }
            return $result;
        }
        return false;
    }

    /**
     * @desc 根据条件更新记录
     * @param string $columStr 更新的字段，如：name=:name:,price=:price:
     * @param string $table 表名的ORM映射，如：'\Shop\Models\BaiyangGoods'
     * @param array $conditions 键值对数组，如：['id'=>1,'name'=>'test1'] (里面要包含$columStr和$whereStr加起来的字段【字段不能重复】)
     * @param string $whereStr 查询的条件，如'id=:id: and name=:name:'
     * @return bool true|false 更新的结果信息
     * @author 吴俊华
     * @date 2016-08-15
     */
    public function update($columStr = '',$table = '',$conditions = [],$whereStr = '')
    {
        if(empty($columStr) || empty($table)){
            return false;
        }
        $phql = "update {$table} set {$columStr}  where {$whereStr}";

        //where条件
        if(empty($whereStr)){
            $phql = "update {$table} set {$columStr}";
        }
        //数据绑定
        if (!empty($conditions)) {
            $result = $this->modelsManager->executeQuery($phql,$conditions);
        } else {
            $result = $this->modelsManager->executeQuery($phql);
        }

        if (is_object($result)) {
            return $result->success();
        }
    }

    /**
     * @desc 根据条件更新记录
     * @param string $columStr 更新的字段，如：name=:name:,price=:price:
     * @param string $table 表名的ORM映射，如：'\Shop\Models\BaiyangGoods'
     * @param string $whereStr 查询的条件，如'id=:id: and name=:name:'
     * @return bool true|false 更新的结果信息
     * @author CSL
     * @date 2018-01-02
     */
    public function nativeUpdate($columStr = '',$table = '',$whereStr = '')
    {
        if(empty($columStr) || empty($table)){
            return false;
        }
        $sql = "update {$table} set {$columStr} ";
        //where条
        if ($whereStr) {
            $sql .= "where {$whereStr}";
        }

        $stmt = $this->dbRead->prepare($sql);
        return $stmt->execute();
    }

    /**
     * @desc 根据条件删除记录
     * @param string $table 表名的ORM映射，如：'\Shop\Models\BaiyangGoods'
     * @param array $conditions 键值对数组，如：['id'=>1,'name'=>'test1']
     * @param string $whereStr 删除的条件，如id=:id: and name=:name: (字段跟上面的键值对数组要对应)
     * @return bool true|false 删除的结果信息
     * @author 吴俊华
     * @date 2016-08-15
     */
    public function delete($table = '',$conditions = [],$whereStr = '')
    {
        if(empty($table)){
            return false;
        }
        $phql = "delete from {$table} where {$whereStr}";

        //where条件
        if(empty($whereStr)){
            $phql = "delete from {$table}";
        }

        //数据绑定
        if (!empty($conditions)) {
            $result = $this->modelsManager->executeQuery($phql,$conditions);
        } else {
            $result = $this->modelsManager->executeQuery($phql);
        }

        if (is_object($result)) {
            return $result->success();
        }
    }

    /**
     * @desc 统计满足条件的记录条数
     * @param string $table 表名的ORM映射，如：'\Shop\Models\BaiyangGoods'
     * @param array $conditions 键值对数组，如：['id'=>1,'price'=>'100']
     * @param string $whereStr 查询的条件，如'id > :id: and price > :price:' (字段跟上面的键值对数组要对应)
     * @return int|bool $rowCount|false 满足条件的记录条数或false
     * @author 吴俊华
     * @date 2016-08-16
     */
    public function count($table = '',$conditions = [],$whereStr = '')
    {
        if(empty($table)){
            return false;
        }
        try{
            $rowCount = $table::count(
                [
                    $whereStr,
                    "bind" => $conditions
                ]
            );
            return $rowCount;
        } catch (\Exception $ex){
            return false;
        }
    }

    /**
     * @desc 根据条件更新记录
     * @param string $key 要查找的缓存键名
     * @return array  要查找的结果信息
     * @author 梁伟
     * @date 2016-09-07
     */
    public function getCacheRedis($key)
    {
        return $this->cache->getValue($key);
    }

    /**
     * @desc 根据条件更新记录
     * @param string $key 要缓存的键名
     * @pram array() $value 要缓存的值
     * @param int 缓存时间(默认0，永久)
     * @author 梁伟
     * @date 2016-09-07
     */
    public function setCacheRedis($key,$value,$time=0)
    {
        return $this->cache->setValue($key,$value,$time);
    }

    /**
     * @desc 删除缓存记录
     * @param string $key 要删除的键名
     * @author 梁伟
     * @date 2016-09-07
     */
    public function delCacheRedis($key)
    {
        return $this->cache->delete($key);
    }

    /**
     * @desc 选择缓存库
     * @param string $num 要选择的库
     * @author 梁伟
     * @date 2016-09-07
     */
    public function setDbRedis($num)
    {
        return $this->cache->selectDb($num);
    }

}
