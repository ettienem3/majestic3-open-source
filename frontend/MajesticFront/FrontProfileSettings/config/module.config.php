<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'FrontProfileSettings\Controller\ProfileSettings' => 'FrontProfileSettings\Controller\ProfileSettingsController',
        	'FrontProfileSettings\Controller\NativeProfileSettings' => 'FrontProfileSettings\Controller\NativeProfileSettingsController',
        ),
    ),
		
	'router' => array(
			'routes' => array(
					'front-profile-settings' => array(
							'type'    => 'segment',
							'options' => array(
									'route'    => '/front/profile/admin[/:action][/:id]',
									'defaults' => array(
											'controller' => 'FrontProfileSettings\Controller\ProfileSettings',
											'action'     => 'index',
									),
							),
					),
					
					'front-profile-native-settings' => array(
							'type'    => 'segment',
							'options' => array(
									'route'    => '/front/profile/native/admin[/:action]',
									'defaults' => array(
											'controller' => 'FrontProfileSettings\Controller\NativeProfileSettings',
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
