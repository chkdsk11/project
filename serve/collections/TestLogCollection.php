<?php
/**
 * Created by PhpStorm.
 * User: hp
 * Date: 2017/1/19
 * Time: 13:51
 */
namespace Shop\Collections;
use Phalcon\Mvc\MongoCollection;

class TestLogCollection extends MongoCollection
{

    public $error;
    public $msg;
    public $type;

    public function getSource()
    {
        return 'test_log';
    }
}