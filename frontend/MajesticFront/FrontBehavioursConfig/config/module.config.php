<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'FrontBehavioursConfig\Controller\Index' => 'FrontBehavioursConfig\Controller\IndexController',
        	'FrontBehavioursConfig\Controller\ProfileSummary' => 'FrontBehavioursConfig\Controller\ProfileSummaryController',
        ),
    ),

	'router' => array(
			'routes' => array(
					'front-behaviours-config' => array(
							'type'    => 'segment',
							'options' => array(
									'route'    => '/front/behaviours/config[/:action][/:id]',
// 									'constraints' => array(
// 											'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
// 									),
									'defaults' => array(
											'controller' => 'FrontBehavioursConfig\Controller\Index',
											'action'     => 'index',
									),
							),
					),
					
					'front-behaviours-profile-summary' => array(
							'type'    => 'segment',
							'options' => array(
									'route'    => '/front/behaviours/summary[/:action]',
									'defaults' => array(
											'controller' => 'FrontBehavioursConfig\Controller\ProfileSummary',
											'action'     => 'app',
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
