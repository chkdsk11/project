<?php
namespace Shop\Admin\Controllers;
//use Shop\Services\;
use Shop\Datas\BaseData;

class CpsQrcodeController extends ControllerBase
{
    public function listAction(){
        $base = BaseData::getInstance();
        $param = array(
            'channel_id'=>'',
            'province'=>'',
            'act_id'=>'',
            'city'=>'',
            'name'=>'',
            'user_name'=>'',
            'status'=>'',
            'username'=>'',
            'size'=>'',
            'img'=>'',
            'stu'=>'',
            'show_channel_qrcode'=>''

        );
        $usels =  $this->config->domain['static'];

        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }
        if($param['stu']==2&&$param['img']!=''&&$param['name']!=''){

            $this->download_qrcode_img($param['img'],$param['name']);
        }
        $channel = array();
        $data['column'] = 'channel_id,channel_name,tags';
        $data['table'] = $data_cps_channel = '\Shop\Models\BaiyangCpsChannel';
        $data['where'] = " where channel_status = 1 ";
        $channels =  $base->getData($data);
        if ($channels) {
            foreach ($channels as $key => $val) {
                $channel['channel_name'][$val['channel_id']] = $val['channel_name'];
                $channel['channel_tags'][$val['channel_id']] = $val['tags'];
            }
        } else {
            $channel = array(
                'channel_name' => array(),
                'channel_tags' => array(),
            );
        }
        $data = array();
        $this->view->setVar('address',$this->get_address());
        if($param['province']){
            $this->view->setVar('city',$this->get_address($param['province']));
        }
        if($param['channel_id']){
            $this->view->setVar('act',$this->get_activity($param['channel_id']));
        }

        if($param['channel_id']&&isset($param['stu'])&&$param['stu']==1){
            $array =  $this->get_activity($param['channel_id']);
            $address_str = '<option value="0">全部</option>';
            foreach($array as $row){
                $address_str .= '<option value="'.$row['act_id'].'">'.$row['act_name'].'</option>';
            }
            echo $address_str; exit;
        }


        if($param['province']&&isset($param['stu'])&&$param['stu']==1){
            $array =  $this->get_address($param['province']);
            $address_str = '<option value="0">全部</option>';
            foreach($array as $row){
                $address_str .= '<option value="'.$row['id'].'">'.$row['region_name'].'</option>';
            }
            echo $address_str; exit;
        }

        $this->view->setVar('channel_lset',$channels);

        $this->view->setVar('channel_lset',$channels);
        $this->view->setVar('channel',$param);
        $param['page']  =   $this->request->get('page','trim',1);
        $param['url'] = $this->automaticGetUrl();
        $count_sql = "WHERE a.cps_status<>0 ";
        if($param['province'])
        {
            $count_sql .= " AND a.province = ".$param['province'].' ';
        }
        if($param['city'])
        {
            if($param['city']){ $count_sql .= " AND a.city = ".$param['city'].' '; }
        }

        if($param['channel_id'])
        {
            $count_sql .= " AND a.channel_id = ".$param['channel_id'].' ';
        }
        if( $param['name'])
        {
            $count_sql .= " AND a.user_name like '%".$param['name'].'%\' ';
        }if( $param['username'])
        {
            $count_sql .= " AND a.user_id like '%".$param['username'].'%\' ';
        }

