<?php
/**
 * Module.php - Module Class
 *
 * Module Class File for Bookrequest Module
 *
 * @category Config
 * @package Bookrequest
 * @author Verein onePlace
 * @copyright (C) 2020  Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.0
 */

namespace OnePlace\Bookrequest;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Mvc\MvcEvent;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Session\Config\StandardConfig;
use Laminas\Session\SessionManager;
use Laminas\Session\Container;

class Module {
    /**
     * Module Version
     *
     * @since 1.0.0
     */
    const VERSION = '1.0.0';

    /**
     * Load module config file
     *
     * @since 1.0.0
     * @return array
     */
    public function getConfig() : array {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * Load Models
     */
    public function getServiceConfig() : array {
        return [
            'factories' => [
                # Bookrequest Module - Base Model
                Model\BookrequestTable::class => function($container) {
                    $tableGateway = $container->get(Model\BookrequestTableGateway::class);
                    return new Model\BookrequestTable($tableGateway,$container);
                },
                Model\BookrequestTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Bookrequest($dbAdapter));
                    return new TableGateway('bookrequest', $dbAdapter, null, $resultSetPrototype);
                },
            ],
        ];
    }

    /**
     * Load Controllers
     */
    public function getControllerConfig() : array {
        return [
            'factories' => [
                Controller\BookrequestController::class => function($container) {
                    $oDbAdapter = $container->get(AdapterInterface::class);
                    return new Controller\BookrequestController(
                        $oDbAdapter,
                        $container->get(Model\BookrequestTable::class),
                        $container
                    );
                },
                Controller\ApiController::class => function($container) {
                    $oDbAdapter = $container->get(AdapterInterface::class);
                    return new Controller\ApiController(
                        $oDbAdapter,
                        $container->get(Model\BookrequestTable::class),
                        $container
                    );
                },
            ],
        ];
    }
}
