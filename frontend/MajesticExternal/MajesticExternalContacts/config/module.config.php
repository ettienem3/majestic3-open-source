<?php
return array(
    'controllers' => array(
        'invokables' => array(
        	'MajesticExternalContacts\Controller\Unsubscribe' 	=> 'MajesticExternalContacts\Controller\UnsubscribeController',
        	'MajesticExternalContacts\Controller\Noint' 		=> 'MajesticExternalContacts\Controller\NointController',
        ),
    ),
		
	'router' => array(
			'routes' => array(
					'majestic-external-contacts-unsub' => array(
							'type'    => 'segment',
							'options' => array(
									'route'    => '/contacts/info/:reg_id[/:action]',
									'defaults' => array(
											'controller' => 'MajesticExternalContacts\Controller\Unsubscribe',
											'action'     => 'index',
									),
							),
					),
					
					'majestic-external-contacts-noint' => array(
							'type'    => 'segment',
							'options' => array(
									'route'    => '/contacts/info/noint/:reg_id/:comm_history_id',
									'defaults' => array(
											'controller' => 'MajesticExternalContacts\Controller\Noint',
											'action'     => 'noint',
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
//         'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/external/contacts'           => __DIR__ . '/../view/contacts-layout/layout.phtml',
        ),

        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
