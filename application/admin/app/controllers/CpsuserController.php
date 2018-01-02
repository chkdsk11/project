<?php
namespace Shop\Admin\Controllers;
//use Shop\Services\;
use Shop\Datas\BaseData;
use Shop\Services\BaseService;
class CpsUserController extends ControllerBase
{
    public $per_page_type = array(10, 20, 50, 100);
    //订单状态
    public $order_status = array(
        'paying' => '待付款',
        'shipping' => '待发货',
        'shipped' => '已发货',
        'evaluating' => '待评价',
        'refund' => '退款/售后',
        'canceled' => '交易关闭',
        'finished' => '订单完成',
    );
      /**
     * 推广会员列表
     */
    public function listAction()
    {
        $base = BaseData::getInstance();
        
        $param = array(
          'invite_code'=>'',
          'user_id'=>'', 
          'user_name'=>'', 
          'employee_id'=>'', 
          'channel_id'=>'', 
          'start_time'=>'', 
          'end_time'=>'',
          'cps_id'=>'',
          'csv'=>''   
        );

        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }
        
        $this->view->setVar('channel',$param);
        
        if($param['cps_id']){
            if($param['stu']==2){
              $whereStr = "cps_id = {$param['cps_id']}";
              $table = '\Shop\Models\BaiyangCpsUser';
              $res = $base->update("cps_status = :cps_status:",$table,['cps_status'=>2],$whereStr);
              echo 2;exit;  
            }else{
              $whereStr = "cps_id = {$param['cps_id']}";
              $table = '\Shop\Models\BaiyangCpsUser';
              $res = $base->update("cps_status = :cps_status:",$table,['cps_status'=>1],$whereStr);
              echo 1;exit;   
            }
             
        }
        
        $data['column'] = 'channel_id,channel_name,tags';
        $data['table'] = $data_cps_channel = '\Shop\Models\BaiyangCpsChannel';
        $data['where'] = " where channel_status = 1 ";
        $ch =  $base->getData($data); 
        foreach($ch as $val ){
            $channel[$val['channel_id']] = $val['channel_name'];
        } 
        $data = array();
        $this->view->setVar('channel_lset',(array)$ch);
        $param['page']  =   $this->request->get('page','trim',1);
        $param['url'] = $this->automaticGetUrl();
        if ($param['invite_code']) {
            $check_code = preg_match('/^[A-Z]{5,6}$/', $param['invite_code']);
            $param['invite_code'] = empty($check_code) ? $param['invite_code']:strtoupper($param['invite_code']);
        }
        $count_sql= " WHERE cps_status<>0 ";
        if (isset($param['user_id']) && !empty($param['user_id'])) {
            $count_sql .= "AND user_id=".$param['user_id']." ";
        }
        if (isset($param['invite_code']) && !empty($param['invite_code'])) {
            $count_sql .= "AND invite_code='".$param['invite_code']."' ";
        }
        if (isset($param['user_name']) && !empty($param['user_name'])) {
            $count_sql .= "AND user_name LIKE '%".$param['user_name']."%' ";
        }
        if (isset($param['employee_id']) && !empty($param['employee_id'])) {
            $count_sql .= "AND employee_id='".$param['employee_id']."' ";
        }
        if (isset($param['channel_id']) && !empty($param['channel_id'])) {
            $count_sql .= "AND channel_id=".$param['channel_id']." ";
        }
        if (isset($param['start_time']) && $param['start_time']) {
            $start_time = strtotime($param['start_time']);
            $count_sql .= " AND add_time >= {$start_time} ";
        }
        if(isset($param['end_time']) && $param['end_time'])
        {
            $end_time = strtotime($param['end_time']);
            $count_sql .= " AND add_time <= {$end_time} ";
        }
        $data['where'] = $count_sql;
        $data['table']='\Shop\Models\BaiyangCpsUser';
        $counts = $base->countData($data);
        if(($counts>6000) && $param['csv']==1){
            die('数量超过6000不可以直接导出，请先使用筛选条件筛选');
        }

        if(empty($counts)){
            $list = [
            'res'  => 'success',
            'list' => 0,
            'page' => ""
            ];
        
            $this->view->setVar('list',$list); 
            return;
        }
        $pages['page'] = (int)isset($param['page'])?$param['page']:1;//当前页
        $pages['counts'] = $counts;
        $pages['url'] = $param['url'];
        $page = $this->page->pageDetail($pages);
        $data['column'] = '*' ;
        $data['order'] = 'ORDER BY cps_id DESC';
        
        if($param['csv']!=1){
             $data['limit'] = "LIMIT ".$page['record'].','.$page['psize'];
        }
        
