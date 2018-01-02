<?php
/**
 * 文件上传
 * User: 杨永坚
 * Date: 2016/08/23
 * Time: 16:00
 */
namespace Shop\Libs;
use Shop\Libs\LibraryBase;
class Upload extends LibraryBase{
    // request控制器对象
    private $request;
    // 是否自动创建目录
    private $auto_mkdir = true;
    // 文件上传路径
    private $file_path;
    // 上传文件最大尺寸
    private $file_size;
    // 上传文件的格式限制
    private $file_type;
    // file文件对象
    private $file;
    // 错误信息
    private $error_info;
    // 错误状态,默认无错误
    private $error_state = false;
    // 上传成功后文件真实路径
    private $file_realpath = array();
    // 子目录创建
    private $sub_name;


    public function __construct($request, $file_path, $file_size, $file_type)
    {
        $this->request   = $request;
        $this->file_path = '/' . trim($file_path, '/') . '/' . date('Y-m-d') . '/';
        $this->file_size = $file_size;
        $this->file_type = $file_type;
    }

    /**
     * 获取文件真实保存路径
     * @return array
     */
    public function getFileRealPath()
    {
        return $this->file_realpath;
    }

    /**
     * 执行上传文件
     * @return bool
     */
    public function uploadfile()
    {
        // 检测是否有上传文件
        if($this->request->hasFiles() == true){
            // 获取上传文件的相关信息
            foreach($this->request->getUploadedFiles() as $file){
                $this->file = $file;
                // 检查文件大小
                $this->checkSize();
                // 检查文件类型
                $this->checkType();
                // 移动文件到指定目录
                $this->move();
            }
        }else{
            $this->error_state = true;
            $this->error_info = '暂无上传文件';
        }
    }

    /**
     * 上传主题包
     * @return bool
     */
    public function uploadtheme($filename)
    {
        // 检测是否有上传文件
        if($this->request->hasFiles() == true){
            // 获取上传文件的相关信息
            foreach($this->request->getUploadedFiles() as $file){
                $this->file = $file;
                // 检查文件大小
                $this->checkSize();
                // 检查文件类型
                $this->checkType();
                // 移动文件到指定目录
                $this->movetheme($filename);

            }
        }else{
            $this->error_state = true;
            $this->error_info = '暂无上传文件';
        }
    }
    /**
     * 移动文件
     *  * @return bool
     */
    private function movetheme($filename)
    {
        if(!$this->error_state){
            // 检查并创建目录
            $this->checkDir();
            // 目录路径
            $filepath = '/' . trim($this->file_path , '/') . '/';
            $filename = $filename . '.' . $this->file->getExtension();
            // 移动文件
            if(!$this->file->moveTo($filepath . $filename)){
                $this->error_state = true;
                $this->error_info = '上传文件失败';
            }else{
                $this->file_realpath[] = array(
                    'src' => $filepath . $filename,
                    'filePath' => $filepath,
                    'fileName' => $filename,
                    'ext' => $this->file->getExtension()
                );
            }
        }
    }

    /**
     * 移动文件
     *  * @return bool
     */
    private function move()
    {
        if(!$this->error_state){
            // 检查并创建目录
            $this->checkDir();
            // 目录路径
            $filepath = '/' . trim($this->file_path , '/') . '/';
            // 生成文件名称
            $filename = $this->getFileName();
            // 移动文件
            if(!$this->file->moveTo($filepath . $filename)){
                $this->error_state = true;
                $this->error_info = '上传文件失败';
            }else{
                $this->file_realpath[] = array(
                    'src' => $filepath . $filename,
                    'filePath' => $filepath,
                    'fileName' => $filename,
                    'ext' => $this->file->getExtension()
                );
            }
        }
    }

    /**
     * 生成文件名规则
     * @return string
     */
    private function getFileName()
    {
        return uniqid() . '.' . $this->file->getExtension();
    }

    /**
     * 检查并创建目录
     * @return bool
     */
    private function checkDir()
    {
        if(!$this->error_state){
            if(!is_dir($this->file_path)){
                if($this->auto_mkdir){
                    if(!$this->mkdir($this->file_path)){
                        $this->error_state = true;
                        $this->error_info[] = '目录创建失败';
                    }
                }else{
                    $this->error_state = true;
                    $this->error_info[] = '上传目录不存在';
                }
            }
        }
    }

    /**
     * 检测上传文件的大小
     * @return bool
     */
    private function checkSize()
    {
        if(!$this->error_state){
            if($this->file->getSize() > $this->file_size){
                $this->error_state = true;
                $this->error_info[] = '上传文件过大';
            }
        }
    }
    /**
     * 递归创建目录
     * @param $dir
     * @return bool
     */
    private function mkdir($dir)
    {
        if(!is_dir($dir)){
            if(!$this->mkdir(dirname($dir))){
                return false;
            }
            if(!mkdir($dir,0777)){
                return false;
            }
        }
        return true;
    }

    /**
     * 检查上传文件类型
     * @return bool
     */
    private function checkType()
    {
        if(!$this->error_state){
            if(is_array($this->file_type)){
            	if(!in_array(strtolower($this->file->getExtension()) , array_map('strtolower' , $this->file_type))){
                    $this->error_state = true;
                    $this->error_info[] = '文件类型错误';
                }
            }else if(is_string($this->file_type)){
                if(strtolower($this->file->getExtension()) != strtolower($this->file_type)){
                    $this->error_state = true;
                    $this->error_info[] = '文件类型错误';
                }
            }else{
                $this->error_state = true;
                $this->error_info[] = '文件类型错误';
            }
        }
    }

    /**
     * 返回错误信息
     * @return mixed
     */
    public function errInfo()
    {
        return $this->error_info;
    }

    /**
     * 返回错误状态
     * @return bool
     */
    public function errState()
    {
        return $this->error_state;
    }
    
}