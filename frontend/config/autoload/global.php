<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file .
 */

return array(
	'front_end_application_config' => array(
		/**
		 * Globally manage caching capability
		 */
		'cache_enabled' => false,

		//enable system form caches
		'cache_enabled_system_forms' => true,

		//encryption keys
		/**
		 * You should replace these with your own values upon installation!
		 */
		'security' => array(
			'secret_key' => '$@Dpm7a>}$:`Sq);',
			'secret_iv' => 'P+%gJ>:/]9KD!k~3',
		),
	),

	/**
	 * If you have redis cache installed and available to you, configure it here
	 */
    'cache_redis_config_common' => array(
			'adapter' => array(
				'name' => 'redis',
				'options' => array(
					'server' => array(
						'host' => '127.0.0.1',
						'port' => '6379',
						'timeout' => '30',
					),
					'ttl' => 3600,
					'namespace' => 'FrontEndCache',
				),
			),
    		'plugins' => array(
    				'exception_handler' => array('throw_exceptions' => true),
    				array(
    					'name' => 'serializer',
    					'options' => array(),
    				),
    				array(
    					'name' => 'clearexpiredbyfactor',
    					'options' => array(),
    				),
    				array(
    						'name' => 'optimizebyfactor',
    						'options' => array(),
    				),
    		),
		),

	/**
	 * Default local file system cache, makes use of the ./data/cache folder
	 */
	'cache_filesystem_config_common' => array(
			'adapter' => array(
					'name' => 'filesystem',
					'options' => array(
							'cache_dir' => "./data/cache",
							'ttl' => 3600,
							'dir_permission' => 0777,
							'file_permission' => 0666,
							'namespace' => 'FrontEndCache',
					),

			),
			'plugins' => array(
					// Don't throw exceptions on cache errors
					'exception_handler' => array(
							'throw_exceptions' => true, //@TODO disable exception on production
					),
					array(
							'name' => 'serializer',
							//'options' => 'Zend\Serializer\Adapter\PhpSerialize',
							'options' => array(),
					),
					array(
							'name' => 'clearexpiredbyfactor',
							'options' => array(),
					),
					array(
							'name' => 'optimizebyfactor',
							'options' => array(),
					),
			)
		),

	/**
	 * Global value for where general shared content should be pulled from, ie css, js, images etc
	 */
	'cdn_config' => array(
			'url' => 'https://cdn-aws.majestic3.com',
		),
);