        $result =  $base->getData($data);
        if ($result) {
            $curr_one = $this->get_last_time(1);
            $last_one = $this->get_last_time(2);
            $last_end = $this->get_last_time(3);
            $cpsId = implode(',', array_column($result, 'cps_id'));
            $inviteCode = array_column($result, 'invite_code');
            foreach ($inviteCode as $k => $v) {
                $inviteCode[$k] = "'".$v."'";
            }
            $inviteCodeStr = implode(',', $inviteCode);
            $invite_all = $this->get_invite_all($cpsId);  //所有邀请记录
            $order_all = $this->get_order_all($inviteCodeStr);    //有效订单

            #重构 所有邀请记录 数组
            $invite_all_arr = array();
            if(!empty($invite_all)){
                foreach($invite_all as $val){
                    $invite_all_arr[$val['user_id']][] = $val;
                }
            }
            unset($invite_all,$inviteCode,$inviteCodeStr);

            #重构 有效订单 数组
            $order_all_arr = array();
            if(!empty($order_all)){
                foreach($order_all as $val){
                    $order_all_arr[$val['user_id']][] = $val;
                }
            }
            unset($order_all);
            foreach ($result as $key => $value) {
                $result[$key]['channel_id'] = isset($channel[$value['channel_id']])
                    ? $channel[$value['channel_id']] : $value['channel_id'];

                $result[$key]['reg_num'] = 0;             //注册总数
                $result[$key]['order_num'] = 0;           //有效订单总数
                $result[$key]['last_reg_num'] = 0;        //上月注册数
                $result[$key]['last_order_num'] = 0;      //上月有效订单数
                $result[$key]['last_mny'] = 0;            //上月应结算金额
                $result[$key]['curr_mny'] = 0;            //本月应结算金额
                $result[$key]['count_back_amount'] = 0;   //返利总金额    add CSL 20160602

                if (isset($invite_all_arr[$value['user_id']])) {
                    $invite_all = $invite_all_arr[$value['user_id']];
                    foreach ($invite_all as $k => $v) {
                        $result[$key]['reg_num'] += 1;
                        $result[$key]['count_back_amount'] += $v['back_amount'];
                        if (($last_one <= $v['add_time']) && ($v['add_time'] < $last_end)) {
                            $result[$key]['last_reg_num'] += 1;
                            $result[$key]['last_mny'] += $v['back_amount'];
                        } elseif ($v['add_time'] >= $curr_one) {
                            $result[$key]['curr_mny'] += $v['back_amount'];
                        }
                    }
                }

                if (isset($order_all_arr[$value['user_id']])) {
                    $order_all = $order_all_arr[$value['user_id']];
                    foreach ($order_all as $k => $v) {
                        $result[$key]['order_num'] += 1;
                        $result[$key]['count_back_amount'] += $v['back_amount'];
                        if (($last_one <= $v['order_time']) && ($v['order_time'] < $last_end)) {
                            $result[$key]['last_order_num'] += 1;
                            $result[$key]['last_mny'] += $v['back_amount'];
                        } elseif ($v['order_time'] >= $curr_one) {
                            $result[$key]['curr_mny'] += $v['back_amount'];
                        }
                    }
                }
                $result[$key]['count_back_amount'] = sprintf('%.2f', $result[$key]['count_back_amount']);
                $result[$key]['last_mny'] = sprintf('%.2f', $result[$key]['last_mny']);
                $result[$key]['curr_mny'] = sprintf('%.2f', $result[$key]['curr_mny']);
            }
        }
        $list = [
            'res'  => 'success',
            'list' => $result,
            'page' => $page['page']
        ];
       
