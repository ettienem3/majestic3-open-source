<?php
namespace FrontCore\Events;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontUserLogin\Models\FrontUserSession;
use FrontProfileSettings\Models\NativeProfileSettingsModel;

class FrontCoreUserMenuEvents extends AbstractCoreAdapter
{
	/**
	 * Container for the View Model
	 * @var \Zend\View\Model\ViewModel
	 */
	private $objViewModel;

	/**
	 * Container for the configured arrat from which the menu is generated
	 * @var unknown
	 */
	private $arr_menu = false;

	/**
	 * Container for the Logged in User's Session Object
	 * @var \Zend\Session\Container
	 */
	private $objUser;

	/**
	 * Container for Profile Settings
	 * @var \FrontProfileSettings\Entities\FrontProfileNativeSettingsProfileEntity
	 */
	private $objProfileSettings;

	public function registerEvents()
	{
		$eventManager = $this->getEventManager()->getSharedManager();
		$serviceManager = $this->getServiceLocator();

		//register listeners
		/**
		 * Reset user menu to default
		 */
		$eventManager->attach(
				"*",
				'user.menu.reset',
				function ($event) use ($serviceManager) {
					return $serviceManager->get(__CLASS__)->configureDefaultMenu($event);
				}//end function
			);

		/**
		 * Retrieve current user menu
		 */
		$eventManager->attach(
				"*",
				'user.menu.retrieve',
				function ($event) use ($serviceManager) {
					return $serviceManager->get(__CLASS__)->retrieveConfiguredMenu($event);
				}//end function
			);

		/**
		 * Revise current user menu
		 */
		$eventManager->attach(
				"*",
				'user.menu.reconfigure',
				function ($event) use ($serviceManager) {
					return $serviceManager->get(__CLASS__)->setConfiguredMenu($event);
				}//end function
			);
	}//end function

	/**
	 * Register view model
	 * @param \Zend\View\Model\ViewModel $objViewModel
	 */
	public function setViewModel($objViewModel)
	{
		$this->objViewModel = $objViewModel;
	}//end function

	/**
	 * Load the current menu configuration for the user
	 * @param unknown $event
	 */
	private function retrieveConfiguredMenu($event)
	{
		$arr_data = array(
			'arr_menu' => $this->arr_menu,
			'objProfileSettings' => $this->objProfileSettings,
			'objUser' => $this->objUser,
		);

		return $arr_data;
	}//end function

	/**
	 * Manually reconfigure user set menu
	 * @param unknown $event
	 */
	private function setConfiguredMenu($event)
	{
		$arr_data = $event->getParam('arr_data');
		$arr_menu = $event->getParam('arr_menu');

		if (is_array($arr_menu) && isset($arr_menu['arr_primary_menu']))
		{
			$this->objViewModel->setVariable('arr_primary_menu', $arr_menu['arr_primary_menu']);
			$this->arr_menu['arr_primary_menu'] = $arr_menu['arr_primary_menu'];
			$flag_primary_menu_set = TRUE;
		}//end if

		if (is_array($arr_menu) && isset($arr_menu['arr_secondary_menu']))
		{
			$this->objViewModel->setVariable('arr_secondary_menu', $arr_menu['arr_secondary_menu']);
			$this->arr_menu['arr_secondary_menu'] = $arr_menu['arr_secondary_menu'];
			$flag_secodary_menu_set = TRUE;
		}//end if

		if (is_array($arr_menu) && isset($arr_menu['arr_plugins']))
		{
			$this->objViewModel->setVariable('arr_plugins', $arr_menu['arr_plugins']);
			$this->arr_menu['arr_plugins'] = $arr_menu['arr_plugins'];
			$flag_plugins_set = TRUE;
		}//end if

		$arr_data = array(
				'arr_menu' => $this->arr_menu,
				'objProfileSettings' => $this->objProfileSettings,
				'objUser' => $this->objUser,
		);

		//clear user cached menu data
		$this->objUser->main_menu_html = false;

		return $arr_data;
	}//end function

