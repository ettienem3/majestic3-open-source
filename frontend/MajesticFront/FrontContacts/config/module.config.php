<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'FrontContacts\Controller\Index' 			=> 'FrontContacts\Controller\IndexController',
        	'FrontContacts\Controller\ContactToolkit' 	=> 'FrontContacts\Controller\ContactToolkitController',
        	'FrontContacts\Controller\ContactComms' 	=> 'FrontContacts\Controller\ContactCommsController',
        ),
    ),
	'router' => array(
			'routes' => array(
					'front-contacts' => array(
							'type'    => 'segment',
							'options' => array(
									'route'    => '/front/contacts[/:action][/:id]',
									'constraints' => array(
											'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
											//'id'     => '[0-9]+',
									),
									'defaults' => array(
											'controller' => 'FrontContacts\Controller\Index',
											'action'     => 'index',
									),
							),
					), //end front-contacts

					'front-contact-toolkit' => array(
							'type' => 'segment',
							'options' => array(
									'route' => '/front/contact/toolkit/:action/:id',
									'defaults' => array(
										'controller' => 'FrontContacts\Controller\ContactToolkit',
									),
							),
					), //end front-contact-toolkit

					'front-contact-comms' => array(
							'type' => 'segment',
							'options' => array(
								'route' => '/front/contact/comms/:id[/:action][/:comms_id]',
								'defaults' => array(
									'controller' => 'FrontContacts\Controller\ContactComms',
									'action' => 'index'
								),
						),
					), //end front-contact-comms
			),
	),

	'navigation' => array(
			'default' => array(
					array(
							'label' => 'My Contacts',
							'route' => 'front-contacts',
					),
			),
	),

    'view_manager' => array(
	    	'template_map' => array(
	    		"contact_send_comms_js" => __DIR__ . '/../view/front-contacts/index/contact_comms_js.phtml',
	    	),

	        'template_path_stack' => array(
	            __DIR__ . '/../view',
	        ),
    		'strategies' => array(
    				'ViewJsonStrategy',
    		),
    ),
);