        if($param['show_channel_qrcode']==1){
            $data['table'] = '\Shop\Models\BaiyangCpsUser as a';
            $cps_channel = '\Shop\Models\BaiyangCpsChannel as b';

            $data['join'] = ' LEFT JOIN  '.$cps_channel.'  on a.channel_id=b.channel_id ';
            $data['where'] = $count_sql;
            $counts = $base->countData($data);

            if(empty($counts)){
                return array('res' => 'success','list' => 0);
            }
            $pages['page'] = (int)isset($param['page'])?$param['page']:1;//当前页
            $pages['counts'] = $counts;
            $pages['url'] = $param['url'];
            $page = $this->page->pageDetail($pages);

            if(isset($param['size'])&&($param['size']==100||$param['size']==200)){
                $page['record'] = 0;
                $page['psize'] = 2000;
                if($counts>2000){
                    die('查询结果过大，请详细筛选！');
                }
            }

            $data['column'] = "a.cps_id, a.user_id, a.user_name, a.employee_id, a.channel_id, a.invite_code, a.cps_status,a.province,a.city,b.channel_name,b.link as channel_share_link";
            $data['order'] = 'order by  a.cps_id desc';
            if(isset($param['size'])&&($param['size']==100||$param['size']==200)){
                $data['limit'] = "";
            }else{
                $data['limit'] = "LIMIT ".$page['record'].','.$page['psize'];
            }

            $result =  $base->getData($data);

        }else{
            if( $param['status'])
            {
                $time = time();
                if($param['status'] ==  1)
                {
                    $count_sql .= " AND c.start_time < {$time} AND end_time > {$time} AND c.is_cancel = 0 ";
                }
                if($param['status'] ==  2)
                {
                    $count_sql .= " AND c.start_time > {$time} AND c.is_cancel = 0 ";
                }
                if($param['status'] ==  3)
                {
                    $count_sql .= " AND c.end_time < {$time} AND c.is_cancel = 0 ";
                }
                if($param['status'] ==  4)
                {
                    $count_sql .= " AND c.is_cancel = 1 ";
                }
            }
            if(isset($param['act_id']) && $param['act_id'])
            {
                $count_sql .= " AND c.act_id = ".$param['act_id'].' ';
            }
            $data['table'] = '\Shop\Models\BaiyangCpsUser as a';
            $cps_channel = '\Shop\Models\BaiyangCpsChannel as b';
            //baiyang_cps_back_activity
            $cps_back_activity = '\Shop\Models\BaiyangCpsBackActivity as c';
            $data['join'] = ' LEFT JOIN  '.$cps_channel.'  on a.channel_id=b.channel_id
                          LEFT JOIN  '.$cps_back_activity.' on c.channel_id=b.channel_id ';
            $data['where'] = $count_sql;
            $counts = $base->countData($data);

            if(empty($counts)){
                return array('res' => 'success','list' => 0);
            }
            $pages['page'] = (int)isset($param['page'])?$param['page']:1;//当前页
            $pages['counts'] = $counts;
            $pages['url'] = $param['url'];
            $page = $this->page->pageDetail($pages);

            if(isset($param['size'])&&($param['size']==100||$param['size']==200)){
                $page['record'] = 0;
                $page['psize'] = 2000;
                if($counts>2000){
                    die('查询结果过大，请详细筛选！');
                }
            }

            $data['column'] = "a.cps_id, a.user_id, a.user_name, a.employee_id, a.channel_id, a.invite_code, a.cps_status,a.province,a.city,b.channel_name,b.link as channel_share_link,c.act_name,c.act_id,c.act_share_link";
            $data['order'] = 'order by  a.cps_id desc';
            if(isset($param['size'])&&($param['size']==100||$param['size']==200)){
                $data['limit'] = "";
            }else{
                $data['limit'] = "LIMIT ".$page['record'].','.$page['psize'];
            }

            $result =  $base->getData($data);
        }
        $param['usrls'] = $usels;
        $this->view->setVar('channel',$param);

        ini_set('max_execution_time', '0');
        $result  = $this->get_qrcode_info($result, $channel['channel_name'],$param['show_channel_qrcode'] );//数据重构

