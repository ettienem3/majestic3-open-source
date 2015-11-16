<?php
namespace FrontCore\Models\Security;

use FrontCore\Adapters\AbstractCoreAdapter;

class CryptoModel extends AbstractCoreAdapter
{
	/**
	 * Encrypt/decrypt value
	 * @param string $action - Options: encrypt/decrypt
	 * @param string $string - Value to be processed
	 * @param array $arr_params
	 * @return string
	 */
	public function sha1EncryptDecryptValue($action, $string, array $arr_params)
	{
		$output = false;

		$encrypt_method = "AES-256-CBC";

		//are keys set?
		if (!isset($arr_params["secret_key"]))
		{
			$arr_config = $this->getServiceLocator()->get("config");
			$arr_params = $arr_config["security"];
		}//end if

		$secret_key = $arr_params["secret_key"];
		$secret_iv = $arr_params["secret_iv"];

		// hash
		$key = hash('sha256', $secret_key);

		//iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
		$iv = substr(hash('sha256', $secret_iv), 0, 16);

		if( $action == 'encrypt' )
		{
			$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
			$output = base64_encode($output);
		} else if( $action == 'decrypt' ) {
			$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
		}//end if

		return $output;
	}//end function
}//end class