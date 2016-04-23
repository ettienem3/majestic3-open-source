<?php
namespace FrontPowerTools\Controller;

use FrontCore\Adapters\AbstractCoreActionController;

class EmailTemplatesController extends AbstractCoreActionController
{
	public function indexAction()
	{
		var_dump(time()); exit;
	}//end function

	public function ajaxLoadEmailTemplatesAction()
	{
		$arr_templates = array(
				(object) array("title" => "Standard", "description" => "Template", "url" => "/plugins/comm_templates/standard/index.html"),
				(object) array("title" => "Corporate", "description" => "Template", "url" => "/plugins/comm_templates/corporate/index.html"),
				(object) array("title" => "Fashion", "description" => "Template", "url" => "/plugins/comm_templates/fashion/index.html"),
				(object) array("title" => "Majushi", "description" => "Template", "url" => "/plugins/comm_templates/majushi/index.html"),
				(object) array("title" => "Ocean Breeze", "description" => "Template", "url" => "/plugins/comm_templates/oceanbreeze/index.html"),
				(object) array("title" => "Rosemary", "description" => "Template", "url" => "/plugins/comm_templates/rosemary/index.html"),
				(object) array("title" => "Technology", "description" => "Template", "url" => "/plugins/comm_templates/technology/index.html"),
		);



		echo json_encode(array("error" => 0, "templates" => $arr_templates), JSON_FORCE_OBJECT);
		exit;
	}//end function
}//end class