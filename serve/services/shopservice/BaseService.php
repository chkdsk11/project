<?php
/**
 * Created by PhpStorm.
 * User: 康涛
 * Date: 2016/8/9 0009
 * Time: 上午 11:05
 * service基类
 */

namespace Shop\Services;
use Shop\Datas\BaseData;

use Phalcon\Mvc\User\Component;
use Shop\Home\Services\LogService;

class BaseService extends Component
{
    protected static $instance=null;

    /**
     * 单例
     * @return class
     */
    public static function getInstance()
    {
        if(empty(static::$instance)){
            static::$instance=new static();
        }
        return static::$instance;
    }

    /**
     * 格式化返回数组
     * @param $prompt string 要返回的提示信息
     * @param $url string 要跳转的路径(空为不跳转)
     * @param $param array() 要返回的数据(可以为空)
     * @param $act string 要返回状态 正确|错误(success|error,默认success)
     * @return array() 返回组成的数组信息
     * User: 梁伟
     * Date: 2016/8/25
     * Time: 20:19
     */
    public function arrayData($prompt = '',$url = '',$param = array(),$act = 'success')
    {
        return array(
            'status' => $act,
            'info' => $prompt,
            'url'  =>   $url,
            'data'   => $param,
        );
    }

    /**
     * @remark 将数组拼接成字段
     * @param $param=array() 参数
     * @param $notArr=array() 不需要拼接的参数
     * @return string
     * @author 杨永坚
     */
    protected function jointString($param, $notArr)
    {
        $string = '';
        foreach($param as $k=>$v){
            if(!in_array($k, $notArr)){
                $string .= empty($string) ? "{$k}=:{$k}:" : ",{$k}=:{$k}:";
            }
        }
        return $string;
    }

    /**
     * @remark 上传文件
     * @param $request 上传的文件
     * @param $fileSavepath 上传路径
     * @param $thumb=array() 生成缩略图大小 例：array(array(320, 320), array(160, 160)) 宽x高
     * @param $fileSize 文件大小
     * @param $fileType 文件类型
     * @return $res 返回上传信息
     * @author 杨永坚
     * @modify 梁伟 图片上传到FastDfs服务器 2016-11-19
     */
    public function uploadFile($request, $fileSavepath = '', $thumb = '', $fileSize = '', $fileType = '')
    {
        $upload = LibService::getInstance()->uploadFiles($request, $fileSavepath, $fileSize, $fileType);

        $upload->uploadfile();
        if(!$upload->errState()){
            // 返回文件保存真实路径
            $fileRealPath = $upload->getFileRealPath();
            foreach($fileRealPath as $key => $val){
                //是否生成缩略图
                if($thumb){
                    //生成缩略图对应的尺寸
                    foreach($thumb as $k => $v){
                        // 临时目录
                        $tempDir = $this->config['uploadFile']['fileTmp'] . "images/";
                        if(!is_dir($tempDir)){
                            mkdir($tempDir, 0777, true);
                        }
                        $thumbPath = $tempDir . $val['fileName'];
                        //生成
                        $this->image->make($val['src'])->resize($v[0], $v[1])->save($thumbPath);
                        //上传到FastDfs
                        $tmpUrl = $this->FastDfs->uploadByFilename($thumbPath,2,'G1');
                        @unlink($thumbPath);
                        if(!$tmpUrl)return array('status' => 'error','info' => '上传失败');
                        $fileRealPath[$key]['thumb'][] = $this->config['domain']['img'].$tmpUrl;
                    }
                }else{
                    //替换成绝对路径
                    $tmpUrl = $this->FastDfs->uploadByFilename($val['src'],2,'G1');
                    if(!$tmpUrl)return array('status' => 'error','info' => '上传失败');
                    $fileRealPath[$key]['src'] = $this->config['domain']['img'].$tmpUrl;

                }
                @unlink($val['src']);
            }

            $res = [
                'status' => 'success',
                'data' => $fileRealPath,
                'info' => '上传成功'
            ];
        }else{
            // 打印错误信息
            $res = [
                'status' => 'error',
                'info' => $upload->errInfo()
            ];
        }
        return $res;
    }

