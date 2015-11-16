<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'FrontUserLogin\Controller\Index' => 'FrontUserLogin\Controller\IndexController',
        ),
    ),
	'router' => array(
			'routes' => array(
					'front-user-login' => array(
							'type'    => 'segment',
							'options' => array(
									'route'    => '/user/login[/:action]',
									'constraints' => array(
											'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
									),
									'defaults' => array(
											'controller' => 'FrontUserLogin\Controller\Index',
											'action'     => 'login',
									),
							),
					),
			),
	),

	'navigation' => array(
			'default' => array(
					array(
							'label' => 'Logout',
							'route' => 'front-user-login',
							'action' => 'logout',
					),
			),
	),

    'view_manager' => array(
// 	         'display_not_found_reason' => true,
// 	         'display_exceptions'       => true,
// 	         'doctype'                  => 'HTML5',
// 	         'not_found_template'       => 'error/404',
// 	         'exception_template'       => 'error/index',
	        'template_map' => array(
	        	'layout/login-header'	=> __DIR__ . '/../view/login-layout/layout-login-header.phtml',
	            'layout/login'          => __DIR__ . '/../view/login-layout/layout-login.phtml',
	         	//'layout/login/header' 	=> __DIR__ . '/../view/login-layout/layout-login-header.phtml',
	         	'layout/login/body' 	=> __DIR__ . '/../view/login-layout/layout-login-body.phtml',
	        ),

	        'template_path_stack' => array(
	             __DIR__ . '/../view',
	        ),
    	),
);
