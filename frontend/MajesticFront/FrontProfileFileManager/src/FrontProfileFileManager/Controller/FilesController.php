<?php
namespace FrontProfileFileManager\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Validator\Regex;

class FilesController extends AbstractActionController
{
	/**
	 * Container for the Front Profile File Manager Model
	 * @var \FrontProfileFileManager\Models\FrontProfileFileManagerModel
	 */
	private $model_front_file_manager;

    public function indexAction()
    {
    	$mode = $this->params()->fromQuery("mode", "");
    	$disable_upload = $this->params()->fromQuery("disable_uploads", "");

    	if ($this->params()->fromQuery("dialog-layout") == 1)
    	{
    		$this->layout("layout/behaviors-view");
    	}//end if

        //load files
        $objFiles = $this->getFrontProfileFileManagerModel()->fetchFiles($mode);

        return array(
        	"mode" => strtolower($mode),
        	"disable_upload" => $disable_upload,
        	"objFiles" => $objFiles,
        );
    }//end function

    public function ajaxLoadFilesAction()
    {
    	//load files
    	try {
	    	$objFiles = $this->getFrontProfileFileManagerModel()->fetchFiles("", (array) $this->params()->fromQuery());
	    	echo json_encode(array("error" => 0, "files" => $objFiles), JSON_FORCE_OBJECT); exit;
    	} catch (\Exception $e) {
    		echo json_encode(array("error" => 1, "response" => $e->getMessage()), JSON_FORCE_OBJECT); exit;
    	}//end if
    }//end function

