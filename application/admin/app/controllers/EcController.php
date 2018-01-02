<?php
/**
 * Created by PhpStorm.
 * User: lw
 * Date: 2016/8/16
 * Time: 15:50
 */

namespace Shop\Admin\Controllers;
use Phalcon\Mvc\Controller;
use Shop\Services\CategoryProductRuleService;
use Shop\Services\CategoryService;
use Shop\Services\SpuService;
use Shop\Services\SkuService;
use Shop\Services\BaseService;
use Shop\Models\CacheGoodsKey;
use Shop\Datas\BaseData;


class EcController extends Controller
{
    public function initialize()
    {
        if(!$this->config->ec){
            die('不允许导入数据！');
        }
    }
    //导入分类
    public function ecCategoryAction()
    {
        $base = BaseData::getInstance();
        $fileUrl = '/data/aa/category.xlsx';
        $aa = $this->excel->importExcel($fileUrl,'xlsx');
        $table = '\Shop\Models\BaiyangCategory';
        foreach($aa as $v){
            $data = array(
                'category_name'=>$v[0],
                'alias'=>'',
            );
            if($v[2]=='顶级'){
                $data['level'] = 1;
                $data['pid'] = 0;
                $data['has_child'] = 1;
            }else{
                $res = $base->select('id,level',$table,['name'=>trim($v[2],' ')],'category_name=:name: order by id desc');
                if(!$res){
                    echo '错误：父分类没有找到----分类名：'.$v[0].'父分类：'.$v[2].'<br>';
                    continue;
                }
                $data['level'] = $res[0]['level']+1;
                $data['pid'] = $res[0]['id'];
                $data['has_child'] = ($data['level']==3)?0:1;
            }
            $res = $base->insert($table,$data);
            if(!$res){
                echo '错误：分类添加失败------分类名：'.$v[0].'父分类：'.$v[2].'<br>';
            }
        }
        $this->updateCategoryPathAction();
        die('导入分类成功！');
    }

    //修改分类路径
    public function updateCategoryPathAction()
    {
        $base = BaseData::getInstance();
        $table = '\Shop\Models\BaiyangCategory';
        $tmp = $base->select('id,pid',$table);
        foreach($tmp as $v){
            if($v['pid']==0){
                $path = $v['id'];
            }else{
                $aa = $base->select('category_path',$table,['pid'=>$v['pid']],'id=:pid:');
                if(!$aa){
                    echo '错误：父分类没有找到----分类id：'.$v['id'].'父分类id：'.$v['pid'].'<br>';
                    continue;
                }
                $path = $aa[0]['category_path'].'/'.$v['id'];
            }
            $res = $base->update('category_path=:category_path:',$table,['id'=>$v['id'],'category_path'=>$path],'id=:id:');
            if(!$res){
                echo '错误：修改失败----分类id：'.$v['id'].'父分类id：'.$v['pid'].'<br>';
            }
        }
        echo '修改分类路径成功！<br>';
    }

    //导入spu数据
    public function ecSpuAction()
    {
        $base = BaseData::getInstance();
        $fileUrl = '/data/aa/spu.xlsx';
        $aa = $this->excel->importExcel($fileUrl,'xlsx');
        $table = '\Shop\Models\BaiyangSpu';
        foreach($aa as $v){
            $code = trim($v[0],' ');
            if(empty($code)){continue;}
            $data = array(
                'spu_name'=>trim($v[1],' '),
                'add_time'=>time(),
                'update_time'=>time(),
                'code'=>$code,
            );
            $drug_type = 4;
            switch(trim($v[2],' ')){
                case '处方药':$drug_type=1;break;
                case '红色非处方药':$drug_type=2;break;
                case '绿色非处方药':$drug_type=3;break;
            }
            $data['drug_type'] = $drug_type;
            $cate = $this->getCategory(trim($v[6],' '),trim($v[5],' '));
            if(!$cate){
                continue;
            }
            $data['category_path'] = $cate['category_path'];
            $data['category_id'] = $cate['id'];
            $res = $base->insert($table,$data);
            if(!$res){
                echo '错误：spu添加失败------编码：'.$v[0].'--分类：'.$v[6].'<br>';
            }
        }
        die('导入spu数据成功！');
    }

