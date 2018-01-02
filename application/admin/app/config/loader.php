<?php

$loader = new \Phalcon\Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->registerNamespaces(
        array(
            'Shop\Admin\Controllers' => $config->application->controllersDir,
            'Shop\Models' => $config->application->modelsDir,
            'Shop\Libs' => $config->application->libsDir,
            'Shop\Datas' => $config->application->datasDir,
            'Shop\Services' => $config->application->servicesDir,
            'Shop\Admin\Listen' => $config->application->listenDir,
            'Shop\Home\Listen' => APP_PATH . '/serve/listens/homelisten/',
            'Shop\Home\Datas' => APP_PATH . '/serve/datas/homedata/',
            'Shop\Home\Services' => APP_PATH . '/serve/services/homeservice/',
            'Shop\Libs\Sms\Providers' => $config->application->libsDir . 'Sms/providers/',
            'Shop\Rules\Sms'    => APP_PATH . '/serve/rules/sms',
            'Shop\Collections' => APP_PATH . '/serve/collections/',
            'Phalcon' => APP_PATH.'/vendor/phalcon/incubator/Library/Phalcon'
        )
)->register();