    public function uploadFileAction()
    {
    	//load the form
    	$form = $this->getFrontProfileFileManagerModel()->getFileUploadForm();

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		//get file
			$files =  $request->getFiles()->toArray();
			$form->remove("tmp_file");

    		$form->setData($request->getPost());

    		if ($form->isValid($request->getPost()) && count($form->getMessages()) == 0)
    		{
				//extract form data
    			$arr_data = $form->getData();

    			if ($files)
    			{
    				$httpadapter = new \Zend\File\Transfer\Adapter\Http();
    				$filesize  = new \Zend\Validator\File\Size(array('min' => 1, 'max' => 1000000));
					$extension = new \Zend\Validator\File\Extension(array(
							'extension' => array('jpg', 'png', 'gif', 'jpeg', 'pdf', 'csv', 'xls', 'xlsx', 'doc', 'docx', 'txt', 'js', 'css', 'html'),
							"options" => array(
									"messages" => array (
											\Zend\Validator\File\Extension::FALSE_EXTENSION => "File is not valid",
											\Zend\Validator\File\Extension::NOT_FOUND => "File could not be found",
									)
							)));
    				$httpadapter->setValidators(array($filesize, $extension), $files['tmp_file']['name']);

    				if ($httpadapter->isValid())
    				{
    					//set uploads path
    					$uploads_path = "./data/tmp/uploads";
    					if (!is_dir($uploads_path))
    					{
    						mkdir($uploads_path, 0755, TRUE);
    					}//end if

    					$httpadapter->setDestination($uploads_path);
    					if($httpadapter->receive($files['fileupload']['name']))
    					{
    						$newfile = $httpadapter->getFileName();

    						$arr_data["url"] = "";
    						$arr_data["data"] = base64_encode(file_get_contents($newfile));

    						//remove local file
    						unlink($newfile);
    					}//end if
    				} else {
    					$flag_file_upload_error = FALSE;
						foreach($httpadapter->getMessages() as $k => $v)
						{
							$flag_file_upload_error = TRUE;
							$this->flashMessenger()->addErrorMessage($v);
						}//end foreach
    				}//end if
    			}//end if

    			try {
    				if (!isset($arr_data["data"]) || $arr_data["data"] == "")
    				{
    					if ($flag_file_upload_error !== TRUE)
    					{
    						$this->flashMessenger()->addErrorMessage("An unknown error has occured, the file could not be uploaded");
    					}//end if
    					//redirect back to uploads page to reset form
    					return $this->redirect()->toRoute("front-profile-file-manager", array("action" => "upload-file"));
    				}//end if

    				$mode = $this->params()->fromQuery("mode", "");
    				if ($mode != "")
    				{
    					$arr_data["mode"] = $mode;
    				}//end if

    				$this->getFrontProfileFileManagerModel()->uploadFile($arr_data["mode"], $arr_data);

    				//set message
    				$this->flashMessenger()->addInfoMessage("File has been uploaded successfully");

    				return $this->redirect()->toRoute("front-profile-file-manager");
    			} catch (\Exception $e) {
    				//set error message
    				$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());

    				//redirect back to uploads page to reset form
    				return $this->redirect()->toRoute("front-profile-file-manager", array("action" => "upload-file"));
    			}//end catch
    		} else {
    			$arr_messages = $form->getMessages();

    			//reload the form because file selector is removed
    			$form = $this->getFrontProfileFileManagerModel()->getFileUploadForm();
    			$form->setData($request->getPost());
    			$form->setMessages($arr_messages);
    		}//end if
    	}//end if

    	return array(
    		"form" => $form,
    	);
    }//end function

    public function ajaxUploadFileAction()
    {
    	//load the form
    	$form = $this->getFrontProfileFileManagerModel()->getFileUploadForm();

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		//get file
    		$files =  $request->getFiles()->toArray();
    		$form->remove("tmp_file");
    		$form->remove("mode");
    		$form->remove("filename");

    		$arr_post_data = $request->getPost();
    		$arr_post_data["mode"] = "image";
    		$arr_post_data["additional_path"] = "";

    		$form->setData($arr_post_data);

    		if ($form->isValid($request->getPost()))
    		{
    			//extract form data
    			$arr_data = $form->getData();
    			
    			//clear file name, it is derived from the uploaded data
				$arr_data["filename"] = "";
				
    			if ($files)
    			{
    				$httpadapter = new \Zend\File\Transfer\Adapter\Http();
    				$filesize  = new \Zend\Validator\File\Size(array('min' => 1, 'max' => 1000000));
    				$extension = new \Zend\Validator\File\Extension(array(
    						'extension' => array('jpg', 'png', 'gif', 'jpeg', 'pdf', 'csv', 'xls', 'xlsx', 'csv', 'docx', 'txt', 'js', 'css', 'html'),
    						"options" => array(
    								"messages" => array (
    										\Zend\Validator\File\Extension::FALSE_EXTENSION => "File is not valid",
    										\Zend\Validator\File\Extension::NOT_FOUND => "File could not be found",
    								)
    						)));
    				$httpadapter->setValidators(array($filesize, $extension), $files['file']['name']);

    				if ($httpadapter->isValid())
    				{
    					//set uploads path
    					$uploads_path = "./data/tmp/uploads";
    					if (!is_dir($uploads_path))
    					{
    						mkdir($uploads_path, 0755, TRUE);
    					}//end if

    					$httpadapter->setDestination($uploads_path);

    					if($httpadapter->receive($files['file']['name']))
    					{
    						$newfile = $httpadapter->getFileName();

    						//set form data
    						$arr_t = explode("/", $newfile);
    						if (!isset($arr_data["filename"]) || $arr_data["filename"] == "")
    						{
    							$f = array_pop($arr_t);
    							$f = preg_replace('/[^a-zA-Z0-9_.]/', '', $f);
    							if ($f == "")
    							{
    								$f = time();	
    							}//end if
    							
    							$arr_data["filename"] = $f;
    						}//end if

    						$arr_data["url"] = "";
    						$arr_data["data"] = base64_encode(file_get_contents($newfile));

    						//determine mode from file uploaded
    						if (is_array(getimagesize($newfile)))
    						{
    							$arr_data["mode"] = "image";
    						} else {
    							$arr_data["mode"] = "document";
    						}//end if

    						//remove local file
    						unlink($newfile);
    					}//end if
    				} else {
    					//file validation failed
    					echo json_encode(array("error" => 1, "response" => "File validation failed. File could not be uploaded: " . implode(",", $httpadapter->getMessages())), JSON_FORCE_OBJECT); exit;
    				}//end if
    			}//end if

    			try {
    				$this->getFrontProfileFileManagerModel()->uploadFile($arr_data["mode"], $arr_data);

    				//set message
					echo json_encode(array("error" => 0, "response" => "File uploaded"), JSON_FORCE_OBJECT); exit;
    			} catch (\Exception $e) {
    				$arr_t = explode("||", $e->getMessage());
    				$obj = json_decode(array_pop($arr_t));
    				$arr_t = explode(":", $obj->HTTP_RESPONSE_MESSAGE);
    				echo json_encode(array("error" => 1, "response" => array_pop($arr_t)), JSON_FORCE_OBJECT); exit;
    			}//end catch
    		} else {
    			//form validation failed
    			$r = "Form validation failed";
    			echo json_encode(array("error" => 1, "response" => $r), JSON_FORCE_OBJECT); exit;
    		}//end if
    	}//end if

    	echo json_encode(array("error" => 1, "response" => "AJAX request needs to be POST"), JSON_FORCE_OBJECT); exit;
    	exit;
    }//end function

    public function deleteFileAction()
    {
    	$location = $this->params()->fromQuery("location", "");
    	$mode = $this->params()->fromQuery("mode", "");
    	if ($location == "" || $mode == "")
    	{
    		$this->flashMessenger()->addErrorMessage("File could be removed. Location or Mode is not set");
    		return $this->redirect()->toRoute("front-profile-file-manager");
    	}//end if

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		if (strtolower($request->getPost("delete")) == "yes")
    		{
	    		try {
	    			$this->getFrontProfileFileManagerModel()->deleteFile($mode, $location);

	    			$this->flashMessenger()->addSuccessMessage("File has been removed");
	    		} catch (\Exception $e) {
	    			$this->flashMessenger()->addErrorMessage($e->getMessage());
	    		}//end catch
    		}//end if

    		return $this->redirect()->toRoute("front-profile-file-manager");
    	}//end if

		return array(
			"location" => $location,
		);
    }//end function

    /**
     * Create an instance of the Front Profile File Manager Model using the Service Manager
     * @return \FrontProfileFileManager\Models\FrontProfileFileManagerModel
     */
    private function getFrontProfileFileManagerModel()
    {
    	if (!$this->model_front_file_manager)
    	{
    		$this->model_front_file_manager = $this->getServiceLocator()->get("FrontProfileFileManager\Models\FrontProfileFileManagerModel");
    	}//end if

    	return $this->model_front_file_manager;
    }//end function
}//end class
