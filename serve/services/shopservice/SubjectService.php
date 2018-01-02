<?php
/**
 * Created by PhpStorm.
 * User: 陈河源
 * Date: 2016/5/27
 * Time: 上午 15:16
 * 后台促销活动类
 */

namespace Shop\Services;

use Shop\Datas\BaiyangSkuData;
//use Shop\Models\BaiyangPromotionEnum;
//use Shop\Models\BaiyangLimitPromotionEnum;
use Shop\Datas\BaseData;
//use Shop\Datas\BaiyangPromotionData;
//use Shop\Models\CacheKey;

class SubjectService extends BaseService
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance = null;
	//单商品可用标签数量上限
	private $tag_max = 6;

    /**
     * @desc 获取专题活动列表信息
     * @param array $param 查询条件
     * @return array [] 结果信息
     * @author 陈河源
     */
    public function getSubjectList($param)
    {
        //查询的基本条件
        $table = "\\Shop\\Models\\BaiyangSubject";
        $join='';
        $selections = ' id,title,update_time,status,link,channel ';
        if(isset($param['param']['channel']) && !empty($param['param']['channel'])){
            $conditions = [
                'channel' => $param['param']['channel']
            ];
            $whereStr = 'channel = (:channel:) ';
        }

        //输入的查询条件
        if(!empty($param['param']['title'])){
            $whereStr .= ' AND title like :title:';
            $conditions['title'] = '%'.$param['param']['title'].'%';
        }
        if(!empty($param['param']['start'])&&!empty($param['param']['end'])){
            $whereStr .= ' AND update_time between  :start: and :end: ';
            $conditions['start'] = strtotime($param['param']['start']);
            $conditions['end'] = strtotime($param['param']['end']);
        }
        if(isset($param['param']['status'])){
            $conditions['status'] = $param['param']['status'];
            $whereStr .= ' AND status = :status:';
        }
        //总记录数
        $counts = BaseData::getInstance()->count($table, $conditions, $whereStr);
        if(empty($counts)){
            return ['res' => 'error','list' => '','voltValue' => $param['param']];
        }
        //分页
        $pages['page'] = isset($param['page']) ? $param['page'] : 1;//当前页
        $pages['counts'] = $counts;
        $pages['psize'] = isset($param['psize'])? $param['psize'] : 15;
        $pages['url'] = $param['url'];
        $pages['url_back'] = isset($param['url_back']) ? $param['url_back'] : '';
        $pages['home_page'] = isset($param['home_page']) ? $param['home_page'] : '';
        $pages['size'] = isset($param['size'])?$param['size']:5;
        $page = $this->page->pageDetail($pages);
        $whereStr .= ' ORDER BY update_time DESC LIMIT '.$page['record'].','.$page['psize'];
        $result = BaseData::getInstance()->select($selections,$table,$conditions,$whereStr,$join);
        if(empty($result)){
            return ['res' => 'error'];
        }
        foreach ($result as $key => $val) {
            $result[$key]['update_time'] = date('Y-m-d H:i:s', $val['update_time']);
            $result[$key]['id'] = ($val['id'] > 0) ? $val['id'] : '';
            $result[$key]['title'] = $val['title'];
            $result[$key]['link'] = $val['link'];
			$result[$key]['channel'] = ($val['channel'] == 95) ? 'PC端' : '移动端';
			switch ($val['status'])
			{
			case 0:
			  $result[$key]['status'] = '未发布';
			  break;  
			case 1:
			  $result[$key]['status'] = '已发布';
			  break;
			case 2:
			  $result[$key]['status'] = '已停用';
			  break;
			}
			//附加地址
			if(!empty($val['link'])){
				$other_url = $this->config['subject_file']['other_url'];
				$port = ($val['channel'] == 95) ? 'pc' : 'wap';
				if(isset($other_url)&&!empty($other_url)){
					if(isset($other_url[$port])&&is_array($other_url[$port])){
						foreach($other_url[$port] as $v){
							if(!empty($v)){
								$result[$key]['link2'] .= "或<a class='blue' href='".$v."'  target='_blank'>".$v."</a>"; 
							}
						}
					}
				}
				
			}
        }
		
        return ['res'  => 'succcess', 'list' => $result, 'page' => $page['page'], 'voltValue' => $param['param']];
    }

    /**
     * @desc 添加专题
     * @param array $param 专题数据信息
     * @return array [] 结果信息
     * @author 陈河源
     */
    public function addSubject($param)
    {
        $subject = []; //专题信息

        $subject['title'] = $param['title'];
        $subject['keywords'] = isset($param['keywords']) ? $param['keywords'] : $param['title'];
        $subject['description'] = $param['description'];
        $subject['share_title'] = isset($param['share_title']) ? $param['share_title'] : '';
        $subject['shareUrl'] = isset($param['shareUrl']) ? $param['shareUrl'] : '';
        $subject['share_img'] = isset($param['share_img']) ? $param['share_img'] : '';
        $subject['background'] = $param['background'];
        $subject['order'] = isset($param['order']) ? $param['order'] : 0;
        $subject['link'] = isset($param['link']) ? $param['link'] : '';
        $subject['status'] = isset($param['status']) ? $param['status'] : 0;
        $subject['channel'] = isset($param['channel']) ? $param['channel'] : 91;
        $subject['create_time'] = isset($param['create_time']) ? $param['create_time'] : time();
        $subject['update_time'] = isset($param['update_time']) ? $param['update_time'] : time();
		$subject['component_detail'] = isset($param['component_detail']) ? $param['component_detail'] : '';
		if($param['channel'] != 95 && $subject['share_title'] == ''){
            $subject['share_title'] = $subject['title'];
        }

        if(!($param['title'])){
            return $this->arrayData('标题不能为空！', '', '', 'error');
        }
        if(!($param['keywords'])){
            return $this->arrayData('关键字不能为空！', '', '', 'error');
        }
        if(!($param['description'])){
            return $this->arrayData('描述不能为空！', '', '', 'error');
        }
        if(mb_strlen($param['title'],'UTF8') > 15){
            return $this->arrayData('标题超过15字！', '', '', 'error');
        }
        // 开启事务
        $this->dbWrite->begin();
        $promotionId = BaseData::getInstance()->insert('\Shop\Models\BaiyangSubject',$subject,true);
        if(empty($promotionId)){
            $this->dbWrite->rollback();
            return $this->arrayData('添加失败！', '', '', 'error');
        }
        $this->dbWrite->commit();
        //BaiyangPromotionData::getInstance()->getPromotionsInfo('', true); // 缓存活动
		
        $url = $param['url']."?id=".$this->getSubjectId();
        return $this->arrayData('已设置，请下一步！', $url);
    }
	
	/**
     * @desc 编辑专题
     * @param array $param 专题数据信息
     * @return array [] 结果信息
     * @author 陈河源
     */
    public function editSubject($param)
    {
        if(!($param['id'])){
            return $this->arrayData('专题id不能为空！', '', '', 'error');
        }
        if(!($param['config'])){
            return $this->arrayData('config不能为空！请添加组件设置', '', '', 'error');
        }
        if(!($param['html'])){
            return $this->arrayData('html不能为空！请添加组件设置', '', '', 'error');
        }
		if(!($param['url'])){
            return $this->arrayData('url不能为空！请添加跳转地址', '', '', 'error');
        }
		
        $subject = []; //专题信息

        $subject['id'] = $param['id'];
        $subject['component_detail'] = isset($param['config']) ? json_encode($param['config']) : '';
        $subject['link'] = '';
        $subject['status'] = 1;
        $subject['channel'] = isset($param['channel']) ? $param['channel'] : 91;
        $subject['update_time'] = time();
		
		//生成link及文件
		$this->checkdir($this->config['subject_file']['dir']);
		$file = $param['channel'] == 91 ? '/subject/mobile/subject_id_'.$param['id'].'.html' : '/subject/pc/subject_id_'.$param['id'].'.html' ;
		$fileurl = $this->config['subject_file']['dir'].$file ;
		$subject['link'] = $this->config['subject_file']['dir_url'][$this->config->environment].$file ;
		file_put_contents($fileurl,html_entity_decode($param['html']));
		
		//移动端分享链接如果不设置则默认是本身
		if($param['channel'] != 95){
			$old_info = $this->getSubjectInfo($param);
			if(empty($old_info[0]['shareUrl'])){
				$subject['shareUrl'] = $subject['link'];
			}
		}
		
		//记录log
		$admin_id = $this->session->get('admin_id');
		$admin_account = $this->session->get('admin_account');
		$array = array('subject_id' => $param['id'], 'user_id' =>$admin_id,'admin_account' => $admin_account, 'field_name' => 'component_detail', 'old_value' => '忽略...', 'new_value' => '忽略...', 'channel' =>$param['channel'], 'add_time' => time());
		$this->insertlog($array);
		
        // 开启事务
        $this->dbWrite->begin();
		$columStr1 = $this->jointString($subject, array('id'));
        $whereStr = 'id = :id:';
        //更新操作
        $Result = BaseData::getInstance()->update($columStr1,'\Shop\Models\BaiyangSubject',$subject,$whereStr);
        if(empty($Result)){
            $this->dbWrite->rollback();
            return $this->arrayData('保存失败！', '', '', 'error');
        }
        $this->dbWrite->commit();
        $url = isset($param['url']) ? $param['url'] : '';
        return $this->arrayData('保存成功！', $url);
    }
	
	/**
     * @desc 更新专题的状态
     * @param array $param 参数
     * @return array [] 结果信息
     * @author 陈河源
     */
    public function updateSubjectStatusToStop($param)
    {
        if(empty($param['id'])){
			return $this->arrayData('参数错误！', '', '', 'error');
		}
        //静态文件中要变换的字符串
		$change =  ($param['channel'] == 95) ? '<head>' : '<head>' ;
		$stop_file = ($param['channel'] == 95) ? $this->config['subject_file']['dir_url'][$this->config->environment].'/subject/pc/stop.html' : $this->config['subject_file']['dir_url'][$this->config->environment].'/subject/mobile/stop.html' ;
		$replace = ($param['channel'] == 95) ? '<meta http-equiv="refresh" content="0;url='.$stop_file.'">' : '<meta http-equiv="refresh" content="0;url='.$stop_file.'">' ;
		
		//获取主题状态和地址
		$table = "\\Shop\\Models\\BaiyangSubject";
		$selections = 'status,link';
        $conditions = [
            'id' => $param['id']
        ];
        $whereStr = 'id = :id:';
        $subject = BaseData::getInstance()->select($selections,$table,$conditions,$whereStr);

        $updateConditions = [];
        if(!empty($subject)){
            foreach($subject as $value){
                if($value['status']<1){
					return $this->arrayData('已发布的专题才能停用', '', '', 'error');
				}
				$updateConditions['id'] = $param['id'];
                $updateConditions['status'] = ($value['status'] == 2) ? 1 : 2;
                if($value['link']){
                    if(strpos($value['link'],'?')){
                        $value['link'] =substr($value['link'],0,strpos($value['link'],'?'))."?_v=".rand(100000,999999);
                    }else{
                        $value['link'] =$value['link']."?_v=".rand(100000,999999);
                    }
                }
                $updateConditions['link'] = $value['link'];
                $result = BaseData::getInstance()->update('status = :status:,link = :link:',$table,$updateConditions,'id = :id:');
                if(!empty($result)){
                    
					if(!empty($value['link'])){
						$file = ($param['channel'] == 95) ? 'subject/pc/subject_id_'.$param['id'].'.html' : 'subject/mobile/subject_id_'.$param['id'].'.html' ;
						$fileurl = $this->config['subject_file']['dir'].$file ;
						//模版源码
						$html = file_get_contents($fileurl);
						//停用
						if($updateConditions['status'] == 2){
							if (strpos($html, $replace) !== false) {
							//如果包含omr/online
						   }else{
							   $refresh = $change.$replace;
								$html = str_replace($change,$refresh,$html);
						   }
						}
						//启用
						if($updateConditions['status'] == 1){
							$html = str_replace($replace,'',$html);
							$html= implode('',explode($replace,$html));
						}
						file_put_contents($fileurl,html_entity_decode($html));
					}
					
					$url = "/subjectmobile/list?page=".$param['page'];
					return $this->arrayData('更改成功',$url );
                }
            }
        }
        return $this->arrayData('操作失败,请肖后再试!', '', '', 'error');
    }

    /**
     * @desc 获取组件列表信息
     * @param array $param 查询条件
     * @return array [] 结果信息
     * @author 陈河源
     */
    public function getWidgetList($param)
    {
        //查询的基本条件
        $table = "\\Shop\\Models\\BaiyangSubjectComponent";
        $join='';
        $selections = ' component_id,component_name,status ';
        //渠道
        if(isset($param['param']['channel']) && !empty($param['param']['channel'])){
            $conditions = [
                'channel' => $param['param']['channel']
            ];
            $whereStr = 'channel = :channel: ';
        }

        //输入的查询条件
        if(!empty($param['param']['component_name'])){
            $whereStr .= ' AND component_name like :component_name:';
            $conditions['component_name'] = '%'.$param['param']['component_name'].'%';
        }
        if(isset($param['param']['status'])){
            $conditions['status'] = $param['param']['status'];
            $whereStr .= ' AND status = :status:';
        }
        //总记录数
        $counts = BaseData::getInstance()->count($table, $conditions, $whereStr);
        if(empty($counts)){
            return ['res' => 'error','list' => '','voltValue' => $param['param']];
        }
        //分页
        $pages['page'] = isset($param['page']) ? $param['page'] : 1;//当前页
        $pages['counts'] = $counts;
        $pages['psize'] = isset($param['psize'])? $param['psize'] : 15;
        $pages['url'] = $param['url'];
        $pages['url_back'] = isset($param['url_back']) ? $param['url_back'] : '';
        $pages['home_page'] = isset($param['home_page']) ? $param['home_page'] : '';
        $pages['size'] = isset($param['size'])?$param['size']:5;
        $page = $this->page->pageDetail($pages);
        $whereStr .= ' ORDER BY component_id DESC LIMIT '.$page['record'].','.$page['psize'];
        $result = BaseData::getInstance()->select($selections,$table,$conditions,$whereStr,$join);
        if(empty($result)){
            return ['res' => 'error'];
        }
        foreach ($result as $key => $val) {
            $result[$key]['component_id'] = ($val['component_id'] > 0) ? $val['component_id'] : '';
            $result[$key]['component_name'] = $val['component_name'];
            $result[$key]['status'] = $val['status'];
        }
        return ['res'  => 'succcess', 'list' => $result, 'page' => $page['page'], 'voltValue' => $param['param']];
    }

    /**
     * @desc 根据id获取组件详情信息
     * @param int $componentId 组件id
     * @return array|bool $promotionDetail|false 详情信息
     * @author 陈河源
     */
    public function getComponentById($componentId)
    {
        $table = '\Shop\Models\BaiyangSubjectComponent as aa LEFT JOIN \Shop\Models\BaiyangSubjectComponentField as bb on aa.component_id = bb.component_id';
        $selections = 'aa.component_id,aa.component_name,aa.status,aa.html_value,aa.css_value,aa.javascript_value,bb.field_id,bb.field_name,bb.field_label,bb.field_type,bb.select_value';
        $conditions = [
            'component_id' => $componentId,
        ];
        $whereStr = 'aa.component_id = :component_id:';
        $componentDetail = BaseData::getInstance()->select($selections,$table,$conditions,$whereStr);
        if(!empty($componentDetail)){
            //组件
			$componentDetailId = [
                'component_id'     => trim($componentDetail[0]['component_id']),
                'component_name'   => trim($componentDetail[0]['component_name']),
                'status'            => trim($componentDetail[0]['status']),
                'html_value'       => trim($componentDetail[0]['html_value']),
                'css_value'        => trim($componentDetail[0]['css_value']),
                'javascript_value' => trim($componentDetail[0]['javascript_value']),
            ];
            //组件字段
			$fieldDetailId = array();
			$field_ids = '';
            if(!empty($componentDetail[0]['field_name'])){
                foreach($componentDetail as $k => $v){
                    $fieldDetailId[$v['field_id']] = [
                        'field_id'     => $v['field_id'],
                        'field_name'   => trim($v['field_name']),
                        'field_label'  => trim($v['field_label']),
                        'field_type'   => trim($v['field_type']),
                        'select_value' => $v['field_type'] == 3 ? trim($v['select_value']) : '',
                    ];
                    $field_ids .= ','.$v['field_id'];
                }
            }
            $componentDetail = [
                'component' => $componentDetailId,
                'field'     => $fieldDetailId,
                'field_ids' => trim($field_ids,',')
            ];
            return $componentDetail;
        }
        return false;
    }

    /**
     * @desc 添加/编辑专题活动
     * @param array $param 促销活动数据信息
     * @return array [] 结果信息
     * @author 陈河源
     */
    public function editWidget($param)
    {
        $component = []; //组件活动信息
        $field = []; //组件活动信息

        $component['component_id'] = isset($param['id']) ? $param['id'] : '';
        $component['html_value'] = isset($param['html_value']) ? $param['html_value'] : '';
        $component['css_value'] = isset($param['css_value']) ? $param['css_value'] : '';
        $component['javascript_value'] = isset($param['javascript_value']) ? $param['javascript_value'] : '';
        $component['component_name'] = $param['component_name'];
        $component['status'] = isset($param['status']) ? $param['status'] : 1;
        $component['channel'] = isset($param['channel']) ? $param['channel'] : 91;

        $field['field_name'] = isset($param['field_name']) ? $param['field_name'] : array();
        $field['field_label'] = isset($param['field_label']) ? $param['field_label'] : array();
        $field['field_type'] = isset($param['field_type']) ? $param['field_type'] : array();
        $field['select_value'] = isset($param['select_value']) && ($field['field_type'] == 3) ? str_replace(['，',' ','|'],',',$param['select_value']) : '';

        if(!($param['component_name'])){
            return $this->arrayData('组件名称不能为空！', '', '', 'error');
        }
        if(!($param['html_value'])){
            return $this->arrayData('HTML不能为空！', '', '', 'error');
        }
		if(!($param['url'])){
            return $this->arrayData('跳转地址错误 ！', '', '', 'error');
        }
        if(!empty($field['field_name'])&&!empty($field['field_name'][0])){
            foreach ($param['field_name'] as $item) {
                if(!ctype_alnum($item)){
                    return $this->arrayData('字段名必须是字母和数字！', '', '', 'error');
                }
            }
        }


        if(!empty($param['id'])){
            //编辑当前组件

            // 开启事务
            $this->dbWrite->begin();
            $columStr1 = $this->jointString($component, array('component_id'));
            $whereStr = 'component_id = :component_id:';
            //更新操作
            $componentResult = BaseData::getInstance()->update($columStr1,'\Shop\Models\BaiyangSubjectComponent',$component,$whereStr);
            if(empty($componentResult)){
                $this->dbWrite->rollback();
                return $this->arrayData('编辑失败！', '', '', 'error');
            }
            //字段进行重组（根据键值变化进行更新，删除，添加）。
            $now_key = array_keys($param['field_name']);
            $old_key = explode(',',$param['field_ids']);
            $del_key = array_diff($old_key,$now_key);
            foreach ($del_key as $item) {
                //删除字段
                BaseData::getInstance()->delete("\Shop\Models\BaiyangSubjectComponentField",
                    [ 'field_id' => $item],"field_id = :field_id:");
            }
            foreach ($param['field_name'] as $key => $item) {
                //更新旧数据
                if(!empty($item)){
                    if(in_array($key,$old_key)){
                        $array = [
                            'field_id'     => $key,
                            'field_name'   => trim($param['field_name'][$key]),
                            'field_label'  => trim($param['field_label'][$key]),
                            'field_type'   => trim($param['field_type'][$key]),
                            'select_value' => trim($param['select_value'][$key]),
                        ];
                        $columStr1 = $this->jointString($array, array('field_id'));
                        $whereStr = 'field_id = :field_id:';
                        //字段更新
                        $componentResult = BaseData::getInstance()->update($columStr1,'\Shop\Models\BaiyangSubjectComponentField',$array,$whereStr);
                        if(empty($componentResult)){
                            $this->dbWrite->rollback();
                            return $this->arrayData('编辑失败！', '', '', 'error');
                        }
                    }else{
                        $field['component_id'] = $component['component_id'];
                        $field['field_name'] = $param['field_name'][$key];
                        $field['field_label'] = $param['field_label'][$key];
                        $field['field_type'] = $param['field_type'][$key];
                        $field['select_value'] = $param['field_type'][$key] == 3 ? $param['select_value'][$key] : '';
                        //添加字段
                        BaseData::getInstance()->insert('\Shop\Models\BaiyangSubjectComponentField',$field,true);
                    }
                }
            }


            $this->dbWrite->commit();
            return $this->arrayData('编辑成功！', $param['url']);
        }else{
            //添加新组件
            // 开启事务
            $this->dbWrite->begin();
            $componentId = BaseData::getInstance()->insert('\Shop\Models\BaiyangSubjectComponent',$component,true);
            if(empty($componentId)){
                $this->dbWrite->rollback();
                return $this->arrayData('添加失败！', '', '', 'error');
            }
			$this->dbWrite->commit();
			//获取新添加的数据
            $conditions = [
                'channel' => $component['channel'],
            ];
            $whereStr =  'channel = :channel: ORDER BY component_id DESC LIMIT 0,1';
            $component_id = BaseData::getInstance()->select('component_id',"\\Shop\\Models\\BaiyangSubjectComponent",$conditions,$whereStr);
            foreach($param['field_name'] as $k => $v){
                if(!empty($v)){
                    $field['component_id'] = $component_id[0]['component_id'];
                    $field['field_name'] = $param['field_name'][$k];
                    $field['field_label'] = $param['field_label'][$k];
                    $field['field_type'] = $param['field_type'][$k];
                    $field['select_value'] = $param['field_type'][$k] == 3 ? $param['select_value'][$k] : '';

                    $this->dbWrite->begin();
					BaseData::getInstance()->insert('\Shop\Models\BaiyangSubjectComponentField',$field,true);
					$this->dbWrite->commit();
                }
            }
            return $this->arrayData('添加成功！', $param['url']);
        }

    }

    /**
     * @desc 变更组件的状态
     * @param int $component_id 组件id
     * @return array [] 结果信息
     * @author 陈河源
     */
    public function updateComponentStatus($component_id)
    {
        if(empty($component_id)){
			return $this->arrayData('参数错误！', '', '', 'error');
		}
		$table = '\Shop\Models\BaiyangSubjectComponent';
        $selections = 'status ';
        $conditions = [
            'component_id' => $component_id
        ];
        $whereStr = 'component_id = :component_id:';
        $component = BaseData::getInstance()->select($selections,$table,$conditions,$whereStr);

        $updateConditions = [];
        if(!empty($component)){
            foreach($component as $value){
                $updateConditions['component_id'] = $component_id;
                $updateConditions['status'] = ($value['status'] > 0) ? 0 : 1;
                $result = BaseData::getInstance()->update('status = :status:',$table,$updateConditions,'component_id = :component_id:');
                if(!empty($result)){
                    return $this->arrayData('更改成功');
                }
            }
        }
        return $this->arrayData('操作失败,请肖后再试!', '', '', 'error');
    }

    /**
     * @desc 专题商品标签列表
     * @param array $param 数据信息
     * @return array [] 结果信息
     * @author 陈河源
     */
    public function listGoodTag($param)
    {
        //查询的基本条件
        $table = "\\Shop\\Models\\BaiyangSubjectTag";
        $join='';
        $selections = ' tag_id as id,tag_name,start_time,end_time,status ';
        //限购和限时优惠
        if(isset($param['param']['type']) && !empty($param['param']['type'])){
            $conditions = [
                'type' => $param['param']['type']
            ];
            $whereStr = 'type = :type: ';
        }

        //输入的查询条件
        if(!empty($param['param']['tag_name'])){
            $whereStr .= ' AND tag_name like :tag_name:';
            $conditions['tag_name'] = '%'.$param['param']['tag_name'].'%';
        }
        /*
        if(!empty($param['param']['start'])&&!empty($param['param']['end'])){
            $whereStr .= ' AND update_time between  :start: and :end: ';
            $conditions['start'] = strtotime($param['param']['start']);
            $conditions['end'] = strtotime($param['param']['end']);
        }
        */
        if(isset($param['param']['status'])&&$param['param']['status'] != '全部'){
            $arr = ['禁用' => 0 ,'启用' => 1];
			$conditions['status'] = $arr[$param['param']['status']];
            $whereStr .= ' AND status = :status:';
        }
        //总记录数
        $counts = BaseData::getInstance()->count($table, $conditions, $whereStr);
        if(empty($counts)){
            return ['res' => 'error','list' => '','voltValue' => $param['param']];
        }
        //分页
        $pages['page'] = isset($param['page']) ? $param['page'] : 1;//当前页
        $pages['counts'] = $counts;
        $pages['psize'] = isset($param['psize'])? $param['psize'] : 15;
        $pages['url'] = $param['url'];
        $pages['url_back'] = isset($param['url_back']) ? $param['url_back'] : '';
        $pages['home_page'] = isset($param['home_page']) ? $param['home_page'] : '';
        $pages['size'] = isset($param['size'])?$param['size']:5;
        $page = $this->page->pageDetail($pages);
        $whereStr .= ' ORDER BY update_time DESC LIMIT '.$page['record'].','.$page['psize'];
        $result = BaseData::getInstance()->select($selections,$table,$conditions,$whereStr,$join);
        if(empty($result)){
            return ['res' => 'error'];
        }
        foreach ($result as $key => $val) {
            $result[$key]['update_time'] = date('Y-m-d H:i:s', $val['start_time'])."——".date('Y-m-d H:i:s', $val['end_time']);
            $result[$key]['id'] = ($val['id'] > 0) ? $val['id'] : '';
            $result[$key]['tag_name'] = $val['tag_name'];
            $result[$key]['status'] = ($val['status'] == 1) ? '启用' : '禁用';
			
			//更新过期标签
			$updateConditions = [];
			if($val['end_time'] < time()){
				$updateConditions['tag_id'] = $val['id'];
                BaseData::getInstance()->update('status = 0',$table,$updateConditions,'tag_id = :tag_id:');
				$result[$key]['status'] = '禁用';
			}
        }
        return ['res'  => 'succcess', 'list' => $result, 'page' => $page['page'], 'voltValue' => $param['param']];
    }

    /**
     * @desc 编辑专题商品标签
     * @param array $param 数据信息
     * @return array [] 结果信息
     * @author 陈河源
     */
    public function editGoodTag($param)
    {
        $tag = []; //标签信息
		$max_ids = ''; //超过标签上限的商品

        $tag['tag_id'] = $param['id'];
		$tag['tag_name'] = $param['tag_name'];
        $tag['start_time'] = strtotime($param['start_time']);
        $tag['end_time'] = strtotime($param['end_time']);
        $tag['product_ids'] = str_replace(['，',' ','|'],',',$param['product_ids']);
        $tag['subject_ids'] = isset($param['subject_ids']) ? $param['subject_ids'] : '0';
        $tag['update_time'] = time();

        if(!($param['id'])){
            return $this->arrayData('Tag_id不能为空！', '', '', 'error');
        }
		if(!($param['tag_name'])){
            return $this->arrayData('标签名称不能为空！', '', '', 'error');
        }
        if(!($param['start_time'])||!($param['start_time'])){
            return $this->arrayData('开始时间或结束时间不能为空！', '', '', 'error');
        }
        if(!($param['product_ids'])){
            return $this->arrayData('商品ID不能为空！', '', '', 'error');
        }
		if(!($param['subject_ids'])){
            return $this->arrayData('主题不能为空！', '', '', 'error');
        }
		//检查商品id
		$wrong_product_ids = $this -> checkWrongGoodIds($tag['product_ids']);
		if(!empty($wrong_product_ids)){
			return $this->arrayData('商品ID：'.$wrong_product_ids.'出现错误！', '', '', 'error');
        }
		//获取所有未过期商品标签
		$goodsTag = $this->goodsTag();
		$goodids = array_filter(explode(',',$param['product_ids']));
		
        //单商品的标签不能超过6个
		foreach ($goodids as $id) {
			$num = 0;
			foreach($goodsTag as $k => $v){
				$ids = array_filter(explode(',',$v['product_ids']));
				if(in_array($id,$ids)){
					$num++;
				}
			}
			if($num>=$this->tag_max){
				$max_ids .= $id.' ';
			}
		}
		if(!empty($max_ids)){
			return $this->arrayData('商品ID：'.$max_ids.'已满上限，不能再添！', '', '', 'error');
        }
		
		// 开启事务
        $this->dbWrite->begin();
        $columStr1 = $this->jointString($tag, array('tag_id'));
        $whereStr = 'tag_id = :tag_id:';
        //更新操作
        $tagResult = BaseData::getInstance()->update($columStr1,'\Shop\Models\BaiyangSubjectTag',$tag,$whereStr);
        if(empty($tagResult)){
            $this->dbWrite->rollback();
            return $this->arrayData('编辑失败！', '', '', 'error');
        }
        $this->dbWrite->commit();
        //清缓存
        $this->clearSubjectCache($param['subject_ids']);
        return $this->arrayData('编辑成功！', $param['url']);

    }

    /**
     * @desc 添加/编辑专题商品标签
     * @param array $param 数据信息
     * @return array [] 结果信息
     * @author 陈河源
     */
    public function addGoodTag($param)
    {
        $tag = []; //标签信息

        $tag['type'] = $param['type'];
        $tag['tag_name'] = $param['tag_name'];
        $tag['start_time'] = strtotime($param['start_time']);
        $tag['end_time'] = strtotime($param['end_time']);
        $tag['product_ids'] = str_replace(['，',' ','|'],',',$param['product_ids']);
        $tag['subject_ids'] = isset($param['subject_ids']) ? $param['subject_ids'] : '0';
        $tag['status'] = isset($param['status']) ? $param['status'] : 1;
        $tag['create_time'] = time();
        $tag['update_time'] = time();

        if(!($param['tag_name'])){
            return $this->arrayData('标签名称不能为空！', '', '', 'error');
        }
        if(!($param['start_time'])||!($param['start_time'])){
            return $this->arrayData('开始时间或结束时间不能为空！', '', '', 'error');
        }
        if(!($param['product_ids'])){
            return $this->arrayData('商品ID不能为空！', '', '', 'error');
        }
		if(!($param['subject_ids'])){
            return $this->arrayData('主题不能为空！', '', '', 'error');
        }
        //检查商品id
		$wrong_product_ids = $this -> checkWrongGoodIds($tag['product_ids']);
		if(!empty($wrong_product_ids)){
			return $this->arrayData('商品ID：'.$wrong_product_ids.'出现错误！', '', '', 'error');
        }
		
		//获取所有未过期商品标签
		$goodsTag = $this->goodsTag();
		$goodids = array_filter(explode(',',$param['product_ids']));
		
        //单商品的标签不能超过6个
        $max_ids = '';
		foreach ($goodids as $id) {
			$num = 0;
			foreach($goodsTag as $k => $v){
				$ids = array_filter(explode(',',$v['product_ids']));
				if(in_array($id,$ids)){
					$num++;
				}
			}
			if($num>=$this->tag_max){
				$max_ids .= $id.' ';
			}
		}
		if(!empty($max_ids)){
			return $this->arrayData('商品ID：'.$max_ids.'已满上限，不能再添！', '', '', 'error');
        }
		
        // 开启事务
        $this->dbWrite->begin();
        $tagId = BaseData::getInstance()->insert('\Shop\Models\BaiyangSubjectTag',$tag,true);
        if(empty($tagId)){
            $this->dbWrite->rollback();
            return $this->arrayData('添加失败！', '', '', 'error');
        }
        $this->dbWrite->commit();
        //清缓存
        $this->clearSubjectCache($param['subject_ids']);
        return $this->arrayData('添加成功！', 'goodList');

    }
	
	/**
     * @desc 检查商品id
     * @param array $good_ids 商品ID
     * @return string  返回不存在的商品id
     * @author 陈河源
     */
    public function checkWrongGoodIds($good_ids)
    {
        //检查商品id
		$ids_arr = explode(",", $good_ids);
		$info = array_filter(array_map(function ($v) use (&$sign) {
            $result = BaseData::getInstance()->select(
                        "id",
                        "\\Shop\\Models\\BaiyangGoods",
                        ["id" => $v],
                        "id = :id: ")[0];
			if (empty($result)){
				return $v;
            }
        }, $ids_arr));
		if(!empty($info)){
			return implode(',',$info);
        }
		return '';
    }
	
	/**
     * @remark 标签商品页导入商品id
     * @param $array
     * @return array 数组中重复的部分
     * @author 陈河源
     */
	public function importTag($params)
    {
        $fileName = $params['filename'];
        $addLargeTag = $params['tag_id'];
        $result = '';
        set_time_limit(0);
        $fp = fopen($fileName, "r");
        $csvData = array();
        while($data = fgetcsv($fp, 1000))
        {
            $count = count($data);
            for($i = 0; $i < $count; $i++)
            {
                $csvData[$i][] = iconv("gbk",'utf-8',$data[$i]);
            }
        }
        fclose($fp);
        $count = count($csvData[0]);
        if($count > 0){
			//检查是否重复
			$repeatData = array_unique($this->FetchRepeatMemberInArray($count));
			if( $repeatData ) {
				$repeatStr = implode(', ', $repeatData);
				return $this->arrayData('导入失败！存在重复的商品id有 : '. $repeatStr, '', '', 'error');
			}
			//检查是否商品id
			$data = implode(',', array_unique($csvData[0]));
			$wrong_product_ids = $this -> checkWrongGoodIds($data);
			if(!empty($wrong_product_ids)){
				return $this->arrayData('导入失败！错误的商品id有：'.$wrong_product_ids.'', '', '', 'error');
			}
			$result = $data;
			if(!empty($wrong_product_ids)){
				$wrong_ids = explode(',',$wrong_product_ids);
				$result = implode(',',array_diff($csvData[0],$wrong_ids));
			}
		}
        unlink($fileName);
        return $result ? $this->arrayData($result) : $this->arrayData($result, '', '', 'error');
    }
	
	/**
     * @remark 查询数组中重复的值
     * @param $array
     * @return array 数组中重复的部分
     * @author 陈河源
     */
    protected function FetchRepeatMemberInArray($array) {
        $len = count ( $array );
        for($i = 0; $i < $len; $i ++) {
            for($j = $i + 1; $j < $len; $j ++) {
                if ($array [$i] == $array [$j]) {
                    $repeat_arr [] = $array [$i];
                    break;
                }
            }
        }
        return $repeat_arr;
    }
	
	/**
     * @desc 编辑专题价格标签
     * @param array $param 数据信息
     * @return array [] 结果信息
     * @author 陈河源
     */
    public function editPriceTag($param)
    {
        $tag = []; //标签信息

        $tag['tag_id'] = $param['id'];
		$tag['tag_name'] = $param['tag_name'];
        $tag['start_time'] = strtotime($param['start_time']);
        $tag['end_time'] = strtotime($param['end_time']);
        $tag['img_url'] = $param['img_url'];
		$tag['background'] = $param['background'];
        $tag['subject_ids'] = isset($param['subject_ids']) ? $param['subject_ids'] : '0';
        $tag['update_time'] = time();

        if(!($param['id'])){
            return $this->arrayData('Tag_id不能为空！', '', '', 'error');
        }
		if(!($param['tag_name'])){
            return $this->arrayData('标签名称不能为空！', '', '', 'error');
        }
        if(!($param['start_time'])||!($param['start_time'])){
            return $this->arrayData('开始时间或结束时间不能为空！', '', '', 'error');
        }
        if(!($param['subject_ids'])){
            return $this->arrayData('专题不能为空！', '', '', 'error');
        }
		
		// 开启事务
        $this->dbWrite->begin();
        $columStr1 = $this->jointString($tag, array('tag_id'));
        $whereStr = 'tag_id = :tag_id:';
        //更新操作
        $tagResult = BaseData::getInstance()->update($columStr1,'\Shop\Models\BaiyangSubjectTag',$tag,$whereStr);
        if(empty($tagResult)){
            $this->dbWrite->rollback();
            return $this->arrayData('编辑失败！', '', '', 'error');
        }
        $this->dbWrite->commit();
		//清缓存
        $this->clearSubjectCache($param['subject_ids']);
        return $this->arrayData('编辑成功！', $param['url']);

    }

    /**
     * @desc 添加/编辑专题价格标签
     * @param array $param 数据信息
     * @return array [] 结果信息
     * @author 陈河源
     */
    public function addPriceTag($param)
    {
        $tag = []; //标签信息

        $tag['type'] = $param['type'];
        $tag['tag_name'] = $param['tag_name'];
        $tag['start_time'] = strtotime($param['start_time']);
        $tag['end_time'] = strtotime($param['end_time']);
        $tag['img_url'] = $param['img_url'];
		$tag['background'] = $param['background'];
        $tag['subject_ids'] = isset($param['subject_ids']) ? $param['subject_ids'] : '0';
        $tag['status'] = isset($param['status']) ? $param['status'] : 1;
        $tag['create_time'] = time();
        $tag['update_time'] = time();

        if(!($param['tag_name'])){
            return $this->arrayData('标签名称不能为空！', '', '', 'error');
        }
        if(!($param['start_time'])||!($param['start_time'])){
            return $this->arrayData('开始时间或结束时间不能为空！', '', '', 'error');
        }
		if(!($param['subject_ids'])){
            return $this->arrayData('专题不能为空！', '', '', 'error');
        }
        //添加新组件
        // 开启事务
        $this->dbWrite->begin();
        $tagId = BaseData::getInstance()->insert('\Shop\Models\BaiyangSubjectTag',$tag,true);
        if(empty($tagId)){
            $this->dbWrite->rollback();
            return $this->arrayData('添加失败！', '', '', 'error');
        }
        $this->dbWrite->commit();
		//清缓存
        $this->clearSubjectCache($param['subject_ids']);
		$url = isset($param['url']) ? $param['url'] : priceList;
        return $this->arrayData('添加成功！', $url);

    }
	
	/**
     * @desc 变更标签的状态
     * @param int $tag_id 组件id
     * @return array [] 结果信息
     * @author 陈河源
     */
    public function updateTagStatus($tag_id)
    {
        if(empty($tag_id)){
			return $this->arrayData('参数错误！', '', '', 'error');
		}
		$table = '\Shop\Models\BaiyangSubjectTag';
        $selections = ' status,end_time,product_ids,subject_ids ';
        $conditions = [
            'tag_id' => $tag_id
        ];
        $whereStr = 'tag_id = :tag_id:';
        $tag = BaseData::getInstance()->select($selections,$table,$conditions,$whereStr);

        //过期标签不更新
		if(isset($tag[0]['end_time'])){
			if($tag[0]['end_time'] < time()){
				return $this->arrayData('失败，标签已过期！', '', '', 'error');
			}
		}
		
		if(isset($tag[0]['product_ids'])){
			//获取所有未过期商品标签
			$goodsTag = $this->goodsTag();
			$goodids = array_filter(explode(',',$tag[0]['product_ids']));
			
			//单商品的标签不能超过6个
            $max_ids = '';
			foreach ($goodids as $id) {
				$num = 0;
				foreach($goodsTag as $k => $v){
					$ids = array_filter(explode(',',$v['product_ids']));
					if(in_array($id,$ids)){
						$num++;
					}
				}
				if($num>=$this->tag_max){
					$max_ids .= $id.' ';
				}
			}
			if(!empty($max_ids)){
				return $this->arrayData('存在商品ID：'.$max_ids.'已满6个商品标签上限，不能更新！', '', '', 'error');
			}
		}
		
		$updateConditions = [];
        if(!empty($tag)){
            $tag = $tag[0];
            $updateConditions['tag_id'] = $tag_id;
            $updateConditions['status'] = ($tag['status'] > 0) ? 0 : 1;
            $result = BaseData::getInstance()->update('status = :status:',$table,$updateConditions,'tag_id = :tag_id:');
            //清缓存
            $this->clearSubjectCache($tag['subject_ids']);
            if(!empty($result)){
                return $this->arrayData('更改成功');
            }
        }
        return $this->arrayData('操作失败,请肖后再试!', '', '', 'error');
    }
	
	/**
     * @desc 根据id获取标签详情信息
     * @param int $tagId 组件id
     * @return array|bool $promotionDetail|false 详情信息
     * @author 陈河源
     */
    public function getTagById($tagId)
    {
        $table = '\Shop\Models\BaiyangSubjectTag';
        $selections = 'tag_id,tag_name,start_time,end_time,background,product_ids,subject_ids,img_url';
        $conditions = [
            'tag_id' => $tagId,
        ];
        $whereStr = 'tag_id = :tag_id:';
        $tagDetail = BaseData::getInstance()->select($selections,$table,$conditions,$whereStr);
        if(!empty($tagDetail)){
            //组件
			$componentDetailId = [
                'tag_id'     => trim($tagDetail[0]['tag_id']),
                'tag_name'   => trim($tagDetail[0]['tag_name']),
                'start_time'            => date('Y/m/d H:i',trim($tagDetail[0]['start_time'])),
                'end_time'       => date('Y/m/d H:i',trim($tagDetail[0]['end_time'])),
                'background'        => trim($tagDetail[0]['background']),
                'product_ids' => trim($tagDetail[0]['product_ids']),
				'subject_ids' => trim($tagDetail[0]['subject_ids']),
				'product_ids_list' => explode(',',trim($tagDetail[0]['product_ids'])),
				'subject_ids_list' => explode(',',trim($tagDetail[0]['subject_ids'])),
				'img_url' => trim($tagDetail[0]['img_url']),
            ];
            return $componentDetailId;
        }
        return false;
    }

    /**
     * @desc 专题商品标签列表
     * @param array $param 数据信息
     * @return array [] 结果信息
     * @author 陈河源
     */
    public function allsubjectList($param='')
    {
        //查询的基本条件
        $table = "\\Shop\\Models\\BaiyangSubject";
        $join='';
        $selections = ' id,title,update_time,status,channel ';
        $conditions = [];
        $whereStr = '';

        //输入的查询条件
        if(!empty($param)){
            $param = str_replace('，',',',$param);
            if(is_numeric($param)||strpos($param,',')){
                $whereStr .= ' id IN ('.$param.') ';
            }else{
                $whereStr .= ' title like :input: ';
                $conditions['input'] = '%'.$param.'%';
            }

        }
        $result = BaseData::getInstance()->select($selections,$table,$conditions,$whereStr,$join);
        if(!empty($result)){
            foreach($result as $k => $v){
                //$result[$k]['status'] = ((int)$v['status'] > 0) ? '已发布' : '未发布';
                $result[$k]['channel'] = ((int)$v['channel'] == 95) ? 'PC端' : '移动端';
                $result[$k]['update_time'] = date('Y-m-d', $v['update_time']);
				$result[$k]['tag_name'] = '';
				$result[$k]['product_ids'] = '';
				switch ($v['status'])
				{
				case 0:
				  $result[$k]['status'] = '未发布';
				  break;  
				case 1:
				  $result[$k]['status'] = '已发布';
				  break;
				case 2:
				  $result[$k]['status'] = '已停用';
				  break;
				}
				
				$table = "\\Shop\\Models\\BaiyangSubjectTag";
				$join='';
				$selections = ' tag_name,product_ids ';
				$conditions = ['id' => $v['id']];
				$whereStrm = "type = 1 and FIND_IN_SET(:id:,subject_ids)";
				$subject_ids = BaseData::getInstance()->select($selections,$table,$conditions,$whereStrm,$join);
				if(!empty($subject_ids)){
					foreach($subject_ids as $key => $value){
						if($value['product_ids'] > 0){
							$result[$k]['tag_name'] .= $value['tag_name'].' ';
							$result[$k]['product_ids'] .= $value['product_ids'].' ';
						}
					}
				}
				
            }
            return $result;
        }else{
            return '0';
        }
    }
	
	/**
     * @desc 获取专题活动列表信息
     * @param array $param 查询条件
     * @return array [] 结果信息
     * @author 陈河源
     */
    public function getallsubjectList($param)
    {
        //查询的基本条件
        $table = "\\Shop\\Models\\BaiyangSubject";
        $join='';
        $selections = ' id,title,update_time,status,channel ';
		$conditions = [];
		$whereStr = ' id > 0 ';
		if(isset($param['param']['channel']) && !empty($param['param']['channel'])){
            $conditions = [
                'channel' => $param['param']['channel']
            ];
            $whereStr = 'channel = (:channel:) ';
        }

        //输入的查询条件
        if(!empty($param['param']['title'])){
            $whereStr .= ' AND title like :title:';
            $conditions['title'] = '%'.$param['param']['title'].'%';
        }
        if(!empty($param['param']['start'])&&!empty($param['param']['end'])){
            $whereStr .= ' AND update_time between  :start: and :end: ';
            $conditions['start'] = strtotime($param['param']['start']);
            $conditions['end'] = strtotime($param['param']['end']);
        }
        if(isset($param['param']['status'])){
            $conditions['status'] = $param['param']['status'];
            $whereStr .= ' AND status = :status:';
        }
		//当前标签id
		if(isset($param['param']['id'])){
			$subjectids = $this->getTagById($param['param']['id']);
        }
        //总记录数
        $counts = BaseData::getInstance()->count($table, $conditions, $whereStr);
        if(empty($counts)){
            return ['res' => 'error','list' => '','voltValue' => $param['param']];
        }
        //分页
        $pages['page'] = isset($param['page']) ? $param['page'] : 1;//当前页
        $pages['counts'] = $counts;
        $pages['psize'] = isset($param['psize'])? $param['psize'] : 20;
        $pages['url'] = $param['url'];
        $pages['url_back'] = isset($param['url_back']) ? $param['url_back'] : '';
        $pages['home_page'] = isset($param['home_page']) ? $param['home_page'] : '';
        $pages['size'] = isset($param['size'])?$param['size']:5;
        $page = $this->page->pageDetail($pages);
        $whereStr .= ' ORDER BY update_time DESC LIMIT '.$page['record'].','.$page['psize'];
        $result = BaseData::getInstance()->select($selections,$table,$conditions,$whereStr,$join);
        if(empty($result)){
            return ['res' => 'error'];
        }
		
        foreach ($result as $key => $val) {
            $result[$key]['update_time'] = date('Y-m-d', $val['update_time']);
            $result[$key]['id'] = ($val['id'] > 0) ? $val['id'] : '';
            $result[$key]['title'] = $val['title'];
            //$result[$key]['link'] = $val['link'];
            //$result[$key]['status'] = ($val['status'] == 1) ? '已发布' : '未发布';
			$result[$key]['channel'] = ($val['channel'] == 95) ? 'PC端' : '移动端';
			$result[$key]['tag_name'] = '';
			$result[$key]['product_ids'] = '';
			$result[$key]['checked'] = '';
			switch ($val['status'])
			{
			case 0:
			  $result[$key]['status'] = '未发布';
			  break;  
			case 1:
			  $result[$key]['status'] = '已发布';
			  break;
			case 2:
			  $result[$key]['status'] = '已停用';
			  break;
			}
			if(isset($subjectids['subject_ids_list'])){
				$result[$key]['checked'] = in_array($val['id'],$subjectids['subject_ids_list']) ? 'checked' : '';
			}
				
				//获取标签信息
				$table = "\\Shop\\Models\\BaiyangSubjectTag";
				$join='';
				$selectionsm = ' type,tag_name ';
				$conditionsm = ['id' => $val['id']];
				$whereStrm = " FIND_IN_SET(:id:,subject_ids) ";
				$subject_ids = BaseData::getInstance()->select($selectionsm,$table,$conditionsm,$whereStrm,$join);
				if(!empty($subject_ids)){
					foreach($subject_ids as $k => $value){
						if(isset($param['param']['type'])&&$param['param']['type']==$value['type']){
							$result[$key]['tag_name'] .= $value['tag_name'].' ';
						}
					}
				}
        }
		
		//独立添一个分页样式
		$page_post_num = array(
		"<a onclick='tiaopost(".($pages['page']>1 ? $pages['page']-1 : 1).")'>上一页</a>",
		"<a onclick='tiaopost(1)'>首页</a>",
		);
		$max = $pages['counts']/$pages['psize'];
		//计算出开始页码和结束页码
        $sNumber = 1;
        $eNumber = 1;
        if (ceil($max) <= $pages['size'] ) {
            $sNumber = 1;
			$eNumber = ceil($max);
        } else {
            $mNumber = $pages['size']  % 2 == 0 ? $pages['size']  / 2 : ($pages['size']  - 1) / 2 + 1;
            $sNumber = $pages['page'] - $mNumber + 1;
            if ($sNumber < 1)
                $sNumber = 1;
            $eNumber = $sNumber + $pages['size']  - 1;
            if (ceil($max)+ 1 <= $eNumber) {
                $eNumber = ceil($max);
            }
        }
        $htmlString = '';
        for ($p = $sNumber; $p <= $eNumber; $p++) {
            if ($p == $pages['page']) {
                $htmlString.='<strong class="selected">' . $p . '</strong>'."\r\n";
            } else {
                $htmlString .= "<a onclick='tiaopost(".($p).")' >" . $p . "</a>"."\r\n";
            }
        }
		$page_post_num[] = $htmlString;
		
		$page_post_num[] = "<a onclick='tiaopost(".ceil($max).")'>末页</a>";
		$page_post_num[] = "<a onclick='tiaopost(".($pages['page']<ceil($max) ? $pages['page']+1 : $pages['page']).")'>下一页</a>";
		$page_post_num[] = '<span  class="span">共'.ceil($max).'页</span>';
		
		$page_post = implode(' ',$page_post_num);
		$page_html = '<div class="page">' .$page_post.'</div><link rel="stylesheet" href="http://'.$this->config->domain->static.'/assets/css/page.css" />';
		
        return ['res'  => 'succcess', 'list' => $result, 'page' => $page_html, 'voltValue' => $param['param']];
    }

    /**
     * @desc 组件列表
     * @param array $param 数据信息
     * @return array [] 结果信息
     * @author 陈河源
     */
    public function widgetList($param='')
    {
        //查询的基本条件
        $table = "\\Shop\\Models\\BaiyangSubjectComponent";
        $join='';
        $selections = ' component_id,component_name,status,html_value,css_value,javascript_value ';
        //渠道
        if(!empty($param)){
            $conditions = [
                'channel' => $param
            ];
            $whereStr = 'channel = :channel: AND status = 1 ';
        }
        $whereStr .= ' ORDER BY component_id DESC';
        $result = BaseData::getInstance()->select($selections,$table,$conditions,$whereStr,$join);
        if(empty($result)){
            return ['res' => 'error'];
        }
        foreach ($result as $key => $val) {
            $result[$key]['component_id'] = ($val['component_id'] > 0) ? $val['component_id'] : '';
            $result[$key]['component_name'] = trim($val['component_name']);
            $result[$key]['html_value'] = html_entity_decode($val['html_value']);
            $result[$key]['css_value'] = html_entity_decode($val['css_value']);
            $result[$key]['javascript_value'] = html_entity_decode($val['javascript_value']);
            $result[$key]['status'] = $val['status'];
            $result[$key]['field'] = $this->getwidgetfield( $val['component_id']);
        }
        return ['res'  => 'succcess', 'list' => $result];
    }

    /**
     * @desc 组件字段
     * @param array $param 数据信息
     * @return array [] 结果信息
     * @author 陈河源
     */
    public function getwidgetfield($param='')
    {
        //查询的基本条件
        $table = "\\Shop\\Models\\BaiyangSubjectComponentField";
        $join='';
        $selections = ' field_id,field_name,field_label,field_value,field_type,select_value ';
        //渠道
        if( !empty($param)){
            $conditions = [
                'component_id' => $param
            ];
            $whereStr = 'component_id = :component_id: ';
            $result = BaseData::getInstance()->select($selections,$table,$conditions,$whereStr,$join);
            if(empty($result)){
                return [];
            }
            foreach($result as $key => $value){
                if($value['field_type'] != 3){
                    $result[$key]['select_value'] = '';
                }
            }
            return $result;
        }
        return [];
    }
	
	/**
     * @desc 检查是否存在目录
     * @author 陈河源
     */
    public function checkdir($uploadDir)
    {
       //目录不存在则创建
		if(!is_dir($uploadDir.'/subject'))
		{
			mkdir($uploadDir.'/subject', 0777);
		}
		
		if(!is_dir($uploadDir.'/subject/mobile'))
		{
			mkdir($uploadDir.'/subject/mobile', 0777);
		}
		
		if(!is_dir($uploadDir.'/subject/pc'))
		{
			mkdir($uploadDir.'/subject/pc', 0777);
		}
    }
	
	/**
     * 修改SkuAd信息
     * User: 梁伟
     * Date: 2016/9/8
     * Time: 18:27
     */
    public function updateTemplate($param)
    {
        if(!isset($param['contents']) || empty($param['contents'])){
            return $this->arrayData('内容不能为空','','','error');
        }
		if(!isset($param['file']) || empty($param['file'])){
            return $this->arrayData('文件地址不能为空','','','error');
        }
		//更新进模版
		file_put_contents($param['file'],html_entity_decode($param['contents']));
		return $this->arrayData('更新成功！');
    }
	
	/**
     * @desc 获取专题信息
     * @param array $param 主题id
     * @return array [] 结果信息
     * @author 陈河源
     */
    public function getSubject($param)
    {
        if(!isset($param['id']) || empty($param['id'])){
            return $this->arrayData('id不能为空','','','error');
        }
		if(!isset($param['channel']) || empty($param['channel'])){
            return $this->arrayData('channel不能为空','','','error');
        }
		//获取专题详情信息
		$subject = $this ->getSubjectInfo($param);
		if(!empty($subject)){
			//组件列表
			$WidgetList = $this ->WidgetList($param['channel']);
			foreach($subject as $v){
				//模版信息
				$file = ($param['channel'] == 95) ? 'pc.html' : 'mobile.html';
				$fileurl =  APP_PATH.'/static/assets/subjecttpl/'.$file;
				$html = file_get_contents($fileurl);
				$v['share_title'] = empty($v['share_title']) ? $v['title'] :$v['share_title'];
				$html = str_replace("{{title}}",$v['title'],$html);
				$html = str_replace("{{description}}",$v['description'],$html);
				$html = str_replace("{{keywords}}",$v['keywords'],$html);
				$html = str_replace("{{share_title}}",$v['share_title'],$html);
				$html = str_replace("{{shareUrl}}",$v['shareUrl'],$html);
				$html = str_replace("{{share}}",$v['share_img'],$html);
				$html = str_replace("{{background}}",$v['background'],$html);
                $config_domain = ($param['channel'] == 95)
                    ?  $this->config['pc_url'][$this->config['environment']]
                    : $this->config['wap_base_url'][$this->config['environment']];
                $html = str_replace("{{config_domain}}",$config_domain,$html);
				$meta =  array(
                    'title' => $v['title'],
                    'description' => $v['description'],
                    'keywords' => $v['keywords'],
                    'share_title'=>$v['share_title'],
                    'shareUrl'=>$v['shareUrl'],
                    'share'=>$v['share_img'],
                    'config_domain' => $config_domain,
                );
				//旧组件信息
				$component_detail = html_entity_decode(json_decode($v['component_detail'],true));
			}
			return array('WidgetList' => $WidgetList['list'],'oldWidget' => $component_detail,'meta' => $meta,'html' => $html);
		}
        return [];
    }
	
	/**
     * @desc 获取专题详情信息
     * @param array $param 参数
     * @return array [] 结果信息
     * @author 陈河源
     */
    public function getSubjectInfo($param)
    {
        if(!isset($param['id']) || empty($param['id'])){
            return $this->arrayData('参数有误','','','error');
        }
		//查询的基本条件
        $table = "\\Shop\\Models\\BaiyangSubject";
        $join='';
        $selections = ' * ';
		$conditions = [
            'id' => $param['id']
        ];
        $whereStr = ' id = :id: ';
        $meta = BaseData::getInstance()->select($selections,$table,$conditions,$whereStr,$join);
        if(!empty($meta)){
			 return $meta;
		}
        return [];
    }
	
	/**
     * @desc 获取专题详情信息
     * @param array $param 参数
     * @return array [] 结果信息
     * @author 陈河源
     */
    public function getSubjectId()
    {
        //查询的基本条件
        $table = "\\Shop\\Models\\BaiyangSubject";
        $join='';
        $selections = ' id ';
		$conditions = [];
        $whereStr = ' id = (select max(id) from \\Shop\\Models\\BaiyangSubject)';
        $ids = BaseData::getInstance()->select($selections,$table,$conditions,$whereStr,$join);
        if(!empty($ids)){
			 foreach($ids as $val){
				 return $val['id'];
			 }
		}
		return '';
    }
	
	/**
     * @desc 编辑专题
     * @param array $param 专题数据信息
     * @return array [] 结果信息
     * @author 陈河源
     */
    public function updateSubject($param)
    {
        if(!($param['id'])){
            return $this->arrayData('专题id不能为空！', '', '', 'error');
        }
        if(!($param['title'])){
            return $this->arrayData('标题不能为空！', '', '', 'error');
        }
        if(!($param['keywords'])){
            return $this->arrayData('keywords不能为空！', '', '', 'error');
        }
		if(!($param['description'])){
            return $this->arrayData('description不能为空！', '', '', 'error');
        }
		if(mb_strlen($param['title'],'UTF8') > 15){
            return $this->arrayData('标题超过15字！', '', '', 'error');
        }
		
        $subject = []; //专题信息
		$array = []; //log

        $subject['id'] = $param['id'];
        $subject['title'] = $param['title'];
        $subject['keywords'] = $param['keywords'];
        $subject['description'] = $param['description'];
        $subject['share_title'] = '';
        $subject['shareUrl'] = $param['shareUrl'];
        $subject['share_img'] = $param['share_img'];
        $subject['background'] = $param['background'];
        $subject['update_time'] = time();
		if(isset($param['share_title'])){
			$subject['share_title'] = !empty($param['share_title']) ? $param['share_title'] : $param['title'];
		}
		//记录log
		$old_info = $this->getSubjectInfo($param);
		$admin_id = $this->session->get('admin_id');
		$admin_account = $this->session->get('admin_account');
		foreach($param as $kay => $val){
			foreach($old_info[0] as $k => $v){
				if($kay == $k && $val != $v){
					$array = array('subject_id' => $param['id'], 'user_id' =>$admin_id, 'admin_account' => $admin_account, 'field_name' => $kay, 'old_value' => $v, 'new_value' => $val, 'channel' =>$param['channel'], 'add_time' => time());
					$this->insertlog($array);
				}
			}
		}
		
        // 开启事务
        $this->dbWrite->begin();
		$columStr1 = $this->jointString($subject, array('id'));
        $whereStr = 'id = :id:';
        //更新操作
        $Result = BaseData::getInstance()->update($columStr1,'\Shop\Models\BaiyangSubject',$subject,$whereStr);
        if(empty($Result)){
            $this->dbWrite->rollback();
            return $this->arrayData('操作失败！', '', '', 'error');
        }
        $this->dbWrite->commit();
		
        $url = isset($param['url']) ? $param['url'].'?id='.$param['id'] : '';
        return $this->arrayData('已设置，请下一步！', $url);
    }
	
	/**
     * @desc 查看商品id的标签
     * @param array $param 查询条件
     * @return array [] 结果信息
     * @author 陈河源
     */
    public function viewgoodtag($param)
    {
		if(empty($param)){
			return $this->arrayData('内容不能为空', '', '', 'error');
        }
		//检查是否商品id
		$param = str_replace(['，',' ','|'],',',$param);
		$wrong_product_ids = $this -> checkWrongGoodIds($param);
		if(!empty($wrong_product_ids)){
			return $this->arrayData('以上商品id有误：'.$wrong_product_ids.'', '', '', 'error');
		}
		$result = [];
		
		//所有未过期商品标签
		$alltag = $this->goodsTag();
		$goodids = array_filter(explode(',',$param));
		
        //商品的专题与标签
		foreach ($goodids as $id) {
			$subject_ids = $channel = $id_title_channel = '';
			//获取专题
			foreach($alltag as $k => $v){
				$ids = array_filter(explode(',',$v['product_ids']));
				if(in_array($id,$ids)){
					//$tag_name .= '<p> '.$v['tag_name'].'</p>';
					$subject_ids .= $v['subject_ids'].',';
				}
			}
			if($subject_ids){
				$subject_ids = trim($subject_ids, ',');
				$subject = $this->getTitleBySubjectIds($subject_ids);
				foreach($subject as $val){
					$channel = ($val['channel'] == 95) ? 'PC端' : '移动端';
					$id_title_channel .= '<p> '.$channel.' 标题：'.$val['title'].'</p>';
				}
				$result[] = array('product_id' => $id,'subject'=>$id_title_channel);
			}
        }
        return $this->arrayData('获取成功', '', $result, 'success');
    }
	
	/**
     * @desc 获取未过期商品标签
     * @return array [] 结果信息
     * @author 陈河源
     */
    public function goodsTag()
    {
		$table = '\Shop\Models\BaiyangSubjectTag';
        $selections = 'tag_name,product_ids,subject_ids';
        $conditions = [
            'end_time' => time(),
        ];
        $whereStr = ' type = 1 and status = 1 and end_time >= :end_time: ';
        $tagDetail = BaseData::getInstance()->select($selections,$table,$conditions,$whereStr);
        if(!empty($tagDetail)){
            return $tagDetail;
        }
        return false;
    }
	
	/**
     * @desc 根据subject_ids获取专题标题
     * @return array [] 结果信息
     * @author 陈河源
     */
    public function getTitleBySubjectIds($subject_ids)
    {
		$table = '\Shop\Models\BaiyangSubject';
        $selections = 'id,title,channel';
        $conditions = [
            'subject_ids' => $subject_ids,
        ];
        $whereStr = " FIND_IN_SET(id,:subject_ids:) ";
        $Subject = BaseData::getInstance()->select($selections,$table,$conditions,$whereStr);
        if(!empty($Subject)){
            return $Subject;
        }
        return false;
    }
	
	/**
     * @desc 复制专题
     * @param array $param 专题数据信息
     * @return array [] 结果信息
     * @author 陈河源
     */
    public function copySubject($param)
    {
        if(!($param['id'])){
            return $this->arrayData('ID不能为空！', '', '', 'error');
        }
        if(!($param['channel'])){
            return $this->arrayData('channel不能为空！', '', '', 'error');
        }
		$prev_subject = $this -> getSubjectInfo($param);
		
		$subject = [];

        $subject['title'] = $prev_subject[0]['title'];
        $subject['keywords'] = !empty($prev_subject[0]['keywords']) ? $prev_subject[0]['keywords'] : $prev_subject[0]['title'];
        $subject['description'] = $prev_subject[0]['description'];
        $subject['share_title'] =  !empty($prev_subject[0]['share_title']) ? $prev_subject[0]['share_title'] : $prev_subject[0]['title'];
        $subject['shareUrl'] = $prev_subject[0]['shareUrl'];
        $subject['share_img'] =  !empty($prev_subject[0]['share_img']) ? $prev_subject[0]['share_img'] : '';
        $subject['background'] = $prev_subject[0]['background'];
        $subject['create_time'] =  time();
        $subject['update_time'] =  time();
        $subject['channel'] =  isset($param['channel']) ? $param['channel'] : 91;
        //$subject['status'] =  $prev_subject[0]['status'];
        $subject['status'] =  0;
        $subject['order'] =  !empty($prev_subject[0]['order']) ? $prev_subject[0]['order'] : 0;
        $subject['link'] =  '';
		$subject['component_detail'] =  !empty($prev_subject[0]['component_detail']) ? $prev_subject[0]['component_detail'] : '';
        
        // 开启事务
        $table = '\Shop\Models\BaiyangSubject';
		$this->dbWrite->begin();
        $promotionId = BaseData::getInstance()->insert($table,$subject,true);
        if(empty($promotionId)){
            $this->dbWrite->rollback();
            return $this->arrayData('添加失败！', '', '', 'error');
        }
        $this->dbWrite->commit();

        /*
         //新复制的专题为未发布状态, 重新编辑后才生成新链接
		if(!empty($prev_subject[0]['link'])){
			$new_id = $this->getSubjectId();
			//生成静态文件
			$html = file_get_contents($subject['link']);
			$file = ($param['channel'] == 95) ? '/subject/pc/subject_id_'.$new_id.'.html' : '/subject/mobile/subject_id_'.$new_id.'.html' ;
			$fileurl = $this->config['subject_file']['dir'].$file ;
			file_put_contents($fileurl,html_entity_decode($html));
			//更新link
			$update['id'] = $new_id;
			$update['link'] = $this->config['subject_file']['dir_url'][$this->config->environment].$file ;

			// 开启事务
			$this->dbWrite->begin();
			$columStr1 = $this->jointString($update, array('id'));
			$whereStr = 'id = :id:';
			//更新操作
			$Result = BaseData::getInstance()->update($columStr1,'\Shop\Models\BaiyangSubject',$update,$whereStr);
			if(empty($Result)){
				$this->dbWrite->rollback();
				return $this->arrayData('保存失败！', '', '', 'error');
			}
			$this->dbWrite->commit();
		}
		*/
		
		$url = "/subjectmobile/list?page=".$param['page'];
        return $this->arrayData('复制成功！', $url);
    }
	
	/**
     * @desc 插入专题log
     * @param array $param 专题数据信息
     * @author 陈河源
     */
    public function insertlog($param)
    {
        if(empty($param)){
            return false;
        }
		
		$table = '\Shop\Models\BaiyangSubjectLog';
		$this->dbWrite->begin();
		$promotionId = BaseData::getInstance()->insert($table,$param,true);
		$this->dbWrite->commit();
    }
	
	/**
     * @desc 获取专题LOG信息
     * @param array $param 查询条件
     * @return array [] 结果信息
     * @author 陈河源
     */
    public function getLogList($param)
    {
        //查询的基本条件
        $table = "\Shop\Models\BaiyangSubjectLog";
        $join='';
        $selections = ' * ';
        if(isset($param['param']['channel']) && !empty($param['param']['channel'])){
            $conditions = [
                'channel' => $param['param']['channel']
            ];
            $whereStr = 'channel = (:channel:) ';
        }

        //输入的查询条件
        if(!empty($param['param']['start'])&&!empty($param['param']['end'])){
            $whereStr .= ' AND add_time between  :start: and :end: ';
            $conditions['start'] = strtotime($param['param']['start']);
            $conditions['end'] = strtotime($param['param']['end']);
        }
        //总记录数
        $counts = BaseData::getInstance()->count($table, $conditions, $whereStr);
        if(empty($counts)){
            return ['res' => 'error','list' => '','voltValue' => $param['param']];
        }
        //分页
        $pages['page'] = isset($param['page']) ? $param['page'] : 1;//当前页
        $pages['counts'] = $counts;
        $pages['psize'] = isset($param['psize'])? $param['psize'] : 15;
        $pages['url'] = $param['url'];
        $pages['url_back'] = isset($param['url_back']) ? $param['url_back'] : '';
        $pages['home_page'] = isset($param['home_page']) ? $param['home_page'] : '';
        $pages['size'] = isset($param['size'])?$param['size']:5;
        $page = $this->page->pageDetail($pages);
        $whereStr .= ' ORDER BY log_id DESC LIMIT '.$page['record'].','.$page['psize'];
        $result = BaseData::getInstance()->select($selections,$table,$conditions,$whereStr,$join);
        if(empty($result)){
            return ['res' => 'error'];
        }
		foreach ($result as $key => $val) {
            $result[$key]['add_time'] = date('Y-m-d H:i:s', $val['add_time']);
        }
        return ['res'  => 'succcess', 'list' => $result, 'page' => $page['page'], 'voltValue' => $param['param']];
    }
	
	/**
     * @desc 删除组件
     * @param array $param 查询条件
     * @return array [] 结果信息
     * @author 陈河源
     */
    public function deleWidget($param)
    {
		if(!($param['id'])){
            return $this->arrayData('ID不能为空！', '', '', 'error');
        }
        if($param['is_del'] != true){
            return $this->arrayData('is_del不能为空！', '', '', 'error');
        }
		//$table= '\Shop\Models\BaiyangSubjectComponent';
	    $conditions = ['component_id'=>$param['id']];
        if($param['id']&&$param['is_del']){
			// 开启事务
			$this->dbWrite->begin();
			$res = BaseData::getInstance()->delete('\Shop\Models\BaiyangSubjectComponent',$conditions," component_id= :component_id: "); 
			$res2 = BaseData::getInstance()->delete('\Shop\Models\BaiyangSubjectComponentField',$conditions," component_id= :component_id: "); 
			if(empty($res)){
				$this->dbWrite->rollback();
				return $this->arrayData('删除失败！', '', '', 'error');
			}
			if(empty($res2)){
				$this->dbWrite->rollback();
				return $this->arrayData('删除失败！', '', '', 'error');
			}
			$this->dbWrite->commit();
			return $this->arrayData('删除成功！'); 
        }else{
            return $this->arrayData('删除失败！', '', '', 'error');
        } 
		
    }

    /**
     * 清除专题对应缓存
     * @param $subjectId string | array 专题ID
     * @return bool
     * @author CSL 2017-12-11
     */
    public function clearSubjectCache($subjectId)
    {
        if (!$subjectId || (!is_array($subjectId) && !is_string($subjectId))) {
            return false;
        }
        if (is_array($subjectId)) {
            $subjectId = implode(',', $subjectId);
            $subjectId = explode(',', $subjectId);
        } elseif (is_string($subjectId)) {
            $subjectId = explode(',', $subjectId);
        }
        foreach ($subjectId as $id) {
            $this->RedisCache->delete('tag_subjectid_'.$id);
        }
        return true;
    }
}
