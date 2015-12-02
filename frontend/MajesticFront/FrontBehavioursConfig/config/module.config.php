<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'FrontBehavioursConfig\Controller\Index' => 'FrontBehavioursConfig\Controller\IndexController',
        ),
    ),
		
	'router' => array(
			'routes' => array(
					'front-behaviours-config' => array(
							'type'    => 'segment',
							'options' => array(
									'route'    => '/front/behaviours/config[/:action][/:id]',
									'constraints' => array(
											'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
									),
									'defaults' => array(
											'controller' => 'FrontBehavioursConfig\Controller\Index',
											'action'     => 'index',
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
