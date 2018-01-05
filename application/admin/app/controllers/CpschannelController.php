<?php
/**
 * Created by PhpStorm.
 * User: lw
 * Date: 2016/8/16
 * Time: 15:50
 */

namespace Shop\Admin\Controllers;
//use Shop\Services\;
use Shop\Datas\BaseData;
use Shop\Services\BaseService;
use Shop\Services\CpsActivityService;

class CpsChannelController extends ControllerBase
{
    private $type_name = array(
        1 => '全场',
        3 => '品牌',
        4 => '商品'
    );

    //SPU列表
    public function listAction()
    {
        $base = BaseData::getInstance();
        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }
        $this->view->setVar('channel',isset($param['channel'])?$param['channel']:'');
        // print_r($param);exit;
        $param['page']  =   $this->request->get('page','trim',1);
        $param['url'] = $this->automaticGetUrl();
        // echo 1;
        $data['table'] = '\Shop\Models\BaiyangCpsChannel';
        if($param['channel']){
            $where =" and  ( channel_id LIKE '%".$param['channel']."%'";
            $where .=" OR channel_name LIKE '%".$param['channel']."%' ) ";
        }
        $data['where'] = "where channel_status<>0".$where;


        $counts = $base->countData($data);
        if(empty($counts)){
            return array('res' => 'success','list' => 0);
        }



        $pages['page'] = (int)isset($param['page'])?$param['page']:1;//当前页
        $pages['counts'] = $counts;
        $pages['url'] = $param['url'];
        $page = $this->page->pageDetail($pages);

        $data['column'] = '*' ;
        $data['order'] = 'ORDER BY channel_id DESC';
        $data['limit'] = "LIMIT ".$page['record'].','.$page['psize'];

        $result =  $base->getData($data);

        $list = [
            'res'  => 'success',
            'list' => $result,
            'page' => $page['page']
        ];
        // print_r($list); exit;

        $this->view->setVar('list',$list);
    }
    public function pauseAction(){
        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }

        if($param['id']!=""){
            $base = BaseData::getInstance();
            $table = '\Shop\Models\BaiyangCpsChannel';
            if($param['dt']!=""){
                $columStr = "channel_status = :channel_status:";
                $whereStr = "channel_id = {$param['id']}";
                //$res = $base->update('category_path=:category_path:',$table,['id'=>$v['id'],'category_path'=>$path],'id=:id:');
                $res = $base->update($columStr,$table,['channel_status'=>$param['dt']],$whereStr);
                if($res){
                    $data['status']='success';
                    die(json_encode($data));
                }
            }

        }
    }
    public function deleAction(){
        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }
        if($param['id']!=""){
            $base = BaseData::getInstance();
            $table = '\Shop\Models\BaiyangCpsChannel';
            $table_s =    '\Shop\Models\BaiyangCpsUser';


            $whereStr = "channel_id = {$param['id']}";

            $res_s = $base->update("cps_status = :cps_status:",$table_s,['cps_status'=>0],$whereStr);
            if($res_s){
                $res = $base->update("channel_status = :channel_status:",$table,['channel_status'=>0],$whereStr);
                if($res){
                    $data['status']='success';
                    die(json_encode($data));
                }
            }

        }
    }
    public function editAction(){
        $base = BaseData::getInstance();
        $par = $_GET;
        if($par['id']){
            $channel_one =     $this->get_channel_one($par['id']);
            $data['relation_list']  = $this->get_cps_activity_detail($par['id']);
            //print_r( $data['relation_list']);exit;

            $data['channel'] = $channel_one[0];
            $data['has_result'] =1;
            $data['coupon']  = $this->get_coupon($par['id']);
            //print_r($data);exit;
            if($_POST&&$_POST['channel_id']!=""){

                $param = $this->request->getPost();
                $param =  $this->check_data($param,$pid=1);

                if(isset($param['success'])&&$param['success']!=""){
                    return $this->response->setJsonContent(BaseService::getInstance()->arrayData($param['success'], '', '', ''));
                }
                $param['channel_id'] =  $_POST['channel_id'];
                $param['logo'] = $_POST['brand_logo'];

                $param['username'] = $_SESSION['admin']['admin_account'];
                $table = '\Shop\Models\BaiyangCpsChannel';
                $where = " channel_id = {$param['channel_id']} ";
                $columStr = "wap_back_amount=:wap_back_amount:,channel_name=:channel_name:,title=:title:,content=:content:,link=:link:,logo=:logo:,channel_image=:channel_image:,back_amount=:back_amount:,tags=:tags:,creator=:creator:,
                updater=:updater:,is_permanent=:is_permanent:,expire_day=:expire_day:,description=:description:" ;

                $base->update($columStr,$table,['wap_back_amount'=>$param['wap_back_amount'],'channel_name'=>$_POST['channel_name'],'title'=>$param['title'],'content'=>$param['content'],'link'=>$param['link'],'logo'=>$param['logo']
                    ,'channel_image'=>$param['channel_image'],'back_amount'=>$param['back_amount'],'tags'=>$param['tags'],'creator'=>$param['username'],
                    'updater'=>time(),'is_permanent'=>$param['is_permanent'],'expire_day'=>$param['expire_day'],'description'=>$param['description']],$where,true);


                if($param['is_permanent']==1&&$param['channel_id']!=""){

                    $table = '\Shop\Models\BaiyangCpsChannelRebate';
                    $where = " channel_id = {$param['channel_id']}";
                    $base->delete($table,'',$where);
                    $base->insert($table,['channel_id'=>$param['channel_id'],'back_percent'=>$_POST['back_percent'],'first_rebate'=>$_POST['first_rebate']],true);

                }else if($param['is_permanent']==0&&$param['channel_id']!="") {
                    $table = '\Shop\Models\BaiyangCpsChannelRebate';
                    $where = " channel_id = {$param['channel_id']} ";
                    $base->delete($table,'',$where);
                }
                if(!empty($param['first_rebates']) || !empty($param['back_percents'])){
                    if($param['channel_id']!=""){
                        $tables = '\Shop\Models\BaiyangCpsBackActivityRelation';
                        $where = " channel_id = {$param['channel_id']} and act_id = 0 ";
                        //DELETE FROM `baiyang_cps_back_activity_relation` WHERE (`act_id`='0') AND (`belong_id`='6033136') AND (`back_percent`='4.00') AND (`first_rebate`='3.00') AND (`channel_id`='110') LIMIT 1


                        $base->delete($tables,'',$where);
                        foreach ($param['item_list'] as $key => $value) {
                            $percent = floatval($_POST['back_percents'][$key]);
                            $first = floatval($_POST['first_rebates'][$key]);
                            $p =  $base->insert($tables,['act_id'=>0,'belong_id'=>$value,'back_percent'=>$percent,'first_rebate'=>$first,'channel_id'=>$param['channel_id']],true);
                        }}

                }else if($param['channel_id']!="") {
                    $table = '\Shop\Models\BaiyangCpsBackActivityRelation';
                    $where = " channel_id = {$param['channel_id']} ";
                    $base->delete($table,'',$where);
                }
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData("修改成功", '/cpschannel/list'));
            }
            $this->view->setVar('id',$par['id']);
            $this->view->setVar('list',$data);
        }

    }
    public function addAction(){
        $base = BaseData::getInstance();

        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }

        if(isset($param['goods'])&&$param['goods']){
            $data['table'] = '\Shop\Models\BaiyangGoods';

            $where = " where  product_type = 0 AND (sale_timing_app = 1 OR sale_timing_wap = 1 OR sale_timing_wechat = 1 OR is_on_sale = 1) ";
            if(isset($param['product_id_']) && !empty($param['product_id_'])){
                $where .= "AND id in ({$param['product_id_']}) ";
            }
            if(isset($param['name']) && !empty($param['name'])){
                $where .= "AND goods_name like '%{$param['name']}%' ";
            }

            $data['where'] = $where;
            // $data['order'] = 'ORDER BY id DESC';
            $data['column'] = 'id as product_id, goods_name as name' ;
            $result = $base->getData($data);
            if($result){
                $html ='';
                foreach ($result as $val){
                    $html .=  "<option value='".$val['product_id']."'>".$val['name']."</option>";
                }
                die($html);
            }else{
                die("full");
            }
        }

        if($_POST){
            $param = $this->postParam($this->request->getPost(), 'trim', '', 'add_time');
            $param =  $this->check_data($param);
            $param['username'] =$_SESSION['admin']['admin_account'];
            if(isset($param['success'])){
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData($param['success'], '', '', 'error'));
            }else{

                $table = '\Shop\Models\BaiyangCpsChannel';
                $rebate['channel_id'] =    $base->insert($table,['wap_back_amount'=>$_POST['wap_back_amount'],'channel_name'=>$param['channel_name'],'title'=>$param['title'],'content'=>$param['content'],'link'=>$param['link'],'logo'=>$_POST['brand_logo']
                    ,'channel_image'=>$param['channel_image'],'back_amount'=>$_POST['back_amount'],'tags'=>$param['tags'],'creator'=>$param['username'],'add_time'=>$param['add_time'],
                    'updater'=>$param['add_time'],'is_permanent'=>$param['is_permanent'],'expire_day'=>$param['expire_day'],'description'=>$param['description']],true);

                $table = '\Shop\Models\BaiyangCpsChannelRebate';
                $base->insert($table,['channel_id'=>$rebate['channel_id'],'back_percent'=>$_POST['back_percent'],'first_rebate'=>$_POST['first_rebate']],true);


                $table = '\Shop\Models\BaiyangCpsBackActivityRelation';
                if(!empty($param['first_rebates']) || !empty($param['back_percents'])){


                    foreach ($param['item_list'] as $key => $value) {
                        $percent = floatval($_POST['back_percents'][$key]);
                        $first = floatval($_POST['first_rebates'][$key]);

                        $p =  $base->insert($table,['act_id'=>0,'belong_id'=>$value,'back_percent'=>$percent,'first_rebate'=>$first,'channel_id'=>$rebate['channel_id']],true);

                    }

                }
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData("添加成功", '/cpschannel/list'));
            }


            // $base->insert();
        }
    }

    /***
     * 推广活动
     */
    public function activityAction(){
        $type_name = array(
            1 => '全场',
            3 => '品牌',
            4 => '商品'
        );
        $param = array(
            'type_id'=>'',
            'act_name'=>'',
            'channel'=>'',
            'act_status'=>''
        );

        $base = BaseData::getInstance();

        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }
        $this->view->setVar('channel',$param);
        $param['page']  =   $this->request->get('page','trim',1);
        $param['url'] = $this->automaticGetUrl();
        $data['column'] = 'channel_id,channel_name';
        $data['table'] = '\Shop\Models\BaiyangCpsChannel';
        $data['where'] = " where channel_status = 1 ";
        $channel =  $base->getData($data);
        $ch_name = array();
        foreach($channel as $v ){
            $ch_name[$v['channel_id']]['channel_name']  = $v['channel_name'];
            $ch_name[$v['channel_id']]['channel_id']  = $v['channel_id'];
        }

        $data = array();

        $data['table'] = '\Shop\Models\BaiyangCpsBackActivity';
        $where = 'where 1=1';
        if(isset($param['act_name']) && $param['act_name'])
        {
            $where .= ' AND act_name like "%' . addslashes($param['act_name']) . '%" ';
        }
        if(isset($param['type_id']) && $param['type_id'])
        {
            $where .= " AND type_id=" . intval($param['type_id']);
        }
        if (isset($param['channel']) && $param['channel']) {
            $where .= " AND channel_id=" . intval($param['channel']);
        }
        if(isset($param['act_status']) && $param['act_status'])
        {
            $cur_time = time();

            //未开始
            if($param['act_status'] == 'start'){
                $where .= " AND {$cur_time} < start_time AND is_cancel <> 1";
            }
            //已结束
            else if($param['act_status'] == 'end'){
                $where .= " AND {$cur_time} > end_time AND is_cancel <> 1";
            }
            //进行中
            else if($param['act_status'] == 'middle'){
                $where .= " AND {$cur_time} BETWEEN start_time AND end_time AND is_cancel <> 1";
            } else if($param['act_status'] == 'cancel'){
                $where .= " AND is_cancel = 1";
            }
        }
        $data['where'] = $where;
        $counts = $base->countData($data);
        // print_r($param); exit;
        $this->view->setVar('channel_lset',$channel);

        if(empty($counts)){
            return array('res' => 'success','list' => 0);
        }
        $pages['page'] = (int)isset($param['page'])?$param['page']:1;//当前页
        $pages['counts'] = $counts;
        $pages['url'] = $param['url'];
        $page = $this->page->pageDetail($pages);

        $data['column'] = 'act_id,act_name,act_desc,channel_id,type_id,sort,start_time,
