<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'FrontCommsBulkSend\Controller\Index' => 'FrontCommsBulkSend\Controller\IndexController',
        	'FrontCommsBulkSend\Controller\BulkSend' => 'FrontCommsBulkSend\Controller\BulkSendController',
        ),
    ),
		
	'router' => array(
			'routes' => array(
					'front-comms-bulksend' => array(
							'type'    => 'segment',
							'options' => array(
									'route'    => '/front/comms/bulksend[/:action][/:id][/:bulk_send_id]',
									'defaults' => array(
											'controller' => 'FrontCommsBulkSend\Controller\Index',
											'action'     => 'index',
									),
							),
					),
					
					'front-comms-bulksend-admin' => array(
							'type'    => 'segment',
							'options' => array(
									'route'    => '/front/comms/bulksend/admin[/:action][/:id]',
									'defaults' => array(
											'controller' => 'FrontCommsBulkSend\Controller\BulkSend',
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
