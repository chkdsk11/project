<?php
/**
 * Created by PhpStorm.
 * User: 康涛
 * Date: 2016/10/21 0021
 * Time: 下午 2:44
 */

namespace Shop\Libs;
use Shop\Libs\LibraryBase;

class FastDfsClient extends LibraryBase
{
    protected $fs;

    /**
     * FastDfsClient constructor.
     */
    public function __construct()
    {
        if (class_exists('FastDFS', false) && empty($this->fs)) {
            $this->fs = new \FastDFS();
        } else {
            throw new \Exception('fastDfs class is not exists');
        }
    }

    /**
     * @param $filename
     * @param string $group_name
     * @return bool
     * 文件是否已经存在
     */
    public function fileExist($filename, $group_name = '')
    {
        if (empty($filename) || !is_string($filename)) {
            return false;
        }
        if (empty($group_name)) {
            return $this->fs->storage_file_exist1($filename) ? true : false;
        } else {
            return $this->fs->storage_file_exist($group_name, $filename) ? true : false;
        }
    }

    /**
     * @param $file_id
     * @param string $group_name
     * @return bool
     *  得到文件信息
     */
    public function getFileInfo($file_id, $group_name = '')
    {
        if (empty($file_id)) {
            return false;
        }
        if (empty($group_name)) {
            return $this->fs->get_file_info1($file_id);
        } else {
            return $this->fs->get_file_info($group_name, $file_id);
        }
    }

    /**
     * @param $file_id
     * @param string $group_name
     * @return int
     * 得到文件大小
     */
    public function getFileSize($file_id, $group_name = '')
    {
        $file_info = $this->get_file_info($file_id, $group_name);
        if ($file_info) {
            return isset($file_info['file_size']) ? $file_info['file_size'] : 0;
        }
        return 0;
    }

    /**
     * @param $file_id
     * @param string $group_name
     * @return int
     * 得到文件上传时间
     */
    public function getCreateTimestamp($file_id, $group_name = '')
    {
        $file_info = $this->get_file_info($file_id, $group_name);
        if ($file_info) {
            return isset($file_info['create_timestamp']) ? $file_info['create_timestamp'] : 0;
        }
        return 0;
    }

    /**
     * @param $local_filename
     * @param $file_id
     * @param string $group_name
     * @return mixed
     * 下载文件到本地
     */
    public function downloadFileToFile($local_filename, $file_id, $group_name = '')
    {
        if (empty($group_name)) {
            return $this->fs->storage_download_file_to_file1($file_id, $local_filename);
        } else {
            return $this->fs->storage_download_file_to_file($group_name, $file_id, $local_filename);
        }
    }

    /**
     * @param $filename
     * @param int $return_type
     * 1 return array('group_name','filename');
     * 2 return string group_name/file_id;
     * 3 return string file_id; This string without group_name;
     *
     * @return bool|string
     * 上传文件
     */
    public function uploadByFilename($filename, $return_type = 2,$groupName='G2')
    {
        if(file_exists($filename)) {
            $file_ext_name = pathinfo($filename, PATHINFO_EXTENSION);
            $file_info = $this->fs->storage_upload_by_filename($filename, $file_ext_name, $meta_list = [], $groupName);
            if (!$file_info) // if upload fail, try again.
            {
                $file_info = $this->fs->storage_upload_by_filename($filename, $file_ext_name = '', $meta_list = [], $groupName);
            }
            if ($file_info) {
                switch ($return_type) {
                    case 1:
                        return $file_info;
                    case 2:
                        return $file_info['group_name'] . FDFS_FILE_ID_SEPERATOR . $file_info['filename'];
                    case 3:
                        return $file_info['filename'];
                }
            }
        }
        return false;
    }

    /**
     * @param $filename
     * @param $master_file_id
     * @param $prefix_name
     * @param string $group_name
     * @return bool
     * 上传文件
     */
    public function uploadSlaveByFilename($filename, $master_file_id, $prefix_name, $group_name = '')
    {
        if (empty($filename) || empty($master_file_id) || empty($prefix_name)) {
            return false;
        }
        if (!$this->file_exist($master_file_id, $group_name)) {
            return false;
        }
        if (empty($group_name)) {
            // with group_name/file_id
            $slave_file_info = $this->fs->storage_upload_slave_by_filename1($filename, $master_file_id, $prefix_name);
        } else {
            // array('group_name','filename')
            $slave_file_info = $this->fs->storage_upload_slave_by_filename($filename, $group_name, $master_file_id,
                $prefix_name);
        }
        return $slave_file_info;
    }

    /**
     * @param $filename
     * @param string $group_name
     * @return bool
     * 删除文件
     */
    public function deleteFile($fileName, $groupName = '')
    {
        if (empty($groupName)) {
            return $this->fs->storage_delete_file1($fileName) ? true : false;
        } else {
            return $this->fs->storage_delete_file($groupName, $fileName) ? true : false;
        }
    }

    /**
     * 获得fastdfs错误信息
     */
    public function getFastdfsError()
    {
        return $this->fs->get_last_error_info();
    }

    /**
     * 关闭tracker连接
     */
    public function __destruct()
    {
        $this->fs->tracker_close_all_connections();
    }
}