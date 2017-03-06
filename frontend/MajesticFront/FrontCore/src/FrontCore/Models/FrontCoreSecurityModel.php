<?php
namespace FrontCore\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
//@TODO is this class still valid or required????
class FrontCoreSecurityModel extends AbstractCoreAdapter
{
	/*
	 * Encryption key value
	 */
	private $encryption_key = "'c58,YB!)DHuw-wr6Fr6$2";
	private $current_key = "rw4JAKMqZ.>p;jgBg3_=a";

	public function encodeValue($value)
	{
		return $this->encode($value);
	}//end function

	public function decodeValue($value)
	{
		return $this->decode($value);
	}//end function

	/**
	 * Util for encrypt and decrypt of values
	 * @param string $hexdata
	 * @return string
	 */
	private function hex2bin($hexdata)
	{
		$bindata="";
		for ($i=0;$i<strlen($hexdata);$i+=2) {
			$bindata.=chr(hexdec(substr($hexdata,$i,2)));
		}
		return $bindata;
	}//end function

	/**
	 * Decode an encrypted value
	 * @param string $value
	 * @return string
	 */
	private function decode($value)
	{
		$decrypted_data = "";
		if ($value != "")
		{
			if (!$this->current_key)
			{
				throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : Unable to decode value. Encryption key is invalid", 500);
			}//end if

			$key = substr($this->current_key,0,54); //keys max length is 54 characters
			$value = $this->hex2bin($value);
			$td = mcrypt_module_open("blowfish","","ecb","");
			$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td),MCRYPT_RAND);
			mcrypt_generic_init($td,$key,$iv);
			$decrypted_data = mdecrypt_generic($td,$value);
			mcrypt_generic_deinit($td);
			mcrypt_module_close($td);
		}//end if

		return trim($decrypted_data);
	}//end function

	/**
	 * Encrypt a value
	 * @param string $value
	 * @return string
	 */
	private function encode($value)
	{
		$encrypted_data = "";
		if ($value != "")
		{
			if (!$this->current_key)
			{
				throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : Unable to decode value. Encryption key is invalid", 500);
			}//end if

			$key = substr($this->current_key,0,54); //keys max length is 54 characters
			$td = mcrypt_module_open("blowfish","","ecb","");
			$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td),MCRYPT_RAND);
			mcrypt_generic_init($td,$key,$iv);
			$encrypted_data = mcrypt_generic($td,$value);
			mcrypt_generic_deinit($td);
			mcrypt_module_close($td);
		}//end if
		return bin2hex($encrypted_data);
	}//end function

}//end class