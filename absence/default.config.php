<?php

	if(is_file('../main.inc.php'))$dir = '../';
	else  if(is_file('../../../main.inc.php'))$dir = '../../../';
	else  if(is_file('../../../../main.inc.php'))$dir = '../../../../';
	else  if(is_file('../../../../../main.inc.php'))$dir = '../../../../../';
	else $dir = '../../';


	if(!defined('INC_FROM_DOLIBARR') && defined('INC_FROM_CRON_SCRIPT')) {
		include($dir."master.inc.php");
	}
	elseif(!defined('INC_FROM_DOLIBARR')) {
		include($dir."main.inc.php");
	} else {
		global $dolibarr_main_db_host, $dolibarr_main_db_name, $dolibarr_main_db_user, $dolibarr_main_db_pass;
	}

	if(!defined('DB_HOST')) {
		define('DB_HOST',$dolibarr_main_db_host);
		define('DB_NAME',$dolibarr_main_db_name);
		define('DB_USER',$dolibarr_main_db_user);
		define('DB_PASS',$dolibarr_main_db_pass);
		define('DB_DRIVER',$dolibarr_main_db_type);
	}
	
	define('USE_CALENDAR', true);
	dol_include_once('/abricot/inc.core.php');
	
	define('DOL_ADMIN_USER', __val($conf->global->RH_DOL_ADMIN_USER, 'admin') );
	
	define('USER_MAIL_SENDER', __val($conf->global->RH_USER_MAIL_SENDER,'webmaster@atm-consulting.fr'));
	
	define('DATE_RTT_CLOTURE', __val($conf->global->RH_DATE_RTT_CLOTURE,'28-02-2014'));
	define('DATE_CONGES_CLOTURE', __val($conf->global->RH_DATE_CONGES_CLOTURE,'31-05-2014'));
	
	$TJourNonTravailleEntreprise= explode(',', __val($conf->global->RH_JOURS_NON_TRAVAILLE,'aucun'))  ;//array('samedi','dimanche');

	$TTypeMetier=array(
	/*	'cadre'=>'Cadre'
		,'35h'=>'Au 35h'
		,'39h'=>'Au 39h'
		,'noRTT'=>'Sans RTT'*/
	);
