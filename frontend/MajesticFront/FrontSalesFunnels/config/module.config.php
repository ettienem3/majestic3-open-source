<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'FrontSalesFunnels\Controller\SalesFunnels' => 'FrontSalesFunnels\Controller\SalesFunnelsController',
        ),
    ),
		
	'router' => array(
			'routes' => array(
					'front-sales-funnels' => array(
							'type'    => 'segment',
							'options' => array(
									//'route'    => '/front/sales-funnels/:reg_id[/:action][/:id]',
									'route'    => '/front/trackers/:reg_id[/:action][/:id]',
									'defaults' => array(
											'controller' => 'FrontSalesFunnels\Controller\SalesFunnels',
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
