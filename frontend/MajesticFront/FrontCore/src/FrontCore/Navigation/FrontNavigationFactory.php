<?php
namespace FrontCore\Navigation;

use FrontUserLogin\Models\FrontUserSession;

use Zend\Navigation\Service\AbstractNavigationFactory;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Navigation\Navigation;
use Zend\Session\Container;

class FrontNavigationFactory extends AbstractNavigationFactory implements ServiceLocatorAwareInterface
{
	/**
	 * Array of routes that should not be managed by access control rules
	 * @var array
	 */
	protected $arr_ignore_route_map = array(
				'home',
			);

	/**
	 * Container for the User Session
	 * @var object
	 */
	protected $objUserSession;

	/**
	 * @return string
	 */
	protected function getName()
	{
		return 'default';
	}//end function

	/**
	 * @param ServiceLocatorInterface $serviceLocator
	 * @return \Zend\Navigation\Navigation
	 */
	public function createService(ServiceLocatorInterface $serviceLocator)
	{
 		if (!FrontUserSession::isLoggedIn())
 		{
 			return new Navigation(array());
 		}//end if

 		//load the user session
 		$objUserSession = new Container("user");

		//load route map
		$arr_route_map = $serviceLocator->get("config")["api_route_vs_front_route_map"];

		//container for routes allowed for logged in user
		$arr_user_navigation = array();

		if (is_array($objUserSession->arr_user_acl))
		{
			$arr_user_navigation = $objUserSession->arr_user_acl;
		} else {
			//check mode of acl engine
			switch(strtolower($objUserSession->acl->profile_acl_mode))
			{
				case "strict":
				case "relaxed":
				default:
					$arr_t = (array) $objUserSession->acl->user_acl_access_allowed;
					foreach ($arr_route_map as $route => $arr_acl_resources)
					{
						foreach ($arr_acl_resources as $resource)
						{
							if ((isset($arr_t[$resource]) || in_array($resource, (array) $objUserSession->acl->user_acl_access_allowed)) && !in_array($route, $arr_user_navigation))
							{
								$arr_user_navigation[] = $route;
							}//end if
						}//end foreach
					}//end foreach
					break;
			}//end switch

			//save user acl to session
			$objUserSession->arr_user_acl = $arr_user_navigation;
		}//end if

		//load navigation
		$arr_pages = $this->getPages($serviceLocator);
		foreach ($arr_pages as $key => $arr_page)
		{
			//get route match from map
			if (in_array(strtolower($arr_page["route"]), $this->arr_ignore_route_map))
			{
				//check second level nav
				if (isset($arr_page["pages"]) && is_array($arr_page["pages"]))
				{
					foreach ($arr_page["pages"] as $kk => $arr_rr)
					{
						if (in_array($arr_rr["route"], $this->arr_ignore_route_map))
						{
							continue;
						}//end if

						if (!in_array($arr_rr["route"], $arr_user_navigation))
						{
// 							unset($arr_pages[$key][$kk]);
						} else {
							//check third level nav
							if (isset($arr_rr["pages"]) && is_array($arr_rr["pages"]))
							{
								foreach ($arr_rr["pages"] as $kkk => $arr_rrr)
								{
									if (in_array($arr_rrr["route"], $this->arr_ignore_route_map))
									{
										continue;
									}//end if

									if (!in_array($arr_rrr["route"], $arr_user_navigation))
									{
// 										unset($arr_pages[$key][$kk][$kkk]);
									}//end if
								}//end foreach
							}//end if
						}//end if
					}//end foreach
				}//end foreach
				continue;
			} else {
				if (!in_array($arr_page["route"], $arr_user_navigation))
				{
// 					unset($arr_pages[$key]);
				}//end if
			}//end if
		}//end foreach

		//sort pages into required groups
		$arr_menu = array(
				"relationship" => array(
					"label" => "Relationships",
					"route" => "home",
				),
				"data" => array(
					"label" => "Data",
					"route" => "home",
				),
				"sales" => array(
					"label" => "Sales",
					"route" => "home",
				),
				"profile-management" => array(
					"label" => "Profile",
					"route" => "home",
				),
		);

		foreach ($arr_pages as $key => $arr_page)
		{
			switch ($arr_page["route"])
			{
				/**
				 * Relationships
				 */
				case "front-comms-admin/journeys":
				case "front-contacts":
					$arr_menu["relationship"]["pages"][] = $arr_page;
					break;

				/**
				 * Data
				 */
				case "front-custom-tables":
				case "front-statuses":
					$arr_menu["data"]["pages"][] = $arr_page;
					break;

				/**
				 * Sales
				 */

				/**
				 * Profile Management
				 */
				case "front-users":
					$arr_menu["profile-management"]["pages"][] = $arr_page;
					break;

				/**
				 * Ignore
				 */
				case "home":
				case "front-inbox-manager":
				case "front-power-tools/announcements":
					//do nothing
					break;

				default:

					break;
			}//end switch
		}//end foreach

		return new Navigation($arr_pages);
	}//end function

	private function generateFrontRouteACLMap($arr_pages)
	{
		foreach ($arr_pages as $key => $arr_page)
		{
			echo "'" . $arr_page["route"] . "' => array(),<br/>";
			if (isset($arr_page["pages"]) && is_array($arr_page["pages"]))
			{
				$this->generateFrontRouteMap($arr_page["pages"]);
			}//end if
		}//end foreach
	}//end function

	public function getServiceLocator()
	{
		return $this->serviceLocator;
	}//end function

	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->serviceLocator = $serviceLocator;
	}//end function
}//end class