end_time,no_include,is_cancel' ;
        $data['order'] = 'ORDER BY act_id DESC';
        $data['limit'] = "LIMIT ".$page['record'].','.$page['psize'];

        $result =  $base->getData($data);

        if($result) {
            $curr_time = strtotime('now');
            foreach ($result as $key => $val) {
                if($val['is_cancel'])
                {
                    $result[$key]['act_status'] = '已取消';
                }else if ($val['start_time'] > $curr_time && !$val['is_cancel']) {
                    $result[$key]['act_status'] = '未开始';
                }else if ($curr_time > $val['end_time'] && !$val['is_cancel']) {
                    $result[$key]['act_status'] = '已结束';
                }else {
                    $result[$key]['act_status'] = '进行中';
                }
                $result[$key]['start_time'] = date('Y-m-d H:i:s', $val['start_time']);
                $result[$key]['end_time'] = date('Y-m-d H:i:s', $val['end_time']);
                $result[$key]['type_name'] = $type_name[$val['type_id']];
                $result[$key]['channel_id'] = isset($ch_name[$val['channel_id']])
                    ? $ch_name[$val['channel_id']]['channel_name'] : '未设置渠道';          //add 20160603 CSL
            }
        }
        //print_r($result);exit;

        $list = [
            'res'  => 'success',
            'list' => $result,
            'page' => $page['page']
        ];


        $this->view->setVar('list',$list);
    }

    public function acaddAction(){
        $param = $_GET;
        $activityService = CpsActivityService::getInstance();
        if (isset($param['isTemplate']) && $param['isTemplate']) {
            $activityService->exportTemplate($param);exit;
        }
        if (isset($param['import']) && $param['import']) {
            if (!$this->request->hasFiles()) {
                return $this->response->setJsonContent([
                    'status' => 'error',
                    'info' => '请选择上传文件'
                ]);
            }
            $type = isset($_FILES['file']['name']) && $_FILES['file']['name']
                ? substr($_FILES['file']['name'], strrpos($_FILES['file']['name'], '.')+1) : '';
            if (!in_array($type,['xlsx','xls'])) {
                return $this->response->setJsonContent([
                    'status' => 'error',
                    'info' => '上传文件格式错误（请上传xlsx或xls格式文件）'
                ]);
            }
            $import = BaseService::getInstance()->filesUpload($this->request, '', '', $type);
            if($import['status'] == 'success') {
                $param['filePath'] = $import['data'][0]['filePath'].$import['data'][0]['fileName'];
                $param['fileType'] = $type;
                $importData = $activityService->getImportData($param);
                if (isset($importData['status'])) {
                    return $this->response->setJsonContent($importData);
                }
                return $this->response->setJsonContent(array_merge([
                    'status' => 'success',
                    'info' => '上传成功',
                ], $importData));
            }
            return $this->response->setJsonContent([
                'status' => 'error',
                'info' => '上传失败',
                'data' => $import,
            ]);
        }

        $base = BaseData::getInstance();
        $data['column'] = 'channel_id,channel_name,tags';
        $data['table']  = '\Shop\Models\BaiyangCpsChannel';
        $data['where'] = " where channel_status = 1 ";

        $ch =  $base->getData($data);
        $data = array();
        $this->view->setVar('channel_list',$ch);
        //查询品牌
        if(isset($param['bar'])&&$param['bar']!=""){
            $data['column'] = 'id as brand_id, brand_name as name';
            $data['table']  = '\Shop\Models\BaiyangBrands';
            if(isset($param['product_id_'])&&$param['product_id_']!=""){
                $data['where']  =  " where id={$param['product_id_']} ";
            }else if(isset($param['name'])&&$param['name']!=""){
                $data['where']  =  " where brand_name LIKE  '%{$param['name']}%' ";
            }

            $ch =  $base->getData($data);
            $data = array();
            if($ch){
                $html ='';
                foreach ($ch as $val){
                    $html .=  "<option value='".$val['brand_id']."'>".$val['name']."</option>";
                }
                die($html);
            }else{
                die("full");
            }
        }

        //添加推广活动数据提交
        if($_POST){
            $data =  $this->_check_cps_data($_POST);
            $data['username'] = $_SESSION['admin']['admin_account'];
            if(isset($data['error_msg'])&&$data['error_msg']){
                //print_r($data);  exit;
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData($data['error_msg'], '', '', 'error'));
            }
            $item_list = isset($data['item_list']) ? $data['item_list'] : array();
            $result = $this->_get_same_activity_condition("",$data['type_id'], $data['channel_id'], $data['start_time'], $data['end_time'], $item_list);
            if($result['error_msg']){
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData($result['error_msg'], '', '', 'error'));
            }

            $relation['item_list'] = $result['item_list'];
            $relation['back_percent'] = $result['back_percent'];
            $relation['first_rebate'] = $result['first_rebate'];

            $table = '\Shop\Models\BaiyangCpsBackActivity';
            $relation['act_id'] = $base->insert($table,['no_include'=>'','act_name'=>$data['act_name'],'act_desc'=>$_POST['act_desc'],'channel_id'=>$_POST['channel_id'],'type_id'=>$data['type_id'],
                'start_time'=>$data['start_time'],'end_time'=>$data['end_time'],'creator'=>$data['username'] ,'add_time'=>time(),'updater'=>$data['username'],
                'update_time'=>time(),'for_users'=>$data['for_users'],'act_logo'=>$data['act_logo'],'act_image'=>$data['act_image'],'act_share_link'=>$data['act_share_link'],'act_share_title'=>$data['act_share_title'],
                'act_share_content'=>$data['act_share_content'],'sort'=>$data['sort']],true);

            $table = '\Shop\Models\BaiyangCpsBackActivityRelation';

            if(!empty($data['item_list'])){
                foreach ($data['item_list'] as $key => $value) {
                    $percent = floatval($data['back_percent'][$key]);
                    $first = floatval($data['first_rebate'][$key]);
                    $base->insert($table,['act_id'=>$relation['act_id'],'belong_id'=>$value,'back_percent'=>$percent,'first_rebate'=>$first,'channel_id'=>0 ],true);
                }
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData("添加成功", '/cpschannel/activity'));
            }

        }
    }
    public function aceditAction(){
        $base = BaseData::getInstance();

        $data['column'] = 'channel_id,channel_name,tags';
        $data['table']  = '\Shop\Models\BaiyangCpsChannel';
        $data['where'] = " where channel_status = 1 ";

        $ch =  $base->getData($data);
        $data = array();
        $this->view->setVar('channel_list',$ch);

        $act_id = $_GET['act_id'];
        if(isset($_GET['act_id'])&&$_GET['act_id']){
            $item_list = isset($data['item_list']) ? $data['item_list'] : array();

            if($_POST){
                $data = $this->_check_cps_data($_POST);
                if(isset($data['error_msg'])&&$data['error_msg']){
                    return $this->response->setJsonContent(BaseService::getInstance()->arrayData($data['error_msg'], '', '', 'error'));
                }
                $table = '\Shop\Models\BaiyangCpsBackActivity';
               // $result = $this->_get_same_activity_condition($act_id,$data['type_id'], $data['channel_id'], $data['start_time'], $data['end_time'], $item_list);
                /*if($result){
                    return $this->response->setJsonContent(BaseService::getInstance()->arrayData($result['error_msg'], '', '', 'error'));
                }*/
                $result = $data;
                $data['username'] = $_SESSION['admin']['admin_account'];

                $columStr = "act_desc=:act_desc:,channel_id=:channel_id:,type_id=:type_id:,updater=:updater:,
                update_time=:update_time:,for_users=:for_users:,act_logo=:act_logo:,act_image=:act_image:,act_share_link=:act_share_link:,
                act_share_title=:act_share_title:,act_share_content=:act_share_content:,sort=:sort:,start_time=:start_time:,end_time=:end_time:";
                $whereStr = " act_id = {$data['act_id']} ";
                $base->update($columStr,$table,[
                    'act_desc'=>$result['act_desc'],
                    'channel_id'=>$result['channel_id'],
                    'type_id'=>$result['type_id'],
                    'updater'=>$data['username'],
                    'update_time'=>time(),
                    'for_users'=>$result['for_users'],
                    'act_logo'=>$result['brand_logo'],
                    'act_image'=>$result['list_image'],
                    'act_share_link'=>$result['act_share_link'],
                    'act_share_title'=>$result['act_share_title'],
                    'act_share_content'=>$result['brand_desc'],
                    'sort'=>$result['sort'],
                    'start_time'=>$result['start_time'],
                    'end_time'=>$result['end_time'],
                ],$whereStr);

                return $this->response->setJsonContent(BaseService::getInstance()->arrayData("编辑成功", '/cpschannel/activity'));
            }else{
                $data['has_result'] = 0;
                //是否显示编辑按钮
                $data['allow_edit'] = 1;
                $data['activity'] = $this->get_cps_activity($act_id);
                $data['has_result'] = !empty($data['activity']) ? 1 : 0;
                if($data['has_result'])
                {
                    if($data['activity']['is_cancel'])
                    {
                        $data['allow_edit'] = 0;
                    }
                    if($data['activity']['end_time'] <= time())
                    {
                        $data['allow_edit'] = 0;
                    }
                }

                $d['column'] = 'channel_id,channel_name,tags';
                $d['table']  = '\Shop\Models\BaiyangCpsChannel';
                $d['where'] = " where channel_status = 1 ";

                $ch =  $base->getData($d);
                if ($ch) {
                    foreach ($ch as $item) {
                        $channel[$item['channel_id']] = $item['channel_name'];
                    }
                }
                $data['channel_list'] = $channel;
            }
        }else{
            return $this->response->setJsonContent(BaseService::getInstance()->arrayData("错误", '/cpschannel/activity', '', 'error'));
        }
        $this->view->setVar('list',$data);

        $this->view->setVar('act_id',$act_id);
    }
    public function copyAction(){
        $base = BaseData::getInstance();

        $data['column'] = 'channel_id,channel_name,tags';
        $data['table']  = '\Shop\Models\BaiyangCpsChannel';
        $data['where'] = " where channel_status = 1 ";

        $ch =  $base->getData($data);
        $this->view->setVar('channel_list',$ch);
        if(isset($_GET['p_id'])){
            $this->view->setVar('p_id',$_GET['p_id']);
        }

        $act_id = $_GET['act_id'];
        if(isset($_GET['act_id'])&&$_GET['act_id']){
            if($_POST){
                $data = $this->_check_cps_data($_POST);
                if(isset($data['error_msg'])&&$data['error_msg']){
                    return $this->response->setJsonContent(BaseService::getInstance()->arrayData($data['error_msg'], '', '', 'error'));
                }
                if($data['channel_id']){
                    $p['column'] = 'channel_id';
                    $p['table']  = '\Shop\Models\BaiyangCpsBackActivity';
                    $p['where'] = " where channel_id = {$data['channel_id']} and act_id = {$act_id} and (" . time() . " between start_time and end_time)";
                    $c =  $base->getData($p,true);
                    if($c){
                        return $this->response->setJsonContent(BaseService::getInstance()->arrayData('该渠道已存在该活动，请重选渠道', '', '', 'error'));
                    }
                }

                $data['username'] = $_SESSION['admin']['admin_account'];
                if(isset($data['error_msg'])&&$data['error_msg']){
                    return $this->response->setJsonContent(BaseService::getInstance()->arrayData($data['error_msg'], '', '', 'error'));
                }
                $item_list = isset($data['item_list']) ? $data['item_list'] : array();
                $result = $this->_get_same_activity_condition("",$data['type_id'], $data['channel_id'], $data['start_time'], $data['end_time'], $item_list);
                if($result['error_msg']){
                    return $this->response->setJsonContent(BaseService::getInstance()->arrayData($result['error_msg'], '', '', 'error'));
                }

                $relation['item_list'] = $result['item_list'];
                $relation['back_percent'] = $result['back_percent'];
                $relation['first_rebate'] = $result['first_rebate'];

                $table = '\Shop\Models\BaiyangCpsBackActivity';
                $actData = array(
                    'no_include'=>'',
                    'act_name'=>$data['act_name'],
                    'act_desc'=>$_POST['act_desc'],
                    'channel_id'=>$_POST['channel_id'],
                    'type_id'=>$data['type_id'],
                    'start_time'=>$data['start_time'],
                    'end_time'=>$data['end_time'],
                    'creator'=>$data['username'] ,
                    'add_time'=>time(),
                    'updater'=>$data['username'],
                    'update_time'=>time(),
                    'for_users'=>$data['for_users'],
                    'act_logo'=>$data['brand_logo'],
                    'act_image'=>$data['list_image'],
                    'act_share_link'=>$data['act_share_link'],
                    'act_share_title'=>$data['act_share_title'],
                    'act_share_content'=>$data['brand_desc'],
                    'sort'=>$data['sort']
                );
                $relation['act_id'] = $base->insert($table,$actData,true);

                $table = '\Shop\Models\BaiyangCpsBackActivityRelation';

                if(!empty($item_list)){
                    foreach ($item_list as $key => $value) {
                        $percent = floatval($data['back_percent'][$key]);
                        $first = floatval($data['first_rebate'][$key]);
                        $p =  $base->insert($table,['act_id'=>$relation['act_id'],'belong_id'=>$value,'back_percent'=>$percent,'first_rebate'=>$first,'channel_id'=>0 ],true);
                    }
                }
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData("复制成功", '/cpschannel/activity'));
            }else{
                $data['has_result'] = 0;
                //是否显示编辑按钮
                $data['allow_edit'] = 1;
                $data['activity'] = $this->get_cps_activity($act_id);
                $data['has_result'] = !empty($data['activity']) ? 1 : 0;
                if($data['has_result'])
                {
                    if($data['activity']['is_cancel'])
                    {
                        $data['allow_edit'] = 0;
                    }
                    if($data['activity']['end_time'] <= time())
                    {
                        $data['allow_edit'] = 0;
                    }
                }

                $d['column'] = 'channel_id,channel_name,tags';
                $d['table']  = '\Shop\Models\BaiyangCpsChannel';
                $d['where'] = " where channel_status = 1 ";

                $ch =  $base->getData($d);
                if ($ch) {
                    foreach ($ch as $item) {
                        $channel[$item['channel_id']] = $item['channel_name'];
                    }
                }
                $data['channel_list'] = $channel;
            }

        }else{
            return $this->response->setJsonContent(BaseService::getInstance()->arrayData("错误", '/cpschannel/activity', '', 'error'));
        }
        $this->view->setVar('list',$data);

        $this->view->setVar('act_id',$act_id);
    }

    public function csvAction(){
        $base = BaseData::getInstance();
        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }
        $data =  $this->get_statistics_data($param['id']);
        $str = "商品id, 商品名称,商品数量,有效订单数,订单总金额,邀请码,推广员ID,推广员姓名,邀请注册人数,地区,下单时间,注册时间\n";
        $str = iconv('UTF-8','GB2312',$str);
        if($data){


            foreach (array_reverse($data) as $key=>$val)
            {
                $goods_name = $val['goods_name'] ? iconv('utf-8','gbk',$val['goods_name']) : "";
                $user_name =  $val['user_name'] ? iconv('utf-8','gbk',$val['user_name']) : "";
                // $goods_name = $val['goods_name'] ? $val['goods_name'] : "";
                // $user_name =  $val['user_name'] ? $val['user_name'] : "";
                $goods_id = $val['id'] ? $val['id'] : "";
                $qty_sum = $val['qty_sum'] ? $val['qty_sum'] : "";
                $str .= $goods_id.','.$goods_name.','.$qty_sum.','.$val['order_count'].','.$val['price_count'].','.$val['invite_code'].','.$val['cps_id'].','.$user_name.','.$val['invite_count'].','.' '.','.$val['order_time'].','.$val['add_time']."\n";
            }

        }
        $filename = date('渠道统计数据Ymd').'.csv';
        $this->export_csv($filename,$str);
    }

    public function uploadAction()
    {
        if ($this->request->hasFiles())
        {
            $res = BaseService::getInstance()->uploadFile($this->request,$this->config['application']['uploadDir'].'images/brands/');
            //判断是否是编辑上传图片，是则返回编辑器json格式
            if($this->getParam('dir', 'trim', '') == 'image'){
                $res = array('error' => 0, 'url' => $res['data'][0]['src']);
            }
            return $this->response->setJsonContent($res);
        }
    }
    /**
     * 检验数据
     */
    public function check_data($datas,$pid="")
    {
        $base = BaseData::getInstance();
        $curr_time = time();
        $new_data = array();
        if (isset($datas['channel_id'])&&$datas['channel_id']) {
            $new_data['update_time'] = $curr_time;
            //$new_data['updater'] = $this->session->username;
        }
        // $new_data['creator'] = $this->session->username;
        $new_data['add_time'] = $curr_time;
        //验证渠道名称和渠道标签
        if (isset($datas['channel_name']) && isset($datas['tags'])) {
            if (!$datas['channel_name'] && !$datas['tags']) {

                return array('success' => '渠道名称和渠道标签必填');
            } elseif (!$datas['channel_name']) {

                return array('success' => '渠道名称必填');
            } elseif (!$datas['tags']) {

                return array('success' => '渠道标签必填');
            }
            $check =  preg_match('/^[a-zA-Z]{2}$/', $datas['tags']);
            if (!$check) {

                return array('success' => '渠道标签格式错误（两个字母组成）');
            } else {
                $datas['tags'] = strtoupper($datas['tags']);
            }
            //获取所有有效的渠道
            $data['column'] = 'channel_id,channel_name,tags';
            $data['table'] = $data_cps_channel = '\Shop\Models\BaiyangCpsChannel';
            $data['where'] = " where channel_status = 1 ";
            $ch =  $base->getData($data);
            $channel_name = $channel_tags = array();

            foreach ($ch as $val) {
                $channel_name[$val['channel_name']] = '';
                $channel_tags[$val['tags']] = '';
            }

            if (isset($channel_name[$datas['channel_name']]) && isset($channel_tags[$datas['tags']])&&$pid=="") {

                return array('success' => '渠道名称 <font color="red">'.$datas['channel_name'].'</font> 和渠道标签 <font color="red">'.$datas['tags'].'</font> 已存在');

            } elseif (isset($channel_name[$datas['channel_name']])&&$pid=="") {

                return array('success' => '渠道名称 <font color="red">'.$datas['channel_name'].'</font> 已存在');
            } elseif (isset($channel_tags[$datas['tags']])&&$pid=="") {

                return array('success' => '渠道标签 <font color="red">'.$datas['tags'].'</font> 已存在');
            }
            $new_data['channel_name'] = $datas['channel_name'];
            $new_data['tags'] = $datas['tags'];
        }
        //注册返利 app
        $new_data['back_amount'] = !empty($datas['back_amount']) ? (float)$datas['back_amount']:(float)0;

        //注册返利 wap
        $new_data['wap_back_amount'] = !empty($datas['wap_back_amount']) ? (float)$datas['wap_back_amount']:(float)0;


        if($new_data['back_amount']<=0 || $new_data['wap_back_amount']<=0 || $new_data['back_amount']<0.01 || $new_data['wap_back_amount']<0.01){
            return array('success' => '注册返利金额不能为负数或零不能低于0.01');

        }
        //是否永久
        $new_data['is_permanent'] = $datas['is_permanent'] ? 1 : 0;
        //返利周期
        if (!isset($datas['expire_day'])) {

            return array('success' => '返利周期必填');
        } elseif (!preg_match('/^[0-9]{1,}$/', $datas['expire_day'])) {

            return array('success' => '返利周期必须是正整数');
        }
        $new_data['expire_day'] = $datas['expire_day'];
        //永久活动返利

        if (isset($new_data['is_permanent'])&&$new_data['is_permanent']==1) {
            if (  !$datas['first_rebate'] && !$datas['back_percent']) {

                return array('success' => '永久渠道的首单返利和正常返利必填');
            } elseif (!$datas['first_rebate']) {

                return array('success' => '永久渠道的首单返利必填');
            } elseif (!$datas['back_percent']) {

                return array('success' => '永久渠道的正常返利必填');
            } elseif (!preg_match('/^\d{1,2}(\.\d{1,2})?$/', $datas['first_rebate']) ||
                !preg_match('/^\d{1,2}(\.\d{1,2})?$/', $datas['back_percent'])) {

                return array('success' => '请输入合理的返利');
            }
            $new_data['rebate']['first_rebate'] = $datas['first_rebate'];
            $new_data['rebate']['back_percent'] = $datas['back_percent'];
            $new_data['rebate']['add_time'] = $curr_time;
        }

        if(!empty($datas['item_list'])){
            //首单返利

            if (empty($datas['first_rebates'])) {
                return array(
                    'success' => "请合理输入参加csp活动的首单返利！"
                );
            }
            foreach ($datas['first_rebates'] as $key => $percent) {
                if ($percent < 0) {

                    return array(
                        'success' => '请合理输入的首单返利！'
                    );

                }
                if (!$percent && !preg_match('/^\d{1,2}(\.\d{1,2})?$/', $percent)) {
                    return array(
                        'error_msg' => '请合理输入的首单返利！'
                    );
                }
            }
            //正常返利
            if (empty($datas['back_percents'])) {
                return array(
                    'success' => "请合理输入参加csp活动的正常返利！"
                );
            }
            foreach ($datas['back_percents'] as $key => $percent) {
                if ($percent < 0) {
                    return array(
                        'success' => '请合理输入的正常返利！'
                    );

                }
                if (!$percent && !preg_match('/^\d{1,2}(\.\d{1,2})?$/', $percent)) {
                    return array(
                        'success' => '请合理输入的正常返利！'
                    );
                }
            }
        }

        $new_data['item_list'] = isset($datas['item_list']) ? $datas['item_list'] : array();
        $new_data['first_rebates'] = isset($datas['first_rebates']) ? $datas['first_rebates'] : array();
        $new_data['back_percents'] = isset($datas['back_percents']) ? $datas['back_percents'] : array();

        //验证分享信息
        if (!isset($datas['title'])) {

            return array('success' => '分享标题必填');
        }
        $new_data['title'] = $datas['title'];
        //描述
        if (!$datas['content']) {
            return array('success' => '分享描述必填');
        }
        $new_data['content'] = $datas['content'];
        //链接
        if (isset($datas['link'])&&$datas['link']) {
            $check_link = preg_match('/^https?/', $datas['link']);
            if (!$check_link) {
                return array('success' => '分享链接格式错误（如：http://www.baiyjk.com）');
            }

        } else {
            return array('success' => '分享链接必填');

        }
        $new_data['link'] = isset($datas['link'])?$datas['link']:'';

        //logo图
        if (!isset($datas['brand_logo'])) {
            return array('success' => '请上活动logo图');
        }
        $new_data['logo'] = isset($datas['brand_logo'])?$datas['brand_logo']:'';
        //活动背景图
        if (!isset($datas['list_image'])) {
            return array('success' => '请上传活动背景图');
        }
        $new_data['channel_image'] = isset($datas['list_image'])?$datas['list_image']:'';

        //活动说明
        if (!isset($datas['brand_desc'])) {
            return array('success' => '活动说明必填');
        }
        $new_data['description'] = isset($datas['brand_desc'])?$datas['brand_desc']:'';
        //删除旧数组

        unset($datas);
        return $new_data;
    }
    public function get_channel_one($id)
    {
        $base = BaseData::getInstance();
        $data['column'] = 'c.channel_id,c.channel_name,c.tags,r.first_rebate,c.back_amount,c.wap_back_amount,c.is_permanent,
        c.expire_day,r.back_percent,r.first_rebate,c.title,c.content,c.link,c.logo,c.channel_image,c.description';
        $data['table'] =  '\Shop\Models\BaiyangCpsChannel as c';
        $data['where'] = " where c.channel_id = {$id} group by c.channel_id";
        $baiyang_cps_channel_rebate =    '\Shop\Models\BaiyangCpsChannelRebate as r';
        $data['join'] = ' left join '.$baiyang_cps_channel_rebate.' on c.channel_id = r.channel_id ';

        $param =  $base->getData($data);
        return $param;

    }
    private function get_cps_activity_detail($channel_id)
    {
        if(!$channel_id)
        {
            return array();
        }
        $base = BaseData::getInstance();
        $data['column'] = 'g.goods_name as relation_name ,r.act_id,r.belong_id,r.back_percent,r.first_rebate,r.channel_id';
        $data['table'] =  '\Shop\Models\BaiyangCpsBackActivityRelation as r';
        $data['where'] = " where r.channel_id = {$channel_id}";
        $baiyang_cps_channel_rebate = '\Shop\Models\BaiyangGoods as g';
        $data['join'] = ' left join '.$baiyang_cps_channel_rebate.' on r.belong_id = g.id ';

        $param =  $base->getData($data);

        return $param;
    }
    public function get_coupon($channel_id)
    {
        if (!$channel_id) {
            return false;
        }
        $user = array(
            '0' => '所有人',
            '2' => '指定人群',
            '3' => '新会员',
            '5' => '老会员',
        );
        $base = BaseData::getInstance();
        $data['column'] = 'coupon_name name,group_set for_users,coupon_value value';
        $data['table'] =  '\Shop\Models\BaiyangCoupon';
        $data['where'] = " where channel_id = {$channel_id} and end_provide_time > ".time();

        $coupon =  $base->getData($data);
        if ($coupon) {
            foreach ($coupon as $k => $v) {
                $coupon[$k]['for_users'] = isset($user[$v['for_users']]) ? $user[$v['for_users']] : $v['for_users'];
            }
        }
        return $coupon;
    }// function get_coupon END


    public function get_statistics_data($channel_id)
    {
        // $this->load->model('User_model');

        $base = BaseData::getInstance();
        //统计推广员信息
        $data['column'] = " cu.cps_id,cu.user_id,cu.user_name,cu.invite_code,count(cil.invite_code) as invite_count,cu.channel_id,FROM_UNIXTIME(cu.add_time,'%Y-%m-%d %H:%i:%s') as add_time";
        $data['table'] =  '\Shop\Models\BaiyangCpsUser as cu';
        $data['where'] = " where cu.channel_id = {$channel_id} group by cu.invite_code order by cu.add_time";
        $BaiyangCpsInvite_log =    '\Shop\Models\BaiyangCpsInviteLog as cil';
        $data['join'] = ' left join '.$BaiyangCpsInvite_log.' on cil.cps_id=cu.cps_id';
        $invite_info =  $base->getData($data);
        // print_r($invite_info);exit;

        //统计订单信息

        $data = array();
        $data['column'] = " cu.invite_code,count(col.order_sn) as order_count,sum(col.real_pay) as price_count,FROM_UNIXTIME(col.order_time,'%Y-%m-%d %H:%i:%s') as order_time";
        $data['table'] =  '\Shop\Models\BaiyangCpsUser as cu';
        $data['where'] = " where cu.channel_id={$channel_id} and col.order_status IN ('shipping','shipped','evaluating','finished') group by cu.invite_code order by cu.add_time";
        $BaiyangCpsOrderLog =    '\Shop\Models\BaiyangCpsOrderLog as col';
        $data['join'] = ' left join '.$BaiyangCpsOrderLog.' on col.invite_code = cu.invite_code ';
        $order_info =  $base->getData($data);

        //统计商品信息
        $data = array();
        $data['column'] = " col.invite_code,g.id,g.goods_name,sum(codl.qty) as qty_sum";
        $data['table'] =  '\Shop\Models\BaiyangCpsOrderLog as col';
        $data['where'] = " where  col.channel_id={$channel_id} and order_status IN ('shipping','shipped','evaluating','finished')
                group by codl.invite_code,codl.goods_id order by col.order_time";
        $BaiyangCpsOrderDetailLog = '\Shop\Models\BaiyangCpsOrderDetailLog as codl';
        $BaiyangGoods =    '\Shop\Models\BaiyangGoods as g';
        $data['join'] = ' left join '.$BaiyangCpsOrderDetailLog.' on codl.order_sn=col.order_sn
                          left join '.$BaiyangGoods.' on g.id=codl.goods_id ';
        $goods_info =  $base->getData($data);
        if($invite_info){
            foreach ($invite_info as $ikey=>$ival)
            {
                $invite_info[$ikey]['order_count'] = 0;
                $invite_info[$ikey]['price_count'] = 0;
                $invite_info[$ikey]['order_time'] = "";
                if($order_info){
                    foreach ($order_info as $okey=>$oval)
                    {
                        if($ival['invite_code'] == $oval['invite_code'])
                        {
                            $invite_info[$ikey]['order_count'] = $oval['order_count'];
                            $invite_info[$ikey]['price_count'] = $oval['price_count'];
                            $invite_info[$ikey]['order_time'] = $oval['order_time'];
                        }
                    }
                }
            }

            foreach ($invite_info as $ikey=>$ival)
            {
                $invite_info[$ikey]['id'] = "";
                $invite_info[$ikey]['goods_name'] = "";
                $invite_info[$ikey]['qty_sum'] = "";
                if($goods_info){
                    foreach ($goods_info as $gkey=>$gval)
                    {
                        if($gval['invite_code'] == $ival['invite_code'])
                        {
                            $invite_info[] = array_merge($gval,$ival);
                            unset($invite_info[$ikey]);
                        }
                    }
                }
            }

            //重新计算有效订单数
            $all_order = $this->get_order_all();

            $i = 0;
            foreach ($invite_info as $inkey=>$inval)
            {
                foreach ($all_order as $orkey=>$orval)
                {
                    if($inval['user_id'] == $orval['user_id'])
                    {
                        $i++;
                    }
                }
                $invite_info[$inkey]['order_count'] = $i;
                $i = 0;
            }
        }
        //echo "<pre />";print_r($invite_info);exit;
        return $invite_info;
    }

    public function get_order_all()
    {   $base = BaseData::getInstance();
        $data = array();
        $data['column'] = " us.user_id, ord.order_sn, ord.order_time, SUM(ROUND(cod.back_amount, 2)) back_amount ";
        $data['table'] =  '\Shop\Models\BaiyangCpsOrderLog as ord';
        $data['where'] = " where  us.cps_status<>0 AND ch.channel_status<>0 AND ord.order_status IN ('shipping','shipped','evaluating','finished')
        GROUP BY ord.order_sn, cod.invite_code ORDER BY us.user_id";

        $BaiyangCpsOrderDetailLog =    '\Shop\Models\BaiyangCpsOrderDetailLog as cod';
        $BaiyangCpsUser =    '\Shop\Models\BaiyangCpsUser as us';
        $BaiyangCpsChannel =    '\Shop\Models\BaiyangCpsChannel as ch';
        $data['join'] = ' left join '.$BaiyangCpsOrderDetailLog.' on ord.order_sn= cod.order_sn
                          left join '.$BaiyangCpsUser.' on cod.invite_code = us.invite_code
                          left join '.$BaiyangCpsChannel.' on us.channel_id = ch.channel_id';
        $order =  $base->getData($data);


        return $order;
    }
    function export_csv($filename,$data)
    {
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=".$filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $data; exit;
    }
    /**
     * 获取参加返利活动的条件是否已经参加过
     *
     * @param int $act_id 活动id
     * @param int $type_id 类型id
     * @param int $channel_id 渠道id
     * @param int $start_time 活动开始时间
     * @param array $data 活动条件列表
     * @return array|bool 是否存在相同的条件
     */
    public function _get_same_activity_condition($act_id, $type_id, $channel_id, $start_time, $end_time, $data)
    {
        $type_name = array(
            1 => '全场',
            3 => '品牌',
            4 => '商品'
        );

        $activity_list = $this->get_same_activity_condition($act_id, $type_id, $channel_id);
        if ($activity_list) {
            if($type_id != 1 && $data) {
                foreach ($data as $key => $value) {
                    if (isset($activity_list[$value])) {
                        foreach ($activity_list[$value] as $item) {
                            if (($item['start_time'] <= $start_time && $start_time <= $item['end_time']) || ($item['start_time'] <= $end_time && $end_time <= $item['end_time'])) {
                                return array(
                                    'error_msg' => $type_name[$type_id] . ":{$value}已经参加了返利活动，请勿重复添加！"
                                );
                            }
                        }
                    }
                }
            }
            if($type_id == 1) {
                foreach ($activity_list as $item) {
                    if (($item['start_time'] <= $start_time && $start_time <= $item['end_time']) || ($item['start_time'] <= $end_time && $end_time <= $item['end_time'])) {
                        return array(
                            'error_msg' => $type_name[$type_id] . ":已经设置了返利活动，请勿重复添加！"
                        );
                    }
                }
            }
        }
        return false;
    }
    /**
     * 按活动类型、渠道获取相同活动中的活动条件列表
     *
     * @param int $type_id 类型id
     * @param int $channel_id 渠道id
     * @return bool｜array 活动列表
     */
    public function get_same_activity_condition($act_id="", $type_id, $channel_id)
    {   $base = BaseData::getInstance();
        if(!$type_id || !$channel_id)
        {
            return false;
        }
        $where = " where 1=1 ";

        if ($act_id) {
            $where = "AND act.act_id<>{$act_id}";
        }
        $now_time = time();
        $where  .= " and act.type_id={$type_id} AND act.channel_id={$channel_id} AND {$now_time} < act.end_time AND is_cancel = 0";
        $data['column'] = " act.act_id,act.act_name,act.act_desc,act.channel_id,act.type_id,act.expire_day,act.start_time,act.end_time,
    rel.belong_id,rel.back_percent ";
        $data['table'] =  '\Shop\Models\BaiyangCpsBackActivity as act';
        $data['where'] = $where;

        $BaiyangCpsOrderDetailLog = '\Shop\Models\BaiyangCpsBackActivityRelation as rel';

        $data['join'] = ' left join '.$BaiyangCpsOrderDetailLog.' on act.act_id = rel.act_id ';
        $activity_list =  $base->getData($data);

        $result = array();
        if($activity_list)
        {
            foreach ($activity_list as $item) {
                if($item['type_id'] == 1 && $type_id == 1)
                {
                    $result[] = $item;
                }elseif ($type_id != 1) {
                    $result[$item['belong_id']][] = $item;
                }
            }
        }
        return $result;
    }
    /**
     * @author CSL 20160531 edit
     * @param array $data post 数据
     * @return array cps 活动数据
     */
    public function _check_cps_data($data)
    {

        if(!isset($data['act_name']) || empty($data['act_name']))
        {
            return array(
                'error_msg' => '活动名称长度必须的在1-190个字符之内！'
            );
        }
        if(mb_strlen($data['act_name']) > 190)
        {
            return array(
                'error_msg' => '活动名称长度必须的在1-190个字符之内！'
            );
        }

        //活动时间
        $data['start_time'] = isset($data['start_time']) ? @strtotime($data['start_time']) : 0;
        $data['end_time'] = isset($data['end_time']) ? @strtotime($data['end_time']) : 0;
        if(!$data['start_time'] || !$data['end_time'] || ($data['end_time'] < time()))
        {
            return array(
                'error_msg' => '请选择有效的活动开始时间和结束时间！'
            );
        }
        if ($data['start_time'] > $data['end_time']) {
            return array(
                'error_msg' => '活动开始时间不能大于结束时间！'
            );
        }
        if(($data['start_time'] == $data['end_time']))
        {
            return array(
                'error_msg' => '活动开始时间不能与结束时间相同！'
            );
        }

        if (!isset($data['channel_id']) || !$data['channel_id']) {
            return array(
                'error_msg' => '请选择渠道！'
            );
        }
        $data['channel_id'] = intval($data['channel_id']);

        $data['type_id'] = isset($data['type_id']) ? intval($data['type_id']) : 0;

        if(mb_strlen($data['act_name']) > 190)
        {
            return array(
                'error_msg' => '活动名称长度必须的在1-190个字符之内！'
            );
        }

        if (empty($data['sort'])) {
            return array(
                'error_msg' => '请填写排序！'
            );
        } elseif (!preg_match('/^[0-9]{0,}$/', $data['sort'])) {
            return array(
                'error_msg' => '排序只能是正整数！'
            );
        }

        if($data['type_id'] != 1 ) {
            $data['item_list'] = isset($data['item_list']) ? $data['item_list'] : array();

            $item_list = $this->get_brand_product_list($data['type_id'], $data['item_list']);
            if (!$data['item_list']) {
                return array(
                    'error_msg' => "请添加参加csp活动的{$this->type_name[$data['type_id']]}！"
                );
            } else {
                $item_list = array_flip(array_column($item_list,'brand_id'));
                $value = array();
                foreach ($data['item_list'] as $val) {
                    if (!isset($item_list[$val])) {
                        $value[] = $val;
                    }
                }
                if ($value) {
                    return array(
                        'error_msg' => "ID为 ".implode('、',$value)." 的商品在四个端都已下架 ！"
                    );
                }
            }
            //首单返利
            $data['first_rebate'] = isset($data['first_rebates']) ? $data['first_rebates'] : array();

            if (empty($data['first_rebate'])) {
                return array(
                    'error_msg' => "请合理输入参加csp活动的{$this->type_name[$data['type_id']]}首单返利！"
                );
            }
            foreach ($data['first_rebate'] as $key => $percent) {
                if (!$percent && !preg_match('/^\d{1,2}(\.\d{1,2})?$/', $percent)) {
                    return array(
                        'error_msg' => '请合理输入 ' . $data['name_arr'][$key] . ' 的首单返利！'
                    );
                }
            }
            //正常返利
            $data['back_percent'] = isset($data['back_percents']) ? $data['back_percents'] : array();
            if (empty($data['back_percent'])) {
                return array(
                    'error_msg' => "请合理输入参加csp活动的{$this->type_name[$data['type_id']]}正常返利！"
                );
            }
            foreach ($data['back_percent'] as $key => $percent) {
                if (!$percent && !preg_match('/^\d{1,2}(\.\d{1,2})?$/', $percent)) {
                    return array(
                        'error_msg' => '请合理输入 ' . $data['name_arr'][$key] . ' 的正常返利！'
                    );
                }
            }
        }
        if($data['type_id'] == 1 )
        {
            $data['first_rebate'][] = isset($data['all_first_rebate']) ? $data['all_first_rebate'] : array();
            if (!isset($data['first_rebate'][0]) && !preg_match('/^\d{1,2}(\.\d{1,2})?$/', $data['first_rebate'][0])) {
                return array(
                    'error_msg' => '请输入合理的' . $this->type_name[$data['type_id']] . '首单返利比例！'
                );
            }
            $data['back_percent'][] = isset($data['all_back_percent']) ? $data['all_back_percent'] : array();
            if (!isset($data['back_percent'][0]) && !preg_match('/^\d{1,2}(\.\d{1,2})?$/', $data['back_percent'][0])) {
                return array(
                    'error_msg' => '请输入合理的' . $this->type_name[$data['type_id']] . '正常返利比例！'
                );
            }
            $data['item_list'] = array(0);
        }

        if (empty($data['act_share_title'])) {
            return array(
                'error_msg' => '请填写分享标题！'
            );
        } elseif (mb_strlen($data['act_share_title']) > 150) {
            return array(
                'error_msg' => '分享标题少于150个字符！'
            );
        }

        if (empty($data['act_desc'])) {
            return array(
                'error_msg' => '请填写分享描述！'
            );
        } elseif(mb_strlen($data['act_desc']) > 250) {
            return array(
                'error_msg' => '分享描述少于250个字符！'
            );
        }

        //过滤前后空格
        if(isset($data['act_share_link'])){


            $data['act_share_link'] = str_replace('#', '?', trim($data['act_share_link']));
            if (empty($data['act_share_link'])) {
                return array(
                    'error_msg' => '请填写分享链接！'
                );
            } elseif (mb_strlen($data['act_share_link']) > 250) {
                return array(
                    'error_msg' => '分享链接少于250个字符！'
                );
            }
        } /*elseif (!preg_match('/^[A-Za-z]+:\/\/[A-Za-z0-9-_:]+\.[A-Za-z0-9-_%&\?|\#\/.=:]+$/', $data['act_share_link'])) {
            return array(
                'error_msg' => '分享链接格式错误（如：http://www.baiyjk.com）！'
            );
        }*/
        $data['act_logo'] = isset($data['brand_logo'])?$data['brand_logo']:"";
        if ($data['act_logo']=="") {
            return array(
                'error_msg' => '请上活动logo图！'
            );
        }
        $data['act_image'] = isset($data['list_image'])?$data['list_image']:"";
        if ($data['act_image']=="") {
            return array(
                'error_msg' => '请上传活动背景图！'
            );
        }
        $data['act_share_content'] = isset($data['brand_desc'])?$data['brand_desc']:"";
        if ($data['act_share_content']=="") {
            return array(
                'error_msg' => '请填写活动说明！'
            );
        }
        unset($data['do_submit'], $data['all_back_percent'], $data['all_first_rebate'], $data['act_logo_image'],
            $data['act_image_image'], $data['name_arr']);

        // $data['no_include'] = isset($data['no_include']) ? $data['no_include'] : '';
        return $data;
    }
    public function get_brand_product_list($type_id, $id_list)
    {    $base = BaseData::getInstance();
        if(empty($id_list))
        {
            return false;
        }
        //品牌
        if($type_id == '3')
        {

            $id_list = is_array($id_list) ? implode(',', $id_list) : $id_list;
            $data['column'] = " id brand_id,brand_name ";
            $data['table'] =  '\Shop\Models\BaiyangBrands ';
            $data['where'] = " where id IN ({$id_list})" ;
            return $activity_list =  $base->getData($data);

        }else if($type_id == '4')
        {
            $id_list = is_array($id_list) ? implode(',', $id_list) : $id_list;
            $data['column'] = " id brand_id,goods_name brand_name ";
            $data['table'] =  '\Shop\Models\BaiyangGoods ';
            $data['where'] = " where id IN ({$id_list}) AND (is_on_sale=1 OR sale_timing_app=1 OR sale_timing_wap=1 OR sale_timing_wechat=1) AND product_type=0" ;
            return $activity_list =  $base->getData($data);
        }else {
            return false;
        }
    }



    public function get_cps_activity($act_id)
    {
        $act_id = intval($act_id);
        if($act_id)
        {
            $data = $this->get_cps_activity_byid($act_id);

            if($data)
            {
                $result = $this->_get_cps_activity_relation_info($data['act_id'], $data['type_id']);
                $data['relation_list'] = $result;

                return $data;
            }else {
                return array();
            }
        }
        return array();
    }
    /**
     * 按活动id判断是否存在该活动
     *
     * @param int $act_id cps活动id
     * @return bool|array 行数
     */
    public function get_cps_activity_byid($act_id)
    {   $base = BaseData::getInstance();
        $act_id = intval($act_id);
        if(!$act_id)
        {
            return false;
        }

        $data['column'] = " act_id,act_name,act_desc,channel_id,type_id,expire_day,  FROM_UNIXTIME(start_time,'%Y/%m/%d %h:%i:%s') as start_time ,
FROM_UNIXTIME(end_time,'%Y/%m/%d %h:%i:%s') as end_time   ,no_include,is_cancel,sort,act_share_title,act_share_content,act_share_link,act_logo,act_image,for_users ";
        $data['table'] =  '\Shop\Models\BaiyangCpsBackActivity ';
        $data['where'] = " where  act_id={$act_id} " ;
        return $activity_list =  $base->getData($data,true);
    }
    /**
     * 获取cps活动关联的ID
     *
     * @param int $act_id 活动id
     * @param int $type_id 类型id
     * @return array cps活动关联的列表
     */
    private function _get_cps_activity_relation_info($act_id, $type_id)
    {   $base = BaseData::getInstance();

        if(!$act_id || !$type_id)
        {
            return array();
        }
        if($type_id == 3)
        {
            $data=array();
            $data['column'] = " cps.act_id,cps.belong_id,cps.back_percent,cps.first_rebate, b.brand_name as relation_name ";
            $data['table'] =  '\Shop\Models\BaiyangCpsBackActivityRelation as cps ';
            $data['where'] = " where  cps.act_id = {$act_id} " ;
            $baiyang_brands = '\Shop\Models\BaiyangBrands as b';
            $data['join'] = " LEFT JOIN ".$baiyang_brands." ON cps.belong_id = b.id";

            return $activity_list =  $base->getData($data);

        }elseif($type_id == 4)
        {

            $data=array();
            $data['column'] = " cps.act_id,cps.belong_id,cps.back_percent,cps.first_rebate, g.goods_name as relation_name ";
            $data['table'] =  '\Shop\Models\BaiyangCpsBackActivityRelation as cps';
            $data['where'] = " where  cps.act_id = {$act_id} " ;
            $baiyang_goods = '\Shop\Models\BaiyangGoods as g';
            $data['join'] = " LEFT JOIN ".$baiyang_goods." ON  cps.belong_id = g.id";
            return $activity_list =  $base->getData($data);

        }elseif($type_id == 1)
        {
            $data=array();
            $data['column'] = " act_id,belong_id,back_percent,first_rebate,channel_id";
            $data['table'] =  '\Shop\Models\BaiyangCpsBackActivityRelation';
            $data['where'] = " where  act_id = {$act_id} " ;

            return $activity_list =  $base->getData($data);
        }
    }

}