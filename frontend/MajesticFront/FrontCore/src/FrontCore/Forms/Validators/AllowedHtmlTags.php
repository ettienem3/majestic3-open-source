<?php
namespace FrontCore\Forms\Validators;

use Zend\Validator\AbstractValidator;

class AllowedHtmlTags extends AbstractValidator
{
	const INVALID_TAGS = 'invalid tags';
	const ALLOWED_TAGS = 'allowed tags';

	protected $messageTemplates = array(
			self::INVALID_TAGS => "content contains invalid html elements",
			self::ALLOWED_TAGS => "tags allowed: ",
	);

	public function isValid($value)
	{
		$this->setValue($value);

		$isValid = $this->validateTags($value);
		return $isValid;
	}//end function

	private function validateTags($value)
	{
		$fresult = "<a><abbr><area><b><br><center><del><div><em><font><hr><i><img><ins><map><p><pre><span><strike><strong><sub><sup><u>";
		$fresult .= "<li><ol><ul>";
		$fresult .= "<h1><h2><h3><h4><h5><h6>";
		$fresult .= "<table><tbody><td><tfoot><th><thead><tr>";
		$html = strip_tags($value, $fresult);

		if ($html != $value)
		{
			$this->messageTemplates[self::INVALID_TAGS] .= ". Elements allowed: $fresult.";
			$this->abstractOptions['messageTemplates'] = $this->messageTemplates;
			$this->error(self::INVALID_TAGS);
			return FALSE;
		}//end if

		return TRUE;
	}//end function
}//end class