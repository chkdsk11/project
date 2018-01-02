<?php
/**
 * Created by PhpStorm.
 * User: lw
 * Date: 2016/8/16
 * Time: 15:50
 */

namespace Shop\Admin\Controllers;
use Shop\Services\AppsettingService;


class AppsettingController extends ControllerBase
{
    public $service;
    public function initialize()
    {
        parent::initialize();
        $this->service =  AppsettingService::getInstance();
    }

    public function orderAction()
    {
        if($this->request->isPost()){
            $param = $this->postParam($this->request->getPost(), 'trim', '');
            $param['config_sign'] = 'order_auto_audit_pass_time';
            //print_r($param);die;
            $result =$this->service->updateOrderConf($param);
            return $this->response->setJsonContent($result);
        }else{
            $conf = $this->service->getorderconf();
            //注入函数到模版
            $volt = $this->di->get("volt", [$this->view, $this->di]);
            $compiler = $volt->getCompiler();
            $compiler->addFunction('in_array', 'in_array');
            $set_data = array();
            foreach($conf as $v){
                if($v['config_sign']=='order_no_audit_goods_type'){// 订单不开启自动审核的类型
                    $set_data[$v['config_sign']] = explode(',',$v['config_value']);
                }else{
                    $set_data[$v['config_sign']] = $v['config_value'];
                }
            }
            $this->view->setVar('conf',$set_data);
        }

    }

    public function accesoriesAction(){
        if($this->request->isPost()){
            $param = $this->postParam($this->request->getPost(), 'trim', '');

            $param['config_sign'] = 'displayAPPaccesories';

            $result =$this->service->editData($param);
            return $this->response->setJsonContent($result);

        }else{
            $conf = $this->service->getaccesoriesconf();
            $this->view->setVar('conf',$conf);
        }
    }
}