<?php

	define('ROOT','/var/www/ATM/dolibarr/htdocs/');
	define('COREROOT','/var/www/ATM/atm-core/');
	define('COREHTTP','http://127.0.0.1/ATM/atm-core/');
	define('HTTP','http://127.0.0.1/ATM/dolibarr/');

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
	define('DIR_DOC_OUTPUT', '/var/lib/dolibarr/documents/absence/');
	
	define('DATE_RTT_CLOTURE', '28-02-2014');
	define('DATE_CONGES_CLOTURE', '31-05-2014');
	
	$TJourNonTravailleEntreprise=array('samedi','dimanche');

	$TTypeMetier=array(
		'cadre'=>'Cadre'
		,'35h'=>'Au 35h'
		,'39h'=>'Au 39h'
		,'noRTT'=>'Sans RTT'
	);