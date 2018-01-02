<?php
/**
 * 权限资源数据模型
 * Class BaiyangAdminRole
 * Author: edgeto/qiuqiuyuan
 * Date: 2017/5/9
 * Time: 15:52
 */
namespace Shop\Models;
use Phalcon\Validation;
use Phalcon\Mvc\Model\Message;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Mvc\Model\Validator\Numericality as NumericalityValidator;

class BaiyangAdminResource extends BaseModel
{

	/**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_admin_resource';
    }

    /**
     * 验证
     * @return [type] [description]
     * @author [edgeto/qiuqiuyuan] <[<email address>]>
     */
    public function validation()
    {
    	$validator = new Validation();
    	if(empty($this->name)){
    		$message = new Message(
                "名称不能为空！",
                "name",
                "String"
            );
            $this->appendMessage($message);
            return false;
    	}
    	if(!is_numeric($this->show_order) || $this->show_order < 0){
    		$message = new Message(
                "导航排序只能是大于或等于0整数！",
                "show_order",
                "Numeric"
            );
            $this->appendMessage($message);
            return false;
    	}
        return $this->validate($validator);
    }

    /**
     * [beforeCreate description]
     * @return [type] [description]
     */
    public function beforeCreate()
    {
        $this->add_time = time();
        $this->update_time = time();
    }

    /**
     * [beforeUpdate description]
     * @return [type] [description]
     */
    public function beforeUpdate()
    {
        // Set the modification date
        $this->update_time = time();
    }

    /**
     * 使用原生Sql来批量更新
     * @param  array  $data [description]
     * @return [type]       [description]
     */
    public function insertAll($data = array(),$source = '')
    {
        if(empty($source)){
            $source = $this->getSource();
        }
        $sql = "INSERT INTO `{$source}` (";
        if(isset($data[0])){
            $fields = array_keys($data[0]);
            foreach ($fields as $field) {
                $sql .= "`" . $field . "`,";
            }
            $sql = rtrim($sql,',') . ") values ";
            foreach ($data as $key => $value) {
                $sql .= "(";
                foreach ($value as $k => $v) {
                    $sql .= "'" . $v . "',";
                }
                $sql = rtrim($sql,',');
                $sql .= "),";
            }
            $sql = rtrim($sql,',');
            $res = $this->getWriteConnection()->execute($sql);
            return $res;
        }
        return false;
    }

}