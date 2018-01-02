<?php
  namespace Shop\Admin\Controllers;
//use Shop\Services\;
use Shop\Datas\BaseData;

class CpsDoctorController extends ControllerBase
{
    public function listAction()
    {
        $base = BaseData::getInstance();
        $param = array(
            'start_time'=>'',
            'end_time'=>'',
            'channel_id'=>'',
            'doctor_name'=>'',
            'user_name'=>'',
            'order_id'=>'',
        );
        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }
        
        if($param['start_time']&&$param['end_time']){
             $param['start_time'] = strtotime($param['start_time']);
             $param['end_time'] =   strtotime($param['end_time']);
        }

        $data['column'] = 'channel_id,channel_name,tags';
        $data['table'] = $data_cps_channel = '\Shop\Models\BaiyangCpsChannel';
        $data['where'] = " where channel_status = 1 ";
        $ch =  $base->getData($data); 
        $data = array();
        $this->view->setVar('channel_lset',$ch);
        $doctor_channel = array(33);
        $str_doctor_channel = implode(",",array_keys($doctor_channel));
        $count_sql = "WHERE bcol.order_sn<>'' AND bcol.order_status IN ('shipping','shipped','evaluating','finished','refund') AND (bog.status IS NULL OR bog.status<>'3')";
        if (isset($param['order_id']) && !empty($param['order_id'])) {
            $count_sql .= "AND bcol.order_sn ='".$param['order_id']."' ";
        }
        if (isset($param['channel_id']) && !empty($param['channel_id'])) {
            $count_sql .= "AND bcu.channel_id =".$param['channel_id']." ";
        }else{
            $count_sql .= "AND bcu.channel_id IN ({$str_doctor_channel}) ";
        }
        if (isset($param['user_name']) && !empty($param['user_name'])) {
            $count_sql .= "AND bu.username ='".$param['user_name']."' ";
        }
        if (isset($param['doctor_name']) && !empty($param['doctor_name'])) {
            $count_sql .= "AND bcu.user_id ='".$param['doctor_name']."' ";
        }
        if (!empty($param['start_time'])&&!empty($param['end_time'])) {
            $count_sql .= "AND bcol.order_time >=".$param['start_time']." AND bcol.order_time <= ".$param['end_time'];
        }
       
        $data['table'] = '\Shop\Models\BaiyangCpsUser as bcu';
        $data['where'] = $count_sql;
        $o =  '\Shop\Models\BaiyangCpsOrderDetailLog as bcodl';
        $c = '\Shop\Models\BaiyangCpsOrderLog as bcol';
        $b = '\Shop\Models\BaiyangOrder as bo';
        $d = '\Shop\Models\BaiyangKjOrderDetail as bkod';
        $g = '\Shop\Models\BaiyangGoods as bg';
        $u = '\Shop\Models\BaiyangUser as bu';
        $r = '\Shop\Models\BaiyangOrderGoodsReturnReason as bog';
        //baiyang_cps_back_activity
        $cps_back_activity = '\Shop\Models\BaiyangCpsBackActivity as c';
        $data['join'] = ' LEFT JOIN  '.$o.' on  (bcu.invite_code=bcodl.invite_code)
                          LEFT JOIN  '.$c.' on  (bcol.order_sn=bcodl.order_sn) 
                          LEFT JOIN  '.$b.' on  (bcol.order_sn=bo.order_sn)
                          LEFT JOIN  '.$d.' on  (bcodl.order_sn=bkod.order_sn AND bcodl.goods_id=bkod.goods_id) 
                          LEFT JOIN  '.$g.' on  (bcodl.goods_id=bg.id) 
                          LEFT JOIN  '.$u.' on  (bcol.user_id=bu.username)
                          LEFT JOIN  '.$r.' on  (bog.order_sn=bcol.order_sn) ';
        $counts = $base->countData($data); 
        $this->view->setVar('channel',$param);  
        if(empty($counts)){
            $list = [
            'res'  => 'success',
            'list' => 0,
           ];
            
            return $list;
        }
        $param['page']  =   $this->request->get('page','trim',1);
        $param['url'] = $this->automaticGetUrl();
        $pages['page'] = (int)isset($param['page'])?$param['page']:1;//当前页
        $pages['counts'] = $counts;
        $pages['url'] = $param['url'];
        $page = $this->page->pageDetail($pages); 
        $data['column'] = "bcol.order_sn,bu.username AS user_name ,bu.real_name,bcu.invite_code as cps_id,bcu.user_id AS doctor_name,
                           bcu.user_name AS doctor_real_name,
                           FROM_UNIXTIME(bcol.order_time,'%Y %D %M %h:%i:%s %x')
                           bcol.order_time,bg.goods_name,bcodl.price*bcodl.qty AS c_price,
                           bkod.price*bkod.tax_rate*0.01  AS tax,bcodl.qty AS qty,bo.user_coupon_price+bo.youhui_price AS youhui_price,bo.carriage,bo.total";
        $data['order'] = 'ORDER BY channel_id DESC';
        $data['limit'] = "LIMIT ".$page['record'].','.$page['psize'];
       
