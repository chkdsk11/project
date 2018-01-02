<?php
/**
 * Created by PhpStorm.
 * User: 杨永坚
 * Date: 2016/9/1
 * Time: 9:33
 */

namespace Shop\Models;


class BaiyangVideoEnum
{
    const SECRETKEY = '502ff53498db16469e62128d7abfaec9';// 用户密钥
    const USER_UNIQUE = 'xcqvozcnpv';//UUID
    const VER = '2.0';// 默认值
    const API_URL = 'http://api.letvcloud.com/open.php';//接口地址
    const FORMAT = 'json';//返回数据格式 支持json和xml
    const VIDEO_TYPE = array(
        'update' => 'video.update',
        'del' => 'video.del',
        'get' => 'video.get',
        'image' => 'image.get',
        'init' => 'video.upload.init',
        'resume' => 'video.upload.resume'
    );
    const VIDEO_CONFIG = array(
        'video_url' => 'http://yuntv.letv.com/bcloud.html',
        'video_sign' => '30934',
        'auto_play' => 1,
        'width' => 800,
        'height' => 450
    );
    const VIDEO_STATUS = array(
        '已审核' => 10,
        '转码失败' => 20,
        '审核失败' => 21,
        '片源错误' => 22,
        '发布失败' => 23,
        '上传失败' => 24,
        '处理中' => 30,
        '审核中' => 31,
        '无视频源' => 32,
        '上传初始化' => 33,
        '视频上传中' => 34,
        '停用'=>40
    );
}