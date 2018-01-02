<?php
/**
 * 百洋钱包模型
 * Class BaiyangBestyoopay
 * Author: edgeto/qiuqiuyuan
 * Date: 2017/6/27
 * Time: 15:00
 */ 
namespace Shop\Models;
use Phalcon\Validation;
use Phalcon\Mvc\Model\Message;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Mvc\Model\Validator\Numericality as NumericalityValidator;
class BaiyangBestyoopay extends BaseModel
{

	/**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'baiyang_bestyoopay';
    }

    /**
     * [beforeCreate description]
     * @return [type] [description]
     */
    public function beforeCreate()
    {
        $this->add_time = time();
    }

}