    //判断分类信息
    public function getCategory($cate,$pcate)
    {
        $base = BaseData::getInstance();
        $cates = $base->select('id,pid,category_path','\Shop\Models\BaiyangCategory',['name'=>$cate],'category_name=:name: and level=3');
        if(!$cates){
            echo '错误：父分类没有找到----分类名：'.$cate.'---<br>';
        }
        if(count($cates)==1){
            return $cates[0];
        }else{
            foreach($cates as $v){
                $tmp = $base->count('\Shop\Models\BaiyangCategory',['name'=>$pcate,'id'=>$v['pid']],'category_name=:name: and id=:id: and level=2');
                if($tmp){
                    return $v;
                }
            }
            echo '错误：未找到符合要求分类信息-------分类信息'.$cate.'----'.$pcate.'<br>';
            return false;
        }
    }

    //导入品规信息
    public function ecRulesAction()
    {
        $base = BaseData::getInstance();
        $fileUrl = '/data/aa/rules.xlsx';
        $aa = $this->excel->importExcel($fileUrl,'xlsx');
        $categoryRule = CategoryProductRuleService::getInstance();
        foreach($aa as $v){
            $cate = $this->getCategory(trim($v[2],' '),trim($v[1],' '));
            if(!$cate){
//                echo '错误：未找到该分类：-----分类名：'.trim($v[2],' ').'<br>';
                continue;
            }
            if(!empty(trim($v[3],' '))){
                $res = $categoryRule->addCategoryProductRule(array(
                    'category_id'=>$cate['id'],
                    'order'=>1,
                    'name'=>trim($v[3],' '),
                ));
                if(!$res){
                    echo '错误：第一个品规添加失败！分类名：'.$v[3].'<br>';
                }
            }
            if(!empty(trim($v[4],' '))){
                $res = $categoryRule->addCategoryProductRule(array(
                    'category_id'=>$cate['id'],
                    'order'=>2,
                    'name'=>trim($v[4],' '),
                ));
                if(!$res){
                    echo '错误：第一个品规添加失败！分类名：'.$v[4].'<br>';
                }
            }
            if(!empty(trim($v[5],' '))){
                $res = $categoryRule->addCategoryProductRule(array(
                    'category_id'=>$cate['id'],
                    'order'=>3,
                    'name'=>trim($v[5],' '),
                ));
                if(!$res){
                    echo '错误：第一个品规添加失败！分类名：'.$v[5].'<br>';
                }
            }
        }
        die('导入分类属性成功！');
    }