        if($param['csv']==1){
            header("Content-type:text/csv");
            header("Content-Disposition:attachment;filename=".date('Y-m-d').".csv");
            header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
            header('Expires:0');
            header('Pragma:public');
            $str = "会员编号,用户ID,姓名,员工ID,会员渠道,CPSID,注册会员总数,有效订单总数,上月注册会员数,上月订单有效数,"
                    . "上月应结算金额,本月预估结算金额,返利总金额\n";
            $str = iconv('utf-8','gbk',$str);
            if ($result) {
                
                foreach($result as $row){
                    $str .= $row['cps_id'].",".$row['user_id'].",".iconv('utf-8','gbk',$row['user_name']).",".$row['employee_id'].","
                         .iconv('utf-8','gbk',$row['channel_id']).",".$row['invite_code'].",".$row['reg_num'].",".$row['order_num'].","
                         .$row['last_reg_num'].",".$row['last_order_num'].",".$row['last_mny'].",".$row['curr_mny'].","
                         .$row['count_back_amount']."\n";
                }
            }
            echo $str;
            die; 
        }
        $this->view->setVar('list',$list);
    }
    public function clearingAction(){
      $user_id = $_POST['checked'];
      $this->clearing($user_id);
        
    }
    public function  addAction(){
        ini_set('max_execution_time', '0');
        $base = BaseData::getInstance();
        $param = $_GET;
        $data['column'] = 'channel_id,channel_name,tags';
        $data['table'] = $data_cps_channel = '\Shop\Models\BaiyangCpsChannel';
        $data['where'] = " where channel_status = 1 ";
        $ch =  $base->getData($data);
        $this->view->setVar('channel_lset',$ch);
        if(isset($param['re'])&&isset($param['stu'])&&$param['re']&&$param['stu']==1){
             $array = $this->city($param['re'],1);
             $address_str = '<option value="0">全部</option>';
             foreach($array as $row){
                $address_str .= '<option value="'.$row['id'].'">'.$row['region_name'].'</option>';
             }
             echo $address_str; exit;
        }
        //导入模板
        if(isset($param['isTemplate'])&&$param['isTemplate']==1){
            $headArray = ['会员ID','姓名','员工号','省','市','医院','科室'];
            $result = [[13581133880,'陈林',1104111,'北京','北京','南方第三附属医院','耳鼻喉科'],
                [13188748678,'郭道洋',1104122,'安徽','安庆','南方第三附属医院','耳鼻喉科'],
                [18053555809,'李晓巍',1104133,'安徽','池州','南方第三附属医院','耳鼻喉科']
            ];
            $this->excel->exportExcel($headArray,$result,'user','推广会员','xls');exit;
        }
        //批量导入
        if (isset($param['isImport']) && $param['isImport'] == 1) {
            if (!$this->request->hasFiles()) {
                return $this->response->setJsonContent([
                    'status' => 'error',
                    'info' => '请选择上传文件'
                ]);
            }
            $type = isset($_FILES['userFile']['name']) && $_FILES['userFile']['name']
                ? substr($_FILES['userFile']['name'], strrpos($_FILES['userFile']['name'], '.')+1) : '';
            if (!in_array($type,['xlsx','xls'])) {
                return $this->response->setJsonContent([
                    'status' => 'error',
                    'info' => '上传文件格式错误（请上传xlsx或xls格式文件）'
                ]);
            }
            $import = BaseService::getInstance()->filesUpload($this->request, '', '', $type);
            if($import['status'] == 'success') {
                $importData = $this->excel->importExcel($import['data'][0]['filePath'].$import['data'][0]['fileName'], $type);
                return $this->response->setJsonContent([
                    'status' => 'success',
                    'info' => '上传成功',
                    'data' => $importData,
                ]);
            }
            return $this->response->setJsonContent([
                'status' => 'error',
                'info' => '上传失败',
                'data' => $import,
            ]);
        }
        if ($_POST) {
            $p = $_POST;
            $no_user = [];
            if(isset($p['user_id'])&&$p['user_id']){
               $userinfo =  $this->sel_userinfo($p['user_id']);
               foreach($userinfo as $key=>$row){
                 if(!$row){
                    $no_user[] = $p['user_id'][$key];
                    unset($p['user_id'][$key]);
                 }
               }
            }
        
            $check_data = $this->check_datas($p['channel_id'], $p['user_id'], $p['user_name'], $p['employee_id'],$ch,$p['county'],$p['city'],$p['hospital'],$p['department']);
            $repetition = $check_data['repetition'];
            $user = $check_data['data'] ? $check_data['data'] : [];

            //存入表
            if ($user) {
                foreach ($user as $item) {
                    $table = '\Shop\Models\BaiyangCpsUser';
                    $base->insert($table,$item);
                }
            }

            $success = '';
            if($no_user) {
                $success .= implode($no_user,',').'不是商城用户，无法添加！</br>';
            }
            $repetitionArr = [];
            if ($repetition) {
                $success .= '会员 '.$repetition.' 已是推广会员</br>';
                $repetitionArr = explode(',', $repetition);
            }
            $errorNum = count($no_user) + count($repetitionArr);
            if ($errorNum > 0 && count($user) > 0) {
                $success .= '其余会员添加成功';
            }
            unset($no_user,$repetitionArr,$user,$repetition);
            if ($success) {
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData("{$success}", '', '', 'error'));
            } else {
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData("添加成功", '/cpsuser/list'));
            }
        }
        $red =  $this->city(); 
        $this->view->setVar('red',$red);
    }
   
    public function daoruAction(){
        // $_FILES
        $base = BaseData::getInstance();
        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }
        //导入模板
        if(isset($param['lzi'])&&$param['lzi']==1){
            //定义表头
            $str = "用户ID,邀请码,地区,业务员\n";

            $str .= '18911111111,FFHHHH,广东广州,张三'."\n";
            $str .= '18922222222,GGGGGG,广东言广州,李四'."\n";
            $str = iconv('utf-8','gbk',$str);
            $filename = date('导入模板').'.csv';
            header("Content-type:text/csv");
            header("Content-Disposition:attachment;filename=".$filename);
            header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
            header('Expires:0');
            header('Pragma:public');
            echo $str;exit;
        }
        //渠道
        if (!isset($_POST['channel']) || !$_POST['channel']) {
            echo '请选择渠道再导入！';
            exit;
        }
        $channel = $_POST['channel'];
        if (empty ($_FILES['Filedata']['name'])) {
            echo '请选择要导入的文件！';
            exit;
        }
        $kuozham =  explode('.',$_FILES['Filedata']['name']);
        //$allowed_types =  array('xlsx','xls','cvs');
        if(!isset($kuozham[1])){
            echo '导入错误';exit;
            // return $this->response->setJsonContent(BaseService::getInstance()->arrayData("导入错误", '/cpsuser/list', 'error'));
        }
        if($kuozham[1]=='csv'){
            $table=    '\Shop\Models\BaiyangCpsUser';
            $fileUrl = $_FILES['Filedata']['tmp_name'];
            if(!$fileUrl){
                echo '导入错误';exit;
                // return $this->response->setJsonContent(BaseService::getInstance()->arrayData("导入错误", '/cpsuser/list', 'error'));
            }
            $fopen =  fopen($fileUrl, 'r');
            $i = 1;
            $array = array();
            fgetcsv($fopen);
            $isError = 0;
            while(!feof($fopen))
            {
                if($i>1){
                    $array =  fgetcsv($fopen);
                    if ($array[0]) {
                        $dataArr = array(//'user_id'=>$user_id,
                            'user_id'=>$array[0],
                            'invite_code'=>$array[1],
                            'area'=>$array[2],
                            'clerk'=>$array[3],
                            'channel_id'=>$channel,
                        );
                        $data['column'] = 'cps_id';
                        $data['table'] = $table;
                        $data['where'] = " where invite_code = '{$array[1]}' ";
                        $ch =  $base->getData($data,true);
                        if(!$ch){
                            $tmp =$base->insert($table,$dataArr);
                            if(!$tmp){
                                echo "错误：".$array[0]."----".$array[1]."----",$array[2]."----".$array[3]."<br>";
                                $isError += 1;
                                continue;
                            }
                        }else{
                            echo "错误：".$array[0]."----".$array[1]."----".iconv("GB2312", "UTF-8", $array[2])."----".iconv("GB2312", "UTF-8", $array[3])."<br>";
                            $isError += 1;
                            continue;
                        }
                    }
                }
                $i++;
            }
            echo !$isError ? "导入成功" : "{$isError} 条数据导入失败";
            fclose($fopen);
            exit;
        }else{
            echo '请选导入的正确的文件！';exit;
        }
    }
     public function showAction(){
         $base = BaseData::getInstance();
         $data['column'] = 'channel_id,channel_name,tags';
         $data['table'] = $data_cps_channel = '\Shop\Models\BaiyangCpsChannel';
         $data['where'] = " where channel_status = 1 ";
         $ch =  $base->getData($data);
         if ($ch) {
             foreach ($ch as $key => $val) {
                 $channels['channel_name'][$val['channel_id']] = $val['channel_name'];
                 $channels['channel_tags'][$val['channel_id']] = $val['tags'];
             }
         } else {
             $channels = array(
                 'channel_name' => array(),
                 'channel_tags' => array(),
             );
         }

         $this->view->setVar('channel_lset',$ch);
         foreach($this->request->get() as $k=>$v){
             if($k != 'shop_category'){
                 $param[$k]  =   $this->getParam($k,'trim');
             }
         }
         $this->view->setVar('channel',$param);

         if(isset($param['user']) && $param['user']){
             $data['table'] = '\Shop\Models\BaiyangCpsUser as cu';
             $data['column'] = 'cu.cps_id, cu.user_id, cu.user_name, cu.employee_id, cu.channel_id,cu.add_time,r.true_name, cu.invite_code, cu.cps_status,cu.province,cu.city ' ;
             $region = '\Shop\Models\BaiyangRegion as r';
             $data['join'] = " left join  ".$region." on r.id=cu.city ";

             if($param['user']==""){
                 $data['where'] = " where cu.cps_status <> 0 and cu.user_id = {$param['user_id']}";
             }else{
                 $data['where'] = " where cu.cps_status <> 0 and cu.user_id = {$param['user']}";
             }
             $chet =  $base->getData($data);
             $code = $channel_id = array();
             if ($chet) {
                 foreach ($chet as $val) {
                     $code[$val['channel_id']] = $val['invite_code'];
                 }
                 $code = array_filter($code);

                 $this->view->setVar('code',$code);
                 ///
                 $data = array();
                 $data['table'] = '\Shop\Models\BaiyangCpsOrderLog as co';
                 $data['column'] = 'co.clearing_time,co.clearing,co.m_channel_id,co.freight,co.balance,co.pay_time,'
                     . 'co.user_id,co.order_status,co.order_time,co.discount_data,co.pay_id,co.order_sn,cu.cps_status,'
                     . 'cu.area, cu.short_code_office,cu.invite_code AS user_invite_code,co.real_pay' ;
                 $region = '\Shop\Models\BaiyangCpsOrderDetailLog as cod';
                 $region_1 = '\Shop\Models\BaiyangCpsUser as cu';
                 $region_2 = '\Shop\Models\BaiyangCpsChannel as ch';
                 $data['join'] = " left join  ".$region." on co.order_sn = cod.order_sn
                          left join  ".$region_1." on cod.invite_code = cu.invite_code
                          left join  ".$region_2." on cu.channel_id = ch.channel_id ";
                 $count_sql = " where 1=1  and cu.invite_code!= '' AND cu.user_id='{$param['user']}'";
                 if (isset($param['order_id'])&&$param['order_id']) {
                     $count_sql .= "AND co.order_sn='".$param['order_id']."' ";
                 }
                 if (isset($param['user_id'])&&$param['user_id']) {
                     $count_sql .= "AND co.user_id='".$param['user_id']."' ";
                 }
                 if (isset($param['channel_id'])&&$param['channel_id']) {
                     $count_sql .= "AND cu.channel_id=".$param['channel_id']." ";
                 }
                 if (isset($param['code'])&&$param['code']!=""&&$param['code']!=0) {
                     $count_sql .= "AND cod.invite_code='".$param['code']."' ";
                 }
                 if (isset($param['platform'])&&$param['platform']!="") {
                     $count_sql .= "AND co.platform_id=".$param['platform']." ";
                 }
                 if (!isset($param['start_time']) && !isset($param['end_time'])) {
                     $count_sql .= "AND  co.order_time BETWEEN ".strtotime(date('Y-m-01 00:00:00', strtotime('-1 month')))." AND ".time()." ";
                 } else {
                     if ($param['start_time'] && $param['end_time']) {
                         $count_sql .= "AND co.order_time BETWEEN ".strtotime($param['start_time'])." AND ".strtotime($param['end_time'])." ";
                     } elseif ($param['start_time'] && !$param['end_time']) {
                         $count_sql .= "AND co.order_time BETWEEN ".strtotime($param['start_time'])." AND ".time()." ";
                     } elseif (!$param['start_time'] && $param['end_time']) {
                         $count_sql .= "AND co.order_time <= ".strtotime($param['end_time'])." ";
                     }
                 }
                 if (isset($param['order_status'])) {
                     $count_sql .= "AND co.order_status ='".$param['order_status']."' ";
                 }
                 $data['where'] = $count_sql." GROUP BY co.order_sn ORDER BY co.order_time DESC,co.order_sn DESC";
                 $old_data =  $base->getData($data);
                 $counts = count($old_data);

                 if(empty($counts)){
                     return array('res' => 'success','list' => 0);
                 }
                 $param['page']  =   $this->request->get('page','trim',1);
                 $param['url'] = $this->automaticGetUrl();
                 $pages['page'] = (int)isset($param['page'])?$param['page']:1;//当前页
                 $pages['counts'] = $counts;
                 $pages['url'] = $param['url'];
                 $page = $this->page->pageDetail($pages);

                 $data['limit'] = "LIMIT ".$page['record'].','.$page['psize'];
                 $list =  $base->getData($data);
                 if(!$list){
                     return array('res' => 'success','list' => 0);
                 }
                 $order_sn = array();
                 foreach ($list as $key => $val) {
                     $order_sn[] = "'{$val['order_sn']}'";
                     $list[$key]['price'] = '0.00';
                     $list[$key]['moneyOff'] = '0.00';
                 }
                 if ($order_sn) {
                     $order_goods_price = $this->get_order_goods_price($order_sn);
                     $orderPromotion = $this->getOrderPromotion($order_sn);
                     foreach ($list as $k => $v) {
                         if (isset($order_goods_price[$v['order_sn']])) {
                             $list[$k]['price'] = $order_goods_price[$v['order_sn']];
                         }
                         if (isset($orderPromotion[$v['order_sn']])) {
                             $list[$key]['moneyOff'] = $orderPromotion[$v['order_sn']];
                         }
                     }
                 }

                 $channel = isset($channels['channel_name']) ? $channels['channel_name'] : [];
                 if($order_sn){
                     $o = implode(',', $order_sn);
                     $data = array();
                     $data['table'] = '\Shop\Models\BaiyangOrderPromotionDetail as op';
                     $data['column'] = ' op.promotion_id as coupon_id ,op.promotion_name as  name,cp.id ' ;
                     $region = '\Shop\Models\BaiyangCoupon as cp ';
                     $data['join'] = " left join  ".$region." on op.promotion_id = cp.coupon_sn ";
                     $data['where'] = " where op.discount_type = 1 AND op.order_sn IN ({$o})";
                     $result  = $base->getData($data);
                     $coupon = array();
                     if ($result) {
                         foreach ($result as $k => $v) {
                             if ($v['id']) {
                                 $coupon[$v['id']] = $v['name'];
                             }else {
                                 $coupon[$v['coupon_id']] = $v['name'];
                             }
                         }
                     }
                 }

                 //支付方式
                 $pay_type = array(
                     '1' => '支付宝',
                     '2' => '微信',
                     '3' => '货到付款',
                     '4' => '红包',
                     '5' => 'Apple',
                     '6' => '银联',
                     '7' => '余额'
                 );
                 //订单状态
                 $order_status = $this->order_status;
                 //客户端类型
                 $channel_type = array(
                     '89' => 'IOS',
                     '90' => 'Andriod',
                     '95' => 'PC',
                     '91' => 'WAP',
                     '85' => 'WeChat'
                 );
                 $order_detail = $this->get_order_detail_all();

                 $coupon = $this->get_coupon_all(array_column($list,'order_sn'));

                 foreach ($list as $key => $val) {
                     $list[$key]['back_amount'] = 0;
                     if ($order_detail) {
                         foreach($order_detail as $value) {
                             if ($val['order_sn'] == $value['order_sn'] && $val['user_invite_code'] == $value['invite_code']) {
                                 $list[$key]['back_amount'] += round($value['back_amount'], 2);
                                 $list[$key]['invite_code'] = $value['invite_code'];
                                 $list[$key]['channel_id'] = isset($channel[$value['channel_id']])
                                     ? $channel[$value['channel_id']]:'未设置';
                             }
                         }
                     }

                     $list[$key]['back_amount'] = isset($list[$key]['back_amount'])?sprintf('%.2f', $list[$key]['back_amount']):0;
                     $list[$key]['order_time'] = $val['order_time'] ? date('Y-m-d H:i:s', $val['order_time']) : '';
                     $list[$key]['pay_id'] = isset($pay_type[$val['pay_id']]) ? $pay_type[$val['pay_id']] : '未支付';
                     $list[$key]['pay_time'] = $val['pay_time'] ? date('Y-m-d H:i:s', $val['pay_time']) : '';
                     $list[$key]['m_channel_id'] = isset($channel_type[$val['m_channel_id']])
                         ? $channel_type[$val['m_channel_id']] : '';
                     $list[$key]['order_status'] = isset($order_status[$val['order_status']])
                         ? $order_status[$val['order_status']]:'';
                     $list[$key]['clearing'] = $val['clearing'] ? '是' : '否';
                     $list[$key]['clearing_time'] = $val['clearing_time'] ? date('Y-m-d H:i:s', $val['clearing_time']) : '';
                     $list[$key]['coupon_name'] = '未使用优惠券';
                     $list[$key]['coupon_amount'] = '0.00';
                     if (!empty($val['discount_data'])) {
                         $discount_data = json_decode($val['discount_data'], true);
                         if (isset($discount_data['coupon'])) {
                             $list[$key]['coupon_name'] = isset($coupon[$discount_data['coupon']['coupon_id']])
                                 ? $coupon[$discount_data['coupon']['coupon_id']]:'';
                             $list[$key]['coupon_amount'] = isset($discount_data['coupon']['value'])
                                 ? $discount_data['coupon']['value']:'0.00';
                         }
                     }
                 }
                 $this->view->setVar('list',['list' => $list]);
             }
         }
     }
    public function get_last_time ($type = 0)
    {
        $where = strtotime(date('Y-m',time()) . '-01 00:00:01');
        $last_one = strtotime(date('Y-m-01', strtotime('-1 month', $where)));//上个月第一天
        $last_end = strtotime(date('Y-m-t 23:59:59', strtotime('-1 month', $where)));//上个月最后一天
        $curr_one = mktime(0, 0, 0, date('m'), 1, date('Y'));//本月第一天
        if ($type === 1) {
            return $curr_one;
        } elseif ($type === 2) {
            return $last_one;
        } elseif ($type === 3) {
            return $last_end;
        } else {
            return false;
        }
    }
    
    public function get_invite_all ($cpsId)
    {
        if (!$cpsId) {
            return false;
        }
        $base = BaseData::getInstance();
        $data['table']='\Shop\Models\BaiyangCpsUser AS cps';
        $data['column'] = 'cps.user_id, log.log_id, log.back_amount, log.add_time' ;
        $CpsChannel =  '\Shop\Models\BaiyangCpsChannel AS ch';
        $CpsInviteLog =  '\Shop\Models\BaiyangCpsInviteLog AS log';
        $data['join'] = ' LEFT JOIN  '.$CpsChannel.' ON cps.channel_id = ch.channel_id
                          LEFT JOIN  '.$CpsInviteLog.' ON cps.invite_code = log.invite_code' ;
        $data['where'] = " where cps.cps_status <> 0 AND ch.channel_status <> 0 AND log.log_id <> 0 AND log.cps_id IN ({$cpsId})";
        return $result =  $base->getData($data);
    }
    
    
    public function get_order_all($inviteCode = '', $userId = '')
    {
        if (!$inviteCode && !$userId) {
            return false;
        }
        $where = '';
        if ($inviteCode && $userId) {
            $where = " AND cod.invite_code IN ({$inviteCode}) AND ord.user_id IN ({$userId}) ";
        } elseif ($inviteCode) {
            $where = " AND cod.invite_code IN ({$inviteCode}) ";
        } elseif ($userId) {
            $where = " AND ord.user_id IN ({$userId}) ";
        }
        $base = BaseData::getInstance();
        $data['column'] = 'us.user_id, ord.order_sn, ord.order_time, SUM(ROUND(cod.back_amount, 2)) back_amount' ;
        $data['where'] = " where us.cps_status<>0 AND ch.channel_status<>0 and "
            . "ord.order_status IN ('shipping','shipped','evaluating','finished') {$where} "
            . "GROUP BY ord.order_sn, cod.invite_code ORDER BY us.user_id ";
        $data['table'] = '\Shop\Models\BaiyangCpsOrderLog as ord';
        $CpsOrderDetailLog =  '\Shop\Models\BaiyangCpsOrderDetailLog AS cod';
        
        $CpsUser = '\Shop\Models\BaiyangCpsUser as us';
        $CpsChannel =  '\Shop\Models\BaiyangCpsChannel AS ch';
        $data['join']  =  ' LEFT JOIN '.$CpsOrderDetailLog.' ON ord.order_sn = cod.order_sn
                            LEFT JOIN '.$CpsUser.' ON cod.invite_code = us.invite_code
                            LEFT JOIN '.$CpsChannel.' ON us.channel_id = ch.channel_id ';
        
        return $result =  $base->getData($data);
        
    }
    public function clearing($user_id) {
        $curr_time = time();
     
        if (!$user_id) {
            echo json_encode(array('code' => 400));
            die;
        }

        $order_all = $this->get_order_all('',$user_id);
        $user_id = array_flip($user_id);
        $last_one = $this->get_last_time(2);
        $last_end = $this->get_last_time(3);
        $order_list = array();
        if ($order_all) {
            foreach ($order_all as $val) {
                if (isset($user_id[$val['user_id']]) && $last_one <= $val['order_time'] && $val['order_time'] < $last_end) {
                    $order_list[$val['order_id']] = "'".$val['order_id']."'";
                }
            }
            $where = implode(',', $order_list);
            $updata = $this->user_clearing($where, $curr_time);
            if ($updata) {
                echo json_encode(array('code' => 200));
                die;
            } else {
                echo json_encode(array('code' => 300));
                die;
            }
        } else {
            echo json_encode(array('code' => 400));
            die;
        }

    }
    public function user_clearing($where, $time)
    {
        if (!$where) {
            return false;
        }

        $base = BaseData::getInstance();
        $table =    '\Shop\Models\BaiyangCpsOrderLog';
   
            
        $whereStr = " clearing = 0 AND order_sn IN ({$where}) ";
            
        $res_s = $base->update("clearing = :clearing:,clearing_time=:clearing_time:",$table,['clearing'=>1,'clearing_time'=>time()],$whereStr);
        
        return $res_s ? true : false;
    }
    public function city($id="",$p=""){
       $base = BaseData::getInstance();
       if($id==''){
            $data['where'] = " where pid = 1"; 
       }else if($id!=""&&$p==1){
            $data['where'] = " where pid ={$id}"; 
       }elseif($id!=""&&$p!=1){
            $data['where'] = " where  id={$id}"; 
       }
       
       $data['table'] = '\Shop\Models\BaiyangRegion';
       $data['column'] = 'region_name,pid,id' ;
     
       return  $data =  $base->getData($data);
    }
    public function sel_userinfo($user_id){
        $base = BaseData::getInstance();
        if(!is_array($user_id)){
            return;
        }
        $arr = array();
        $data['table'] = '\Shop\Models\BaiyangUser';
        $data['column'] = 'user_id ' ;
        
        foreach($user_id as $row){
            
            $data['where'] = " where  username={$row} or user_id={$row} or phone={$row}";
            
            $arr[] =$base->getData($data,true);
        }
        return $arr;
    }
      /**
     * 检验数据
     */
    public function check_datas($id, $user, $name, $employee, $channel,$county,$city,$hospital,$department)
    {   $base = BaseData::getInstance();
        $channel_tags = '';
        //获取会员渠道标签
        foreach ($channel as $k => $v){
            if ($v['channel_id'] == $id) {
                $channel_tags = $v['tags'];
            }
        }
        $curr_time = time();

        $data['table'] = '\Shop\Models\BaiyangCpsUser as cu';
        $data['column'] = 'cu.cps_id, cu.user_id, cu.user_name, cu.employee_id, cu.channel_id,cu.add_time,r.true_name, cu.invite_code, cu.cps_status,cu.province,cu.city ' ;
        $region = '\Shop\Models\BaiyangRegion as r';
        $data['join'] = " left join  ".$region." on r.id=cu.city ";
        $data['where'] = " where cu.cps_status <> 0 ";
        
        $old_data =  $base->getData($data);
        
        
        $invite_code = $user_id = array();
        if ($old_data) {
            foreach ($old_data as $k => $v) {
                if ($v['cps_status'] == 0) {
                    $invite_code[substr($v['invite_code'], 2, 4)] = $v['invite_code'];
                } else {
                    $invite_code[substr($v['invite_code'], 2, 4)] = $v['invite_code'];
                    $user_id[$v['user_id']] = $k;
                }
            }
        }

        $repetition = '';//定义变量，存储会员已存在该渠道的会员ID
        $data = array();//定义空数组，存储重构后的数据
        foreach ($user as $ke => $val) {
            if (isset($user_id[$val])) {  
                $repetition .= $val.',';
            } else {
                $value = [
                    'user_id' => $val,
                    'user_name' => $name[$ke],
                    'employee_id' => $employee[$ke],
                    'channel_id' => $id,
                    'add_time' => $curr_time,
                    'province' => $county[$ke],
                    'city' => $city[$ke],
                    'hospital' => $hospital[$ke],
                    'department' => $department[$ke],
                ];
                $code = $this->create_code($invite_code);//生成邀请码
                if (!empty($code)) {
                    $value['invite_code'] = $channel_tags.$code;
                    $invite_code[$code] = $channel_tags.$code;
                } else {
                    $code = $this->create_code($invite_code);
                    $value['invite_code'] = $channel_tags.$code;
                    $invite_code[$code] = $channel_tags.$code;
                }
                $data[] = $value;
            }
        }

        $datas['repetition'] = rtrim($repetition, ',');
        $datas['data'] = $data;
        return $datas;
    }
    /**
     * 生成邀请码
     */
    public function create_code($invite_code = array(), $lenght = 4)
    {
        $str = range('A','Z');
        $code = '';
        $len = count($str);
        $len--;
        for($i=0; $i<$lenght; $i++) {
            $index = mt_rand(0, $len);
            $code .= $str[$index];
        }
        if (isset($invite_code[$code])) {
            $this->create_code($invite_code, $lenght);
        } else {
            return $code;
        }
    }

    public function getOrderPromotion($orderSn)
    {
        if (!$orderSn) {
            return false;
        } elseif (is_array($orderSn)) {
            $id_str = implode(',', $orderSn);
        } else {
            $id_str = $orderSn;
        }
        $base = BaseData::getInstance();
        $data['table'] = ' \Shop\Models\BaiyangOrderPromotionDetail';
        $data['column'] = 'order_sn,discount_money' ;
        $data['where'] = " where order_sn in ({$id_str}) AND discount_type = 4";
        $result = $base->getData($data);
        if ($result) {
            $result = array_column($result, 'discount_money', 'order_sn');
        }
        return $result;
    }

    public function get_order_goods_price($order_sn)
    {
        if (is_array($order_sn)) {
            $id_str = implode(',', $order_sn);
        } else {
            $id_str = $order_sn;
        }
        $base = BaseData::getInstance();
        $val = array();
        if($id_str){
            $data['table'] = '\Shop\Models\BaiyangOrder ';
            $data['column'] = 'order_sn,goods_price' ;
            $data['where'] = " where order_sn in ({$id_str})";
            $g = $base->getData($data);
            $data = array();
            $data['table'] = '\Shop\Models\BaiyangKjOrder ';
            $data['column'] = 'order_sn,goods_price' ;
            $data['where'] = " where order_sn in ({$id_str})";
            $k = $base->getData($data);
            if($g){
                $val = array_column($g, 'goods_price', 'order_sn');
            }
            if($k){
                $val += array_column($k, 'goods_price', 'order_sn');
            }
        }
        return $val;
    }
    public function get_order_detail_all ()
    {
        
        $base = BaseData::getInstance();
        
        
        $data['table'] = '\Shop\Models\BaiyangCpsOrderDetailLog as ord';
        $data['column'] = 'ord.order_sn,ord.qty,ord.channel_id,ord.invite_code,ord.price,ord.back_amount' ;
        
        $region = '\Shop\Models\BaiyangCpsChannel as ch';
        $data['join'] = " left join  ".$region."  on ord.channel_id = ch.channel_id ";
        $data['where'] = " where ch.channel_status <> 0 ";
        
        $old_data =  $base->getData($data);
        
        return   $old_data;
       
    }
    public function get_coupon_all($order_sn = [])
    {
        if (!$order_sn || !is_array($order_sn) || (count($order_sn) == 1 && strpos($order_sn[0], 'G')!== false)) {
            return array();
        }
        if (count($order_sn) > 1) {
            foreach ($order_sn as $key => $val) {
                $order_sn[$key] = "'".$val."'";
            }
        }
        
        $base = BaseData::getInstance();

        $data['table'] = '\Shop\Models\BaiyangOrderPromotionDetail as op';
        $data['column'] = "op.promotion_id as coupon_id,op.promotion_name as name,cp.id " ;
        
        $region = '\Shop\Models\BaiyangCoupon as cp';
        $data['join'] = " left join  ".$region." on  op.promotion_id = cp.coupon_sn ";
        $data['where'] = " where op.discount_type = 1 AND op.order_sn IN (".implode(',', $order_sn).") ";
        
        $result =  $base->getData($data);

        $coupon = array();
        if ($result) {
            foreach ($result as $k => $v) {
                if ($v['id']) {
                    $coupon[$v['id']] = $v['name'];
                }
                if ($v['coupon_id']) {
                    $coupon[$v['coupon_id']] = $v['name'];
                }
            }
        }
        return $coupon;
    }
}
?>
