<?php
return array(
    'controllers' => array(
        'invokables' => array(
			'FrontReports\Controller\ReportViewer' => 'FrontReports\Controller\ReportViewerController',
        ),
    ),
		
	'router' => array(
			'routes' => array(
					'front-report-viewer' => array(
							'type'    => 'segment',
							'options' => array(
									'route'    => '/front/reports/basic/view[/:action][/:id]',
									'defaults' => array(
											'controller' => 'FrontReports\Controller\ReportViewer',
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
