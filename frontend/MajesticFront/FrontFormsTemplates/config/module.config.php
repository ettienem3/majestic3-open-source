<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'FrontFormsTemplates\Controller\Index' => 'FrontFormsTemplates\Controller\IndexController',
        ),
    ),

	'router' => array(
			'routes' => array(
					'front-form-templates' => array(
							'type'    => 'segment',
							'options' => array(
									'route'    => '/front/form/templates[/:action][/:id]',
									'defaults' => array(
											'controller' => 'FrontFormsTemplates\Controller\Index',
											'action'     => 'index',
									),
							),
					),
			),
	),

	'navigation' => array(
			'default' => array(
				//listed under forms
			),
	),

    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
