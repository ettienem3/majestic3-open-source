<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'MajesticExternalUtilities\Controller\TrackLinks' 	=> 'MajesticExternalUtilities\Controller\TrackLinksController',
        	'MajesticExternalUtilities\Controller\Comms' 		=> 'MajesticExternalUtilities\Controller\CommsController',
        	'MajesticExternalUtilities\Controller\Locations' 	=> 'MajesticExternalUtilities\Controller\LocationsController', 
        ),
    ),
		
	'router' => array(
			'routes' => array(
					'majestic-external-track-links' => array(
							'type'    => 'segment',
							'options' => array(
									'route'    => '/utils/comms/links/track/:comm_id/:link_id',
									'defaults' => array(
											'controller' => 'MajesticExternalUtilities\Controller\TrackLinks',
											'action'     => 't',
									),
							),
					),
					
					'majestic-external-view-comms' => array(
							'type'    => 'segment',
							'options' => array(
									'route'    => '/utils/comms/view[/:comm_history_id]',
									'defaults' => array(
											'controller' => 'MajesticExternalUtilities\Controller\Comms',
											'action'     => 'view-online',
									),
							),
					),
					
					'majestic-external-locations-util' => array(
							'type' => 'segment',
							'options' => array(
								'route' => '/utils/ajax/locations/:action',
								'defaults' => array(
									'controller' => 'MajesticExternalUtilities\Controller\Locations',
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
