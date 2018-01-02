<?php
/**
 * Services are globally registered in this file
 *
 * @var \Phalcon\Config $config
 */
use Shop\Models\BaseModel;

include __DIR__ . '/../../../../common/config/services.php';

/**
 * Shared configuration service
 */
$di->setShared('config', function () {
    return include __DIR__ . '/config.php';
});

//注册默认命名空间
$di->set('dispatcher', function() {
    $dispatcher = new \Phalcon\Mvc\Dispatcher();
    $dispatcher->setDefaultNamespace('Shop\Home\Controllers');
    return $dispatcher;
});

$di->set('model',function(){
    $model=new BaseModel();
    return $model;
});

/**
 * Shared loader service
 */
$di->setShared('loader', function () {
    $config = $this->getConfig();
    /**
     * Include Autoloader
     */
    include __DIR__ . '/loader.php';
    return $loader;
});