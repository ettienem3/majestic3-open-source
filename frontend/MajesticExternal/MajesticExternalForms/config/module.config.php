<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'MajesticExternalForms\Controller\Index' 		=> 'MajesticExternalForms\Controller\IndexController',
        	'MajesticExternalForms\Controller\Console' 		=> 'MajesticExternalForms\Controller\ConsoleController',
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
														"user-bypass-login" => TRUE,
												),
										),
								),

								'vf-ajax-request' => array(
										'type' => 'segment',
										'options' => array(
												'route' => '/vf-ajax-request',
												'defaults' => array(
														'controller' => 'MajesticExternalForms\Controller\Index',
														'action' => 'vf-ajax-request',
														"user-bypass-login" => TRUE,
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
														"user-bypass-login" => TRUE,
												),
										),
								),

								//cache webhook
								'form-cache-webhook' => array(
										'type' => 'segment',
										'options' => array(
												'route' => '/clear-form-cache',
												'defaults' => array(
														'controller' => 'MajesticExternalForms\Controller\Index',
														'action' => 'clear-form-cache',
														"user-bypass-login" => TRUE,
												),
										),
								),
							),
					),
			),
	),

	'console' => array(
			'router' => array(
					'routes' => array(
							'console-external-forms-plugin' => array(
									'options' => array(
											'route' => 'Formprocessaction execute <execute-task> --argument1= --host=',
											'defaults' => array(
													'controller' => 'MajesticExternalForms\Controller\Console',
													'action' => 'execute'
											),
									),
							),
					)
			)
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
        	'layout/external/angular'		=> __DIR__ . '/../view/forms-layout/layout-form-angular.phtml',
        ),

        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
