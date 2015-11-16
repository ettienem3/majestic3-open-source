<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'FrontCommsTemplates\Controller\Index' => 'FrontCommsTemplates\Controller\IndexController',
        ),
    ),

	'router' => array(
			'routes' => array(
					'front-comms-templates' => array(
							'type'    => 'segment',
							'options' => array(
									'route'    => '/front/comms/look-and-feel[/:action][/:id]',
									'defaults' => array(
											'controller' => 'FrontCommsTemplates\Controller\Index',
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
    ),
);