        $result =  $base->getData($data);
        $list = [
            'res'  => 'success',
            'list' => $result,
            'page' => $page['page']
       ];
       $this->view->setVar('list',$list);
    }
    public function csvAction(){
        $base = BaseData::getInstance();
        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }
        $doctor_channel = array(33);
        $str_doctor_channel = implode(",",array_keys($doctor_channel));
        $count_sql = "WHERE bcol.order_sn<>'' AND bcol.order_status IN ('shipping','shipped','evaluating','finished','refund') AND (bog.status IS NULL OR bog.status<>'3')";
        if (isset($param['order_id']) && !empty($param['order_id'])) {
            $count_sql .= "AND bcol.order_sn ='".$param['order_id']."' ";
        }
        if (isset($param['channel_id']) && !empty($param['channel_id'])) {
            $count_sql .= "AND bcu.channel_id =".$param['channel_id']." ";
        }else{
            $count_sql .= "AND bcu.channel_id IN ({$str_doctor_channel}) ";
        }
        if (isset($param['user_name']) && !empty($param['user_name'])) {
            $count_sql .= "AND bu.username ='".$param['user_name']."' ";
        }
        if (isset($param['doctor_name']) && !empty($param['doctor_name'])) {
            $count_sql .= "AND bcu.user_id ='".$param['doctor_name']."' ";
        }
        if (!empty($param['start_time'])&&!empty($param['end_time'])) {
            $count_sql .= "AND bcol.order_time >=".$param['start_time']." AND bcol.order_time <= ".$param['end_time'];
        }
        $data['table'] = '\Shop\Models\BaiyangCpsUser as bcu';
        $data['where'] = $count_sql;
        $o =  '\Shop\Models\BaiyangCpsOrderDetailLog as bcodl';
        $c = '\Shop\Models\BaiyangCpsOrderLog as bcol';
        $b = '\Shop\Models\BaiyangOrder as bo';
        $d = '\Shop\Models\BaiyangKjOrderDetail as bkod';
        $g = '\Shop\Models\BaiyangGoods as bg';
        $u = '\Shop\Models\BaiyangUser as bu';
        $r = '\Shop\Models\BaiyangOrderGoodsReturnReason as bog';
        //baiyang_cps_back_activity
        $cps_back_activity = '\Shop\Models\BaiyangCpsBackActivity as c';
        $data['join'] = ' LEFT JOIN  '.$o.' on  (bcu.invite_code=bcodl.invite_code)
                          LEFT JOIN  '.$c.' on  (bcol.order_sn=bcodl.order_sn) 
                          LEFT JOIN  '.$b.' on  (bcol.order_sn=bo.order_sn)
                          LEFT JOIN  '.$d.' on  (bcodl.order_sn=bkod.order_sn AND bcodl.goods_id=bkod.goods_id) 
                          LEFT JOIN  '.$g.' on  (bcodl.goods_id=bg.id) 
                          LEFT JOIN  '.$u.' on  (bcol.user_id=bu.username)
                          LEFT JOIN  '.$r.' on  (bog.order_sn=bcol.order_sn) ';
        $data['column'] = "bcol.order_sn,bu.username AS user_name ,bu.real_name,bcu.invite_code as cps_id,bcu.user_id AS doctor_name,
                           bcu.user_name AS doctor_real_name,
                           FROM_UNIXTIME(bcol.order_time,'%Y %D %M %h:%i:%s %x')
                           bcol.order_time,bg.goods_name,bcodl.price*bcodl.qty AS c_price,
                           bkod.price*bkod.tax_rate*0.01  AS tax,bcodl.qty AS qty,bo.user_coupon_price+bo.youhui_price AS youhui_price,bo.carriage,bo.total";
        $data['order'] = 'ORDER BY channel_id DESC';
        
        $result =  $base->getData($data);
        $str =  iconv('UTF-8', 'GB2312','订单编号' ).",".iconv('UTF-8', 'GB2312','用户名' ).",".iconv('UTF-8', 'GB2312','姓名' ).",".iconv('UTF-8', 'GB2312','CPSID' ).",".iconv('UTF-8', 'GB2312','医生用户名' ).",".iconv('UTF-8', 'GB2312','医生姓名' ) .",".iconv('UTF-8', 'GB2312','下单时间' ).",".iconv('UTF-8', 'GB2312','商品名称' ).",".iconv('UTF-8', 'GB2312','商品金额' ).",".iconv('UTF-8', 'GB2312','税费' ).",".iconv('UTF-8', 'GB2312','折扣' ).",".iconv('UTF-8', 'GB2312','数量' ).",".iconv('UTF-8', 'GB2312','实付总金额' );
        $str.="\n"; 
        foreach($result as $key=>&$item){
            $item['tax'] = $item['tax']?$item['tax']:0.00;
            $item['youhui_price'] = $item['youhui_price']?$item['youhui_price']:0.00;
            $item['carriage'] = $item['carriage']?$item['carriage']:0.00;
            $item['discount'] = 0.00;
            if($item['youhui_price']>0){
               $item['discount'] = bcmul($item['youhui_price'],bcdiv($item['c_price'],($item['total']-$item['carriage']+$item['youhui_price']),10),10);
               $item['discount'] = round($item['discount'],2);
            }
            $item['total_price'] = bcsub(bcadd($item['c_price'],$item['tax'],2),$item['discount'],2);
            $item['tax'] = round($item['tax'],2);
            
            $item['order_sn'] .= "\t";
            $str.="{$item['order_sn']},{$item['user_name']},".mb_convert_encoding($item['real_name'], "GBK").",{$item['cps_id']},{$item['doctor_name']},".mb_convert_encoding($item['doctor_real_name'], "GBK").",{$item['order_time']},".mb_convert_encoding($item['goods_name'], "GBK").",{$item['c_price']},{$item['tax']},{$item['discount']},{$item['qty']},{$item['total_price']}\n";
        }
        $date = date('Y-m-d H:i:s');
        // $str = "订单编号,身份证,用户名,下单时间,商品名称,商品金额,数量,实付总金额";
        $filename = date('Ymd').'.csv'; //设置文件名 
       
        header("Content-type:text/csv");  
        header("Content-Disposition:attachment;filename=".$filename);  
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');  
        header('Expires:0');  
        header('Pragma:public');   
        
        echo $str;exit;
    }
}
?>
