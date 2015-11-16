<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'FrontFormAdmin\Controller\Index' 					=> 'FrontFormAdmin\Controller\IndexController',
        	'FrontFormAdmin\Controller\Fields' 					=> 'FrontFormAdmin\Controller\FieldsController',
        	'FrontFormAdmin\Controller\FormFields' 				=> 'FrontFormAdmin\Controller\FormFieldsController',
        	'FrontFormAdmin\Controller\GenericFields' 			=> 'FrontFormAdmin\Controller\GenericFieldsController',
        	'FrontFormAdmin\Controller\ReplaceFields' 			=> 'FrontFormAdmin\Controller\ReplaceFieldsController',
        	'FrontFormAdmin\Controller\SalesFunnelOptions' 		=> 'FrontFormAdmin\Controller\SalesFunnelOptionsController',
        ),
    ),

	'router' => array(
			'routes' => array(
					'front-form-admin' => array(
							'type'    => 'literal',
							'options' => array(
									'route'    => '/front/form/admin',
									'constraints' => array(
											'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
											//'id'     => '[0-9]+',
									),
									'defaults' => array(
											'controller' => 'FrontFormAdmin\Controller\Index',
											'action'     => 'index',
									),
							),

							//create child routes
							'may_terminate' => TRUE,
							'child_routes' => array(
									//create/edit/delete form
									'form' => array(
												'type' => 'segment',
												'options' => array(
															'route' => '/form/:action[/:id]',
															'defaults' => array(
																		'controller' => 'FrontFormAdmin\Controller\Index',
																		'action' => 'index',
																	),
														),
											),

									//manage sales funnel advanced options
									'sales-funnel' => array(
											'type' => 'segment',
											'options' => array(
													'route' => '/tracker/:action/:id',
													'defaults' => array(
															'controller' => 'FrontFormAdmin\Controller\SalesFunnelOptions',
															'action' => 'sf-advanced-settings',
													),
											),
									),

									//create/edit/delete fields
									'fields' => array(
											'type' => 'segment',
											'options' => array(
													'route' => '/fields/:action[/:id]',
													'defaults' => array(
															'controller' => 'FrontFormAdmin\Controller\Fields',
															'action' => 'index',
													),
											),
									),

									//create/edit/delete generic fields
									'generic-fields' => array(
											'type' => 'segment',
											'options' => array(
													'route' => '/fields/generic[/:action][/:id]',
													'defaults' => array(
															'controller' => 'FrontFormAdmin\Controller\GenericFields',
															'action' => 'index',
													),
											),
									),

									//create/edit/delete replace fields
									'replace-fields' => array(
											'type' => 'segment',
											'options' => array(
													'route' => '/fields/replace[/:action][/:id]',
													'defaults' => array(
															'controller' => 'FrontFormAdmin\Controller\ReplaceFields',
															'action' => 'index',
													),
											),
									),

									//manipulate fields added to forms
									'form-fields' => array(
											'type' => 'segment',
											'options' => array(
													'route' => '/form/field/:action/:form_id[/:field_id][/:field_type]',
													'constraints' => array(
															'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
													),
													'defaults' => array(
															'controller' => 'FrontFormAdmin\Controller\FormFields',
															'action' => 'index',
													),
											),
									),
							),
					),
			),
	),

	'navigation' => array(
			'default' => array(
					array(
							'label' => 'My Forms',
							'route' => 'front-form-admin',
							'pages' => array(
									array(
											'label' => 'My Fields',
											'route' => 'front-form-admin/fields',
									),
									array(
											'label' => 'Setup generic #fields',
											'route' => 'front-form-admin/generic-fields',
									),
									array(
											'label' => 'Replace Fields',
											'route' => 'front-form-admin/replace-fields',
									),
									array(
											'label' => 'Look & Feel',
											'route' => 'front-form-templates',
									),
							),
					),
				),
			),

    'view_manager' => array(
        'template_path_stack' => array(
          "FrontFormAdmin" =>  __DIR__ . '/../view',
        ),
    	'template_map' => array(
    		'form-fields-layout/edit-tracker-field' => __DIR__ . '/../view/front-form-admin/form-fields/edit-tracker-field.phtml',
    		'form-fields-layout/form-field-helper' => __DIR__ . '/../view/front-form-admin/form-fields/form-field-helper.phtml',
    	),
    ),
);
