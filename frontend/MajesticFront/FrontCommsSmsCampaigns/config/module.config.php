<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'FrontCommsSmsCampaigns\Controller\Index' => 'FrontCommsSmsCampaigns\Controller\IndexController',
        	'FrontCommsSmsCampaigns\Controller\SmsCampaignsReplies' => 'FrontCommsSmsCampaigns\Controller\SmsCampaignsRepliesController',
        ),
    ),
		
	'router' => array(
			'routes' => array(
					'front-comms-sms-campaigns' => array(
							'type'    => 'segment',
							'options' => array(
									'route'    => '/front/comms/sms-campaigns[/:action][/:id]',
									'constraints' => array(
											'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
									),
									'defaults' => array(
											'controller' => 'FrontCommsSmsCampaigns\Controller\Index',
											'action'     => 'index',
									),
							),
					),
					'front-comms-sms-campaign-replies' => array(
							'type'    => 'segment',
							'options' => array(
									'route'    => '/front/comms/sms-campaign-replies/:sms_campaign_id[/:action][/:reply_id]',
									'defaults' => array(
											'controller' => 'FrontCommsSmsCampaigns\Controller\SmsCampaignsReplies',
											'action'     => 'index',
									),
							),
					),
			),
	),
		
	'navigation' => array(
			'default' => array(
				//listed under comms admin
			),
	),
		
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
