<?php
return array(
    'controllers' => array(
        'invokables' => array(
        		'FrontStatuses\Controller\ContactStatuses' => 'FrontStatuses\Controller\ContactStatusesController',

        ),
    ),
		
	'router' => array(
			'routes' => array(
					'front-statuses' => array(
							'type'    => 'segment',
							'options' => array(
									'route'    => '/front/statuses[/:action][/:id]',
									'constraints' => array(
											'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
									),
									'defaults' => array(
											'controller' => 'FrontStatuses\Controller\ContactStatuses',
											'action'     => 'index',
									),
							),
							
							'may_terminate' => TRUE,
							'child_routes' => array( // Create segment child "contact/statuses" routes
									'contact-statuses' => array(
											'type' => 'segment', //contains data values
											'options' => array(
													'route' => 'contact/statuses[/:id]',
													'defaults' => array(
															'controller' => 'FrontStatuses\Controller\ContactStatuses',
													),	//end default
											),	// end options
									),	// end contact-statuses
							),	// end child_routes
					),
			),
	),
		
	'navigation' => array(
			'default' => array(
					array(
							'label' => 'Statuses',
							'route' => 'front-statuses',
					),
			),
	),
		
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
