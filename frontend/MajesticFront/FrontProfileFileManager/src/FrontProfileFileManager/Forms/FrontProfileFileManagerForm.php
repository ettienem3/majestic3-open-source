<?php
namespace FrontProfileFileManager\Forms;

use Zend\Validator\Regex;
use FrontCore\Forms\FrontCoreSystemFormBase;

class FrontProfileFileManagerForm extends FrontCoreSystemFormBase
{
	public function __construct()
	{
		parent::__construct("front-profile-file-manager-uploads");
		//set form to submit method
		$this->setAttribute("method", "post");

		$this->add(array(
				"name" => "mode",
				"type" => "select",
				"attributes" => array(
					"id" => "mode",
					"required" => "required",
					"title" => "Specify which section the file will be uploaded. Options are Images or Documents. Where the file does not fit the section profile, the upload will fail.",
				),
				"options" => array(
					"label" => "Specify section",
					"empty_option" => "--select--",
					"value_options" => array(
						"image" => "Images",
						"document" => "Documents",
					),
				),
		));

		$this->add(array(
				"name" => "filename",
				"type" => "text",
				"attributes" => array(
					"id" => "filename",
					"title" => "Set the filename the file should be uploaded with. Where none is set, the filename will used as received",
					"required" => "required",
				),
				"options" => array(
					"label" => "Filename",
				),
				"filters" => array(

				),
				"required" => TRUE,
				"validators" => array(
						array(
								'name' => "Regex",
								'options' => array(
										'pattern' => "/[^a-zA-Z0-9_.]/",
										'messages' => array(
												Regex::NOT_MATCH => "Filename contains illegal characters. Only alpha numeric, underscore and full stop characters are allowed",
										),
								),
						),
				),
		));

		$this->add(array(
			"name" => "additional_path",
			"type" => "hidden",
			"attributes" => array(
				"id" => "additional_path",
			),
			"options" => array(
				"label" => "Sub folder",
			),
			"filters" => array(

			),
			"validators" => array(

			),
		));

		$this->add(array(
				"name" => "tmp_file",
				"type" => "file",
				"attributes" => array(
						"id" => "tmp_file",
						"title" => "Select file form filesystem",
						"required" => "required",
				),
				"options" => array(
						"label" => "Set file to be upload",
				),
		));

		$this->add(array(
				"name" => "submit",
				"type" => "submit",
				"attributes" => array(
						"value" => "Submit",
				),
				"options" => array(
						"ignore" => TRUE,
				),
		));
	}//end function
}//end class