    //导入sku-rules信息
    public function ecSkuRulesAction()
    {
        ini_set('memory_limit','1027M');
        ini_set('max_execution_time', '60*60*60');
        set_time_limit(0);
        $base = BaseData::getInstance();
        $fileUrl = '/data/aa/sku_rule.xlsx';
        $aa = $this->excel->importExcel($fileUrl,'xlsx');
        $ruleTable = '\Shop\Models\BaiyangProductRule';
        foreach($aa as $v){
            if(!$v[5])continue;
            $spu = $base->select('spu_id,category_id','\Shop\Models\BaiyangSpu',['code'=>$v[0]],'code=:code:');
            if(!$spu){
                echo '错误：为找到spu信息----商品ID：'.$v[5].'------spu编码：'.$v[0].'<br>';
                continue;
            }
            $rules = $base->select('name_id,name_id2,name_id3','\Shop\Models\BaiyangCategoryProductRule',['category_id'=>$spu[0]['category_id']],'category_id=:category_id:');
            if(!$rules){
                echo '错误：未找到分类品规信息----商品ID：'.$v[5].'------spu编码：'.$v[0].'<br>';
                continue;
            }
            $ruleId1 = 0;
            if(!empty($v[7]) && !empty($rules[0]['name_id'])){
                $ruleDate = array('name'=>$v[7],'pid'=>$rules[0]['name_id']);
                $ruleId1 = $base->select('id',$ruleTable,$ruleDate,'name=:name: and pid=:pid: limit 1')[0]['id'];
                if(!$ruleId1){
                    $tmp = $base->insert($ruleTable,$ruleDate,true);
                    if(!$tmp){
                        echo '错误：添加第一个品规失败-------商品ID：'.$v[5].'------spu编码：'.$v[0].'<br>';
                    }
                    $ruleId1 = (int)$tmp;
                }
            }

            $ruleId2 = 0;
            if(!empty($v[9]) && !empty($rules[0]['name_id2'])){
                $ruleDate = array('name'=>$v[9],'pid'=>$rules[0]['name_id2']);
                $ruleId2 = $base->select('id',$ruleTable,$ruleDate,'name=:name: and pid=:pid: limit 1')[0]['id'];
                if(!$ruleId2){
                    $tmp = $base->insert($ruleTable,$ruleDate,true);
                    if(!$tmp){
                        echo '错误：添加第二个品规失败-------商品ID：'.$v[5].'------spu编码：'.$v[0].'<br>';
                    }
                    $ruleId2 = (int)$tmp;
                }
            }

            $ruleId3 = 0;
            if(!empty($v[11]) && !empty($rules[0]['name_id3'])){
                $ruleDate = array('name'=>$v[9],'pid'=>$rules[0]['name_id3']);
                $ruleId3 = $base->select('id',$ruleTable,$ruleDate,'name=:name: and pid=:pid: limit 1')[0]['id'];
                if(!$ruleId3){
                    $tmp = $base->insert($ruleTable,$ruleDate,true);
                    if(!$tmp){
                        echo '错误：添加第三个品规失败-------商品ID：'.$v[5].'------spu编码：'.$v[0].'<br>';
                    }
                    $ruleId3 = (int)$tmp;
                }
            }
            $data['id']=$v[5];
            $data['rule_value_id']      =  $ruleId1.'+'.$ruleId2.'+'.$ruleId3;
            $data['rule_value0']      =   $ruleId1;
            $data['rule_value1']      =   $ruleId2;
            $data['rule_value2']    =   $ruleId3;
            $data['spu_id'] =   $spu[0]['spu_id'];
            $res = $base->update(
                'spu_id=:spu_id:,rule_value_id=:rule_value_id:,rule_value0=:rule_value0:,rule_value1=:rule_value1:,rule_value2=:rule_value2:',
                '\Shop\Models\BaiyangGoods',
                $data,'id=:id:'
            );
            if(!$res){
                echo '错误：修改失败-------商品ID：'.$v[5].'------spu编码：'.$v[0].'<br>';
            }
        }
        die('导入商品信息成功！');
    }

