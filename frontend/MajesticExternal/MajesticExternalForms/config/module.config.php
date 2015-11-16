<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'MajesticExternalForms\Controller\Index' => 'MajesticExternalForms\Controller\IndexController',
        ),
    ),

	'router' => array(
			'routes' => array(
					'majestic-external-forms' => array(
							'type'    => 'literal',
							'options' => array(
									'route'    => '/forms',
									'defaults' => array(
											'controller' => 'MajesticExternalForms\Controller\Index',
											'action'     => 'index',
									),
							),
							'may_terminate' => TRUE,
							'child_routes' => array(
								//bf
								'bf' => array(
									'type' => 'segment',
									'options' => array(
										'route' => '/bf/:fid[/:reg_id]',
										'defaults' => array(
											'controller' => 'MajesticExternalForms\Controller\Index',
											'action' => 'bf',
											"user-bypass-login" => TRUE,
										),
									),
								),

								'bf-json' => array(
										'type' => 'segment',
										'options' => array(
												'route' => '/bf/json/:fid',
												'defaults' => array(
														'controller' => 'MajesticExternalForms\Controller\Index',
														'action' => 'bf-json',
														"user-bypass-login" => TRUE,
												),
										),
								),

								//ajax-load-contact
								'ajax-load-contact' => array(
										'type' => 'segment',
										'options' => array(
												'route' => '/ajax-load-contact',
												'defaults' => array(
														'controller' => 'MajesticExternalForms\Controller\Index',
														'action' => 'ajax-load-contact',
														"user-bypass-login" => TRUE,
												),
										),
								),

								//bfs
								'bfs' => array(
										'type' => 'segment',
										'options' => array(
												'route' => '/bfs/:fid[/:reg_id]',
												'defaults' => array(
														'controller' => 'MajesticExternalForms\Controller\Index',
														'action' => 'bfs',
														"user-bypass-login" => TRUE,
												),
										),
								),

								//vf
								'vf' => array(
										'type' => 'segment',
										'options' => array(
												'route' => '/vf/:fid[/:reg_id]',
												'defaults' => array(
														'controller' => 'MajesticExternalForms\Controller\Index',
														'action' => 'vf',
												),
										),
								),

								//vfs
								'vfs' => array(
										'type' => 'segment',
										'options' => array(
												'route' => '/vfs/:fid[/:reg_id]',
												'defaults' => array(
														'controller' => 'MajesticExternalForms\Controller\Index',
														'action' => 'vfs',
												),
										),
								),
							),
					),
			),
	),

    'view_manager' => array(
//         'display_not_found_reason' => true,
//         'display_exceptions'       => false,
//         'doctype'                  => 'HTML5',
//         'not_found_template'       => 'error/404',
//          'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/external/forms'         => __DIR__ . '/../view/forms-layout/layout.phtml',
        	'layout/external/forms/json'    => __DIR__ . '/../view/forms-layout/layout-json.phtml',
        	'layout/forms/header'			=> __DIR__ . '/../view/forms-layout/layout-forms-header.phtml',
        ),

        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
