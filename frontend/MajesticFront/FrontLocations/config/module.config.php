<?php
return array(
    'controllers' => array(
        'invokables' => array(
        	'FrontLocations\Controller\Index' => 'FrontLocations\Controller\IndexController',
        ),
    ),

	'router' => array(
			'routes' => array(
					'front-locations' => array(
							'type'    => 'literal',
							'options' => array(
									'route'    => '/front/locations',
									'constraints' => array(
											'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
											'id'     => '[0-9]+',
									),
									'defaults' => array(
											'controller' => 'FrontLocations\Controller\Index',
											'action'     => 'index',
									),
							),

							"may_terminate" => TRUE,
							"child_routes" => array(

									"countries" => array(
											"type" => "segment",
											"options" => array(
													"route" => "/countries[/:action][/:id]",
													"defaults" => array(
															"controller" => "FrontLocations\Controller\Index",
															"action" => "countries",
													),
											),
									),

									"provinces" => array(
											"type" => "segment",
											"options" => array(
													"route" => "/provinces[/:action][/:id]",
													"defaults" => array(
															"controller" => "FrontLocations\Controller\Index",
															"action" => "provinces",
													),
											),
									),

									"cities" => array(
											"type" => "segment",
											"options" => array(
													"route" => "/cities[/:action][/:id]",
													"defaults" => array(
															"controller" => "FrontLocations\Controller\Index",
															"action" => "cities",
													),
											),
									),
							),
					),
			),
	),

	'navigation' => array(
			'default' => array(
				//listed under tools
			),
	),

    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
