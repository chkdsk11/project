<?php

namespace Shop\Home\Services;

use Shop\Home\Datas\BaiyangUserData;
use Shop\Home\Datas\BaseData;
use Shop\Home\Listens\BaseListen;
use Shop\Models\HttpStatus;

use Phalcon\Events\Manager as EventsManager;

class SearchService extends BaseService {
    
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance = null;

    // 加载监听器
    public static function getInstance() {
        if(empty(static::$instance)){
            static::$instance = new SearchService();
        }
        $eventsManager = new EventsManager();
        $eventsManager->attach('baseListen',new BaseListen());
        static::$instance->setEventsManager($eventsManager);
        return static::$instance;
    }

    /**
     * @desc pc智能推荐及结果数量
     * @param array $param
     *       -word string 搜索关键字
     * @return array
     * @author 朱耀昆
     */
    public function pc_getWords($param)
    {

        #此处不使用extract，避免空值报错
        $word = $param['searchName'];

        $data = array();
        if (!$word) {
            $code = HttpStatus::PARAM_ERROR;            #参数不足
        } else {
            $param = array(
                'word' => $word,
            );

            $url = 'http://pces.baiyjk.com/pces/getWords.do';
            $list = $this->func->curl_do($param, $url);
            #print_r($list);die;
            if (isset($list->code) && ($list->code==200)) {
                $dataNum = $list->dataNum;
                if(!empty($dataNum)){
                    foreach($dataNum as $key=>$val){
                        $arr = array();
                        $arr['name'] = $key;
                        $arr['result_count'] = $val;
                        $data[] = $arr;
                    }
                }

                $code = HttpStatus::SUCCESS;
            } else {
                $code = HttpStatus::EMPTY_RESULT;
            }
        }
        return $this->uniteReturnResult($code, $data);
    }

    /**
     * @desc app智能推荐及结果数量
     * @param array $param
     *       -word string 搜索关键字
     * @return array
     * @author 朱耀昆
     */
    public function app_getWords($param)
    {

        #此处不使用extract，避免空值报错
        $word = $param['searchName'];

        $data = array();
        if (!$word) {
            $code = HttpStatus::PARAM_ERROR;            #参数不足
        } else {
            $param = array(
                'searchName' => $word,
            );

            $url = 'http://appes.baiyjk.com/appes/getAppNum.do';
            $list = $this->func->curl_do($param, $url);
            #print_r($list);die;
            if (isset($list->code) && ($list->code==200)) {
                $dataNum = $list->dataNum;
                if(!empty($dataNum)){
                    foreach($dataNum as $key=>$val){
                        $arr = array();
                        $arr['name'] = $key;
                        $arr['result_count'] = $val;
                        $data[] = $arr;
                    }
                }

                $code = HttpStatus::SUCCESS;
            } else {
                $code = HttpStatus::EMPTY_RESULT;
            }
        }

        return $this->uniteReturnResult($code, $data);
    }

    /**
     * @desc wap智能推荐及结果数量
     * @param array $param
     *       -word string 搜索关键字
     * @return array
     * @author 朱耀昆
     */
    public function wap_getWords($param)
    {

        #此处不使用extract，避免空值报错
        $word = $param['searchName'];

        $data = array();
        if (!$word) {
            $code = HttpStatus::PARAM_ERROR;            #参数不足
        } else {
            $param = array(
                'searchName' => $word,
            );

            $url = 'http://wapes.baiyjk.com/wapes/getWapNum.do';
            $list = $this->func->curl_do($param, $url);
            if (isset($list->code) && ($list->code==200)) {
                $dataNum = $list->dataNum;
                if(!empty($dataNum)){
                    foreach($dataNum as $key=>$val){
                        $arr = array();
                        $arr['name'] = $key;
                        $arr['result_count'] = $val;
                        $data[] = $arr;
                    }
                }
                $code = HttpStatus::SUCCESS;
            } else {
                $code = HttpStatus::EMPTY_RESULT;
            }
        }

        return $this->uniteReturnResult($code, $data);
    }




}
