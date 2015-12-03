<?php
namespace FrontUsers\Tables;

use FrontUsers\Entities\FrontUserEntity;
use Zend\Db\TableGateway\TableGateway;

class UsersTable
{
	protected $tableGateway;

	/**
	 * Set table name
	 * @var String
	 */
	public static $tableName = "users";

	public function __construct(TableGateway $tableGateway)
	{
		$this->tableGateway = $tableGateway;
	}//end function

	/**
	 * Select a user
	 * @param array $arr_where
	 * @return \FrontUsers\Entities\FrontUserEntity
	 */
	public function selectUser(array $arr_where)
	{
		$select = $this->tableGateway->getSql()->select();

		//set where
		$select->where($arr_where);

		//limit result
		$select->limit(1);

		//execute
		$objResult = $this->tableGateway->selectWith($select);
		return $objResult->current();
	}//end function

	/**
	 * Create / Update a user
	 * @param FrontUserEntity $objUser
	 * @return \FrontUsers\Entities\FrontUserEntity
	 */
	public function saveUser(FrontUserEntity $objUser)
	{
		//extract data
		$arr_data = array(
			"profile_id" => $objUser->get("profile_id"),
			"profile_identifier" => $objUser->get("profile_identifier"),
			"uname" => $objUser->get("uname_secure"),
			"pword" => $objUser->get("pword_secure"),
		);

		//double check for duplicate entries becuase of mismatch in ids
		$objUserCheck = $this->tableGateway->select(array("profile_identifier" => $objUser->get("profile_identifier")))->current();
		if (is_object($objUserCheck))
		{
			$objUser->set("id", $objUserCheck->get("id"));	
		}//end if
		
		if (is_numeric($objUser->get("id")))
		{
			//update record
			$this->tableGateway->update($arr_data, array("id" => $objUser->get("id")));
		} else {
			//create record
			$this->tableGateway->insert($arr_data);
			$objUser->set("id", $this->tableGateway->getLastInsertValue());
		}//end if

		return $objUser;
	}//end function

	/**
	 * Delete a user
	 * @param FrontUserEntity $objUser
	 */
	public function deleteUser(FrontUserEntity $objUser)
	{
		$this->tableGateway->delete(array("profile_identifier" => $objUser->get("profile_identifier")));
	}//end function
}//end class