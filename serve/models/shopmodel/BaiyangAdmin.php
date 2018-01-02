<?php

namespace Shop\Models;
use Phalcon\Validation;
use Phalcon\Mvc\Model\Message;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Mvc\Model\Validator\Numericality as NumericalityValidator;
use Shop\Models\BaseModel;

class BaiyangAdmin extends BaseModel
{

    /**
     *
     * @var string
     * @Primary
     * @Identity
     * @Column(type="string", length=20, nullable=false)
     */
    public $id;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    public $admin_account;

    /**
     *
     * @var string
     * @Column(type="string", length=32, nullable=false)
     */
    public $admin_password;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_lock;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $add_time;

    /**
     * @var integer
     */
    public $site_id;

    /**
     * @var string
     */
    public $imgurl;


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
        return 'baiyang_admin';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangAdmin[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BaiyangAdmin
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
        if(isset($this->admin_password) && empty($this->admin_account)){
            $message = new Message(
                "管理员名称不能为空！",
                "admin_account",
                "String"
            );
            $this->appendMessage($message);
            return false;
        }
        if(isset($this->admin_password) && empty($this->admin_password)){
            $message = new Message(
                "管理员密码不能为空！",
                "admin_password",
                "String"
            );
            $this->appendMessage($message);
            return false;
        }
        $validator->add(
            "admin_account",
            new Uniqueness(
                [
                    "message" => "管理员名称已存在！",
                ]
            )
        );
        return $this->validate($validator);
    }

    /**
     * [beforeCreate description]
     * @return [type] [description]
     * @author [edgeto/qiuqiuyuan] <[<email address>]>
     */
    public function beforeCreate()
    {
        $this->add_time = time();
        $this->admin_password = md5($this->admin_password);
    }

    /**
     * [beforeUpdate description]
     * @return [type] [description]
     * @author [edgeto/qiuqiuyuan] <[<email address>]>
     */
    public function beforeUpdate()
    {
        $this->admin_password = md5($this->admin_password);
    }

}
