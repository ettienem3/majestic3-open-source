<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'FrontPanels\Controller\SetupPanel' => 'FrontPanels\Controller\SetupPanelController',
        	'FrontPanels\Controller\Panels' => 'FrontPanels\Controller\PanelsController',
        ),
    ),

	'router' => array(
			'routes' => array(
					'front-panels-setup' => array(
							'type'    => 'segment',
							'options' => array(
									'route'    => '/front/panels/setup[/:action][/:id]',
									'defaults' => array(
											'controller' => 'FrontPanels\Controller\SetupPanel',
											'action'     => 'index',
									),
							),
					),

					'front-panels-display' => array(
							'type'    => 'segment',
							'options' => array(
									'route'    => '/front/panels/display[/:action][/:id]',
									'defaults' => array(
											'controller' => 'FrontPanels\Controller\Panels',
											'action'     => 'display-panels',

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
