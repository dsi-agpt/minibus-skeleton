<?php
return array(
    'service_manager' => include 'services-config.php',
    'controllers' => array(
        'invokables' => array(
            'Jobs\Controller\FormationRest' => 'Jobs\Controller\FormationRestController'
        )
    ),
    'data_transfer_agents' => include 'data-transfer-agents.php',
    'router' => array(
        'routes' => include 'routes.config.php'
    ),
    'data_types' => include 'data-types.php',
    'data_endpoints' => include 'data-endpoints.php',
    'data_transfer_agents' => include 'data-transfer-agents.php',
    'acl' => array(
        'roles' => include 'acl-roles-config.php'
    ),
    'view_helpers' => include 'view-helpers-config.php',
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'template_path_stack' => array(
            __DIR__ . '/../view'
        )
    ),
    'doctrine' => include 'doctrine-config.php',
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo'
            )
        )
    )
);
