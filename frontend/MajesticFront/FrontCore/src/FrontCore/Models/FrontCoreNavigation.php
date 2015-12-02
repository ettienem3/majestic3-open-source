<?php
namespace FrontCore\Models\FrontCoreNavigation;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontCoreNavigation extends AbstractCoreAdapter
{
	public function getNavigationArray()
	{
			$arr_navigation = array(
					'default' => array(
							array(
									'label' => 'Home',
									'route' => 'home',
							),
							array(
									'label' => 'Links',
									'route' => 'frontlinks',
									),
							),
					);
			
			return $arr_navigation;
	}//end function
	
}//end class