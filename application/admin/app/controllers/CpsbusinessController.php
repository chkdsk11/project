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
class CpsBusinessController extends ControllerBase
{
    public function listAction()
    {
        $base = BaseData::getInstance();
        $param=array(
            'region_id'=>'',
            'city_id'=>'',
            're'=>'',
            'stu'=>'',
        );
        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }
        if($param['re']&&$param['stu']==1){
            $array = $this->city($param['re'],1);
            $address_str = '<option value="0">请选择</option>';
            foreach($array as $row){
                $address_str .= '<option value="'.$row['id'].'">'.$row['region_name'].'</option>';
            }
            echo $address_str; exit;
        }
        $red =  $this->city();
        $city = $param['region_id'] ? $this->city($param['region_id'],1) : [];
        $this->view->setVar('red',$red);
        $this->view->setVar('city', $city);

        $this->view->setVar('channel',$param);
        $param['page']  =   $this->request->get('page','trim',1);
        $param['url'] = $this->automaticGetUrl();

        $count_sql = "WHERE bu.is_del = 0  ";
        if (isset($param['phone']) && !empty($param['phone'])) {
            $count_sql .= " AND bu.phone =".$param['phone'];
        }
        if (isset($param['id_card']) && !empty($param['id_card'])) {
            $count_sql .= " AND bu.id_card ='".$param['id_card']."' ";
        }

        if(!empty($param['region_id'])){
            $count_sql .= " AND bu.region_id =".$param['region_id'];
        }
        if (isset($param['city_id']) && !empty($param['city_id'])) {
            $count_sql .= " AND bu.city_id  =".$param['city_id'];
        }
        $data['table'] = '\Shop\Models\BaiyangBusiness as bu';

        $data_re = '\Shop\Models\BaiyangRegion as re';
        $data_ce = '\Shop\Models\BaiyangRegion as ce';

        $data['join'] = ' LEFT JOIN  '.$data_re.' ON bu.region_id = re.id
                          LEFT JOIN  '.$data_ce.' ON bu.city_id = ce.id   ';
        $data['where'] = $count_sql;
        $counts = $base->countData($data);
        if(empty($counts)){
            return array('res' => 'success','list' => 0);
        }
        $pages['page'] = (int)isset($param['page'])?$param['page']:1;//当前页
        $pages['counts'] = $counts;
        $pages['url'] = $param['url'];
        $page = $this->page->pageDetail($pages);

