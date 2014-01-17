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
	
	define('USER_MAIL_SENDER', 'webmaster@atm-consulting.fr');
	define('USER_MAIL_RECEIVER', 'webmaster@atm-consulting.fr');
	
	define('DAYS_BEFORE_ALERT',30);

	define('AUTOMATIC_ATTRIBUTION_USER_ENTITY_ON_RESSOURCE', true);
	$TGroupeAutomaticAttributionByAnalytique=array(
		 /*'SG'=>'CPRO Groupe'
		 ,'05'=>'CPRO Informatique'
		 ,'02'=>'Cpro Valence'
		 ,'03'=>'CPRO Alliance'
		 ,'04'=>'CPRO Pixel'
		 ,'06'=>'CPRO VDI'*/
	);