        if(isset($param['size'])&&($param['size']==100||$param['size']==200)){
//            $base_url = APP_PATH."/static/qrcode/";
            $base_url = "/tmp/qrcode/";

            if(!is_dir($base_url.'/download_qrcode_zip')){
                mkdir($base_url.'/download_qrcode_zip');
            }
            if($param['name']){
                $zip_name = $base_url.'/download_qrcode_zip/'.date ( 'YmdH' ).'_' .$param['name'].'_'.$param['channel_id'].'_'.$param['act_id'].'_'.$param['size'].'.zip';
            }else if($param['user_name']){
                $zip_name = $base_url.'/download_qrcode_zip/'.date ( 'YmdH' ).'_' .$param['user_name'].'_'.$param['channel_id'].'_'.$param['act_id'].'_'.$param['size'].'.zip';
            }else{
                $zip_name = $base_url.'/download_qrcode_zip/'.date ( 'YmdH' ).'_' .$param['channel_id'].'_'.$param['act_id'].'_'.$param['size'].'.zip';
            }
            $needdown = array();
            foreach($result as $k=>$val ){
                if($param['size']==100){
                    if (!isset($needdown[$val['chinnal_qrcode_100_1']]) && get_headers($val['chinnal_qrcode_100_1'])[0] != 'HTTP/1.1 404 Not Found') {
                        $needdown[$val['chinnal_qrcode_100_1']]['url'] = $val['chinnal_qrcode_100_1'];
                        $needdown[$val['chinnal_qrcode_100_1']]['name'] = $val['chinnal_qrcode_100_name'];
                    }
                    if (isset($val['qrcode_100_1']) && get_headers($val['qrcode_100_1'])[0] != 'HTTP/1.1 404 Not Found') {
                        $needdown[$val['qrcode_100_1']]['url'] = $val['qrcode_100_1'];
                        $needdown[$val['qrcode_100_1']]['name'] = $val['qrcode_100_name'];
                    }
                }else if($param['size']==200){
                    if (!isset($needdown[$val['chinnal_qrcode_200_1']]) && get_headers($val['chinnal_qrcode_200_1'])[0] != 'HTTP/1.1 404 Not Found') {
                        $needdown[$val['chinnal_qrcode_200_1']]['url'] = $val['chinnal_qrcode_200_1'];
                        $needdown[$val['chinnal_qrcode_200_1']]['name'] = $val['chinnal_qrcode_200_name'];
                    }
                    if (isset($val['qrcode_200_1']) && get_headers($val['qrcode_200_1'])[0] != 'HTTP/1.1 404 Not Found') {
                        $needdown[$val['qrcode_200_1']]['url'] = $val['qrcode_200_1'];
                        $needdown[$val['qrcode_200_1']]['name'] = $val['qrcode_200_name'];
                    }
                }
            }

            $this->download_zip($needdown,$zip_name);
        }

