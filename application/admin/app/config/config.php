<?php

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
        'logDir'        => __DIR__.'/../logs/'
    )
));

$config->merge($configM);

return $config;
