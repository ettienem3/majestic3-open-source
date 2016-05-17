<?php
namespace FrontUsers;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use FrontUsers\Events\FrontUsersEvents;
use FrontUsers\Models\FrontUsersModel;
use FrontUsers\Entities\FrontUserEntity;
use FrontUsers\Models\FrontUserRolesAdminModel;
use FrontUsers\Models\FrontUsersRolesAclLinksModel;
use FrontUsers\Entities\FrontUserRoleAclLinkEntity;
use FrontUsers\Entities\FrontUserRoleAdminEntity;
use FrontUsers\Models\FrontUsersTasksModel;
use FrontUsers\Entities\FrontUsersUserTaskEntity;
use FrontUsers\Entities\FrontUserStandardRoleEntity;
use FrontUsers\Storage\UserFileSystemStorage;
use FrontUsers\Tables\UsersTable;
use FrontUsers\Tables\UserSettingsTable;
use FrontUsers\Entities\FrontUserSettingsEntity;
use FrontUsers\Storage\UserMySqlStorage;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use FrontUsers\Tables\UserPreferencesTable;
use FrontUsers\Entities\FrontUserNativePreferencesEntity;
use FrontUsers\Entities\FrontUserCacheSettingsEntity;
use FrontUsers\Tables\UserNativePreferencesTable;
use FrontUsers\Tables\UserCacheSettingsTable;
use FrontUsers\Models\FrontUsersAclRulesModel;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        /**
         * Register event listeners
         */
        $eventsFrontUsers = $e->getApplication()->getServiceManager()->get("FrontUsers\Events\FrontUsersEvents");
        $eventsFrontUsers->registerEvents();
    }//end function

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }//end function

    public function getAutoloaderConfig()
    {
        return array(
        		'Zend\Loader\ClassMapAutoloader' => array(
        				__DIR__ . '/autoload_classmap.php',
        		),
            	'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }//end function

    public function getServiceConfig()
    {
    	return array(
    			'factories' => array(

    					/**
    					 * Models
    					 */
    					'FrontUsers\Models\FrontUsersModel' => function ($sm) {
    						$model_users = new FrontUsersModel();
    						return $model_users;
    					}, //end function

    					"FrontUsers\Models\FrontUserRolesAdminModel" => function ($sm) {
    						$model_user_roles_admin = new FrontUserRolesAdminModel();
    						return $model_user_roles_admin;
    					}, //end function

    					"FrontUsers\Models\FrontUsersRolesAclLinksModel" => function ($sm) {
    						$model_roles_acl_links = new FrontUsersRolesAclLinksModel();
    						return $model_roles_acl_links;
    					}, //end function

    					"FrontUsers\Models\FrontUsersTasksModel" => function ($sm) {
    						$model_users_tasks = new FrontUsersTasksModel();
    						return $model_users_tasks;
    					}, //end function

    					'FrontUsers\Storage\UserFileSystemStorage' => function ($sm) {
    						$model_user_storage = new UserFileSystemStorage();

    						//set path @TODO read from config
    						$model_user_storage->setPath("./data/user_storage");

    						return $model_user_storage;
    					}, //end function

    					'FrontUsers\Storage\UserMySqlStorage' => function ($sm) {
    						$model_user_storage = new UserMySqlStorage();
    						return $model_user_storage;
    					}, //end function

    					'FrontUsers\Models\FrontUsersAclRulesModel' => function ($sm) {
    						$model = new FrontUsersAclRulesModel();
    						return $model;
    					}, //end function

    					/**
    					 * Entities
    					*/
    					'FrontUsers\Entities\FrontUserEntity' => function ($sm) {
    						$entity_user = new FrontUserEntity();

    						//load crypto
    						$objCrypto = $sm->get("FrontCore\Models\Security\CryptoModel");
    						$entity_user->setCrypto($objCrypto);

    						return $entity_user;
    					}, //end function

    					"FrontUsers\Entities\FrontUserRoleAclLinkEntity" => function ($sm) {
    						$entity_role_acl_link = new FrontUserRoleAclLinkEntity();
    						return $entity_role_acl_link;
    					}, //end function

    					"FrontUsers\Entities\FrontUserRoleAdminEntity" => function ($sm) {
    						$entity_role_admin = new FrontUserRoleAdminEntity();
    						return $entity_role_admin;
    					}, //end function

    					"FrontUsers\Entities\FrontUsersUserTaskEntity" => function ($sm) {
    						$entity_user_task = new FrontUsersUserTaskEntity();
    						return $entity_user_task;
    					}, //end function

    					"FrontUsers\Entities\FrontUserStandardRoleEntity" => function ($sm) {
    						$entity_standard_roles = new FrontUserStandardRoleEntity();
    						return $entity_standard_roles;
    					}, //end function

    					'FrontUsers\Entities\FrontUserSettingsEntity' => function ($sm) {
    						$entity_user_settings = new FrontUserSettingsEntity();
    						return $entity_user_settings;
    					}, //end function

    					'FrontUsers\Entities\FrontUserNativePreferencesEntity' => function ($sm) {
    						$entity = new FrontUserNativePreferencesEntity();
    						return $entity;
    					}, //end function

    					'FrontUsers\Entities\FrontUserCacheSettingsEntity' => function ($sm) {
    						$entity = new FrontUserCacheSettingsEntity();
    						return $entity;
    					}, //end function

    					/**
    					 * Tabes
    					 */
    					'FrontUsers\Tables\UsersTable' => function ($sm) {
    						$adapter = $sm->get("db_frontend");

    						//setup result set to return contacts as a contact entity object
    						$resultSetPrototype = new ResultSet();
    						$entity = $sm->get('FrontUsers\Entities\FrontUserEntity');
    						$resultSetPrototype->setArrayObjectPrototype($entity);

    						$tableGateway = new TableGateway(\FrontUsers\Tables\UsersTable::$tableName, $adapter, NULL, $resultSetPrototype);
    						$table = new UsersTable($tableGateway);
    						return $table;
    					}, //end function

    					'FrontUsers\Tables\UserSettingsTable' => function ($sm) {
    						$adapter = $sm->get("db_frontend");

    						//setup result set to return contacts as a contact entity object
    						$resultSetPrototype = new ResultSet();
    						$entity= $sm->get('FrontUsers\Entities\FrontUserSettingsEntity');
    						$resultSetPrototype->setArrayObjectPrototype($entity);

    						$tableGateway = new TableGateway(\FrontUsers\Tables\UserSettingsTable::$tableName, $adapter, NULL, $resultSetPrototype);
    						$table = new UserSettingsTable($tableGateway);
    						return $table;
    					}, //end function

    					'FrontUsers\Tables\UserPreferencesTable' => function ($sm) {
    						$adapter = $sm->get("db_frontend");

    						//setup result set to return contacts as a contact entity object
    						$resultSetPrototype = new ResultSet();
    						$entity = $sm->get('FrontUsers\Entities\FrontUserSettingsEntity');
    						$resultSetPrototype->setArrayObjectPrototype($entity);

    						$tableGateway = new TableGateway(\FrontUsers\Tables\UserPreferencesTable::$tableName, $adapter, NULL, $resultSetPrototype);
    						$table = new UserPreferencesTable($tableGateway);
    						return $table;
    					}, //end function

    					'FrontUsers\Tables\UserNativePreferencesTable' => function ($sm) {
	    					$adapter = $sm->get("db_frontend");

	    					//setup result set to return contacts as a contact entity object
	    					$resultSetPrototype = new ResultSet();
	    					$entity = $sm->get('FrontUsers\Entities\FrontUserNativePreferencesEntity');
	    					$resultSetPrototype->setArrayObjectPrototype($entity);

	    					$tableGateway = new TableGateway(\FrontUsers\Tables\UserNativePreferencesTable::$tableName, $adapter, NULL, $resultSetPrototype);
	    					$table = new UserNativePreferencesTable($tableGateway);
	    					return $table;
    					}, //end function

    					'FrontUsers\Tables\UserCacheSettingsTable' => function ($sm) {
	    					$adapter = $sm->get("db_frontend");

	    					//setup result set to return contacts as a contact entity object
	    					$resultSetPrototype = new ResultSet();
	    					$entity = $sm->get('FrontUsers\Entities\FrontUserCacheSettingsEntity');
	    					$resultSetPrototype->setArrayObjectPrototype($entity);

	    					$tableGateway = new TableGateway(\FrontUsers\Tables\UserCacheSettingsTable::$tableName, $adapter, NULL, $resultSetPrototype);
	    					$table = new UserCacheSettingsTable($tableGateway);
	    					return $table;
    					}, //end function

    					/**
    					 * Events
    					 */
    					'FrontUsers\Events\FrontUsersEvents' => function ($sm) {
    						$events_frontusers = new FrontUsersEvents();
    						return $events_frontusers;
    					},
    			),

    			"shared" => array(
    					"FrontUsers\Entities\FrontUserEntity" 						=> FALSE,
    					"FrontUsers\Entities\FrontUserSettingsEntity" 				=> FALSE,
    					"FrontUsers\Entities\FrontUserRoleAclLinkEntity" 			=> FALSE,
    					"FrontUsers\Entities\FrontUserRoleAdminEntity" 				=> FALSE,
    					"FrontUsers\Entities\FrontUsersUserTaskEntity" 				=> FALSE,
    					"FrontUsers\Entities\FrontUserStandardRoleEntity" 			=> FALSE,
    					'FrontUsers\Entities\FrontUserNativePreferencesEntity' 		=> FALSE,
    					'FrontUsers\Entities\FrontUserCacheSettingsEntity' 			=> FALSE,

    				), //end shared
    	);
    }//end function
}//end class