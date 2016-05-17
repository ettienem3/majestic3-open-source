<?php
namespace FrontCore\Controller;

use FrontCore\Adapters\AbstractCoreActionController;

class IndexController extends AbstractCoreActionController
{
    public function indexAction()
    {
        return array();
    }

    public function fooAction()
    {
        // This shows the :controller and :action parameters in default route
        // are working when you browse to /module-specific-root/skeleton/foo
        return array();
    }

    public function testAction()
    {
    	$model = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel");
    	$objForm = $model->getSystemForm("Core\\Forms\\SystemForms\\Test\\TestForm");

    	return array("form" => $objForm);
    }//end functin

    public function fbAction()
    {

    }//end function

    public function googleAction()
    {

    }//end function
}
