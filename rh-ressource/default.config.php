<?php

	define('ROOT','/var/www/dolibarr-rh/htdocs/');
	define('COREROOT','/var/www/core/');
	define('COREHTTP','http://127.0.0.1/core/');
	define('HTTP','http://127.0.0.1/dolibarr-rh/htdocs/');

	if(defined('INC_FROM_CRON_SCRIPT')) {
		include(ROOT."master.inc.php");
	}
	else {
		include(ROOT."main.inc.php");
	}

	define('DB_HOST',$dolibarr_main_db_host);
	define('DB_NAME',$dolibarr_main_db_name);
	define('DB_USER',$dolibarr_main_db_user);
	define('DB_PASS',$dolibarr_main_db_pass);
	define('DB_DRIVER','mysqli');

	define('DOL_PACKAGE', true);
	define('USE_TBS', true);
	
	require(COREROOT.'inc.core.php');
	
	define('DOL_ADMIN_USER', 'admin');
	
	define('DIR_DOC_OUTPUT', '/var/lib/dolibarr/documents/ressource/');