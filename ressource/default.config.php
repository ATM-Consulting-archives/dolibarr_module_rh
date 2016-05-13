<?php



	if(is_file('../main.inc.php'))$dir = '../';
	else if(is_file( '../../main.inc.php')) $dir = '../../';
	else  if(is_file('../../../main.inc.php'))$dir = '../../../';
	else  if(is_file('../../../../main.inc.php'))$dir = '../../../../';
	else  if(is_file('../../../../../main.inc.php'))$dir = '../../../../../';
	else {
		exit('Impossible to find main.inc');
	}

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
	
	dol_include_once('/abricot/inc.core.php');

	
	define('DOL_ADMIN_USER', __val($conf->global->RH_DOL_ADMIN_USER, 'admin') );
	
	define('USER_MAIL_SENDER', __val($conf->global->RH_USER_MAIL_SENDER,'webmaster@atm-consulting.fr'));
	define('USER_MAIL_RECEIVER', __val($conf->global->RH_USER_MAIL_RECEIVER,'webmaster@atm-consulting.fr'));
	
	define('DAYS_BEFORE_ALERT', __val($conf->global->RH_DAYS_BEFORE_ALERT,30,'integer'));

	define('AUTOMATIC_ATTRIBUTION_USER_ENTITY_ON_RESSOURCE', __val($conf->global->RH_AUTOMATIC_ATTRIBUTION_USER_ENTITY_ON_RESSOURCE ,0,'integer') );
	$TGroupeAutomaticAttributionByAnalytique=array(
		 /*'SG'=>'CPRO Groupe'
		 ,'05'=>'CPRO Informatique'
		 ,'02'=>'Cpro Valence'
		 ,'03'=>'CPRO Alliance'
		 ,'04'=>'CPRO Pixel'
		 ,'06'=>'CPRO VDI'*/
	);
