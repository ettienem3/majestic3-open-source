<?php
namespace MajesticExternalForms\Controller;

use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Console;
use FrontCore\Adapters\AbstractCoreActionController;

class ConsoleController extends AbstractCoreActionController
{
	/**
	 * Container for the External Forms Model
	 * @var \MajesticExternalForms\Models\MajesticExternalFormsModel $model_external_forms
	 */
	private $model_external_forms;

	public function executeAction()
	{
		$request = $this->getRequest();

		// Make sure that we are running in a console and the user has not tricked our
		// application into running this action from a public web server.
		if (!$request instanceof ConsoleRequest)
		{
			throw new \RuntimeException('You can only use this action from a console!');
		}//end if

		try {
			Console::getInstance()->writeLine("********************************************************", \Zend\Console\ColorInterface::MAGENTA);
			Console::getInstance()->writeLine("Executing: " . $request->getParam("execute-task"));
			Console::getInstance()->writeLine("********************************************************", \Zend\Console\ColorInterface::MAGENTA);

			$arr_data = $this->getExternalFormsModel()->executeConsoleTask($request);
			Console::getInstance()->writeLine(print_r($arr_data, TRUE));
		} catch (\Exception $e) {
			Console::getInstance()->writeLine("Request failed with message: " . $e->getMessage(), \Zend\Console\ColorInterface::RED);
			return;
		}//end catch
	}//end function

	/**
	 * Create an instance of the External Forms Model using the Service Manager
	 * @return \MajesticExternalForms\Models\MajesticExternalFormsModel
	 */
	private function getExternalFormsModel()
	{
		if (!$this->model_external_forms)
		{
			$this->model_external_forms = $this->getServiceLocator()->get('MajesticExternalForms\Models\MajesticExternalFormsModel');
		}//end if

		return $this->model_external_forms;
	}//end function
}//end class