<?php
return array(
    'controllers' => array(
        'invokables' => array(
        	'FrontPowerTools\Controller\Announcements' 			=> 'FrontPowerTools\Controller\AnnouncementsController',
        	'FrontPowerTools\Controller\Webhooks' 				=> 'FrontPowerTools\Controller\WebhooksController',
        	'FrontPowerTools\Controller\EmailTemplates' 		=> 'FrontPowerTools\Controller\EmailTemplatesController',
        ),
    ),

	'router' => array(
			'routes' => array(
					'front-power-tools' => array(
							'type'    => 'literal',
							'options' => array(
									'route'    => '/front/power/tools',
									),

							//create the child routes
							'may_terminate' => TRUE,
							'child_routes' => array(
									'announcements' => array(
											'type' => 'segment',
											'options' => array(
													'route' => '/announcements[/:action][/:id]',
													'defaults' => array(
															'controller' => 'FrontPowerTools\Controller\Announcements',
															'action' => 'index',
													),
											),
									),

									'webhooks' => array(
											'type' => 'segment',
											'options' => array(
													'route' => '/webhooks[/:action][/:id]',
													'defaults' => array(
															'controller' => 'FrontPowerTools\Controller\Webhooks',
															'action' => 'webhooks',
													),
											),
									),
									
									"email-templates" => array(
											'type' => 'segment',
											'options' => array(
												'route' => '/email-templates[/:action][/:id]',
												'defaults' => array(
															'controller' => 'FrontPowerTools\Controller\EmailTemplates',
															'action' => 'index',
												),
											),
									),
							),
					),
			),
	),

	'navigation' => array(
		'default' => array(
				array(
						'label' => 'Tools',
						//'route' => 'front-power-tools/announcements',
						'route' => 'front-statuses',
						'pages' => array(
// 							array(
// 									'label' => 'Announcments',
// 									'route' => 'front-power-tools/announcements',
// 							),

// 							array(
// 									'label' => 'Campaigns',
// 									'route' => 'front-campaigns',
// 							),

							array(
									'label' => 'Locations',
									'route'	=>	'front-locations',
							),

							array(
									'label' => 'Statuses',
									'route' => 'front-statuses',
							),

							array(
									'label' => 'File Manager',
									'route' => 'front-profile-file-manager',
							),

							array(
									'label' => 'Webhooks',
									'route' => 'front-power-tools/webhooks',
							),

// 							array(
// 									'label' => 'Profile Automation',
// 									'route' => 'front-power-tools/profile-automation',
// 							),
						),
					)
				)
			),

    'view_manager' => array(
    	'template_map' => array(
    		'front-newsfeed-panel' => __DIR__ . '/../view/front-power-tools/news-feed/news-feed-sidebar-panel.phtml',
    	),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
