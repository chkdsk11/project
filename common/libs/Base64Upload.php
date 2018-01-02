<?php
/**
 * Base64文件上传
 * User: ZHQ
 * Date: 2017/01/1
 */
namespace Shop\Libs;
use Shop\Libs\LibraryBase;

class Base64Upload extends LibraryBase{

    private $base64File;
    private $size;
    private $fileName;
    private $fileType;
    private $uploadPath;
    private $maxSize = 2048; //KB
    private $minSize = 1;  //KB
    private $allowFileType = array(
        'jpg','png','gif','jpeg'
    );
    private $error;
    private $uploadedRealFile;

    public function setConfig($config)
    {
        if (!is_array($config)) {
            $this->error = '上传文件错误';
        } else {
            foreach ($config as $key => $value) {
                if (is_string($key)) {
                    $this->{$key} = $value;
                }
            }
            $this->getBase64File();
            $this->getSize();
            $this->getFileType();
        }
    }

    public function getBase64File()
    {
        if (is_null($this->error)) {
            if (is_null($this->base64File)) {
                $this->error = '没有上传的文件！';
            } else {
                $this->base64File = base64_decode($this->base64File);
                if (!@imagecreatefromstring($this->base64File)) {
                    $this->error = '上传的图片不完整！';
                }
            }
        }
    }

    public function getSize()
    {
        if (is_null($this->error))
        {
            $this->size = strlen($this->base64File) / 1024; //KB
            $this->size = round($this->size, 4);
        }
    }

    public function isAllowSize()
    {
        if (is_null($this->error)) {
            if ($this->size > $this->maxSize)
            {
                $this->error = '超过了上传最大的图片大小';
            } else if($this->size < $this->minSize)
            {
                $this->error = '不符合上传最小的图片大小';
            } else {
                return true;
            }
        }
        return false;
    }

    /**
     * 判断是否允许上传的类型
     */
    public function isAllowType()
    {
        if (is_null($this->error)) {
            if (!is_null($this->fileType)) {
                if (in_array($this->fileType, $this->allowFileType)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 递归创建目录
     * @param $dir
     * @return bool
     */
    private function mkDir()
    {
        if (is_null($this->error)) {
            if (!is_null($this->uploadPath)) {
                if (!is_dir($this->uploadPath)) {
                    if(!@mkdir($this->uploadPath, 0775, true)) {
                        $this->error = '创建目录失败！';
                    } else {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * 获取文件真实保存路径
     * @return array
     */
    public function getFileRealPath()
    {
        return $this->uploadedRealFile;
    }

    /**
     * 移动文件
     *  * @return bool
     */
    public function save()
    {
        if (is_null($this->error)) {
            if (is_null($this->fileName)) {
                $this->error = '没有文件名称！';
                return false;
            }
            if (!$this->isAllowType() || !$this->isAllowSize())
            {
                return false;
            }
            $this->mkDir();
            $this->uploadPath = rtrim($this->uploadPath, '/');
            $this->uploadedRealFile = $this->uploadPath . '/' . $this->fileName;
            if (@file_put_contents($this->uploadedRealFile, $this->base64File)) {
                return true;
            }
            $this->error = '上传文件失败！';
        } else {
            return false;
        }
    }

    /**
     * 检查上传文件类型
     * @return bool
     */
    private function getFileType()
    {
        if(is_null($this->error)) {
            $bin = substr($this->base64File, 0, 4);
            $strInfo = @unpack("C2chars", $bin);
            $typeCode = intval($strInfo['chars1'].$strInfo['chars2']);
            switch ($typeCode) {
                case 7790:
                    $this->fileType = 'exe';
                    break;
                case 7784:
                    $this->fileType = 'midi';
                    break;
                case 8297:
                    $this->fileType = 'rar';
                    break;
                case 255216:
                    $this->fileType = 'jpg';
                    break;
                case 7173:
                    $this->fileType = 'gif';
                    break;
                case 6677:
                    $this->fileType = 'bmp';
                    break;
                case 13780:
                    $this->fileType = 'png';
                    break;
                default:
                    $this->fileType = 'unkown';
            }
        }
    }


    /**
     * 返回错误状态
     * @return bool
     */
    public function getError()
    {
        return $this->error;
    }

    public function deleteFile($fileName)
    {
        return @unlink($fileName);
    }
}