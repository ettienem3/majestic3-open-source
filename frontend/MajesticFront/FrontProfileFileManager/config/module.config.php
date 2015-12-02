<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'FrontProfileFileManager\Controller\Files' => 'FrontProfileFileManager\Controller\FilesController',
        ),
    ),
	'router' => array(
			'routes' => array(
					'front-profile-file-manager' => array(
							'type'    => 'segment',
							'options' => array(
									'route'    => '/front/file-manager[/:action][/:id]',
									'defaults' => array(
											'controller' => 'FrontProfileFileManager\Controller\Files',
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
