<?php
/**
 * Created by PhpStorm.
 * User: 杨永坚
 * Date: 2016/8/23
 * Time: 17:39
 */

namespace Shop\Services;

use Shop\Services\BaseService;
use Shop\Libs\Upload;

class LibService extends BaseService
{
    ////必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance = null;

    /**
     * @remark 析构上传类
     * @param $request 上传的文件
     * @param $fileSavepath 上传路径
     * @param $fileSize 文件大小
     * @param $fileType 文件类型
     * @return $upload 实例化类
     * @author 杨永坚
     * @modify 梁伟 图片上传到服务器临时存放位置 2016-11-19
     */
    public function uploadFiles($request, $fileSavepath = '', $fileSize = '', $fileType = '')
    {
//        $fileSavepath = $fileSavepath ? $this->config['uploadFile']['filePath']. trim($fileSavepath, '/') . '/' : $this->config['uploadFile']['filePath'];
//        $fileSavepath = $this->config['uploadFile']['fileTmp'];
        $fileSavepath = $fileSavepath ? $fileSavepath:$this->config['uploadFile']['fileTmp'];
        $fileSize = $fileSize ? $fileSize : $this->config['uploadFile']['fileSize'];
        $fileType = $fileType ? $fileType : explode(',', $this->config['uploadFile']['fileType']);
        $upload = new Upload($request, $fileSavepath, $fileSize, $fileType);
        return $upload;
    }
    /**
     * @remark 析构上传类
     * @param $request 上传的文件
     * @param $fileSavepath 上传路径
     * @param $fileSize 文件大小
     * @param $fileType 文件类型
     * @return $upload 实例化类
     * @author 杨永坚
     * @modify 傅艺辉 app主题上传
     */
    public function themeUploads($request, $fileSavepath = '', $fileSize = '', $fileType = '')
    {
        $fileSavepath = $fileSavepath ? $fileSavepath:$this->config['uploadFile']['fileTmp'];
        $fileSavepath = $_SERVER['DOCUMENT_ROOT'].'/'.$this->config['uploadFile']['rootPath'].'/'.$fileSavepath;
        $fileSize = $fileSize ? $fileSize : $this->config['uploadFile']['fileSize'];
        $fileType = $fileType ? $fileType : explode(',', $this->config['uploadFile']['fileType']);
        $upload = new Upload($request, $fileSavepath, $fileSize, $fileType);
        return $upload;
    }
}