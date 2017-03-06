<?php
namespace FrontCore\ViewHelpers;

use Zend\View\Helper\AbstractHelper;
use  Zend\Mvc\Controller\Plugin\PluginInterface;
use Zend\Stdlib\DispatchableInterface as Dispatchable;

class FrontAdminAngularFormRenderHelper extends AbstractHelper implements PluginInterface
{
	/**
	 * Container for the view object
	 * @var \Zend\Mvc\View
	 */
	protected $view;

	/**
	 * Generate a generic form
	 * @param objForm $form
	 * @param \Zend\Mvc\View\View $view
	 * @param array $arr_options - Optional
	 * @return string
	 */
	public function __invoke($form, $view, $arr_options = array())
	{
		$arr_form = array();


		foreach ($form->getElements() as $objElement)
		{
			if ($objElement->getAttribute('type') == 'submit')
			{
				continue;
			}//end if

			$arr_expression_properties = array();

			switch ($objElement->getAttribute('type'))
			{
				case 'text':
				case 'textarea':
				case 'checkbox':
				case 'hidden':
					$type = $objElement->getAttribute('type');
					switch($type)
					{
						case 'text':
							$type = 'input';
							break;

						case 'hidden':
							$type = 'input';
							break;
					}//end switch
					break;

				case 'multi_checkbox':
				case 'Zend\Form\Element\MultiCheckbox':
					$type = 'checkbox';
					break;

				case 'select':
				case 'radio':
					$type = $objElement->getAttribute('type');
					break;
			}//end switch

			$label = $objElement->getLabel();
			if ($label == '')
			{
				$label = "";
			}//end if
			
			switch (strtolower(str_replace(' ', '', $label)))
			{
				case 'countryforcellnumber':
				case 'countryforcworknumber':
				case 'countryforfaxnumber':
				case 'countryforhomenumber':
					$label = 'International Dialing Code';
					break;
			}//end switch

			$placeholder = $objElement->getAttribute('placeholder');
			if ($placeholder == '')
			{
				$placeholder = '';
			}//end if

			$title = $objElement->getAttribute('title');
			if ($title == '')
			{
				$title = '';
			}//end if

			$arr_element = array(
				'key' => $objElement->getName(),
				'type' => $type,
				'modelOptions' => (object) array(
										'getterSetter' => true,
									),
				'templateOptions' => (object) array(
					'type' => $type,
					'label' => $label,
					'placeholder' => $placeholder,
					'title' => $title,
					'style' => $objElement->getAttribute('style'),
					'defaultValue'=> '',
				),
				'validation' => (object) array(
					'show' => true,
				)
			);

			//do some final checks
			switch ($objElement->getAttribute('type'))
			{
				case 'checkbox':
					$arr_element['ngModelAttrs'] = (object) array(
							'checkboxCheckedValue' => (object) array(
								'attribute' => 'ng-true-value',
							),
							'uncheckboxCheckedValue' => (object) array(
									'attribute' => 'ng-false-value',
							)
					);

					$arr_element['templateOptions']->checkboxCheckedValue = 1;
					$arr_element['templateOptions']->uncheckboxCheckedValue = 0;
					$arr_element['templateOptions']->default_value = 0;
					break;

				case 'select':
				case 'radio':
					$arr_element['templateOptions']->valueProp = 'optionID';
					$arr_element['templateOptions']->labelProp = 'optionLabel';
					$arr_element_options = array();
					if ($objElement->getAttribute('type') == 'select')
					{
						$arr_element_options[] = (object) array('optionID' => '', 'optionLabel' => '--select--');
					}//end if
					
					foreach ($objElement->getOptions()['value_options'] as $k => $v)
					{
						$arr_element_options[] = (object) array('optionID' => $k, 'optionLabel' => $v);
					}//end if

					$arr_element['templateOptions']->options = $arr_element_options;
					break;

				case 'text':
				case 'textarea':
					if ($objElement->getAttribute('maxlength') != '')
					{
						$arr_element['templateOptions']->maxlength = $objElement->getAttribute('maxlength');
					}//end if

					if ($objElement->getAttribute('readonly') != '')
					{
						$arr_element['templateOptions']->readonly = $objElement->getAttribute('readonly');
					}//end if
					break;

				case 'hidden':
					$arr_element['templateOptions']->type = 'hidden';
					$arr_element['className'] = 'hidden';
					break;
			}//end switch

			if ($objElement->getAttribute('required') != '')
			{
				$arr_element['templateOptions']->required = true;
				$arr_expression_properties['"templateOptions.required"'] = "true";
			}//end if

			if (is_array($arr_expression_properties) && count($arr_expression_properties) > 0)
			{
// 				$arr_element['expressionProperties'] = 'JSON.parse(\'' . json_encode($arr_expression_properties, JSON_FORCE_OBJECT) . '\');';
			}//end if

			$arr_form[] = (object) $arr_element;
		}//end foreach

		return $arr_form;
	}//end function

	public function setController(Dispatchable $controller)
	{

	}//end function

	public function getController()
	{

	}//end function
}//end class
