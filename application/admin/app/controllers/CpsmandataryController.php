<?php
namespace Shop\Admin\Controllers;
//use Shop\Services\;
use Shop\Datas\BaseData;
class CpsMandataryController extends ControllerBase
{
    public function listAction()
    {
        $base = BaseData::getInstance();
        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }

        //查询地区
        $region_data['table'] = '\Shop\Models\BaiyangRegion';
        $region_data['where'] = "  WHERE level=2 ";
        $region_data['column'] = 'id,region_name' ;
        $region_result =  $base->getData($region_data);
        $this->view->setVar('region_list',$region_result);

        // print_r($param);exit;
        $param['page']  =   $this->request->get('page','trim',1);
        $param['url'] = $this->automaticGetUrl();
        $data['table'] = '\Shop\Models\BaiyangBusinessMandatary as bbm';
        $business =   '\Shop\Models\BaiyangBusiness AS bb';
        $region =   '\Shop\Models\BaiyangRegion AS br';
        $data['join'] = ' LEFT JOIN  '.$business.'  ON bbm.business_id = bb.business_id
                         LEFT JOIN  '.$region.' ON bb.region_id = br.id ' ;
        $sql = " where bb.is_del = 0 ";

        //中文标点
        $char = "。、！？：；﹑•＂…‘’“”〝〞∕¦‖—　〈〉﹞﹝「」‹›〖〗】【»«』『〕〔》《﹐¸﹕︰﹔！¡？¿﹖﹌﹏﹋＇´ˊˋ―﹫︳︴¯＿￣﹢﹦﹤‐­˜﹟﹩﹠﹪﹡﹨﹍﹉﹎﹊ˇ︵︶︷︸︹︿﹀︺︽︾ˉ﹁﹂﹃﹄︻︼（）";

        if(is_array($param) && !empty($param)){
            foreach ($param as $key => $value) {
                if($value !== ''){
                    switch ($key){
                        case "real_name":

                            $pattern = array(
                                "/[[:punct:]]/i", //英文标点符号
                                '/['.$char.']/u', //中文标点符号
                                '/[ ]{2,}/'
                            );
                            $value = preg_replace($pattern, ' ', $value);
                            $sql .= " and (bb.id_card like '%" . $value."%' OR  bb.real_name like '%".$value."%')" ;
                        case "start_time":
                            if(strtotime($value))
                                $sql .= " and bbm.update_time >= " . strtotime($value);
                            break;
                        case "end_time":
                            if(strtotime($value))
                                $sql .= " and bbm.update_time <= " . strtotime($value);
                            break;
                        case "mandatary_real_name":
                            $pattern = array(
                                "/[[:punct:]]/i", //英文标点符号
                                '/['.$char.']/u', //中文标点符号
                                '/[ ]{2,}/'
                            );
                            $value = preg_replace($pattern, ' ', $value);
                            $sql .= " and (bbm.id_card like '%" . $value."%' OR  bbm.real_name like '%".$value."%')" ;
                            break;
                        case "reply_status":
                            $sql .= " and bbm.status = " . $value;break;
                        case "region_id":
                            $sql .= " and bb.region_id = " . $value;break;
                    }
                }
            }
        }

        $data['where'] = $sql;
        $this->view->setVar('channel',$param);
        $counts = $base->countData($data);
        if(empty($counts)){
            //return array('res' => 'success','list' => 0);
        }

        $pages['page'] = (int)isset($param['page'])?$param['page']:1;//当前页
        $pages['counts'] = $counts;
        $pages['url'] = $param['url'];
        $page = $this->page->pageDetail($pages);

        $data['column'] = "bbm.mandatary_id, bb.real_name,br.region_name,bbm.phone,bbm.real_name AS bbm_name,bbm.id_card,
       bbm.id_card_image,bbm.status,FROM_UNIXTIME( bbm.update_time,'%Y /%m /%d %H:%i:%s ') as update_time" ;
        $data['order'] = 'ORDER BY bbm.update_time DESC ';
        $data['limit'] = "LIMIT ".$page['record'].','.$page['psize'];
        $result =  $base->getData($data);

        $list = [
            'res'  => 'success',
            'list' => $result,
            'page' => $page['page']
        ];
        $this->view->setVar('list',$list);
    }
    /**
     * 通过or不通过审核
     * @param string $ids 逗号隔开的id列表
     * @param string $type 类型 pass 通过 fail 不通过
     * @return bool
     */
    public  function examineAction(){
        $base = BaseData::getInstance();
        $reply_id_arr =  isset($_POST['reply_id'])?$_POST['reply_id']:"";
        $reply_status = isset($_POST['reply_status'])?$_POST['reply_status']:"";
        $whereStr = "";
        if($reply_id_arr){
            $reply_id = str_replace(':', ',', $reply_id_arr);
            $whereStr = " mandatary_id in ({$reply_id}) ";
        }else{
            $data['status']='kong';
            die(json_encode($data));
        }

        #剔除易操作的
        $get['table'] = '\Shop\Models\BaiyangBusinessMandatary';
        $get['column'] = 'mandatary_id,phone' ;
        $get['where'] = ' where 1=1 and'.$whereStr ;
        $res = $base->getdata($get);
        $a = '';
        $table = '\Shop\Models\BaiyangBusinessMandatary';

        if($res){
            $reply_id_arr = explode(',',str_replace(':', ',', $reply_id_arr));
            foreach($res as $key=>$val){
                $dtat =   " status = :status:";

                $res = $base->update($dtat,$table,['status'=>$reply_status],$whereStr);
                $a.= $val['phone'].",";
            }

        }
        if($res){
            if($a){
                $data['status']=$a;
            }else{
                $data['status']='ok';
            }

            die(json_encode($data));
        }else{
            $data['status']='on';
            die(json_encode($data));
        }
    }
}
?>
