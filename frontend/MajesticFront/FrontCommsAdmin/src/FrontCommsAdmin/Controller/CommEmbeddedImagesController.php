<?php
namespace FrontCommsAdmin\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class CommEmbeddedImagesController extends AbstractActionController
{	
	/**
	 * Container for the Communication Embedded Images Model
	 * @var \FrontCommsAdmin\Models\FrontCommsAdminCommEmbeddedImagesModel
	 */
	private $model_comm_embedded_images;
	
	public function indexAction()
	{
		$comm_id = $this->params()->fromRoute("comm_id", "");
		$journey_id = $this->params()->fromRoute("journey_id", "");
		
		//load images
		$objImages = $this->getCommEmbeddedImagesModel()->fetchCommEmbeddedImages($comm_id);
		
		return array(
			"comm_id" => $comm_id,
			"journey_id" => $journey_id,
			"objImages" => $objImages,
		);
	}//end function
	
	public function ajaxCreateImageAction()
	{
		$comm_id = $this->params()->fromRoute("comm_id", "");
		$journey_id = $this->params()->fromRoute("journey_id", "");
		
		//load the form
		$form = $this->getCommEmbeddedImagesModel()->getCommEmbeddedImageForm();
		
		$request = $this->getRequest();
		if ($request->isPost())
		{
			$arr_data = $request->getPost();
			//set dummy value for content type
			$arr_data["content_type"] = "image/jpg";
			
			$form->setData($arr_data);
			
			if ($form->isValid($request->getPost()))
			{
				try {
					//embed the image
					$objImage = $this->getCommEmbeddedImagesModel()->createCommEmbeddedImage($comm_id, (array) $form->getData());
					
					echo json_encode(array(
						"error" => 0,
						"response" => "Image embedded",
					),
					JSON_FORCE_OBJECT);
					exit;
				} catch (\Exception $e) {
					//set the error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
					
					echo json_encode(array(
							"error" => 1,
							"response" => "Image could not be embedded. Data validation failed with : " . print_r($form->getMessages(), TRUE),
					),
							JSON_FORCE_OBJECT);
					exit;
				}//end catch
			} else {
				echo json_encode(array(
					"error" => 1,
					"response" => "Image could not be embedded. Data validation failed with : " . print_r($form->getMessages(), TRUE),
				),
				JSON_FORCE_OBJECT);
				exit;
			}//end if
		}//end if

		echo json_encode(array(
			"error" => 1,
			"response" => "No data received",
		),
		JSON_FORCE_OBJECT);
		exit;
	}//end function
	
	public function editImageAction()
	{
		$comm_id = $this->params()->fromRoute("comm_id", "");
		$journey_id = $this->params()->fromRoute("journey_id", "");
		$id = $this->params()->fromRoute("id", "");
		
		if ($id == "")
		{
			$this->flashMessenger()->addErrorMessage("Image could not loaded. ID is not set");
			
			//return to the index page
			return $this->redirect()->toRoute("front-comms-admin/comm-embedded-images", array("journey_id" => $journey_id, "comm_id" => $comm_id));
		}//end if
		
		//load data
		try {
			$objImage = $this->getCommEmbeddedImagesModel()->fetchCommEmbeddedImage($comm_id, $id);
		} catch (\Exception $e) {
			$this->flashMessenger()->addErrorMessage($e->getMessage());
				
			//return to the index page
			return $this->redirect()->toRoute("front-comms-admin/comm-embedded-images", array("journey_id" => $journey_id, "comm_id" => $comm_id));
		}//edn catch
		
		//load form
		$form = $this->getCommEmbeddedImagesModel()->getCommEmbeddedImageForm();
		//make file name field readonly
		$form->get("file_name")->setAttribute("readonly", "readonly");
		
		//bind data
		$form->bind($objImage);
		
		$request = $this->getRequest();
		if ($request->isPost())
		{
			$form->setData($request->getPost());
			
			if ($form->isValid($request->getPost()))
			{
				$objImage = $form->getData();
				$objImage->set("id", $id);
				$objImage->set("comm_id", $comm_id);
				
				try {
					//update the image
					$objImage = $this->getCommEmbeddedImagesModel()->editCommEmbeddedImage($objImage);
					
					//set message
					$this->flashMessenger()->addSuccessMessage("Image has been updated");
					
					//return to the index page
					return $this->redirect()->toRoute("front-comms-admin/comm-embedded-images", array("journey_id" => $journey_id, "comm_id" => $comm_id));
				} catch (\Exception $e) {
					//set the error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
				}//end catch
			}//end if
		}//end if
		
		return array(
				"comm_id" => $comm_id,
				"journey_id" => $journey_id,
				"form" => $form,
		);
	}//end function
	
	public function deleteImageAction()
	{
		$comm_id = $this->params()->fromRoute("comm_id", "");
		$journey_id = $this->params()->fromRoute("journey_id", "");
		$id = $this->params()->fromRoute("id", "");
		
		if ($id == "")
		{
			$this->flashMessenger()->addErrorMessage("Image could not be removed. ID is not set");

			//return to the index page
			return $this->redirect()->toRoute("front-comms-admin/comm-embedded-images", array("journey_id" => $journey_id, "comm_id" => $comm_id));
		}//end if
		
		$request = $this->getRequest();
		if ($request->isPost())
		{
			if (strtolower($request->getPost("delete")) == "yes")
			{
				try {
					$this->getCommEmbeddedImagesModel()->deleteCommEmbeddedImage($comm_id, $id);
					
					//set success message
					$this->flashMessenger()->addSuccessMessage("Image has been removed");
				} catch (\Exception $e) {
					$this->flashMessenger()->addErrorMessage($e->getMessage());
				}//end catch
			}//end if	
			
			//return to the index page
			return $this->redirect()->toRoute("front-comms-admin/comm-embedded-images", array("journey_id" => $journey_id, "comm_id" => $comm_id));
		}//end if
		
		return array(
				"comm_id" => $comm_id,
				"journey_id" => $journey_id,
		);
	}//end function
	
	/**
	 * Create an instance of the Comm Embedded Images Model using the Service Manager
	 * @return \FrontCommsAdmin\Models\FrontCommsAdminCommEmbeddedImagesModel
	 */
	private function getCommEmbeddedImagesModel()
	{
		if (!$this->model_comm_embedded_images)
		{
			$this->model_comm_embedded_images = $this->getServiceLocator()->get("FrontCommsAdmin\Models\FrontCommsAdminCommEmbeddedImagesModel");
		}//end if
		
		return $this->model_comm_embedded_images;
	}//end function
}//end class