	public function configureDefaultMenu()
	{
		//set user object
		if (!$this->objUser)
		{
			$this->objUser = FrontUserSession::isLoggedIn();
		}//end if

		if (!$this->objUser)
		{
			$this->objViewModel->setVariable('arr_primary_menu', array());
			$this->objViewModel->setVariable('arr_secondary_menu', array());
			$this->objViewModel->setVariable('arr_plugins', array());

			$this->arr_menu = array(
					'arr_primary_menu' 		=> array(),
					'arr_secondary_menu' 	=> array(),
					'arr_plugins' 			=> array(),
			);

			return $this->arr_menu;
		}//end if

		//set some vars
		$this->objProfileSettings = NativeProfileSettingsModel::readProfileSettings();
		$objUserPreferences = FrontUserSession::getUserLocalStorageObject();
		$arr_app_config = $this->getServiceLocator()->get('config');

		//aligned to the left
		$arr_primary_menu = array(
				$this->objProfileSettings->get("menu_main_relationship", "Relationship") => array(
						"My Contacts" => array(
								"route" => "front-contacts",
						),

						"My Journeys" => array(
								"route" => "front-comms-admin/journeys",
						),

						"My To-do List" => array(
								"route" => "front-users-tasks",
								"url" => $this->url("front-users-tasks", array("user_id" => $this->objUser->id))
						),

						"advanced" => array(
								"Send Requests" => array(
										"route" => "front-comms-bulksend-admin",
								),

								"Date Triggered Journeys" => array(
										"route" => "front-comms-admin/dates",
										'url' => $this->url('front-comms-admin/dates', array('action' => 'app')),
								),

								"Create / Update a look and feel" => array(
										"route" => "front-comms-templates",
								),

								"My Links" => array(
										"route" => "front-links",
								),
						),
				), //end Relationship

				$this->objProfileSettings->get("menu_main_data", "Data") => array(
						"My Forms" => array(
								"route" => "front-form-admin/form",
						),

						"My Images and Documents" => array(
								"route" => "front-profile-file-manager",
						),

						"My Fields" => array(
								"nested" => array(
										"Manage all fields" => array(
												"route" => "front-form-admin/fields",
										),

								),
						),

						"My # Library" => array(
								"nested" => array(
										"View replace fields" => array(
												"route" => "front-form-admin/replace-fields",
										),

										"Setup generic fields" => array(
												"route" => "front-form-admin/generic-fields",
										),
								),
						),

						"Reports" => array(
								"nested" => array(
										"Basic Reports" => array(
												"route" => "front-report-viewer",
										),
										"Dashboards" => array(
												"route" => "", //url replaces route
												'url' => $this->url('front-dashboard-viewer', array('action' => 'dashboard-reports')),
										),
								)
						),

						"advanced" => array(
								"Create / Update a look and feel" => array(
										"route" => "front-form-templates",
								),

								"Manage Statuses" => array(
										"route" => "front-statuses",
								),

								"Locations" => array(
										"route" => "front-locations",
								),
						),
				), //end Data

				$this->objProfileSettings->get("menu_main_sale", "Sale") => array(
						"My Trackers" => array(
								"route" => "front-form-admin/form",
								"url" => $this->url("front-form-admin/form") . "?forms_type_id=3",
						),

						// 		"My Bridges" => array(
						// 			"route" => "home",
						// 		),
				), //end Sale
		);

		//aligned to the right
		$arr_secondary_menu = array(
				$this->objProfileSettings->get("menu_main_administration", "Administration") => array(
						"Manage Users" => array(
								"route" => "front-users",
						),

						"advanced" => array(
								"Profile Information" => array(
										"route" => "front-profile-settings",
								),

								"Profile Options" => array(
										"route" => "front-profile-native-settings",
								),
								
								"Flush Cache" => array(
										"url" => $this->url("frontcore-cache-manager", array("action" => "flush-profile-cache")),
								),

								"Manage User Roles" => array(
										"route" => "front-users-roles/admin",
								),

								"Manage Data Access Rules" => array(
										"route" => "front-user-data-acl-rules",
								),

								"Setup Profile Panels" => array(
										"route" => "front-panels-setup",
								),

								"Report Configuration" => array(
										'route' => 'front-report-config',
								),

								"Manage Webhooks" => array(
										"route" => "front-power-tools/webhooks",
								),
						),
				), //end My Company

				"Welcome " . $this->objUser->fname => array(
						"Inbox" => array(
								"route" => "front-inbox-manager",
						),

						"My Preferences" => array(
								"url" => $this->url("front-user-login", array("action" => "user-native-preferences")),
						),

						"My Settings" => array(
								"url" => $this->url("front-user-login", array("action" => "user-settings")),
						),

						"My Panels" => array(
								"route" => "front-panels-setup",
								"url" => $this->url("front-panels-setup", array("action" => "user-panels")),
						),

						"Logout" => array(
								"url" => $this->url("front-user-login", array("action" => "logout")),
						),
				), //end user menu
		);

		//set route params array
		$arr_route_params = array(
				"user_id" => $this->objUser->id,
		);

		//amend menu items based on enabled core plugins
		$arr_plugins = $this->objUser->profile->plugins_enabled;

		if (is_array($arr_plugins))
		{
			//links
			if (!in_array("tracking_links", $arr_plugins))
			{
				unset($arr_primary_menu[$this->objProfileSettings->get("menu_main_relationship", "Relationship")]["advanced"]["Setup comm-links"]);
			}//end if

			//look and feel - episodes
			if (!in_array("layout_episode_look_and_feel", $arr_plugins))
			{
				unset($arr_primary_menu[$this->objProfileSettings->get("menu_main_relationship", "Relationship")]["advanced"]["Create / Update a look and feel"]);
			}//end if

			//look and feel - forms
			if (!in_array("layout_form_look_and_feel", $arr_plugins))
			{
				unset($arr_primary_menu[$this->objProfileSettings->get("menu_main_data", "Data")]["advanced"]["Create / Update a look and feel"]);
			}//end if

			//bulk send
			if (!in_array("journeys_bulk_sends", $arr_plugins))
			{
				unset($arr_primary_menu[$this->objProfileSettings->get("menu_main_relationship", "Relationship")]["advanced"]["Send a bulk journey"]);
			}//end if

			//journey dates
			if (!in_array("journeys_comm_dates", $arr_plugins))
			{
				if (isset($arr_primary_menu[$this->objProfileSettings->get("menu_main_relationship", "Relationship")]["advanced"]["Date Triggered Journeys"]))
				{
					unset($arr_primary_menu[$this->objProfileSettings->get("menu_main_relationship", "Relationship")]["advanced"]["Date Triggered Journeys"]);
				}//end if
			}//end if

			//file library
			if (!in_array("file_library", $arr_plugins))
			{
				unset($arr_primary_menu[$this->objProfileSettings->get("menu_main_data", "Data")]["My Images and Documents"]);
			}//end if

			if (!in_array('reports_basic_types', $arr_plugins) && !in_array('reports_advanced_types', $arr_plugins) && !in_array('reports_dashboard_types', $arr_plugins))
			{
				unset($arr_primary_menu[$this->objProfileSettings->get("menu_main_data", "Data")]["Reports"]);
			} else {
				//reports
				if (!in_array('reports_basic_types', $arr_plugins))
				{
					unset($arr_primary_menu[$this->objProfileSettings->get("menu_main_data", "Data")]["Reports"]['nested']['Basic Reports']);
				}//end if

				if (!in_array('reports_advanced_types', $arr_plugins))
				{

				}//end if

				if (!in_array('reports_dashboard_types', $arr_plugins))
				{
					unset($arr_primary_menu[$this->objProfileSettings->get("menu_main_data", "Data")]["Reports"]['nested']['Dashboards']);
				}//end if
			}//end if

			//panels
			if (!in_array("panels", $arr_plugins))
			{
				unset($arr_secondary_menu[$this->objProfileSettings->get("menu_main_administration", "Administration")]["advanced"]["Setup Profile Panels"]);
				unset($arr_secondary_menu["Welcome " . $this->objUser->fname]["My Panels"]);
			}//end if

			if (!in_array("user_panels", $arr_plugins))
			{
				unset($arr_secondary_menu["Welcome " . $this->objUser->fname]["My Panels"]);
			}//end if

			//user to do list
			if (!in_array("user_to_do_list", $arr_plugins))
			{
				unset($arr_primary_menu[$this->objProfileSettings->get("menu_main_relationship", "Relationship")]["My To-do List"]);
			}//end if

			//replace and generic fields
			if (!in_array("fields_replace_fields", $arr_plugins))
			{
				unset($arr_primary_menu[$this->objProfileSettings->get("menu_main_data", "Data")]["My # Library"]["nested"]["View replace fields"]);
			}//end if

			//replace and generic fields
			if (!in_array("fields_generic_fields", $arr_plugins))
			{
				unset($arr_primary_menu[$this->objProfileSettings->get("menu_main_data", "Data")]["My # Library"]["nested"]["Setup generic fields"]);
			}//end if

			//trackers
			if (!in_array("forms_tracker_form_type", $arr_plugins))
			{
				unset($arr_primary_menu[$this->objProfileSettings->get("menu_main_sale", "Sale")]["My Trackers"]);
			}//end if

			//inbox
			if (!in_array("profile_inbox", $arr_plugins))
			{
				unset($arr_secondary_menu["Welcome " . $this->objUser->fname]["Inbox"]);
			}//end if

			//webhooks
			if (in_array('webhooks_trigger_on_contacts', $arr_plugins) && !in_array('webhooks_trigger_on_status_change', $arr_plugins) && !in_array('webhooks_trigger_on_tracker_forms', $arr_plugins) && !in_array('webhooks_trigger_on_web_forms', $arr_plugins))
			{
				unset($arr_secondary_menu[$this->objProfileSettings->get("menu_main_administration", "Administration")]["advanced"]['Manage Webhooks']);
			}//end if

			if (count($arr_primary_menu[$this->objProfileSettings->get("menu_main_sale", "Sale")]) == 0)
			{
				unset($arr_primary_menu[$this->objProfileSettings->get("menu_main_sale", "Sale")]);
			}//end if

			if (count($arr_primary_menu[$this->objProfileSettings->get("menu_main_data", "Data")]) == 0)
			{
				unset($arr_primary_menu[$this->objProfileSettings->get("menu_main_data", "Data")]);
			}//end if
		}//end if

		//adjust main menu links to angular views where available
		if (isset($arr_app_config['frontend_views_config']) && $arr_app_config['frontend_views_config']['enabled'] === TRUE)
		{
			if (isset($arr_app_config['frontend_views_config']['angular-views-enabled']['contact-list']) && $arr_app_config['frontend_views_config']['angular-views-enabled']['contact-list'] === TRUE)
			{
				$arr_primary_menu[$this->objProfileSettings->get("menu_main_relationship", "Relationship")]['My Contacts']['url'] = '/front/contacts/app';
			}//end if

			if (isset($arr_app_config['frontend_views_config']['angular-views-enabled']['links']) && $arr_app_config['frontend_views_config']['angular-views-enabled']['links'] === TRUE)
			{
				$arr_primary_menu[$this->objProfileSettings->get("menu_main_relationship", "Relationship")]['advanced']['My Links']['url'] = '/front/links/app';
			}//end if

			if (isset($arr_app_config['frontend_views_config']['angular-views-enabled']['forms-admin']) && $arr_app_config['frontend_views_config']['angular-views-enabled']['forms-admin'] === TRUE)
			{
				$arr_primary_menu[$this->objProfileSettings->get("menu_main_data", "Data")]['My Forms']['url'] = '/front/form/admin/form/app';
			}//end if
			
			if (isset($arr_app_config['frontend_views_config']['angular-views-enabled']['form-look-and-feel']) && $arr_app_config['frontend_views_config']['angular-views-enabled']['form-look-and-feel'] === TRUE)
			{
				$arr_primary_menu[$this->objProfileSettings->get("menu_main_data", "Data")]['advanced']['Create / Update a look and feel']['url'] = '/front/form/templates/app';
			}//end if

			if (isset($arr_app_config['frontend_views_config']['angular-views-enabled']['inbox']) && $arr_app_config['frontend_views_config']['angular-views-enabled']['inbox'] === TRUE)
			{
				$arr_secondary_menu["Welcome " . $this->objUser->fname]['Inbox']['url'] = '/front/inbox/manager/app';
			}//end if

			if (isset($arr_app_config['frontend_views_config']['angular-views-enabled']['reports-basic']) && $arr_app_config['frontend_views_config']['angular-views-enabled']['reports-basic'] === TRUE)
			{
// 				$arr_primary_menu[$this->objProfileSettings->get("menu_main_data", "Data")]['Reports']['nested']['Basic Reports']['url'] = '/front/reports/basic/view/basic-reports';
// 				unset($arr_primary_menu[$this->objProfileSettings->get("menu_main_data", "Data")]['Reports']['nested']['Basic Reports']['route']);
			}//end if
			
			if (isset($arr_app_config['frontend_views_config']['angular-views-enabled']['journeys']) && $arr_app_config['frontend_views_config']['angular-views-enabled']['journeys'] === TRUE)
			{
				$arr_primary_menu[$this->objProfileSettings->get("menu_main_relationship", "Relationship")]['My Journeys']['url'] = '/front/comms/admin/journeys/app';
			}//end if
			
			if (isset($arr_app_config['frontend_views_config']['angular-views-enabled']['test-journeys']) && $arr_app_config['frontend_views_config']['angular-views-enabled']['test-journeys'] === TRUE)
			{
				$arr_secondary_menu[$this->objProfileSettings->get("menu_main_administration", "Administration")]['advanced']['Manage Journeys Testing']['route'] = 'front-comms-admin/test-journeys';
			}//end if
		}//end if

		//assign data to view
		$this->objViewModel->setVariable('arr_primary_menu', $arr_primary_menu);
		$this->objViewModel->setVariable('arr_secondary_menu', $arr_secondary_menu);
		$this->objViewModel->setVariable('arr_plugins', $arr_plugins);

		$this->arr_menu = array(
				'arr_primary_menu' 		=> $arr_primary_menu,
				'arr_secondary_menu' 	=> $arr_secondary_menu,
				'arr_plugins' 			=> $arr_plugins,
		);

		return $this->arr_menu;
	}//end function

	//Generate url from route helper
	private function url($route, $arr_params = array())
	{
		$url = $this->getServiceLocator()->get('ViewHelperManager')->get('url')->__invoke($route, $arr_params);
		return $url;
	}//end function
}//end class