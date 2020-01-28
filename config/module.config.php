<?php
/**
 * module.config.php - Bookrequest Config
 *
 * Main Config File for Bookrequest Module
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

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    # Bookrequest Module - Routes
    'router' => [
        'routes' => [
            # Module Basic Route
            'bookrequest' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/bookrequest[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9_-]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\BookrequestController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'bookrequest-api' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/bookrequest/api[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\ApiController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],

    # View Settings
    'view_manager' => [
        'template_path_stack' => [
            'bookrequest' => __DIR__ . '/../view',
        ],
    ],
];