    //转移商品数据
    public function zhuanyiAction()
    {
        ini_set('memory_limit','1027M');
        ini_set('max_execution_time', '60*60*60');
        set_time_limit(0);
        $base = BaseData::getInstance();
        $Table = '\Shop\Models\BaiyangGoods';
        $coun = $base->count($Table);
        $limit = 1000;
        $len = ceil($coun/$limit);
        for($i=0;$i<$len;$i++){
            $goods = $base->select('*',$Table,[],'1 limit '.$i*$limit.','.$limit);
            foreach($goods as $v){
                $data['sku_id'] = $v['id'];
                $data['virtual_stock_pc'] = $v['goods_number'];
                $data['virtual_stock_app'] = $v['virtual_stock'];
                $data['virtual_stock_wap'] = $v['virtual_stock'];
                $data['goods_price_pc'] = $v['goods_price'];
                $data['market_price_pc'] = $v['market_price'];
                $data['goods_price_app'] = $v['price'];
                $data['market_price_app'] = $v['market_price'];
                $data['goods_price_wap'] = $v['price'];
                $data['market_price_wap'] = $v['market_price'];
                $data['whether_is_gift'] = 1;
                $data['gift_pc'] = $v['product_type'];
                $data['gift_app'] = ($v['gift_yes'])?0:1;
                $data['gift_wap'] = ($v['gift_yes'])?0:1;
                $data['virtual_stock_wechat'] = $v['virtual_stock'];
                $data['goods_price_wechat'] = $v['price'];
                $data['market_price_wechat'] = $v['market_price'];
                $data['gift_wechat'] = ($v['gift_yes'])?0:1;

                $data1['id'] = $v['id'];
                $data1['is_unified_price']=1;
                $data1['sku_mobile_name']=$v['goods_name'];
                $data1['sku_pc_subheading']=$v['introduction'];
                $data1['sku_mobile_subheading']=$v['packing'];
                $data1['sale_timing_app']=$v['status'];
                $data1['sale_timing_wap']=$v['status'];
                $data1['sale_timing_wechat']=$v['status'];
                $data1['is_use_stock'] = ($v['is_use_stock']==2 || $v['is_use_stock']==3)?3:$v['is_use_stock'];

                $tmp = $base->count('\Shop\Models\BaiyangSkuInfo',['id'=>$v['id']],'sku_id=:id:');
                if($tmp){
                    $res = $base->update(
                        'virtual_stock_pc=:virtual_stock_pc:,
                        virtual_stock_app=:virtual_stock_app:,
                        virtual_stock_wap=:virtual_stock_wap:,
                        goods_price_pc=:goods_price_pc:,
                        market_price_pc=:market_price_pc:,
                        goods_price_app=:goods_price_app:,
                        market_price_app=:market_price_app:,
                        goods_price_wap=:goods_price_wap:,
                        market_price_wap=:market_price_wap:,
                        whether_is_gift=:whether_is_gift:,
                        gift_pc=:gift_pc:,
                        gift_app=:gift_app:,
                        gift_wap=:gift_wap:,
                        virtual_stock_wechat=:virtual_stock_wechat:,
                        goods_price_wechat=:goods_price_wechat:,
                        market_price_wechat=:market_price_wechat:,
                        gift_wechat=:gift_wechat:',
                        '\Shop\Models\BaiyangSkuInfo',
                        $data,'sku_id=:sku_id:'
                    );
                }else{
                    $res = $base->insert('\Shop\Models\BaiyangSkuInfo',$data);
                }
                if(!$res){
                    echo '错误：商品详情info表修改失败------商品ID：'.$v['id'].'<br>';
                    continue;
                }
                $res = $base->update('
                        is_unified_price=:is_unified_price:,
                        sku_mobile_name=:sku_mobile_name:,
                        sku_pc_subheading=:sku_pc_subheading:,
                        sku_mobile_subheading=:sku_mobile_subheading:,
                        sale_timing_app=:sale_timing_app:,
                        sale_timing_wap=:sale_timing_wap:,
                        sale_timing_wechat=:sale_timing_wechat:,
                        is_use_stock=:is_use_stock:','\Shop\Models\BaiyangGoods',$data1,'id=:id:');
                if(!$res){
                    echo '错误：商品详情goods表修改失败------商品ID：'.$v['id'].'<br>';
                }
            }
        }
        die('商品数据转移成功！');
    }

    //导入初始商品数据
    public function ecGoodsAction()
    {
        ini_set('memory_limit','1027M');
        ini_set('max_execution_time', '60*60*60');
        set_time_limit(0);
        $base = BaseData::getInstance();
        $Table = '\Shop\Models\BaiyangGoods1111';
        $coun = $base->count($Table);
        $limit = 1000;
        $len = ceil($coun/$limit);
        for($i=0;$i<$len;$i++){
            $goods = $base->select('*',$Table,[],'1 limit '.$i*$limit.','.$limit);
            foreach($goods as $v){
                $tmp = $base->count('\Shop\Models\BaiyangGoods',['id'=>$v['id']],'id=:id:');
                if($tmp){
                    $where = '';
                    foreach($v as $key=>$a){
                        if($key!='id'){
                            $where .= $key.'=:'.$key.':,';
                        }
                    }
                    $res = $base->update(trim($where,','),'\Shop\Models\BaiyangGoods',$v,'id=:id:');
                }else{
                    $res = $base->insert('\Shop\Models\BaiyangGoods',$v);
                }
                if(!$res){
                    echo '错误：商品信息还原失败------商品ID：'.$v['id'].'<br>';
                }
            }
        }
        die('商品信息还原成功！');
    }

    //导入商品品牌信息
    public function ecSkuBrandAction()
    {
        ini_set('memory_limit','1027M');
        set_time_limit(0);
        $base = BaseData::getInstance();
        $Table = '\Shop\Models\BaiyangSpu';
        $spu = $base->select('spu_id,code',$Table,[],'brand_id=0');
        foreach($spu as $v){
            $sku = $base->select('brand_id','\Shop\Models\BaiyangGoods',['spu_id'=>$v['spu_id']],'spu_id=:spu_id: order by id desc limit 1');
            if(!$sku){
                echo '错误：未找到spu关联的商品信息-----spuID：'.$v['spu_id'].'编码为：'.$v['code'].'<br>';
                continue;
            }

            $res = $base->update('brand_id=:brand_id:',$Table,['brand_id'=>$sku[0]['brand_id'],'spu_id'=>$v['spu_id']],'spu_id=:spu_id:');
            if(!$res){
                echo '错误：修改spu信息失败-----spuID：'.$v['spu_id'].'编码为：'.$v['code'].'<br>';
            }
        }
        die('修改商品品牌信息成功！');
    }

    //导入商品详情数据
    public function ecSkuDetailsAction()
    {
        ini_set('memory_limit','1027M');
        ini_set('max_execution_time', '60*60*60');
        set_time_limit(0);
        $base = BaseData::getInstance();
        $table = '\Shop\Models\BaiyangSkuInfo';
        $aa = $base->select('goods_id,goods_desc,body','\Shop\Models\BaiyangGoodsExtension');
        foreach($aa as $v){
            $com = $base->count($table,['id'=>$v['goods_id']],'sku_id=:id: limit 1');
            if(!$com){
                echo '错误：商品信息不存在------商品ID：'.$v['goods_id'].'<br>';
                continue;
            }
            $data = array(
                'sku_id'=>$v['goods_id'],
                'sku_detail_pc'=>$v['goods_desc'],
                'sku_detail_mobile'=>$v['body'],
            );
            $res = $base->update('sku_detail_pc=:sku_detail_pc:,sku_detail_mobile=:sku_detail_mobile:',$table,$data,'sku_id=:sku_id:');
            if(!$res){
                echo '错误：商品信息更新失败------商品ID：'.$v['goods_id'].'<br>';
                continue;
            }
        }
        die('商品详情导入成功！');
    }

    //导入参数信息
    public function ecCanshuAction()
    {
        ini_set('memory_limit','1027M');
        ini_set('max_execution_time', '60*60*60');
        set_time_limit(0);
        $base = BaseData::getInstance();
//        $aa = $this->cache->getValue('canshu');
//        if(empty($aa)){
            $fileUrl = '/data/aa/canshu.xlsx';
            $aa = $this->excel->importExcel($fileUrl,'xlsx');
//            $this->cache->setValue('canshu',$aa);
//        }
        foreach($aa as $v){
            if(empty($v[0]) || empty($v[4])){continue;}
            if($v[4]=='品牌'){continue;}
            $goods = $base->select('spu_id,attribute_value_id','\Shop\Models\BaiyangGoods',['id'=>$v[0]],'id=:id:');
            if(!$goods){continue;}
            if($goods[0]['spu_id']==0){
                echo '错误：该商品未关联到spu-------商品ID：'.$v[0].'-------------分类信息:'.$v[1].'====>'.$v[2].'====>'.$v[3].'<br>';
                continue;
            }
            $spu = $base->select('category_id','\Shop\Models\BaiyangSpu',['spu_id'=>$goods[0]['spu_id']],'spu_id=:spu_id:');

            if($spu[0]['category_id']<=0){
                echo '错误：分类信息错误-------商品ID：'.$v[0].'-------------分类信息:'.$v[1].'====>'.$v[2].'====>'.$v[3].'<br>';
                continue;
            }
//            //添加参数信息
            $name = array(
                'category_id'=>$spu[0]['category_id'],
                'attr_name'=>trim($v[4],' ')
            );
            $attrName = $base->select('id','\Shop\Models\BaiyangAttrName',$name,'category_id=:category_id: and attr_name=:attr_name:')[0]['id'];
            if(!$attrName){
                $attrName = $base->insert('\Shop\Models\BaiyangAttrName',$name,true);
                if(!$attrName){
                    echo '错误：添加参数名失败-------商品ID：'.$v[0].'-------------参数名:'.$v[4].'<br>';
                    continue;
                }
            }
            $valueName = trim($v[5],' ');
            if(empty($valueName)){continue;}
            $value=array(
                'attr_name_id'=>$attrName,
                'attr_value'=>$valueName
            );
            $attrValue = $base->select('id','\Shop\Models\BaiyangAttrValue',$value,'attr_name_id=:attr_name_id: and attr_value=:attr_value:')[0]['id'];
            if(empty($attrValue)){
                $attrValue = $base->insert('\Shop\Models\BaiyangAttrValue',$value,true);
                if(!$attrValue){
                    echo '错误：添加参数值失败------商品ID：'.$v[0].'-------------参数值:'.$v[5].'<br>';
                    continue;
                }
            }
            $data['id'] = $v[0];
            if(empty($goods[0]['attribute_value_id'])){
                $data['attribute_value_id'] = $attrName.':'.$attrValue;
            }else{
                $data['attribute_value_id']=$goods[0]['attribute_value_id'].','.$attrName.':'.$attrValue;
            }

            $res = $base->update('attribute_value_id=:attribute_value_id:','\Shop\Models\BaiyangGoods',$data,'id=:id:');
            if(!$res){
                echo '错误：修改商品信息失败------商品ID：'.$v[0].'-------------参数值:'.$v[5].'<br>';
            }
        }
        die('导入参数数据成功！');
    }

    //导入pc前端分类
    public function ecCategoryPcAction()
    {
        ini_set('memory_limit','1027M');
        ini_set('max_execution_time', '60*60*60');
        set_time_limit(0);
        $base = BaseData::getInstance();
        $fileUrl = '/data/aa/categoryPc.xlsx';
        $table = '\Shop\Models\BaiyangMainCategory';
        $aa = $this->excel->importExcel($fileUrl,'xlsx');
        $error = array();
        $error[0]='';
        foreach($aa as $v){
            if(empty($v[0])){continue;}
            $data = array(
                'category_name'=>$v[0],
                'alias'=>$v[0],
                'category_logo'=>'',
                'thecoverwap'=>'',
                'sort'=>0
            );

            if($v[1]=='顶级'){
                $data['level'] = 1;
                $data['pid'] = 0;
            }else{
                $res = $base->select('id,level',$table,['name'=>trim($v[1],' ')],'category_name=:name: order by id desc');

                if(!$res){
                    echo '错误：父分类没有找到----分类名：'.$v[0].'-------父分类：'.$v[1].'<br>';
                    continue;
                }
                $data['level'] = $res[0]['level']+1;
                $data['pid'] = $res[0]['id'];
            }
            if(trim($v[4],' ')=='是'){
                $adminCate=$base->select('id,pid','\Shop\Models\BaiyangCategory',['name'=>trim($v[3],' '),'level'=>$data['level']],'category_name=:name: and level=:level:');

                if(!$adminCate){
                    $ddd = array();
                    foreach($res as $aaa=>$vs){
                        if($vs['level']==2){
                            $ddd[]=$vs;
                        }
                    }
                    if(count($ddd)==1){
                        $data['level'] = $ddd[0]['level']+1;
                        $data['pid'] = $ddd[0]['id'];
                        $adminCate=$base->select('id,pid','\Shop\Models\BaiyangCategory',['name'=>trim($v[3],' '),'level'=>$data['level']],'category_name=:name: and level=:level:');
                    }
                    if(count($ddd)>1){
                        echo '分类错误';die;
                    }
                    if(count($ddd)==0){
                        $adminCate=$base->select('id,pid','\Shop\Models\BaiyangCategory',['name'=>trim($v[3],' ')],'category_name=:name:');
                    }
                }

                if(!$adminCate){
                    echo '错误：后台分类未找到--------前端分类名：'.$v[0].'------前端父分类名：'.$v[1].'-----后台分类名：'.$v[3].'<br>';
                    $error[] = array(
                        'info'=>'后台分类未找到',
                        'c1'=>$v[0],
                        'c2'=>$v[1],
                        'c3'=>$v[3],
                    );
                    continue;
                }
                if(count($adminCate)>1){
                    $aa = true;
                    foreach($adminCate as $va){
                        $pcate = $base->count('\Shop\Models\BaiyangCategory',['name'=>trim($v[1],' '),'id'=>$va['pid'],'level'=>$data['level']-1],'category_name=:name: and id=:id: and level=:level:');
                        if($pcate==1){
                            $data['category_link'] = '/list-'.$va['id'].'.html';
                            $aa = false;
                            continue;
                        }
                    }
                    if($aa){
                        echo '错误：找不到正确的后台分类信息：----显示分类名：'.$v[0].'--显示分类父级分类名：'.$v[1].'--后台分类父级名称：'.$v[2].'--后台分类名称：'.$v[3].'<br>';
                        continue;
                    }
                }else{
                    $data['category_link'] = '/list-'.$adminCate[0]['id'].'.html';
                }

            }else{
                $data['category_link'] = '/search.do?keyword='.trim($v[0],' ');
            }
            $res = $base->insert($table,$data);
            if(!$res){
                echo '错误：分类添加失败------分类名：'.$v[0].'父分类：'.$v[1].'<br>';
            }
        }
//        $this->excel->exportExcel(['错误原因','显示分类','显示分类父分类','后台分类'],$error,'aaaa','错误','xlsx');
        die('pc前端分类导入成功！');
    }

    //导入APP前端显示分类信息
    public function ecCategoryAppAction()
    {
        ini_set('memory_limit','1027M');
        ini_set('max_execution_time', '60*60*60');
        set_time_limit(0);
        $base = BaseData::getInstance();
        $fileUrl = '/data/aa/categoryApp.xlsx';
        $table = '\Shop\Models\BaiyangAppCategory';
        $aa = $this->excel->importExcel($fileUrl,'xlsx');
//        $error = array();
//        $error[0]='';
        foreach($aa as $v){
            if(empty($v[0])) continue;
            $data = array(
                'category_name'=>$v[0],
//                'created_at'=>time(),
//                'updated_at'=>time(),
            );

            if($v[1]=='顶级'){
                $data['level'] = 1;
                $data['parent_id'] = 0;
            }else{
                $res = $base->select('category_id,level',$table,['name'=>trim($v[1],' ')],'category_name=:name: order by category_id desc');

                if(!$res){
                    echo '错误：父分类没有找到----分类名：'.$v[0].'-------父分类：'.$v[1].'<br>';
                    continue;
                }
                $data['level'] = $res[0]['level']+1;
                $data['parent_id'] = $res[0]['category_id'];
                if($data['level']>3){
                    $ddd = array();
                    foreach($res as $v11){
                        if($v11['level']==2){
                            $ddd[]=$v11;
                        }
                    }
                    if(count($ddd)==1){
                        $data['level'] = $ddd[0]['level']+1;
                        $data['parent_id'] = $ddd[0]['category_id'];
                    }else{
                        echo '错误：父分类没有找到，太多----分类名：'.$v[0].'-------父分类：'.$v[1].'<br>';
                        continue;
                    }
                }
            }
            if(trim($v[4],' ')=='是'){
                $adminCate=$base->select('id,pid','\Shop\Models\BaiyangCategory',['name'=>trim($v[3],' '),'level'=>$data['level']],'category_name=:name: and level=:level:');

                if(!$adminCate){
                    $ddd = array();
                    foreach($res as $aaa=>$vs){
                        if($vs['level']==2){
                            $ddd[]=$vs;
                        }
                    }
                    if(count($ddd)==1){
                        $data['level'] = $ddd[0]['level']+1;
                        $data['pid'] = $ddd[0]['id'];
                        $adminCate=$base->select('id,pid','\Shop\Models\BaiyangCategory',['name'=>trim($v[3],' '),'level'=>$data['level']],'category_name=:name: and level=:level:');
                    }
                    if(count($ddd)>1){
                        echo '分类错误';die;
                    }
                    if(count($ddd)==0){
                        $adminCate=$base->select('id,pid','\Shop\Models\BaiyangCategory',['name'=>trim($v[3],' ')],'category_name=:name:');
                    }
                }

                if(!$adminCate){
                    echo '错误：后台分类未找到--------前端分类名：'.$v[0].'------前端父分类名：'.$v[1].'-----后台分类名：'.$v[3].'<br>';
                    $error[] = array(
                        'info'=>'后台分类未找到',
                        'c1'=>$v[0],
                        'c2'=>$v[1],
                        'c3'=>$v[3],
                    );
                    continue;
                }
                if(count($adminCate)>1){
                    $aa = true;
                    foreach($adminCate as $va){
                        $pcate = $base->count('\Shop\Models\BaiyangCategory',['name'=>trim($v[1],' '),'id'=>$va['pid'],'level'=>$data['level']-1],'category_name=:name: and id=:id: and level=:level:');
                        if($pcate==1){
                            $data['product_category_id'] = $va['id'];
                            $aa = false;
                            continue;
                        }
                    }
                    if($aa){
                        echo '错误：找不到正确的后台分类信息：----显示分类名：'.$v[0].'--显示分类父级分类名：'.$v[1].'--后台分类父级名称：'.$v[2].'--后台分类名称：'.$v[3].'<br>';
                        continue;
                    }
                }else{
                    $data['product_category_id'] = $adminCate[0]['id'];
                }

            }else{
//                $data['product_category_id'] = '/search.do?keyword='.trim($v[0],' ');
            }

            $res = $base->insert($table,$data);
            if(!$res){
                echo '错误：分类添加失败------分类名：'.$v[0].'父分类：'.$v[1].'<br>';
            }
        }
//        $this->excel->exportExcel(['错误原因','显示分类','显示分类父分类','后台分类'],$error,'aaaa','错误','xlsx');
        $this->updateCategoryAppPathAction();
        die('移动前端分类导入成功！');
    }

    //处理前端分类路径
    public function updateCategoryAppPathAction()
    {
        $base = BaseData::getInstance();
        $table = '\Shop\Models\BaiyangAppCategory';
        $aa = $base->select('category_id,parent_id',$table);
        foreach($aa as $v){
            if($v['parent_id']==0){
                $path = $v['category_id'];
            }else{
                $tmp = $base->select('category_path',$table,['category_id'=>$v['parent_id']],'category_id=:category_id:');
                if(!$tmp){
                    echo '错误：父分类路径获取失败！<br>';
                    continue;
                }
                $path = $tmp[0]['category_path'].'-'.$v['category_id'];
            }
            $res = $base->update('category_path=:category_path:',$table,['category_path'=>$path,'category_id'=>$v['category_id']],'category_id=:category_id:');
            if(!$res){
                echo '错误：修改失败！<br>';
                continue;
            }
        }
        echo '路径处理成功！<br>';
    }
}