    /**
     * @remark 上传文件  只用于文件上传
     * @param $request 上传的文件
     * @param string $fileSavepath 文件保存路径
     * @param string $fileSize  允许文件大小
     * @param string $fileType  允许文件类型
     * @return array
     * @author 杨永坚
     */
    public function filesUpload($request, $fileSavepath = '', $fileSize = '', $fileType = '')
    {
        $upload = LibService::getInstance()->uploadFiles($request, $fileSavepath, $fileSize, $fileType);
        $upload->uploadfile();
        if(!$upload->errState()){

            // 返回文件保存真实路径
            $fileRealPath = $upload->getFileRealPath();
            foreach($fileRealPath as $key => $val){
                //替换成绝对路径
                $fileRealPath[$key]['src'] = 'http://'. $this->config['domain']['static'] . str_replace($this->config['uploadFile']['rootPath'], '', $val['src']);
            }
            $res = [
                'status' => 'success',
                'data' => $fileRealPath,
                'info' => '上传成功'
            ];
        }else{
            // 打印错误信息
            $res = [
                'status' => 'error',
                'info' => $upload->errInfo()
            ];
        }
        return $res;
    }

    /**
     * @remark 上传app主题包
     * @param $request 上传的文件
     * @param string $fileSavepath 文件保存路径
     * @param string $fileSize  允许文件大小
     * @param string $fileType  允许文件类型
     * @return array
     * @author 傅艺辉
     */
    public function themeUpload($request,$filename, $fileSavepath = '', $fileSize = '', $fileType = '')
    {
        $upload = LibService::getInstance()->themeUploads($request, $fileSavepath, $fileSize, $fileType);
        $upload->uploadtheme($filename);
        if(!$upload->errState()){

            // 返回文件保存真实路径
            $fileRealPath = $upload->getFileRealPath();
            foreach($fileRealPath as $key => $val){
                //替换成绝对路径
                $fileRealPath[$key]['src'] = 'http://'. $this->config['domain']['static'] . str_replace($_SERVER['DOCUMENT_ROOT'].'/'.$this->config['uploadFile']['rootPath'], '', $val['src']);
            }
            $res = [
                'status' => 'success',
                'data' => $fileRealPath,
                'info' => '上传成功'
            ];
        }else{
            // 打印错误信息
            $res = [
                'status' => 'error',
                'info' => $upload->errInfo()
            ];
        }
        return $res;
    }

    /**
     * @remark 更新es搜索数据
     * @param string $sku_id 商品ID
     * @param int $spu_id spu ID
     * @author 梁伟
     */
    protected function updateEsSearch($sku_id=0,$spu_id=0)
    {
        if(empty($sku_id) && empty($spu_id)) return ;
        $goodNameId = '';
        if($spu_id > 0){
            $skus  = BaseData::getInstance()->select('id','\Shop\Models\BaiyangGoods',array('spu_id'=>(int)$spu_id),'spu_id=:spu_id:');
            if(!empty($skus)){
                foreach($skus as $v){
                    $goodNameId .= $v['id'].',';
                }
            }
        }else{
            $goodNameId .= $sku_id;
        }
        $data['goodNameId'] = trim($goodNameId,',');
        $requestData = http_build_query($data);
        $esUrl = $this->config['domain']['updateEsSearch'].'pces/searchDataUpdate.do';
        $res = $this->curl->sendPost($esUrl, $requestData);

        $res = json_decode($res,true);
        if($res['code'] != 200){
            $res['error_info'] = '更新es引擎失败';
            LogService::getInstance()->save([
               'prefix'=>'es_error',
                'data' => $res,
            ]);
        }
        //删除pc端redis缓存
        $this->updatePc($data['goodNameId']);
//        $res = $this->curl->sendPost($this->config->pc_url[$this->config->environment].'/shop/goods/deleteCacheFile/', 'goodsIds='.$data['goodNameId']);
//        $res = json_decode($res);
//        if($res['status'] != 200){
//            $res['error_info'] = '更新pc端Nginx缓存失败';
//            LogService::getInstance()->save([
//                'prefix'=>'pc_nginx_error',
//                'data' => $res,
//            ]);
//        }
    }

    //删除pc前台缓存
    public function updatePc($goodNameId)
    {
        if(empty($goodNameId)) return ;
        $res = $this->curl->sendPost($this->config->pc_url[$this->config->environment].'/shop/goods/deleteCacheFile/', 'goodsIds='.$goodNameId);
        $res = json_decode($res);
        if($res->status != 200){
            $res->error_info = '更新pc端Nginx缓存失败';
            LogService::getInstance()->save([
                'prefix'=>'pc_nginx_error',
                'data' => $res,
            ]);
        }
    }
}

