<?php
/**
 * Created by PhpStorm.
 * User: 康涛
 * Date: 2016/8/3 0003
 * Time: 下午 4:30
 */

namespace Shop\Models;

use Phalcon\Mvc\Model;

class BaseModel extends Model
{
    /**
     * 设置数据库连接
     */
    public function initialize()
    {
        $this->setReadConnectionService('dbRead'); //读库
        $this->setWriteConnectionService('dbWrite'); //写库
        $this->setup(['notNullValidations' => false]);
    }

    /**
     * 判断锁是否存在，存在读取写库
     */
    public function isLock()
    {
        $this->setReadConnectionService('dbWrite');
    }

    /**
     * 封装phalcon model的create方法，实现仅更新数据变更字段，而非所有字段更新
     * parent::create？？不行!!原因？？phalcon版本问题？？
     * Author: edgeto
     * Date: 2017/5/9
     * Time: 15:52
     * @param array|null $data
     * @param null $whiteList
     * @return bool
     */
    public function icreate(array $data = null, $whiteList = null){
        if(count($data) > 0){
            $attributes = $this->getModelsMetaData()->getAttributes($this);
            $skip = array_diff($attributes, array_keys($data));
            $skipAttributes = array();
            foreach ($skip as $key => $value) {
                if($value != 'update_time' && $value != 'add_time') {
                    $skipAttributes[] = $value;
                }
            }
            $this->skipAttributesOnCreate($skipAttributes);
        }
        return parent::create($data, $whiteList);
    }

    /**
     * 封装phalcon model的update方法，实现仅更新数据变更字段，而非所有字段更新
     * parent::update？？不行!!原因？？phalcon版本问题？？
     * Author: edgeto
     * Date: 2017/5/9
     * Time: 15:52
     * @param array|null $data
     * @param null $whiteList
     * @return bool
     */
    public function iupdate(array $data = null, $whiteList = null){
        if(count($data) > 0){
            $attributes = $this->getModelsMetaData()->getAttributes($this);
            $skip = array_diff($attributes, array_keys($data));
            $skipAttributes = array();
            foreach ($skip as $key => $value) {
                if($value != 'update_time') {
                    $skipAttributes[] = $value;
                }
            }
            $this->skipAttributesOnUpdate($skipAttributes);
        }
        return parent::save($data, $whiteList);
    }

    /**
     * [insertId description]
     * @param  string $source [description]
     * @return [type]         [description]
     */
    public function insertId($source = '')
    {
        if(!$source){
            $source = $this->getSource();
        }
        return $this->getWriteConnection()->lastInsertId($source);
    }

}