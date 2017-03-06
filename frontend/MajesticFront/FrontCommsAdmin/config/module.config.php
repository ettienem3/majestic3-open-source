<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'FrontCommsAdmin\Controller\Comms'					=> 'FrontCommsAdmin\Controller\CommsController',
        	'FrontCommsAdmin\Controller\CommDates'				=> 'FrontCommsAdmin\Controller\CommDatesController',
        	'FrontCommsAdmin\Controller\Journeys'				=> 'FrontCommsAdmin\Controller\JourneysController',
        	'FrontCommsAdmin\Controller\JourneysTest'			=> 'FrontCommsAdmin\Controller\JourneysTestController',
        	'FrontCommsAdmin\Controller\CommEmbeddedImages'		=> 'FrontCommsAdmin\Controller\CommEmbeddedImagesController',
        ),
    ),
	'router' => array(
			'routes' => array(
					'front-comms-admin' => array(
							'type'    => 'literal',
							'options' => array(
									'route'    => '/front/comms/admin',
							),
							
							//setup child routes
							'may_terminate' => TRUE,
							'child_routes' => array(
								'comms' => array(
									'type' => 'segment',
									'options' => array(
										'route' => '/comms/:journey_id[/:action][/:id]',
										'defaults' => array(
											'controller' => 'FrontCommsAdmin\Controller\Comms',
											'action' => 'index',
										),
									),
								),
									
								'comm-embedded-images' => array(
										'type' => 'segment',
										'options' => array(
												'route' => '/comms/embedded-images/:journey_id/:comm_id[/:action][/:id]',
												'defaults' => array(
														'controller' => 'FrontCommsAdmin\Controller\CommEmbeddedImages',
														'action' => 'index',
												),
										),
								),
									
								'dates' => array(
									'type' => 'segment',
									'options' => array(
											'route' => '/dates[/:action][/:id]',
											'defaults' => array(
													'controller' => 'FrontCommsAdmin\Controller\CommDates',
													'action' => 'app',
											),
									),
								),
									
								'journeys' => array(
									'type'	=> 'segment',
									'options'	=> array(
											'route'		=> '/journeys[/:action][/:id]',
											'defaults'	=> array(
													'controller'	=> 'FrontCommsAdmin\Controller\Journeys',
													'action'		=> 'index',
												),
										),
									),
									
								'test-journeys' => array(
										'type'	=> 'segment',
										'options'	=> array(
												'route'		=> '/test-journeys[/:action][/:id]',
												'defaults'	=> array(
														'controller'	=> 'FrontCommsAdmin\Controller\JourneysTest',
														'action'		=> 'app',
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
							'label' => 'My Journeys',
							'route'	=> 'front-comms-admin/journeys',
							'pages' => array(
// 									array(
// 											'label' => 'Setup Recurring Events',
// 											'route'	=>	'front-comms-admin/dates',
// 									),
						
									
									array(
											'label' => 'Create / Update a look and feel',
											'route' => 'front-comms-templates',
									),
									
									array(
											'label' => 'Setup Communication Links',
											'route' => 'front-links',
									),
									
// 									array(
// 											'label' => 'Setup SMS Campaigns',
// 											'route' => 'front-comms-sms-campaigns',
// 									),
									
// 									array(
// 											'label' => "Manage SMS Accounts",
// 											'route' => "front-sms-accounts-admin",	
// 									),
									
									array(
											'label' => "Send a Bulk Journey",
											'route' => "front-comms-bulksend",
									),
								),
							),
						),
					),
		
    'view_manager' => array(
    	'template_map' => array(
    		"layout-set-comm-delay" 				=> __DIR__ . '/../view/front-comms-admin/comms/layout-set-comm-delay.phtml',
    		"layout-comm-data-additions" 			=> __DIR__ . '/../view/front-comms-admin/comms/layout-comms-data-additions.phtml',
    	),
    		
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
