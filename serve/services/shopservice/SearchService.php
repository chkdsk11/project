<?php
/**
 * Created by PhpStorm.
 * User: lw
 * Date: 2016/8/16
 * Time: 15:50
 * @Explain:    使用redis库8
 * @Explain：    缓存数据,键：SkuAd_id_5     一个skuad信息
 */

namespace Shop\Services;
use Shop\Datas\BaseData;

class SearchService extends BaseService
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance=null;

    /**
     * 获取热销
     */
    public function getHotLinst($param)
    {
        $where = 'WHERE 1 ';
        if (isset($param['keywords']) && $param['keywords']) {
            $where .= "AND keywords LIKE '%{$param['keywords']}%' ";
        }
        $startCount =isset($param['startCount']) ?(int)$param['startCount']:0;
        $endCount = (isset($param['endCount']) && $param['endCount']>0)?(int)$param['endCount']:2147483647;
        if ($startCount && $endCount) {
            $where .= "AND (min_res BETWEEN {$startCount} AND {$endCount}) ";
        } elseif ($startCount && !$endCount) {
            $where .= "AND min_res >= {$startCount} ";
        } elseif (!$startCount && $endCount) {
            $where .= "AND min_res <= {$endCount} ";
        }
        $param['dateArray'] = isset($param['dateAt']) ? explode('~',$param['dateAt']): array(date('Y-m-d',strtotime("-1 day")),date('Y-m-d',strtotime("-1 day")));
        $startDateAt = $param['dateArray'] [0];
        $endDateAt = $param['dateArray'] [1];
        if ($startDateAt && $endDateAt) {
            $where .= "AND (at BETWEEN '{$startDateAt}' AND '{$endDateAt}') ";
        }
        if (isset($param['platformId']) && $param['platformId']) {
            $where .= "AND platform_id = '{$param['platformId']}' ";
        }
         $where .=" GROUP BY keywords,platform_id";
        $pageNum = isset($param['page'])?$param['page'] : '1';
        $pageSize = isset($param['psize'])?$param['psize'] : '15';
        $param['export'] = isset($param['export']) ? $param['export'] :0;
        $orderBy = '';
        if(isset($param['act']) && $param['act']){
            switch ($param['act']){
                case 1:
                    $orderBy = 'ORDER BY count ASC ';
                    break;
                case 2:
                    $orderBy = 'ORDER BY count DESC ';
                    break;
                case 3:
                    $orderBy = 'ORDER BY min_res ASC ';
                    break;
                case 4:
                    $orderBy = 'ORDER BY min_res DESC ';
                    break;
            }
        }

        if($param['export']==1){
            $data['pageSize'] = 1000;
        }
        $baseData = BaseData::getInstance();
        $count = $baseData->countData([
            'table' => 'Shop\Models\BaiyangHistoricalOrigin',
            'where' => $where,
        ]);
        $pages['page'] = $pageNum;//当前页
        $pages['psize'] = $pageSize;
        $pages['counts'] = $count;
        $pages['url']    = $param['url'];
        $pages['isShow'] = true;
        $page = $this->page->pageDetail($pages);
        $column = 'keywords, platform_id as platform_name, sum(count) as count, sum(min_res) as min_res, sum(max_res) as max_res  ';
        $res = $baseData->getData([
            'column' => $column,
            'table' => 'Shop\Models\BaiyangHistoricalOrigin',
            'where' => $where,
            'order' => $orderBy,
            'limit' => "LIMIT {$page['record']},{$page['psize']}"
        ]);
        $list = array();
        if($res){
            foreach($res as $k=>$v){
                $list[$k] = $v;
                $list[$k]['isOn'] = $this->ikWord(['word'=>$v['keywords']]);
            }
        }
        if($param['export']==1){
            $this->view->disable();
            $array = array();
            foreach($list as $k=>$v){
                $array[$k] = array(
                    $v['keywords'],
                    $v['platform_name'],
                    $v['count'],
                    $v['min_res'],
                    $v['max_res'],
                    $v['isOn']?'是':'否',
                );
            }
            $page_tmp = ceil($res['total']/$data['pageSize']);
            if($page_tmp > 1){
                for($i=2;$i<=$page_tmp;$i++){
                    $data['pageNum'] = $i;
                    $res = $this->getHotLinst($data);
                    foreach($res['rows'] as $k=>$v){
                        $list[$k] = $v;
                        $list[$k]['isOn'] = $this->ikWord(['word'=>$v['keywords']]);
                    }
                }
            }
            $name = 'keywords'.date('YmdHis');
            $this->excel->exportExcel(['热词名称','平台名称','搜索次数','搜索的最小商品数','搜索的最大商品数','是否加入词库'],$array,$name,'keywords','xlsx');
        }
        return [
            'list' => $list,
            'page' => $page['page']
        ];
        /*$requestData = http_build_query($param);
        $esUrl = $this->config['domain']['updateEsSearch'].'pces/histories.do';
        $res = $this->curl->sendPost($esUrl, $requestData);
        $res = json_decode($res,true);
        return $res;*/
    }

    /** ,
     * 添加到词库中
     */
    public function addWord($param)
    {
        if(!isset($param['word']) || empty($param['word'])){
            return $this->arrayData('参数错误','','','error');
        }
        $requestData = http_build_query(['word'=>$param['word']]);
        $esUrl = $this->config['domain']['updateEsSearch'].'pces/addIkWord.do';
        $res = $this->curl->sendPost($esUrl, $requestData);
        $res = json_decode($res,true);
        if($res['code'] == 200 && $res['success'] == '成功'){
            return $this->arrayData('"'.$param['word'].'"已成功加入词库,有1分钟延迟！','','','success');
        }else{
            return $this->arrayData('"'.$param['word'].'"加入词库失败!','','','error');
        }
    }

    /**
     * 从词库中去除
     */
    public function removeWord($param)
    {
        if(!isset($param['word']) || empty($param['word'])){
            return $this->arrayData('参数错误','','','error');
        }
        $requestData = http_build_query(['word'=>$param['word']]);
        $esUrl = $this->config['domain']['updateEsSearch'].'pces/addStopWord.do';
        $res = $this->curl->sendPost($esUrl, $requestData);
        $res = json_decode($res,true);
        if($res['code'] == 200 && $res['success'] == '成功'){
            return $this->arrayData('"'.$param['word'].'"已成功撤销加入词库,有1分钟延迟！','','','success');
        }else{
            return $this->arrayData('"'.$param['word'].'"撤销加入词库失败!','','','error');
        }
    }

    /**
     * 添加到黑名单
     */
    public function appendToBlacklist($param)
    {
        if(!isset($param['word']) || empty($param['word'])){
            return $this->arrayData('参数错误','','','error');
        }
        $requestData = http_build_query(['keywords'=>$param['word']]);
        $esUrl = $this->config['domain']['updateEsSearch'].'pces/appendToBlacklist.do';
//        echo $esUrl.'?'.$requestData;die;
        $res = $this->curl->sendPost($esUrl, $requestData);
        $res = json_decode($res,true);
        if($res['code'] == 200 && $res['msg'] == '成功'){
            return $this->arrayData('"'.$param['word'].'"已成功加入黑名单!','','','success');
        }else{
            return $this->arrayData('"'.$param['word'].'"加入黑名单失败!','','','error');
        }
    }

    /**
     * 判断词库中是否存在
     */
    public function ikWord($param)
    {
        $requestData = http_build_query(['word'=>$param['word']]);
        $esUrl = $this->config['domain']['updateEsSearch'].'pces/ikWord.do';
        $res = $this->curl->sendPost($esUrl, $requestData);
        $res = json_decode($res,true);
        return $res['type'];
    }

    /**
     * 获取热门搜索关键词
     */
    public function getHotSearch()
    {
        $baseData = BaseData::getInstance();
        $res = $baseData->getData([
            'column' =>'*',
            'table' => 'Shop\Models\BaiyangHotSearch',
            'where' => 'where 1',
        ]);
        foreach($res as $k=>$v){
            $result[$v['platform']] = $v['search_value'];
        }
        return $result;
    }

    /**
     * 修改或添加热门搜索关键词
     */
    public function editHotSearch($param)
    {
        $baseData = BaseData::getInstance();
        $table = 'Shop\Models\BaiyangHotSearch';
        $data = $this->getHotSearch();
        if(!$data){
            $updata['platform'] = 'pc';
            $baseData->insert($table,$updata);
            $updata['platform'] = 'mobile';
            $baseData->insert($table,$updata);
        }
        foreach($param as $k=>$v){
            $param[$k] = preg_replace("/，/" ,',' ,$param[$k]);
            $param[$k] = explode(',',$param[$k]);
            $param[$k] = array_slice($param[$k],0,9);
            $param[$k] = implode(',',$param[$k]);
            $whereStr = "platform='{$k}'";
            $columStr = "search_value='{$param[$k]}'";
            $baseData->update($columStr,$table,[],$whereStr);
        }
        return $this->arrayData('修改成功','','','success');
    }
}
