<?php
/**
 * Created by PhpStorm.
 * User: hp
 * Date: 2017/1/18
 * Time: 13:46
 */
namespace Shop\Collections;

use Phalcon\Di;
use Phalcon\Mvc\Collection;

class BaseCollections extends Collection
{
    public function initialize()
    {
        $this->setConnectionService('MongoMaster');
    }
    protected static $instance = null;

    public static function getInstance()
    {
        if(empty(static::$instance)){
            static::$instance=new static();
        }
        return static::$instance;
    }

    public function __clone()
    {
        // TODO: Implement __clone() method.
    }

    /**
     * @desc 获取mongodb数据
     * getMongoData('syslog.test_log',
     ['x' => ['$gt' => 1]],
     ['projection' => ['_id' => 0],'sort' => ['x' => -1],])
     * @param $collection
     * @param array $condition
     * @param array $option
     * @return array
     * @author 邓永军
     */
    public function getMongoData($collection,$condition = [],$option = [])
    {
        $record = [];
        $manager= $this->getDI()->get('MongoSlave');
        $query = new \MongoDB\Driver\Query($condition,$option);
        $cursor = $manager->executeQuery($collection,$query);
        foreach ($cursor as $document) {
            $record[] = (array) $document;
        }
        return $record;
    }

    /**
     * @desc 添加mongodb数据
     * addMongoData('syslog.test_log',['name' => 'baiyang']) 单条添加
     * addMongoData('syslog.test_log',[['name' => 'baiyang'],['name' => 'baiyang2']],1) 多条添加
     * @param $collection
     * @param array $insert_data
     * @param int $is_multi_add
     * @return \MongoDB\Driver\WriteError[]
     * @author 邓永军
     */
    public function addMongoData($collection,$insert_data = [],$is_multi_add = 0)
    {
        $manager = $this->getDI()->get('MongoMaster');
        $bulk = new \MongoDB\Driver\BulkWrite;
        $num_arr = [];
        if($is_multi_add == 1){
            foreach ($insert_data as $data){
                $num_arr[] = $bulk->insert($data);
            }
        }else{
            $num_arr[] = $bulk->insert($insert_data);
        }
        $writeConcern = new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 1000);
        try {
            return $manager->executeBulkWrite($collection, $bulk,$writeConcern);
        }catch (\MongoDB\Driver\Exception\BulkWriteException $e){
            return $e->getWriteResult()->getWriteErrors();
        }
    }

    /**
     * @desc 更新mongodb数据
     * updateMongoData('syslog.test_log',
     ['x' => 2],
    ['$set' => ['name' => 'baiyang_2017', 'url' => 'baiyangwang.com']],
    ['multi' => false, 'upsert' => false])
     * @param $collection
     * @param $condition
     * @param $setter
     * @param $option
     * @return \MongoDB\Driver\WriteError[]
     * @author 邓永军
     */
    public function updateMongoData($collection,$condition,$setter,$option)
    {
        $bulk = new \MongoDB\Driver\BulkWrite;
        $bulk->update($condition,$setter,$option);
        $manager = $this->getDI()->get('MongoMaster');
        $writeConcern = new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 1000);
        try {
            return $manager->executeBulkWrite($collection, $bulk,$writeConcern);
        }catch (\MongoDB\Driver\Exception\BulkWriteException $e){
            return $e->getWriteResult()->getWriteErrors();
        }
    }

    /**
     * @desc 删除mongodb数据
     * deleteMongoData('syslog.test_log',
     * ['x' => 1], 1
     * ) limit 为 1 时，删除第一条匹配数据
     *  deleteMongoData('syslog.test_log',
     * ['x' => 1], 0
     * ) limit 为 0 时，删除所有匹配数据
     * @param $collection
     * @param $condition
     * @param $limit
     * @return \MongoDB\Driver\WriteError[]
     * @author 邓永军
     */
    public function deleteMongoData($collection,$condition,$limit)
    {
        $bulk = new \MongoDB\Driver\BulkWrite;
        $bulk->delete($condition, ['limit' => $limit]);
        $manager = $this->getDI()->get('MongoMaster');
        $writeConcern = new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 1000);
        try {
            return $manager->executeBulkWrite($collection, $bulk,$writeConcern);
        }catch (\MongoDB\Driver\Exception\BulkWriteException $e){
            return $e->getWriteResult()->getWriteErrors();
        }
    }
}