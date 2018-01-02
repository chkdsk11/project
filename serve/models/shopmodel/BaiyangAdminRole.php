<?php
/**
 * 管理员角色数据模型
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
use Shop\Models\BaseModel;

class BaiyangAdminRole extends BaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $role_id;

    /**
     *
     * @var string
     * @Column(type="string", length=128, nullable=false)
     */
    public $role_name;

    /**
     *
     * @var string
     * @Column(type="string", length=2048, nullable=false)
     */
    public $menu_id;

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $site_id;

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_enable;

    /**
     *
     * @var string
     * @Primary
     * @Identity
     * @Column(type="string", length=1024, nullable=false)
     */
    public $controller_id;

    /**
     *
     * @var string
     * @Primary
     * @Identity
     * @Column(type="string", length=1024, nullable=false)
     */
    public $module_id;

    /**
     *  初始方法
     */
    public function initialize()
    {
        parent::initialize();
        $this->setup([
            'notNullValidations'=>false
        ]);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_admin_role';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangAdminRole[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangAdminRole
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    /**
     * 验证
     * @return [type] [description]
     * @author [edgeto/qiuqiuyuan] <[<email address>]>
     */
    public function validation()
    {
        $validator = new Validation();
        if(isset($this->role_name) && empty($this->role_name)){
            $message = new Message(
                "角色名称不能为空！",
                "role_name",
                "String"
            );
            $this->appendMessage($message);
            return false;
        }
        $validator->add(
            "role_name",
            new Uniqueness(
                [
                    "message" => "角色名称已存在！",
                ]
            )
        );
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

}
