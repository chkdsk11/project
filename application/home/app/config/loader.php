<?php

$loader = new \Phalcon\Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->registerNamespaces(
    array(
        'Shop\Home\Controllers' => $config->application->controllersDir,
        'Shop\Models' => $config->application->modelsDir,
        'Shop\Libs' => $config->application->libsDir,
        'Shop\Libs\Sms\Providers' => $config->application->libsDir . 'Sms/providers/',
        'Shop\Home\Datas' => $config->application->homeDataDir,
        'Shop\Home\Services' => $config->application->homeServiceDir,
        'Shop\Home\Listens' => $config->application->homeListenDir,
        'Shop\Rules\Sms'    => $config->application->rulesDir
    )
)->register();
