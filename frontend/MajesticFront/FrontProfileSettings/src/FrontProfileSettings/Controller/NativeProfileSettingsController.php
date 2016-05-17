<?php
namespace FrontProfileSettings\Controller;

use Zend\Validator\File\Size;
use FrontCore\Adapters\AbstractCoreActionController;

class NativeProfileSettingsController extends AbstractCoreActionController
{
	/**
	 * Container for the Native Profile Settings Model
	 * @var \FrontProfileSettings\Models\NativeProfileSettingsModel
	 */
	private $model_profile_settings;

	public function indexAction()
	{
		//load form
		$form = $this->getProfileSettingsModel()->getProfileSettingsForm();

		//load profile settings
		$objProfile = $this->getProfileSettingsModel()->fetchProfileSettings();
		$form->bind($objProfile);

		$request = $this->getRequest();
		if ($request->isPost())
		{
			$form->setData($request->getPost());
			if ($form->isValid())
			{
				$objProfileData = $form->getData();

				//upload files
				$arr_files = $this->params()->fromFiles();
				foreach ($arr_files as $key => $arr_file)
				{
					if ($arr_result["name"] == "")
					{
						continue;
					}//end if

					$arr_result = $this->uploadFile($arr_file);
					if ($arr_result["result"] == FALSE)
					{
						$form->get($key)->setMessages($arr_result["errors"]);
					} else {
						$objProfileData->set($key, $arr_result["file_location"]);
					}//end if
				}//end foreach

				try {
					$this->getProfileSettingsModel()->saveProfileSettings($objProfileData);
				} catch (\Exception $e) {
					$this->flashMessenger()->addErrorMessage($e->getMessage());
				}//end catch
			}//end if
		}//end if

		return array(
				"form" => $form,
		);
	}//end function

	private function uploadFile($arr_file)
	{
		//set validators
		$arr_validators = array(
			"filesize" => array("max" => 350000),
			"filemimetype" => array("mimetype" => "image/png,image/x-png,image/svg,image/svg+xml,image/jpg,image/gif")
		);

		$adapter = new \Zend\File\Transfer\Adapter\Http();
		$adapter->setValidators($arr_validators, $arr_file["name"]);

		if (!$adapter->isValid())
		{
			$arr_form_errors = $adapter->getMessages();
			$arr_errors = array();
			foreach($arr_form_errors as $key => $row)
			{
				$arr_errors[] = $row;
			}//end foreach
			return array("result" => false, "errors" => $arr_errors);
		} else {
			$adapter->setDestination($this->getProfileSettingsModel()->getFilesPath());
			if ($adapter->receive($arr_file['name']))
			{
				return array("result" => TRUE, "file_location" => $this->getProfileSettingsModel()->getFilesPath() . "/" . $arr_file["name"]);
			}//end if
		}//end if
	}//end function

	/**
	 * Create an instance of the Native Profile Settings Model using the Service Manager
	 * @return \FrontProfileSettings\Models\NativeProfileSettingsModel
	 */
	private function getProfileSettingsModel()
	{
		if (!$this->model_profile_settings)
		{
			$this->model_profile_settings = $this->getServiceLocator()->get("FrontProfileSettings\Models\NativeProfileSettingsModel");
		}//end if

		return $this->model_profile_settings;
	}//end function
}//end class