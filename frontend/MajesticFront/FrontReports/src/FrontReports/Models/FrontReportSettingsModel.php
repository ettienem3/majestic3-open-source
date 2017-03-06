<?php
namespace FrontReports\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontUserLogin\Models\FrontUserSession;

class FrontReportSettingsModel extends AbstractCoreAdapter
{
	/**
	 * Folder path to save settings
	 * @var string $path_settings
	 */
	private $path_settings = './data/profiles/report_config/';

	/**
	 * Flag indicating if model has been initialized
	 * @var boolean - default False
	 */
	private $flag_model_initialized = FALSE;

	/**
	 * Container for the profile's identifier, used to make up folder path
	 * @var mixed
	 */
	private $profile_identifier = FALSE;

	/**
	 * Load reports available to the profile
	 * The API returns a full list of reports made available, however, a profile might not want to display all.
	 * This config allows a profile to filter which reports should be displayed to users
	 * @return array|boolean
	 */
	public function getReportsAvailableSettings()
	{
		$this->initializeModel();
		if (!$this->flag_model_initialized)
		{
			return FALSE;
		}//end if

		if (is_file($this->path_settings . 'reports_available.dat'))
		{
			$content = file_get_contents($this->path_settings . 'reports_available.dat');
			if ($content != '')
			{
				$content = $this->decryptData($content);
			}//end if

			$arr_reports_available = unserialize($content);
			if (!is_array($arr_reports_available))
			{
				return FALSE;
			}//end if

			return $arr_reports_available;
		}//end if

		return FALSE;
	}//end function

	/**
	 * Set which reports should be made available to a profile
	 * @param array $arr_reports
	 */
	public function setReportsAvailableSettings(array $arr_reports)
	{
		$this->initializeModel();
		if (!$this->flag_model_initialized)
		{
			return FALSE;
		}//end if

		$content = serialize($arr_reports);
		$content = $this->encryptData($content);
		file_put_contents($this->path_settings . 'reports_available.dat', $content);
	}//end function

	/**
	 * Initialize the Report Settings Model
	 * Set various model vars to perform required operations
	 * @return \FrontReports\Models\FrontReportSettingsModel
	 */
	private function initializeModel()
	{
		if (!$this->flag_model_initialized)
		{
			//load user session to get profile identifier
			$objUser = FrontUserSession::isLoggedIn();
			$this->profile_identifier = $objUser->profile->profile_identifier;
			$this->path_settings .= $this->profile_identifier . '/';

			//check if path exists
			if (!is_dir($this->path_settings))
			{
				mkdir($this->path_settings, 755, TRUE);
			}//end if

			$this->flag_model_initialized = TRUE;
		}//end if

		return $this;
	}//end function

	/**
	 * Simple encrypt
	 * @param String $content
	 * @throws \Exception
	 * http://stackoverflow.com/questions/9262109/php-simplest-two-way-encryption
	 */
	private function encryptData($content)
	{
		if (!$this->flag_model_initialized)
		{
			throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : Report Settings Operation failed, required data is not available", 500);
		}//end if

		$nonceSize = openssl_cipher_iv_length('aes-256-ctr');
		$nonce = openssl_random_pseudo_bytes($nonceSize);

		$ciphertext = openssl_encrypt(
					$content,
					'aes-256-ctr',
					hex2bin($this->hexConvert($this->profile_identifier)),
					OPENSSL_RAW_DATA,
					$nonce
				);

		$str = base64_encode($nonce.$ciphertext);
		return $str;
	}//end function

	/**
	 * Simple decrypt
	 * @param string $content
	 * @throws \Exception
	 */
	private function decryptData($content)
	{
		if (!$this->flag_model_initialized)
		{
			throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : Report Settings Operation failed, required data is not available", 500);
		}//end if

		$content = base64_decode($content, true);
		if ($content === FALSE)
		{
			return FALSE;
		}//end if

		$nonceSize = openssl_cipher_iv_length('aes-256-ctr');
		$nonce = mb_substr($content, 0, $nonceSize, '8bit');
		$ciphertext = mb_substr($content, $nonceSize, null, '8bit');

		$str = openssl_decrypt(
					$ciphertext,
					'aes-256-ctr',
					hex2bin($this->hexConvert($this->profile_identifier)),
					OPENSSL_RAW_DATA,
					$nonce
				);

		return $str;
	}//end function

	private function hexConvert($string)
	{
	    $hex='';
	    for ($i=0; $i < strlen($string); $i++)
	    {
	        $hex .= dechex(ord($string[$i]));
	    }//end for

	    return $hex;
	}//end function
}//end class