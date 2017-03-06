<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'FrontLinks\Controller\Index' => 'FrontLinks\Controller\IndexController',
        ),
    ),

	'router' => array(
			'routes' => array(
					'front-links' => array(
							'type'    => 'segment',
							'options' => array(
									'route'    => '/front/links[/:action][/:id]',
									'constraints' => array(
											'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
											//'id'     => '[0-9]+',
									),
									'defaults' => array(
											'controller' => 'FrontLinks\Controller\Index',
											'action'     => 'index',
									),
							),
					),
			),
	),

	'navigation' => array(
			'default' => array(
				//listed under comms
			),
		),

    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    	'template_map' => array(
    		'links/layout-app' 		=> __DIR__ . '/../view/layout/angular-app.phtml',
    	),
    	'strategies' => array(
    			'ViewJsonStrategy',
    	),    		
    ),
);
