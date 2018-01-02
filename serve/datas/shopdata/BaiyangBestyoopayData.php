<?php
/**
 * 百洋钱包数据处理
 * Class BaiyangBestyoopayData
 * Author: edgeto
 * Date: 2017/06/26
 * Time: 15:52
 */
namespace Shop\Datas;
use Shop\Models\CacheKey;
use Shop\Models\BaiyangBestyoopay;

class BaiyangBestyoopayData extends BaseData
{

	/**
     * 必须声明此静态属性，单例模式下防止实例对象覆盖
     * @var null
     */
    protected static $instance = null;

    /**
     * [$table description]
     * @var string
     */
    public $table = "\\Shop\\Models\\BaiyangBestyoopay";

    /**
     * [$error description]
     * @var string
     */
    public $error = '';

    /**
     * [add description]
     * @param array $data [description]
     */
    public function add($data = array())
    {
        if(empty($data)){
            $this->error = '参数不完整或者参数错误！';
            return false;
        }
        $BaiyangBestyoopay = new BaiyangBestyoopay();
        $res = $BaiyangBestyoopay->icreate($data);
        if(empty($res)){
            $messages = $BaiyangBestyoopay->getMessages();
            foreach ($messages as $key => $message) {
                $this->error['message'] = $message->getMessage();
                $this->error['field'] = $message->getField();
                break;
            }
            return false;
        }
        return true;
    }

}