        $data['column'] = 'bu.city_id, re.region_name, bu.business_id,bu.phone,bu.real_name,bu.id_card,bu.user_id,bu.is_del,bu.create_time,ce.region_name as city' ;
        $data['order'] = 'ORDER BY bu.business_id DESC';
        $data['limit'] = "LIMIT ".$page['record'].','.$page['psize'];
        $result =  $base->getData($data);
        $list = [
            'res'  => 'success',
            'list' => $result,
            'page' => $page['page']
        ];
        $this->view->setVar('list',$list);
    }
    public function addAction(){

        $cout = "";
        $data = array();
        $data['red'] =  $this->city();
        if(isset($_GET['province'])&&$_GET['stu']==1){
            $array = $this->city($_GET['province'],1);
            $address_str = '<option value="0">全部</option>';
            foreach($array as $row){
                $address_str .= '<option value="'.$row['id'].'">'.$row['region_name'].'</option>';
            }
            echo $address_str; exit;
        }
        $post = $this->request->getPost();
        if($post){
            $post['region_id'] = $_POST['province']?$_POST['province']:"";
            $post['city_id'] = $_POST['city']?$_POST['city']:"";
            if($post['region_id']==""){
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData('省不能为空', '/cpsbusiness/add', 'error'));

            }else if($post['city_id']=="") {
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData('市不能为空', '/cpsbusiness/add', 'error'));

            }else if( $post['real_name']==""){
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData('真实姓名不能为空', '/cpsbusiness/add', 'error'));
            }else{
                $d[0] = $post['phone'];
                $d[1] = iconv('UTF-8', 'GB2312', trim($post['real_name']));
                $d[2] = $post['id_card'];
                $d[3] = iconv('UTF-8', 'GB2312', trim($post['region_id']));
                $d[4] = iconv('UTF-8', 'GB2312', trim($post['city_id']));

                $ret  = $this->verification($d);

                if(count($ret)>0){

                    foreach($ret as $v){
                        $cout .= "手机号码：".$v['phone']."错误提示（{$v['error']}）<br/>" ;
                    }

                    return $this->response->setJsonContent(BaseService::getInstance()->arrayData($cout, '/cpsbusiness/add', 'error'));
                }else{
                    return $this->response->setJsonContent(BaseService::getInstance()->arrayData("添加成功", '/cpsbusiness/list', ''));
                }
            }

        }
        $this->view->setVar('address', $data['red']);
    }
    public function deleAction(){

        $base = BaseData::getInstance();
        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }
        $table= '\Shop\Models\BaiyangBusiness';
        $dtat = "is_del=:is_del:";
        if($param['id']&&$param['is_del']){
            $res = $base->update($dtat,$table,['is_del'=>$param['is_del']],"business_id={$param['id']} ");
            if($res){
                $data['status']='ok';
                die(json_encode($data));
            }else{
                $data['status']='on';
                die(json_encode($data));
            }
        }else{
            $data['status']='on';
            die(json_encode($data));
        }

    }
    public function editAction(){
        $base = BaseData::getInstance();
        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }
        $data = array();
        $data['red'] =  $this->city();
        if(isset($_GET['province'])&&$_GET['stu']==1){
            $array = $this->city($_GET['province'],1);
            $address_str = '<option value="0">全部</option>';
            foreach($array as $row){
                $address_str .= '<option value="'.$row['id'].'">'.$row['region_name'].'</option>';
            }
            echo $address_str; exit;
        }
        if($param['edit']){
            $data['table'] = '\Shop\Models\BaiyangBusiness';
            $data['column'] = '*' ;
            $data['where'] = " where business_id =  {$param['edit']}";
            $data =  $base->getData($data,true);
            $red =  $this->city();
            $city =  $this->city($data['city_id']);
            $this->view->setVar('edit',$param['edit']);
            $this->view->setVar('red',$red);
            $this->view->setVar('city',$city);
            $this->view->setVar('data',$data);
        }
        $post = array('phone'=>'','province'=>'','city'=>'','id_card'=>'','real_name'=>'');
        $post = $this->request->getPost();
        if($post){
            if($post['phone']==''){
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData("电话不能为空", '/cpsbusiness/edit?edit='.$param['edit'], 'error'));
            }
            if($post['province']==''){
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData("请选择省", '/cpsbusiness/edit?edit='.$param['edit'], 'error'));
            }
            if($post['city']==''){
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData("请选择市", '/cpsbusiness/edit?edit='.$param['edit'], 'error'));
            }
            if($post['id_card']==''){
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData("身份证不能为空", '/cpsbusiness/edit?edit='.$param['edit'], 'error'));
            }
            if($post['real_name']==''){
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData("用户名不能为空", '/cpsbusiness/edit?edit='.$param['edit'], 'error'));
            }
            $id = "";
            $id =  $base->update('phone=:phone:,region_id=:region_id:,city_id=:city_id:,id_card=:id_card:,real_name=:real_name:','\Shop\Models\BaiyangBusiness',['phone'=>$post['phone'],
                'region_id'=>$post['province'],'city_id'=>$post['city'],'id_card'=>$post['id_card'],'real_name'=>$post['real_name']],"business_id = {$param['edit']}",true);
            if($id){
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData("修改成功", '/cpsbusiness/list', ''));
            }
        }


    }
    public function orderAction(){
        $base = BaseData::getInstance();
        $param = array(
            'order_sn'=>'',
            'brand'=>'',
            'region_id'=>'',
            'id_card'=>'',
            'start_time'=>'',
            'end_time'=>'',
            'id_daili'=>'',

        );
        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }

        $this->view->setVar('channel',$param);
        $param['page']  =   $this->request->get('page','trim',1);
        $param['url'] = $this->automaticGetUrl();


        $count_sql = " WHERE bko.order_sn<>'' AND bko.status IN ('shipping','shipped','evaluating','finished') AND bkod.business_id > 0 ";
        if (isset($param['order_sn']) && !empty($param['order_sn'])) {
            $count_sql .= "AND bko.order_sn ='".$search_data['order_sn']."' ";
        }
        if (isset($param['brand']) && !empty($param['brand'])) {
            $count_sql .= "AND bg.brand_id =".$search_data['brand']." ";
        }else{
            $count_sql .= "AND bg.brand_id IN (155,2988) ";
        }
        if (isset($param['region_id']) && !empty($param['region_id'])) {
            $count_sql .= "AND (bbm.id_card ='".$param['id_card']."'  OR bbm.real_name = '{$param['id_card']}') ";
        }
        if (isset($param['id_card']) && !empty($param['id_card'])) {
            $count_sql .= "AND (bbm.id_card ='".$param['id_card']."'  OR bbm.phone = '{$param['id_card']}') ";
        }
        if (!empty($param['start_time'])&&$param['start_time']!='') {
            $count_sql .= "AND bko.add_time >=".$param['start_time'];
        }
        if (!empty($param['start_time'])&&!empty($param['end_time'])&&$param['start_time']!=''&&$param['end_time']!='') {
            $count_sql .= " AND bko.add_time >=".$param['start_time']." AND bko.add_time <= ".$param['end_time'];
        }
        if (isset($param['id_daili']) && !empty($param['id_daili'])) {
            $count_sql .= " AND (bb.phone ='".$param['id_daili']."'  OR bb.real_name = '{$param['id_daili']}') ";
        }
        $data['column'] = "bko.order_sn,bbm.id_card,bbm.real_name,bbm.phone, FROM_UNIXTIME(bko.add_time,'%Y %D %M %h:%i:%s %x') as add_time    ,bkod.goods_name,bkod.price,bkod.goods_number,SUM(bkod.price+(bkod.tax_rate*0.01*bkod.price)) as c_prie , bb.phone as bu_phone ,bb.real_name as bb_name" ;
        $data['where'] = $count_sql ."  group by bko.order_sn,bkod.goods_id";
        $data['table'] = '\Shop\Models\BaiyangBusiness as bb';
        $m = '\Shop\Models\BaiyangBusinessMandatary as bbm';
        $k = '\Shop\Models\BaiyangKjOrder as bko';
        $d = '\Shop\Models\BaiyangKjOrderDetail as bkod';
        $g = '\Shop\Models\BaiyangGoods as bg';
        $data['join'] = ' LEFT JOIN  '.$m.' ON bb.business_id=bbm.business_id
                          LEFT JOIN  '.$k.' ON bbm.user_id=bko.user_id
                          LEFT JOIN  '.$d.' ON bko.order_sn=bkod.order_sn
                          LEFT JOIN  '.$g.' ON bkod.goods_id=bg.id';
        $counts =  count($base->getData($data));
        if(empty($counts)){
            return array('res' => 'success','list' => 0);
        }

        $pages['page'] = (int)isset($param['page'])?$param['page']:1;//当前页
        $pages['counts'] = $counts;
        $pages['url'] = $param['url'];
        $page = $this->page->pageDetail($pages);
        $data['limit'] = "LIMIT ".$page['record'].','.$page['psize'];
        $result =  $base->getData($data);

        $list = [
            'res'  => 'success',
            'list' => $result,
            'page' => $page['page']
        ];
        $this->view->setVar('list',$list);
    }
    public function ordercsvAction(){
        $base = BaseData::getInstance();
        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }
        $count_sql = " WHERE bko.order_sn<>'' AND bko.status IN ('shipping','shipped','evaluating','finished') AND bkod.business_id > 0 ";
        if (isset($param['order_sn']) && !empty($param['order_sn'])) {
            $count_sql .= "AND bko.order_sn ='".$param['order_sn']."' ";
        }
        if (isset($param['brand']) && !empty($param['brand'])) {
            $count_sql .= "AND bg.brand_id =".$param['brand']." ";
        }else{
            $count_sql .= "AND bg.brand_id IN (155,2988) ";
        }
        if (isset($param['region_id']) && !empty($param['region_id'])) {
            $count_sql .= "AND (bbm.id_card ='".$param['id_card']."'  OR bbm.real_name = '{$param['id_card']}') ";
        }
        if (isset($param['id_card']) && !empty($param['id_card'])) {
            $count_sql .= "AND (bbm.id_card ='".$param['id_card']."'  OR bbm.phone = '{$param['id_card']}') ";
        }
        if (!empty($param['start_time'])) {
            $count_sql .= "AND bko.add_time >=".$param['start_time'];
        }
        if (!empty($param['start_time'])&&!empty($param['end_time'])) {
            $count_sql .= " AND bko.add_time >=".$param['start_time']." AND bko.add_time <= ".$param['end_time'];
        }
        if (isset($param['id_daili']) && !empty($param['id_daili'])) {
            $count_sql .= " AND (bb.phone ='".$param['id_daili']."'  OR bb.real_name = '{$param['id_daili']}') ";
        }
        $data['column'] = 'bko.order_sn,bbm.id_card,bbm.real_name,bbm.phone,bko.add_time,bkod.goods_name,bkod.price,bkod.goods_number,SUM(bkod.price+(bkod.tax_rate*0.01*bkod.price)) as c_prie , bb.phone as bu_phone ,bb.real_name as bb_name' ;
        $data['where'] = $count_sql ."  group by bko.order_sn,bkod.goods_id";
        $data['table'] = '\Shop\Models\BaiyangBusiness as bb';
        $m = '\Shop\Models\BaiyangBusinessMandatary as bbm';
        $k = '\Shop\Models\BaiyangKjOrder as bko';
        $d = '\Shop\Models\BaiyangKjOrderDetail as bkod';
        $g = '\Shop\Models\BaiyangGoods as bg';
        $data['join'] = ' LEFT JOIN  '.$m.' ON bb.business_id=bbm.business_id
                          LEFT JOIN  '.$k.' ON bbm.user_id=bko.user_id
                          LEFT JOIN  '.$d.' ON bko.order_sn=bkod.order_sn
                          LEFT JOIN  '.$g.' ON bkod.goods_id=bg.id';
        $result =  $base->getData($data);
        $date = date('Y-m-d H:i:s');

        $str =  iconv('UTF-8', 'GB2312','订单编号' ).",".iconv('UTF-8', 'GB2312','身份证' ).",".iconv('UTF-8', 'GB2312','用户名' ).",".iconv('UTF-8', 'GB2312','用户名真实姓名' ).",".iconv('UTF-8', 'GB2312','代理人手机号' ).",".iconv('UTF-8', 'GB2312','代理人姓名' ).",".iconv('UTF-8', 'GB2312','下单时间' ).",".iconv('UTF-8', 'GB2312','商品名称' ).",".iconv('UTF-8', 'GB2312','商品金额' ) .",".iconv('UTF-8', 'GB2312','数量' ).",".iconv('UTF-8', 'GB2312','实付总金额' );

        $str.="\n";
        foreach($result as $val){
            //echo iconv('UTF-8', 'GB2312',$val['real_name']);
            $val['id_card'] .= "\t";
            $str.="{$val['order_sn']},{$val['id_card']},".$val['phone'].",".iconv('UTF-8', 'GB2312',$val['real_name']).",".iconv('UTF-8', 'GB2312',$val['bb_name']).",{$val['bu_phone']},".date("Y-m-d H:i:s",$val['add_time']).",".mb_convert_encoding($val['goods_name'], "GBK").",{$val['price']},{$val['goods_number']},{$val['c_prie']}\n";
        }
        $filename = date('Ymd').'.csv'; //设置文件名

        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=".$filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');

        echo $str;exit;
    }
    public function detailsAction(){
        $base = BaseData::getInstance();
        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }
        $list = $this->order_details($param['id']);
        //  print_r($list);exit;
        $this->view->setVar('list',$list);
    }

    public function city($id="",$p="")
    {
        $base = BaseData::getInstance();
        if(!$id){
            $data['where'] = " where pid = 1";
        }else if($id&&$p==1){
            $data['where'] = " where pid ={$id}";
        }elseif($id&&$p!=1){
            $data['where'] = " where  id={$id}";
        }

        $data['table'] = '\Shop\Models\BaiyangRegion';
        $data['column'] = 'region_name,pid,id' ;

        return  $data =  $base->getData($data);
    }
    public function drcsvAction(){
        if($_FILES){
            $url = $_FILES['file_upload']['tmp_name'];
            $arr =  explode(".",$_FILES['file_upload']['name']);
            if(isset($arr[1]) && $arr[1]!="csv"){
                echo      "上传文件格式不对";exit;
            }
            if(!isset($arr[1])){
                echo      "请上传文件";exit;
            }
            $file = fopen("$url",'r');
            while ( $var = fgetcsv($file)) {
                $goods_list[] = $var;
            }
            for($i=1;$i<count($goods_list);$i++){

                $ret[$i] = $this->verification($goods_list[$i]);

            }
            if(count($ret)>0 && !empty($ret[1])){
                $cout = '';
                foreach($ret as $v){
                    foreach($v as $p){
                        $cout .= "手机号码：".$p['phone']."错误提示（{$p['error']}）<br/>" ;
                    }
                }
                echo $cout;exit;
            }else{
                echo "导入成功";exit;
            }
        }
    }

    public function csvAction(){


        $base = BaseData::getInstance();
        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }

        if(isset($param['csv'])&&$param['csv']==1){
            $this->csv_template();exit;
        }

        $count_sql = "WHERE bu.is_del = 0  ";
        if (isset($param['phone']) && !empty($param['phone'])) {
            $count_sql .= " AND bu.phone =".$param['phone'];
        }
        if (isset($param['id_card']) && !empty($param['id_card'])) {
            $count_sql .= " AND bu.id_card ='".$param['id_card']."' ";
        }

        if(!empty($param['region_id'])){
            $count_sql .= " AND bu.region_id =".$param['region_id'];
        }
        if (isset($param['region_id']) && !empty($param['region_id'])) {
            $count_sql .= " AND bu.city_id  =".$param['city_id'];
        }
        $data['table'] = '\Shop\Models\BaiyangBusiness as bu';
        $data_re = '\Shop\Models\BaiyangRegion as re';
        $data_ce = '\Shop\Models\BaiyangRegion as ce';

        $data['join'] = ' LEFT JOIN  '.$data_re.' ON bu.region_id = re.id
                          LEFT JOIN  '.$data_ce.' ON bu.city_id = ce.id   ';
        $data['where'] = $count_sql;
        $data['column'] = 'bu.city_id, re.region_name, bu.business_id,bu.phone,bu.real_name,bu.id_card,bu.user_id,bu.is_del,bu.create_time,ce.region_name as city' ;
        $data['order'] = 'ORDER BY bu.business_id DESC';
        $date = date('Y-m-d H:i:s');
        $result =  $base->getData($data);

        // $str = "订单编号,身份证,用户名,下单时间,商品名称,商品金额,数量,实付总金额";

        $str =  iconv('UTF-8', 'GBK','用户名（手机号）' ).",".iconv('UTF-8', 'GB2312','姓名' ).",".iconv('UTF-8', 'GB2312','身份证' ).",".iconv('UTF-8', 'GB2312','省区' ).",".iconv('UTF-8', 'GB2312','市' );



        $str.="\n";


        foreach($result as $val){
            //echo iconv('UTF-8', 'GB2312',$val['real_name']);
            $val['id_card'] .= "\t";
            $str.="{$val['phone']},".iconv('UTF-8', 'GB2312',$val['real_name']).",{$val['id_card']},".iconv('UTF-8', 'GB2312',$val['region_name']).",".iconv('UTF-8', 'GB2312',$val['city']).",\n";
        }
        $filename = date('Ymd').'.csv'; //设置文件名

        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=".$filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');

        echo $str; exit;

        //export_csv($filename,$str); //导出
        //echo $str;

    }

    public function csv_template(){
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=模板.csv");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        $str =  iconv('UTF-8', 'GBK','用户名（手机号）' ).",".iconv('UTF-8', 'GB2312','姓名' ).",".iconv('UTF-8', 'GB2312','身份证' ).",".iconv('UTF-8', 'GB2312','省区' ).",".iconv('UTF-8', 'GB2312','市' );
        $str.="\n";
        echo $str;
    }
    public function verification($val)
    {
        if($val[0]==""){
            $error[1]['phone'] = $val[0];
            $error[1]['error'] = "电话号码为空";
            return $error;
        }
        if($val[1]==""){
            $error[$val[0]]['phone'] = $val[0];
            $error[$val[0]]['error'] = "姓名不能为空";
            return $error;
        }
        if($val[2]==""){
            $error[$val[0]]['phone'] = $val[0];
            $error[$val[0]]['error'] = "身份证为空";
            return $error;
        }
        if($val[3]==""){
            $error[$val[0]]['phone'] = $val[0];
            $error[$val[0]]['error'] = "省为空";
            return $error;
        }
        if($val[4]==""){
            $error[$val[0]]['phone'] = $val[0];
            $error[$val[0]]['error'] = "市为空";
            return $error;
        }
        $base = BaseData::getInstance();
        $data['column'] = "id,idcard";
        $data['where'] = " where phone = {$val[0]} ";
        $data['table'] = '\Shop\Models\BaiyangUser';
        $user = $base->getData($data,true);
        $data = array();

        $error =array();
        if($user['id']&&$user){

            if($user['idcard']){
                if($user['idcard']!=iconv('GB2312', 'UTF-8', trim($val[2]))){
                    $error[$val[0]]['phone'] = $val[0];
                    $error[$val[0]]['error'] = "身份证不正确";
                    return $error;
                }
            }



            $data['column'] = "mandatary_id";
            $data['where'] = " where phone = {$val[0]} ";
            $data['table'] = '\Shop\Models\BaiyangBusinessMandatary';
            $mandatary = $base->getData($data,true);
            $data = array();



            if($mandatary['mandatary_id']&&$mandatary){
                $error[$val[0]]['phone'] = $val[0];
                $error[$val[0]]['error'] = "委托人不能成为业务代表";

            }else{

                $data['column'] = "business_id,id_card,is_del";
                $data['where'] = " where phone = '{$val[0]}' ";
                $data['table'] = '\Shop\Models\BaiyangBusiness';
                $business = $base->getData($data,true);
                $data = array();

                if($business['business_id']&&$business&&$business['is_del']==0){
                    $error[$val[0]]['phone'] = $val[0];
                    $error[$val[0]]['error'] = "电话号码已经存在";
                    return $error;
                }
                $id_card  =   iconv('GB2312', 'UTF-8', trim($val[2]));


                $data['column'] = "id_card,is_del";
                $data['where'] = " where id_card = '{$id_card}' ";
                $data['table'] = '\Shop\Models\BaiyangBusiness';
                $business2 = $base->getData($data,true);
                $data = array();
                if($business2['id_card']&&$business2['is_del']==0){
                    $error[$val[0]]['phone'] = $val[0];
                    $error[$val[0]]['error'] = "该身份证以已经被添加，不可以添加第二次";
                    return $error;
                } else{

                    $real_name =  iconv('GB2312', 'UTF-8', $val[1]);
                    $region_name =  iconv('GB2312', 'UTF-8', trim($val[3]));
                    $city_name =  iconv('GB2312', 'UTF-8', trim($val[4]));


                    $data['column'] = "id";
                    $data['where'] = is_numeric($region_name) ? " where id = {$region_name} " : " where region_name='{$region_name}' ";
                    $data['table'] = '\Shop\Models\BaiyangRegion';
                    $region1 = $base->getData($data,true);
                    $data = array();

                    if($region1['id']&&$region1){

                        $data['column'] = "id";
                        $data['where'] = is_numeric($city_name) ? " where id = {$city_name} " : "where region_name='{$city_name}' ";
                        $data['table'] = '\Shop\Models\BaiyangRegion';
                        $region2 = $base->getData($data,true);
                        $data = array();

                        if($region2&&$region2['id']){
                            $time = time();
                            if($business['is_del']==1){
                                $table = '\Shop\Models\BaiyangBusiness';
                                $res = $base->update("is_del = :is_del:",$table,['is_del'=>0,]," business_id= {$business['business_id']} ");

                            }else{
                                $table = '\Shop\Models\BaiyangBusiness';
                                $id =  $base->insert($table,$param = ['phone'=>$val[0],'real_name'=>$real_name,'id_card'=>$val[2],'region_id'=>$region1['id'],'city_id'=>$region2['id'],'user_id'=>$user['id'],'create_time'=>time()],true);

                                $table = '\Shop\Models\BaiyangBusiness';
                                $base->insert($table,$param = ['phone'=>$val[0],'real_name'=>$real_name,'id_card'=>$val[2],'region_id'=>$region1['id'],'city_id'=>$region2['id'],'id_card_image'=>'','business_id'=>$id,'status'=>1,'user_id'=>$user['id'],'update_time'=>time()]);


//                           $data['column'] = "order_sn";
//                           $data['where'] = " where user_id = {$val[0]} ";
//                           $data['table'] = '\Shop\Models\BaiyangKjOrderDetail';
//                           $order = $base->getData($data);
//                           $data = array();
//
//                           foreach($order AS $p){
//
//                               $table = '\Shop\Models\BaiyangKjOrderDetail';
//                               $res = $base->update("business_id = :business_id:",$table,['business_id'=>$id,]," order_sn= {$p['order_sn']} ");
//
//                           }

                            }
                            $data['column'] = "user_id";
                            $data['where'] = " where user_id = {$user['id']} and tag_id=20";
                            $data['table'] = '\Shop\Models\BaiyangUserGoodsPriceTag';
                            $tag = $base->getData($data,true);
                            $data = array();


                            if(!$tag['user_id']){

                                $table = '\Shop\Models\BaiyangUserGoodsPriceTag';
                                $base->insert($table,$param = ['user_id'=>$user['id'],'tag_id'=>20,'add_time'=>time()]);

                            }
                        }else{
                            $error[$val[0]]['phone'] = $val[0];
                            $error[$val[0]]['error'] = "市名称错误";

                        }

                    }else{
                        $error[$val[0]]['phone'] = $val[0];
                        $error[$val[0]]['error'] = "省名称错误";
                    }

                }
            }

        }else{
            $error[$val[0]]['phone'] = $val[0];
            $error[$val[0]]['error'] = "该用户手机号未注册，请注册之后再添加";
        }
        return $error;
    }

    public function order_details($order_sn)
    {
        $order = array();
        $tax_row = 0;

        $base = BaseData::getInstance();
        $data['column'] = "order_sn,pay_time,add_time,pay_type,payment_name,channel_subid,consignee,telephone,express_type,province,county,city,
              address,user_id,goods_price,carriage,order_tax_amount,real_pay,order_total_amount";
        $data['where'] = " WHERE order_sn = '{$order_sn}' ";
        $data['table'] = '\Shop\Models\BaiyangKjOrder';
        $order = $base->getData($data,true);
        $data = array();
        $order['yunshui'] = 0;
        $order['order_tax_amount'] = 0;


        $data['column'] = "kj.tax_rate,kj.goods_id,kj.goods_name,kj.price ,kj.goods_number ,gs.medicine_type,goods_tax_amount,kj.unit_price";
        $data['where'] = " where kj.order_sn='{$order['order_sn']}'";
        $data['table'] = '\Shop\Models\BaiyangKjOrderDetail as kj';
        $goods = '\Shop\Models\BaiyangGoods as gs ';
        $data['join'] = ' LEFT JOIN  '.$goods.' on gs.id = kj.goods_id';
        $goods = $base->getData($data);
        $data = array();
        $order['goods'] = $goods;
        foreach($goods as $key=>$vla){
            $price = $vla['price'];
            $order['order_tax_amount']+=bcmul($vla['price'],$vla['tax_rate']*0.01,10);
            $tax_row = bcmul(bcdiv($price,$order['goods_price'],20),bcmul($order['carriage'],($vla['tax_rate']*0.01),6),20);
            $order['yunshui'] += $tax_row;
        }

        $order['pay_time'] = $order['pay_time']?date('Y-m-d H:i:s',$order['pay_time']):"";
        $order['add_time'] = $order['add_time']?date('Y-m-d H:i:s',$order['add_time']):"";
        if($order['express_type']==0) $order['express_type']='普通快递'; else if($order['express_type']==90) $order['express_type']='';else if($order['express_type']==91) $order['express_type']='';
        if($order['channel_subid']==89) $order['channel_subid']='苹果'; else if($order['channel_subid']==90) $order['channel_subid']='安卓';else if($order['channel_subid']==91) $order['channel_subid']='wap';else if($order['channel_subid']==95) $order['channel_subid']='pc';



        $data['column'] = "username,nickname";
        $data['where'] = "  WHERE id = {$order['user_id']}";
        $data['table'] = '\Shop\Models\BaiyangUser';
        $user = $base->getData($data,true);
        $data = array();


        if(isset($user['username'])&&$user['username']){
            $order['username'] = $user['username'];
        }

        $order['nickname'] = (isset($user['nickname'])&&$user['nickname'])?$user['nickname']:"无";
        $order['carriage'] = (isset($order['carriage'])&&$order['carriage'])?$order['carriage']:"";
        if(isset($order['province'])&&$order['province']){

            $data['column'] = "region_name";
            $data['where'] = "  WHERE id = '{$order['province']}'";
            $data['table'] = '\Shop\Models\BaiyangRegion';
            $province = $base->getData($data,true);
            $data = array();
            $order['province'] = $province['region_name'];
        }
        if(isset($order['city'])&&$order['city']){

            $data['column'] = "region_name";
            $data['where'] = "  WHERE id = {$order['city']}";
            $data['table'] = '\Shop\Models\BaiyangRegion';
            $province = $base->getData($data,true);
            $data = array();
            $order['city'] = $province['region_name'];
        }
        if($order['county']){

            $data['column'] = "region_name";
            $data['where'] = "  WHERE id = {$order['county']}";
            $data['table'] = '\Shop\Models\BaiyangRegion';
            $province = $base->getData($data,true);
            $data = array();
            $order['county'] = $province['region_name'];
        }
        return $order;
    }

}
?>
