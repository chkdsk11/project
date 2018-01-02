<?php
/**
 * Created by PhpStorm.
 * User: 杨永坚
 * Date: 2016/8/25
 * Time: 14:38
 */

namespace Shop\Services;
use Shop\Services\BaseService;
use Shop\Datas\BaseData;

class VideoService extends BaseService
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance = null;
    private $table = '\Shop\Models\BaiyangVideo';
    private $videoArr = \Shop\Models\BaiyangVideoEnum::VIDEO_TYPE;
    private $BaseData = null;

    public function __construct()
    {
        $this->BaseData = BaseData::getInstance();
    }

    /**
     * @remark 获取视频
     * @param $param=array() 参数
     * @return array
     * @author 杨永坚
     */
    public function getVideoList($param)
    {
        //查询条件
        $where = '';
        $data = array();
        if(!empty($param['seaData']['video_name'])){
            $data['video_name'] = '%'.$param['seaData']['video_name'].'%';
            $where .= "video_name like :video_name:";
        }
        if(!empty($param['seaData']['status'])){
            $data['status'] = (int)$param['seaData']['status'];
            $where .= !empty($where) ? " and status=:status:" : " status=:status:";
        }
        //总记录数
        $counts = $this->BaseData->count($this->table, $data, $where);
        if($counts <= 0){
            return array('res' => 'success', 'list' => array(), 'page' => '');
        }
        //分页
        $pages['page'] = $param['page'];//当前页
        $pages['counts'] = $counts;
        $pages['url'] = $param['url'];
        $pages['url_back'] = $param['url_back'];
        $pages['home_page'] = $param['home_page'];
        $page = $this->page->pageDetail($pages);

        $selections = '*';
        $where .= empty($where) ? 1 : '';
        $where .= ' order by add_time desc limit '.$page['record'].','.$page['psize'];

        $result = $this->BaseData->select($selections, $this->table, $data, $where);
        if(!empty($result)){
            foreach($result as $k => $v){
                $result[$k]['video_duration'] = $this->timeSecond($v['video_duration']);
                $result[$k]['status'] = array_search($v['status'], \Shop\Models\BaiyangVideoEnum::VIDEO_STATUS);
            }
            return array(
                'res'  => 'success',
                'list' => $result,
                'page' => $page['page']
            );
        }else{
            return ['res' => 'error'];
        }
    }

    /**
     * @remark 检测视频\上传视频
     * @param $video_info=array 视频参数
     * @return mixed
     * @author 杨永坚
     */
    public function addCheckVideo($video_info)
    {

        $file_size = isset($video_info['file_size']) ? intval($video_info['file_size']) : 0 ;
        $uploadtype = isset($video_info['uploadtype']) ? intval($video_info['uploadtype']) : 0 ;
        $uc1 = isset($video_info['uc1']) ? intval($video_info['uc1']) : 0 ;
        $uc2 = isset($video_info['uc2']) ? intval($video_info['uc2']) : 0 ;
        $client_ip = $_SERVER['REMOTE_ADDR'];

        // 若有token，则为断点续传，分发给断点续传接口
        if(isset($video_info['token']) && !empty($video_info['token'])){
            return $this->resume($video_info['token'], $client_ip, $uploadtype);
        }

        // 若为上传初始化，视频名称为必须值
        if(isset($video_info['videoname']) && !empty($video_info['videoname'])){
            return $this->init($video_info['videoname'], $file_size, $client_ip, $uploadtype, $uc1, $uc2);
        }

    }

    /**
     * @remark 添加视频
     * @param $video_info=array 视频参数
     * @return array
     * @author 杨永坚
     */
    public function addVideo($video_info)
    {
        $result = $this->selectOneVideo($video_info['video_id']);
        if($result['code'] == 0 && (!empty($result['data']))){
            $videoConfig = \Shop\Models\BaiyangVideoEnum::VIDEO_CONFIG;
            $userUnique = \Shop\Models\BaiyangVideoEnum::USER_UNIQUE;
            $data['video_id'] = $result['data']['video_id'];
            $data['video_name'] = $result['data']['video_name'];
            $data['video_unique'] = $result['data']['video_unique'];
            $data['video_desc'] = $video_info['video_desc'];
            $data['video_url'] = $videoConfig['video_url'].'?uu='.$userUnique.'&vu='.$result['data']['video_unique'].'&pu='.$videoConfig['video_sign'].'&auto_play='.$videoConfig['auto_play'].'&width='.$videoConfig['width'].'&height='.$videoConfig['height'];
            $data['add_time'] = time();
        }else{
            $data['video_id'] = $video_info['video_id'];
            $data['video_name'] = $video_info['video_name'];
            $data['video_unique'] = $video_info['video_unique'];
            $data['video_desc'] = $video_info['video_desc'];
            $data['add_time'] = time();
        }
        $result = $this->BaseData->insert($this->table, $data);
        return $result ? $this->arrayData('添加成功！', '/video/list', '') : $this->arrayData('添加失败！', '', '', 'error');
    }

    /**
     * @remark 修改视频
     * @param $param=array 参数
     * @return array
     * @author 杨永坚
     */
    public function editVideo($param)
    {
        $params = $param;
        $api = $this->videoArr['update'];
        $params['api'] = $api;
        $finalUrl = $this->handleParam($params, $api);
        $result = json_decode($this->curl->sendPost($finalUrl), true);
        if($result['code'] == 0){
            $videoData = $this->selectOneVideo($params['video_id']);
            if($videoData['code'] == 0){
                $columStr = $this->jointString($param, array('id'));
                $where = 'id=:id:';
                $result = $this->BaseData->update($columStr, $this->table, $param, $where);
                return $result ? $this->arrayData('修改成功！', '/video/list', '') : $this->arrayData('修改失败！', '', '', 'error');
            }else{
                return $this->arrayData('修改数据失败！'. $result['message'], '', '', 'error');
            }
        }else{
            return $this->arrayData('修改失败！'. $result['message'], '', '', 'error');
        }
    }

    /**
     * @remark 删除视频
     * @param $video_id=int 视频id
     * @return array
     * @author 杨永坚
     */
    public function delVideo($video_id)
    {
        $api = $this->videoArr['del'];
        $params['video_id'] = $video_id;
        $params['api'] = $api;
        $finalUrl = $this->handleParam($params, $api);
        $info = json_decode($this->curl->sendPost($finalUrl), true);
        if($info['code'] == 0){
            $data['video_id'] = $video_id;
            $where = 'video_id=:video_id:';
            $result = $this->BaseData->delete($this->table, $data, $where);
            return $result ? $this->arrayData('删除成功！') : $this->arrayData('删除失败！', '', '', 'error');
        }else{
            return $this->arrayData('删除失败！'. $info['message'], '', '', 'error');
        }
    }

    /**
     * @remark 获取视频
     * @param $id
     * @return array
     * @author 杨永坚
     */
    public function getVideoInfo($id)
    {
        $data['id'] = $id;
        $where = 'id=:id:';
        $result = $this->BaseData->select('*', $this->table, $data, $where);
        return $result ? array('status'=>'success', 'data'=>$result) : array('status'=>'error');
    }

    /**
     * @remark 更新视频定时器脚本
     * @author 杨永坚
     */
    public function videoCrontab()
    {
        $time = time();
        $param['startTime'] = strtotime('-60 day', $time);
        $param['endTime'] = $time;
        $param['status'] = 10;
        $where = 'add_time >= :startTime: and add_time <= :endTime: and status <> :status:';
        $videoIdList = $this->BaseData->select('video_id', $this->table, $param, $where);
        $videoConfig = \Shop\Models\BaiyangVideoEnum::VIDEO_CONFIG;
        $userUnique = \Shop\Models\BaiyangVideoEnum::USER_UNIQUE;
        foreach ($videoIdList as $k => $v){
            $video_id = $v['video_id'];
            $videoInfo = $this->selectOneVideo($video_id);//获取视频信息
            $extendImage = $this->videoExtendImage($video_id, '640_360');//获取视频截图
            if($videoInfo['data'])
            {
                $data['video_id'] = $video_id;
                $data['status'] = $videoInfo['data']['status'];
                $data['img'] = $videoInfo['data']['img'];
                $data['video_duration'] = $videoInfo['data']['video_duration'];
                $data['error_desc'] = $videoInfo['data']['error_desc'];
                $data['isdownload'] = $videoInfo['data']['isdownload'];
                $data['is_pay'] = $videoInfo['data']['is_pay'];
                $data['add_time'] = strtotime($videoInfo['data']['add_time']);
                $data['video_url'] = $videoConfig['video_url'].'?uu='.$userUnique.'&vu='.$videoInfo['data']['video_unique'].'&pu='.$videoConfig['video_sign'].'&auto_play='.$videoConfig['auto_play'].'&width='.$videoConfig['width'].'&height='.$videoConfig['height'];
                if($extendImage['data']){
                    $data['extend_images'] = serialize(json_encode($extendImage['data']));
                }
                $columStr = $this->jointString($data, array('video_id'));
                $where = 'video_id=:video_id:';
                $result = $this->BaseData->update($columStr, $this->table, $data, $where);
            }
        }
        return $this->arrayData('更新完成！');
    }


    /**
     * @remark 查询单个视频信息
     * @param $video_id=int 视频id
     * @return array
     * @author 杨永坚
     */
    private function selectOneVideo($video_id)
    {
        $api = $this->videoArr['get'];

        $params['video_id'] = $video_id;
        $params['api'] = $api;
        $final_url = $this->handleParam($params, $api);
        return json_decode($this->curl->sendPost($final_url), true);
    }

    /**
     * @remark 获取视频截图
     * @param $video_id=int 视频id
     * @param $image_size 图片尺寸
     * @return json
     * @author 杨永坚
     */
    private function videoExtendImage($video_id, $image_size)
    {
        $api = $this->videoArr['image'];

        $params['video_id'] = $video_id;
        $params['size'] = $image_size;
        $params['api'] = $api;
        $final_url = $this->handleParam($params, $api);
        return json_decode($this->curl->sendPost($final_url), true);

    }

    /**
     * @remark 视频上传初始化
     * @param $video_name=string 视频名称
     * @param $file_size=int 视频大小
     * @param $client_ip=int ip地址
     * @param int $uploadtype 上传文件类型
     * @param int $uc1
     * @param int $uc2
     * @return array
     * @author 杨永坚
     */
    private function init($video_name, $file_size, $client_ip, $uploadtype = 0, $uc1 = 0, $uc2 = 0)
    {
        $api = $this->videoArr['init'];

        $params['video_name'] = $video_name;
        $params['file_size'] = $file_size;
        $params['uploadtype'] = $uploadtype;
        $params['api'] = $api;
        $params['client_ip'] = $client_ip;
        $params['uc1'] = $uc1;
        $params['uc2'] = $uc2;

        $final_url = $this->handleParam($params, $api);
        return $this->curl->sendPost($final_url);

    }

    /**
     * @remark 视频断点续传
     * @param $token
     * @param $client_ip
     * @param int $uploadtype
     * @return mixed
     * @author 杨永坚
     */
    private function resume($token, $client_ip, $uploadtype = 0)
    {
        $api = $this->videoArr['resume'];

        $params['token'] = $token;
        $params['uploadtype'] = $uploadtype;
        $params['api'] = $api;
        $params['client_ip'] = $client_ip;

        $final_url = $this->handleParam($params, $api);

        return $this->curl->sendPost($final_url);
    }

    /**
     * @remark 数据拼接组装
     * @param $params=array() 参数
     * @return string
     * @author 杨永坚
     */
    private function handleParam($params)
    {
        $params['user_unique'] = \Shop\Models\BaiyangVideoEnum::USER_UNIQUE;
        $params['timestamp'] = time();
        $params['format'] = \Shop\Models\BaiyangVideoEnum::FORMAT;
        $params['ver'] = \Shop\Models\BaiyangVideoEnum::VER;
        // 对所有参数按key排序
        ksort($params);
        $url_param = '';
        $keyStr = '';// 用于生成验证码的字符串由参数的键值和用户密钥拼接而成

        foreach($params as $key=>$param) {
            $url_param .= (empty($url_param) ? '?' : '&') . $key . '=' . urlencode($param);
            $keyStr .= $key . $param;
        }

        $keyStr .= \Shop\Models\BaiyangVideoEnum::SECRETKEY;

        $sign = md5($keyStr);  // 计算sign参数
        $url_param .= '&sign=' . $sign;
        $final_url = \Shop\Models\BaiyangVideoEnum::API_URL . $url_param;

        return $final_url;
    }

    /**
     * @remark 将秒数转换为时间（年、天、小时、分、秒）
     * @param $seconds=int 秒数
     * @return string
     * @author 杨永坚
     */
    private function timeSecond($seconds)
    {
        $seconds = (int)$seconds;
        if($seconds > 3600){
            if($seconds > 24 * 3600){
                $days = (int)($seconds / 86400);
                $days_num = $days. "天";
                $seconds = $seconds % 86400;//取余
            }
            $hours = intval($seconds / 3600);
            $minutes = $seconds % 3600;//取余下秒数
            $time = $days_num. $hours. "时". gmstrftime('%M分%S秒', $minutes);
        }else{
            $time = gmstrftime('%H时%M分%S秒', $seconds);
        }
        return $time;
    }

    /**
     * @remark 获取视频信息（分页）
     * @param $page 页码信息
     * @return array
     * @author 梁伟
     */
    public function getVideoAll($page)
    {
        $page = isset($page)?(int)$page:1;
        //总记录数
        $data['status'] = '10';
        $where = 'status=:status:';
        $counts = $this->BaseData->count($this->table, $data, $where);
        if($counts <= 0){
            return array('res' => 'success', 'list' => 0, 'page' => '');
        }
        //计算页码
        $p = 4;
//        $pages['pages'] = ceil($counts/4);
        $page_start = ($page-1)*$p;
        $selections = 'id,video_name,img,video_duration';
        $where .= ' order by add_time desc limit '.$page_start.','.$p;
        $result = $this->BaseData->select($selections, $this->table, $data, $where);
        if($result){
            return array(
                'res'  => 'success',
                'list' => $result,
                'page' => $page,
                'pages'=>ceil($counts/$p)
            );
        }else{
            return ['res' => 'error'];
        }
    }

}