        $list = [
            'res'  => 'success',
            'list' => $result,
            'page' => $page['page']
        ];
        $this->view->setVar('list',$list);
    }

    public function get_qrcode_info($result,$channel,$show_channel_qrcode=0,$is_download=0){
        $province = array();
        $city = array();
        $user_array = array();

        require APP_PATH."/common/libs/Phpqrcode.php";
        $qr =  new \QRcode();

        //如果参数不足 直接返回空
        if(empty($result))return array();
        //重构推广渠道
        foreach($result as $key=>$row) {
            //省市
            $province[] = $row['province'];
            $city[] = $row['city'];
            $arcode = '';
            $channel_arcode = '';
            if(isset($row['act_share_link']) && $row['act_share_link'])//拼接活动分享链接
            {
                $result[$key]['qrcode_url'] = $arcode = $row['act_share_link'].'?invite_code='.$row['invite_code'].'&act_id='.$row['act_id'];
            }
            if(isset($row['channel_share_link']) && $row['channel_share_link'])//拼接渠道分享链接
            {
//                $channel_arcode = $this->config->share_url[$this->config->environment].'?invite_code='.$row['invite_code'];
                $channel_arcode = $row['channel_share_link'].'?invite_code='.$row['invite_code'];
            }

            if ($show_channel_qrcode == 1) {
                $result[$key]['show_channel_qrcode'] = 1 ;
            } else {
                if(!in_array($row['user_id'],$user_array)){
                    $result[$key]['show_channel_qrcode'] = 1 ;
                    $user_array[] = $row['user_id'] ;
                }else{
                    $result[$key]['show_channel_qrcode'] = 2;
                }
            }

            $base_url =  APP_PATH."/static/qrcode/";
            //创建二维码目录
            $dir = $base_url.$row['cps_id'].'/';
            if(!is_dir($base_url)){
                mkdir($base_url);
            }if(!is_dir($dir)){
                mkdir($dir);
            }
            if($show_channel_qrcode!=1){
                $QR1 = $dir.str_replace(array('%',' '),'',$row['user_id']."_".$row['user_name'].'_'.trim($row['act_name'])).'_100.png'; //生成后的活动二维码地址
                $QR2 = $dir.str_replace(array('%',' '),'',$row['user_id']."_".$row['user_name'].'_'.trim($row['act_name'])).'_200.png'; //生成后的活动二维码地址
            }

            $QR3 = $dir.$row['user_id'].'_'.$row['user_name'].'_渠道_100.png'; //渠道二维码地址
            $QR4 = $dir.$row['user_id'].'_'.$row['user_name'].'_渠道_200.png'; //渠道二维码地址

            $errorCorrectionLevel = 'L';//容错级别
            if($show_channel_qrcode!=1){
                if(!file_exists($QR1) && $arcode) {
                    $matrixPointSize = 3;//生成图片大小
                    $qr->png($arcode, $QR1, $errorCorrectionLevel, $matrixPointSize, 2);
                    //替换成绝对路径
                    $tmpUrl = $this->FastDfs->uploadByFilename($QR1,2,'G1');
                    $qr_1 = $this->config['domain']['img'].$tmpUrl; //生成后的活动二维码地址
                    $result[$key]['qrcode_100'] = $qr_1;
                    $result[$key]['qrcode_100_1'] = $this->config['domain']['img'].$tmpUrl;
                    $result[$key]['qrcode_100_name'] = basename($QR1);
                    @unlink($QR1);
                }
                if(!file_exists($QR2) && $arcode) {
                    $matrixPointSize = 6;//生成图片大小
                    $qr->png($arcode, $QR2, $errorCorrectionLevel, $matrixPointSize, 2);
                    //替换成绝对路径
                    $tmpUrl = $this->FastDfs->uploadByFilename($QR2,2,'G1');
                    $qr_2 = $this->config['domain']['img'].$tmpUrl; //生成后的活动二维码地址
                    $result[$key]['qrcode_200'] = $qr_2;
                    $result[$key]['qrcode_200_1'] = $this->config['domain']['img'].$tmpUrl;
                    $result[$key]['qrcode_200_name'] = basename($QR2);
                    @unlink($QR2);
                }
            }

            if(!file_exists($QR3) && $channel_arcode) {
                $matrixPointSize = 3;//生成图片大小
                $qr->png($channel_arcode, $QR3, $errorCorrectionLevel, $matrixPointSize, 2);
                //替换成绝对路径
                $tmpUrl = $this->FastDfs->uploadByFilename($QR3,2,'G1');
                $qr_3 = $this->config['domain']['img'].$tmpUrl; //生成后的活动二维码地址
                $result[$key]['chinnal_qrcode_100'] = $qr_3;
                $result[$key]['chinnal_qrcode_100_1'] = $this->config['domain']['img'].$tmpUrl;
                $result[$key]['chinnal_qrcode_100_name'] = basename($QR3);
                @unlink($QR3);
            }
            if(!file_exists($QR4) && $channel_arcode) {
                $matrixPointSize = 6;//生成图片大小
                $qr->png($channel_arcode, $QR4, $errorCorrectionLevel, $matrixPointSize, 2);
                //替换成绝对路径
                $tmpUrl = $this->FastDfs->uploadByFilename($QR4,2,'G1');
                $qr_4 = $this->config['domain']['img'].$tmpUrl; //生成后的活动二维码地址
                $result[$key]['chinnal_qrcode_200'] = $qr_4;
                $result[$key]['chinnal_qrcode_200_1'] = $this->config['domain']['img'].$tmpUrl;
                $result[$key]['chinnal_qrcode_200_name'] = basename($QR4);
                @unlink($QR4);
            }
        }
        //获取城市
        $citys = $province;
        foreach($city as $line){
            $citys[] = $line;
        }
        $citys = array_unique($citys);
        foreach($citys as $k=>$val){
            if(!$val){unset($citys[$k]);}
        }
        $citys = implode($citys,',');
        $address = array();
        if($citys){
            $address = $this->get_address_info($citys);
        }

        //重构地区信息
        foreach($result as $key=>$row){
            $result[$key]['province']= '';
            $result[$key]['city']= '';
            foreach($address as $k=>$line){

                if($row['province']==$line['id']){ $result[$key]['province']=$line['region_name']; }
                if($row['city']==$line['id']){ $result[$key]['city']=$line['region_name']; }
            }
        }
        if($show_channel_qrcode != 0){
            foreach($result as $key=>$v){
                if($v['show_channel_qrcode']==1){
                    $restl[] = $v;
                }
            }

        }else{
            $restl =  $result;
        }

        return $restl;
    }
    public function get_address_info($address)
    {
        $base = BaseData::getInstance();
        $data['column'] = 'id,pid,region_name,level' ;
        $data['table']='\Shop\Models\BaiyangRegion';
        $data['where'] = " where id in({$address}) ";

        return  $result =  $base->getData($data);
    }
    public function get_activity($type)
    {

        $base = BaseData::getInstance();
        $data['column'] = 'act_id,act_name' ;
        $data['table']='\Shop\Models\BaiyangCpsBackActivity';
        $data['where'] = " where is_cancel=0 AND channel_id={$type}";

        return  $result =  $base->getData($data);
    }
    public function get_address($provice_id = 0)     // 不传为搜索省份信息
    {
        $base = BaseData::getInstance();
        $data['column'] = 'id,pid,region_name,level' ;
        $data['table']='\Shop\Models\BaiyangRegion';
        if($provice_id){
            $data['where'] = " where pid = {$provice_id}";
        }else{
            $data['where'] = " where level = 1";
        }
        return  $result =  $base->getData($data);

    }

    /**单个渠道信息 @return array */
    public function get_channel_info($channe_id)
    {
        $base = BaseData::getInstance();
        $data['column'] = 'channel_id,channel_name,tags';
        $data['table'] = $data_cps_channel = '\Shop\Models\BaiyangCpsChannel';
        $data['where'] = " where channel_id = {$channe_id} ";
        return $ch =  $base->getData($data,true);

    }
    public function get_act_info($cat_id)
    {
        $base = BaseData::getInstance();
        $data['column'] = 'act_id,act_name';
        $data['table'] = $data_cps_channel = '\Shop\Models\BaiyangCpsBackActivity';
        $data['where'] = " where is_cancel=0 AND act_id={$cat_id} ";
        return $ch =  $base->getData($data,true);
    }
    function download_zip($needDown=array(), $filename=''){
        if(!$filename){
//            $base_url = APP_PATH."/static/qrcode/";
            $base_url = "/tmp/qrcode/";
            $filename = $base_url."/download_qrcode_zip/" . date ( 'YmdHis' ) . ".zip"; // 最终生成的文件名（含路径）
        }
        if(file_exists($filename)){
            unlink($filename);
        }
        // 生成文件
        $zip = new \ZipArchive (); // 使用本类，linux需开启zlib，windows需取消php_zip.dll前的注释
        if ($zip->open ( $filename, $zip::CREATE ) !== TRUE) {
            exit ( '无法打开文件，或者文件创建失败' );
        }
        //$fileNameArr 就是一个存储文件路径的数组 比如 array('/a/1.jpg,/a/2.jpg....');
        foreach ( $needDown as $val ) {
//            $zip->addFile ( $val, iconv('UTF-8', 'GBK//IGNORE', basename ( $val ) ) ); // 第二个参数是放在压缩包中的文件名称，如果文件可能会有重复，就需要注意一下
            $contents = file_get_contents( $val['url'] );
            if ( $contents !== false ) {
                $zip->addFromString( iconv('UTF-8', 'GBK//IGNORE', basename ( $val['name'] ) ), $contents );
            }
        }
        $zip->close (); // 关闭
        //下面是输出下载;
        header ( "Cache-Control: max-age=0" );
        header ( "Content-Description: File Transfer" );
        header ( 'Content-disposition: attachment; filename=' . basename ( $filename ) ); // 文件名
        header ( "Content-Type: application/zip" ); // zip格式的
        header ( "Content-Transfer-Encoding: binary" ); // 告诉浏览器，这是二进制文件
        header ( 'Content-Length: ' . filesize ( $filename ) ); // 告诉浏览器，文件大小
        @readfile ( $filename );//输出文件;
        exit();
    }
    //二维码下载
    public function download_qrcode_img($img,$name){
        //接收并检测文件
        $file_headers = @get_headers($img);
        if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
            echo '文件不存在';die;
        }else {
            //输出文件
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: ".$file_headers[4]);
            Header("Content-Disposition: attachment; filename=" . basename($name));
            //清楚多余输出
            ob_start();
            ob_end_flush();
            ob_end_clean();

            readfile($img);exit;
        }
//        if(!file_exists($img)){ echo '文件不存在';die; }
//        //输出文件
//        Header("Content-type: application/octet-stream");
//        Header("Accept-Ranges: bytes");
//        Header("Accept-Length: ".filesize($img));
//        Header("Content-Disposition: attachment; filename=" . basename($img));
//
//        //清楚多余输出
//        ob_start();
//        ob_end_flush();
//        ob_end_clean();
//
//        readfile($img);exit;
    }
}
?>
