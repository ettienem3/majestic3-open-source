<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'FrontUsers\Controller\Index' 				=> 'FrontUsers\Controller\IndexController',
        	'FrontUsers\Controller\UserRolesAdmin' 		=> 'FrontUsers\Controller\UserRolesAdminController',
        	'FrontUsers\Controller\UserRolesAllocate' 	=> 'FrontUsers\Controller\UserRolesAllocateController',
        	'FrontUsers\Controller\RolesAclLinksAdmin' 	=> 'FrontUsers\Controller\RolesAclLinksAdminController',
        	'FrontUsers\Controller\UserTasks' 			=> 'FrontUsers\Controller\UserTasksController',
        	'FrontUsers\Controller\UserAclRules'		=> 'FrontUsers\Controller\UserAclRulesController',
        	'FrontUsers\Controller\UserToolkit'			=> 'FrontUsers\Controller\UserToolkitController',
        ),
    ),
	'router' => array(
			'routes' => array(
					'front-users' => array(
							'type'    => 'segment',
							'options' => array(
									'route'    => '/front/users[/:action][/:id]',
									'defaults' => array(
											'controller' => 'FrontUsers\Controller\Index',
											'action'     => 'index',
									),
							),
					),

					'front-users-tasks' => array(
							'type'    => 'segment',
							'options' => array(
									'route'    => '/front/users/tasks/:user_id[/:action][/:id]',
									'defaults' => array(
											'controller' => 'FrontUsers\Controller\UserTasks',
											'action'     => 'index',
									),
							),
					),

					'front-users-toolkit' => array(
							'type'    => 'segment',
							'options' => array(
									'route'    => '/front/users/toolkit[/:action][/:id]',
									'defaults' => array(
											'controller' => 'FrontUsers\Controller\UserToolkit',
											'action'     => 'iframe-user-toolkit-section',
									),
							),
					),

					'front-users-roles' => array(
							'type' => 'literal',
							'options' => array(
									'route' => '/front/users/roles',
							),

							'may_terminate' => TRUE,
							'child_routes' => array(
									'admin' => array(
										'type' => 'segment',
										'options' => array(
											'route' => '/role[/:action][/:id]',
											'defaults' => array(
												'controller' => 'FrontUsers\Controller\UserRolesAdmin',
												'action'     => 'index',
											),
										),
									), //end of admin child route2

									'user' => array(
											'type' => 'segment',
											'options' => array(
													'route' => '/user[/:action][/:user_id][/:id]',
													'defaults' => array(
															'controller' => 'FrontUsers\Controller\UserRolesAllocate',
													),
											),
									), //end of user child route
							),
					),//end of front-user-roles

					'front-role-acl-links' => array(
						'type' => 'literal',
						'options' => array(
							'route' => '/front/users/roles/acl',
						),

						'may_terminate' => TRUE,
						'child_routes' => array(
							'admin' => array(
								'type' => 'segment',
								'options' => array(
									'route' => '/admin/:role_id[/:action][/:type][/:resource_id]',
									'defaults' => array(
										'controller' => 'FrontUsers\Controller\RolesAclLinksAdmin',
										'action' => 'index',
									),
								),
							),
						),
					), //front-role-acl-links

					'front-user-data-acl-rules' => array(
						'type' => 'segment',
						'options' => array(
							'route' => '/front/user/data/acl[/:user_id][/:action]',
							'defaults' => array(
								'controller' => 'FrontUsers\Controller\UserAclRules',
								'action' => 'index',
							),
						),

					), //front user-data-acl-rules

			),
	),

	'navigation' => array(
			'default' => array(
					array(
							'label' => 'Users',
							'route' => 'front-users',
							'pages' => array(
									array(
											'label' => 'User Roles',
											'route' => 'front-users-roles/admin',
									),
									array(
											'label' => 'User Data Access Rules',
											'route' => 'front-user-data-acl-rules',
									),
							),
					),
			),
	),

    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
