<?php

error_reporting(-1);
ini_set('display_errors',1);

$configPath = __DIR__ . '/../../../../common/config/config.local.php';
if(file_exists($configPath)){
    $config = include $configPath;
}else {
    $config = include __DIR__ . '/../../../../common/config/config.php';
}
$configM = new \Phalcon\Config(array(
    'application' => array(
        'controllersDir' => __DIR__ . '/../controllers/',
        'viewsDir'       => __DIR__ . '/../views/',
        'cacheDir'       => __DIR__. '/../cache/',
        'logDir'        => __DIR__.'/../logs/',
        'homeDataDir'   =>  APP_PATH . '/serve/datas/homedata/',
        'homeServiceDir' => APP_PATH . '/serve/services/homeservice/',
        'homeListenDir' => APP_PATH.'/serve/listens/homelisten/',
        'rulesDir'      => APP_PATH . '/serve/rules/sms'
    ),
    'ClusterRedis'=>[
        'master'=>['127.0.0.1:6379','127.0.0.1:6380','127.0.0.1:6381'],
        'slave'=>['127.0.0.1:6382','127.0.0.1:6383','127.0.0.1:6384'],
        'connTimeout'=>1.5,
        'readTimeout'=>1.5
    ],
    'platform' => '', // 平台
    'channel_subid' => 0, // 渠道号
    // 辣妈
    'mom' => [
        'tag_id' => 0
    ],
    'auth' => [
        'idcard_max_query_number' => 10
    ],

    'express_code'=> [
        'auspost'=>'澳大利亚邮政(英文结果）',
        'aae'=>'AAE',
        'anxindakuaixi'=>'安信达',
        'huitongkuaidi'=>'百世汇通',
        'baifudongfang'=>'百福东方',
        'bht'=>'BHT',
        'bangsongwuliu'=>'邦送物流',
        'cces'=>'希伊艾斯（CCES）',
        'coe'=>'中国东方（COE）',
        'chuanxiwuliu'=>'传喜物流',
        'canpost'=>'加拿大邮政Canada Post（英文结果）',
        'canpostfr'=>'加拿大邮政Canada Post(德文结果）',
        'datianwuliu'=>'大田物流',
        'debangwuliu'=>'德邦物流',
        'dpex'=>'DPEX',
        'dhl'=>'DHL-中国件-中文结果',
        'dhlen'=>'DHL-国际件-英文结果',
        'dhlde'=>'DHL-德国件-德文结果（德国国内派、收的件）',
        'dsukuaidi'=>'D速快递',
        'disifang'=>'递四方',
        'ems'=>'EMS(中文结果)',
        'emsen'=>'EMS（英文结果）',
        'emsguoji'=>'EMS-（中国-国际）件-中文结果/EMS-(China-International）-Chinese data',
        'emsinten'=>'EMS-（中国-国际）件-英文结果/EMS-(China-International）-Englilsh data',
        'fedex'=>'Fedex-国际件-英文结果',
        'fedexcn'=>'Fedex-国际件-中文结果',
        'fedexus'=>'Fedex-美国件-英文结果(说明：如果无效，请偿试使用fedex）',
        'feikangda'=>'飞康达物流',
        'feikuaida'=>'飞快达',
        'rufengda'=>'凡客如风达',
        'fengxingtianxia'=>'风行天下',
        'feibaokuaidi'=>'飞豹快递',
        'ganzhongnengda'=>'港中能达',
        'guotongkuaidi'=>'国通快递',
        'guangdongyouzhengwuliu'=>'广东邮政',

        'youzhengguoji'=>'国际邮件',
        'gls'=>'GLS',
        'gongsuda'=>'共速达',
        'huiqiangkuaidi'=>'汇强快递',
        'tiandihuayu'=>'华宇物流',
        'hengluwuliu'=>'恒路物流',
        'huaxialongwuliu'=>'华夏龙',
        'tiantian'=>'海航天天',
        'haiwaihuanqiu'=>'海外环球',
        'haimengsudi'=>'海盟速递',
        'huaqikuaiyun'=>'华企快运',
        'haihongwangsong'=>'山东海红',
        'jiajiwuliu'=>'佳吉物流',
        'jiayiwuliu'=>'佳怡物流',
        'jiayunmeiwuliu'=>'加运美',
        'jinguangsudikuaijian'=>'京广速递',
        'jixianda'=>'急先达',
        'jinyuekuaidi'=>'晋越快递',
        'jietekuaidi'=>'捷特快递',
        'jindawuliu'=>'金大物流',
        'jialidatong'=>'嘉里大通',
        'kuaijiesudi'=>'快捷速递',
        'kangliwuliu'=>'康力物流',
        'kuayue'=>'跨越物流',
        'lianhaowuliu'=>'联昊通',
        'longbanwuliu'=>'龙邦物流',
        'lanbiaokuaidi'=>'蓝镖快递',
        'lianbangkuaidi'=>'联邦快递（Fedex-中国-中文结果）（说明：国外的请用 fedex）',
        'lianbangkuaidien'=>'联邦快递(Fedex-中国-英文结果）',
        'longlangkuaidi'=>'隆浪快递',
        'menduimen'=>'门对门',
        'meiguokuaidi'=>'美国快递',
        'mingliangwuliu'=>'明亮物流',
        'ocs'=>'OCS',
        'ontrac'=>'onTrac',
        'quanchenkuaidi'=>'全晨快递',
        'quanjitong'=>'全际通',
        'quanritongkuaidi'=>'全日通',
        'quanyikuaidi'=>'全一快递',
        'quanfengkuaidi'=>'全峰快递',
        'sevendays'=>'七天连锁',
        'shentong'=>'申通',
        'shunfeng'=>'顺丰',
        'shunfengen'=>'顺丰',
        'santaisudi'=>'三态速递',
        'shenghuiwuliu'=>'盛辉物流',
        'suer'=>'速尔物流',
        'shengfengwuliu'=>'盛丰物流',
        'shangda'=>'上大物流',
        'saiaodi'=>'赛澳递',
        'shenganwuliu'=>'圣安物流',
        'suijiawuliu'=>'穗佳物流',
        'tnt'=>'TNT（中文结果）',
        'tnten'=>'TNT（英文结果）',
        'ups'=>'UPS（中文结果）',
        'upsen'=>'UPS（英文结果）',
        'youshuwuliu'=>'优速物流',
        'usps'=>'USPS（中英文）',
        'wanjiawuliu'=>'万家物流',
        'wanxiangwuliu'=>'万象物流',
        'xinbangwuliu'=>'新邦物流',
        'xinfengwuliu'=>'信丰物流',
        'neweggozzo'=>'新蛋奥硕物流',
        'hkpost'=>'香港邮政',
        'yuantong'=>'圆通速递',
        'yunda'=>'韵达快运',
        'yuntongkuaidi'=>'运通快递',
        'yuanchengwuliu'=>'远成物流',
        'yafengsudi'=>'亚风速递',
        'yibangwuliu'=>'一邦速递',
        'yuanweifeng'=>'源伟丰快递',
        'yuanzhijiecheng'=>'元智捷诚',
        'yuefengwuliu'=>'越丰物流',
        'yuananda'=>'源安达',
        'yuanfeihangwuliu'=>'原飞航',
        'zhongxinda'=>'忠信达快递',
        'zhimakaimen'=>'芝麻开门',
        'yinjiesudi'=>'银捷速递',
        'zhongtong'=>'中通速递',
        'zhaijisong'=>'宅急送',
        'zhongyouwuliu'=>'中邮物流',
        'zhongsukuaidi'=>'中速快件',
        'zhongtianwanyun'=>'中天万运',
        'jd'   => '京东物流',
    ],

));


$config->merge($configM);

return $config;
