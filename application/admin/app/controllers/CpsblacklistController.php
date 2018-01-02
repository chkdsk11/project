<?php
namespace Shop\Admin\Controllers;
//use Shop\Services\;
use Shop\Datas\BaseData; 
class CpsBlacklistController extends ControllerBase
{
    public function listAction()
    {
        $param = array(
            'phone'=>'',
            'start_time'=>'',
            'end_time'=>'',
            'csv'=>'',
        );
        $base = BaseData::getInstance();
        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }
        if($param['csv']==1){
              $this->csv_template();
              exit;
        }
        $this->view->setVar('channel',$param);
        $count_sql = ' WHERE 1=1 ';
        if ($param['phone']) {
                $count_sql .= ' AND phone like "%' . $param['phone'] . '%" ';
        }
        if ($param['start_time']) {
                $count_sql .= ' AND add_time > ' .strtotime($param['start_time']);
        }
        if ($param['end_time']) {
                $count_sql .= ' AND add_time < '.strtotime($param['end_time']);
        }
        $data['where'] = $count_sql;
        $data['table']='\Shop\Models\BaiyangCpsBigbrandBlacklist';
        $counts = $base->countData($data);
        
       $param['page']  =   $this->request->get('page','trim',1);
       $param['url'] = $this->automaticGetUrl();
       $pages['page'] = (int)isset($param['page'])?$param['page']:1;//当前页
       $pages['url'] = $param['url'];
       $pages['counts'] = $counts;
       $page = $this->page->pageDetail($pages);  
        if(empty($counts)){
            return array('res' => 'success','list' => 0);
        } 

        $data['column'] = "list_id,phone,  FROM_UNIXTIME(add_time,'%Y-%m-%d %H:%i:%s') as   add_time" ;
        $data['order'] = 'ORDER BY list_id DESC';
        $data['limit'] = "LIMIT ".$page['record'].','.$page['psize'];
        $result =  $base->getData($data);
        $list = [
            'res'  => 'success',
            'list' => $result,
            'page' => $page['page']
        ];
        $this->view->setVar('list',$list);
    }
    
    public function blacklistAction(){
        //$data ='';
        $base = BaseData::getInstance();
        if (isset($_FILES)) {
            $url = $_FILES['UpLoadFile']['tmp_name'];
            if(!$url){
                echo '请选择要导入的文件*.csv';exit;
               
            }
            $arr = explode(".", $_FILES['UpLoadFile']['name']);
            if ($arr[1] != "csv") {
                echo '请选择要导入的文件*.csv';exit;
                
            }
            $file = fopen("$url", 'r');
            while ($var = fgetcsv($file)) {
                $goods_list[] = $var;
            }
         
            $p['table']='\Shop\Models\BaiyangCpsBigbrandBlacklist';
            $p['column'] = " phone ";
            $data = [];
            $z = "手机号已存在:<br />";
            $y = "<br />非手机号:<br />";

            $time = time();$a  = $b = 1;
            //验证并处理导入信息
            for ($i = 1; $i < count($goods_list); $i++) {
                if($goods_list[$i][0] && preg_match("/^1[34578]{1}\d{9}$/",$goods_list[$i][0])){

                     $p['where'] = " where phone = {$goods_list[$i][0]} ";
                     $result =  $base->getData($p,true);
                     if(isset($result['phone'])&&$result['phone']!=""){
                        $z .="{$result['phone']},";
                         if($a==10){ $z .="<br />"; $a=0;}
                         $a = $a+1;
                     }else{
                          $table = '\Shop\Models\BaiyangCpsBigbrandBlacklist';
                          $base->insert($table,['phone'=>$goods_list[$i][0],'admin_id'=>$_SESSION['admin_id'],'add_time'=>$time],true);
                     }
                }else{
                    $y .="{$goods_list[$i][0]},";
                    if($b==10){ $y .="<br />"; $b=0;}
                    $b = $b+1;
                }
            }
            echo  $z."<br/>";  
            echo  $y."<br/>";
            echo "其余导入成功" ;
           
        }else{ 
            echo "请选择要导入的文件*.csv";exit;
        }
        
    }
    
    public function addAction(){
        $time = time();
        $base = BaseData::getInstance();
        $param['phone']="";
        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }
       
            
        if($param['phone']){
             
             $p['table']='\Shop\Models\BaiyangCpsBigbrandBlacklist';
             $p['where'] = " where phone = {$param['phone']} ";
             $p['column'] = " phone ";
             
             $result =  $base->getData($p,true);
             if(isset($result['phone'])&&$result['phone']!=""){
               $data['sts'] = "手机号已存在";
             }else{
                $table = '\Shop\Models\BaiyangCpsBigbrandBlacklist';
                $base->insert($table,['phone'=>$param['phone'],'admin_id'=>$_SESSION['admin_id'],'add_time'=>$time],true);
                
                $table = '\Shop\Models\BaiyangUser';
                $base->update("invite_code = :invite_code:",$table,['invite_code'=>'']," user_id =  {$param['phone']} ");
                $data['sts'] = "添加成功";
                $data['on'] = "ok";
                $data['on'] = "ok";
               
                
                
             } 
             
        }else{
             
            $data['sts'] = "手机号为空";
               
        }
        
        echo json_encode($data);exit;   
        
      
    }
    
     /**
     *清除黑名单
     */
    public function delAction()
    {
        $base = BaseData::getInstance();
        $param['id']="";
        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }
        $table = '\Shop\Models\BaiyangCpsBigbrandBlacklist';
        
        if($param['id']!=""){    
          $base->delete($table,'',"list_id = {$param['id']}");
          $data['sts'] = "删除成功";
          $data['on'] = "ok";
        }else{
           $data['sts'] = "删除失败"; 
        }
        echo json_encode($data);exit;
        
        
    }
     //生成模板
    public function csv_template(){
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=地推黑名单模板.csv");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        $str =  iconv('UTF-8', 'GBK','黑名单手机号' );
        $str.="\n";
        echo $str;
    }
}
?>
