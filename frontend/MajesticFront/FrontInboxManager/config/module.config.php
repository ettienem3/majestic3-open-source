<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'FrontInboxManager\Controller\InboxManager' => 'FrontInboxManager\Controller\InboxManagerController',
        ),
    ),

	'router' => array(
			'routes' => array(
					'front-inbox-manager' => array(
							'type'    => 'segment',
							'options' => array(
									'route'    => '/front/inbox/manager[/:action][/:id]',
									'defaults' => array(
											'controller' => 'FrontInboxManager\Controller\InboxManager',
											'action'     => 'index',
									),
							),
					),
			),
	),

	'navigation' => array(
			'default' => array(
					array(
							'label' => 'Inbox',
							'route' => 'front-inbox-manager',
					),
			),
	),

    'view_manager' => array(
	        'template_path_stack' => array(
	            __DIR__ . '/../view',
	        ),
    		'strategies' => array(
    				'ViewJsonStrategy',
    		),
    ),
);
