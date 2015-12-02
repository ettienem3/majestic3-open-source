<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'FrontSmsAccountsAdmin\Controller\SmsAccountsAdmin' => 'FrontSmsAccountsAdmin\Controller\SmsAccountsAdminController',
        ),
    ),
	'router' => array(
			'routes' => array(
					'front-sms-accounts-admin' => array(
							'type'    => 'segment',
							'options' => array(
									'route'    => '/front/sms-accounts/admin[/:action][/:id]',
									'constraints' => array(
											'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
									),
									'defaults' => array(
											'controller' => 'FrontSmsAccountsAdmin\Controller\SmsAccountsAdmin',
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
