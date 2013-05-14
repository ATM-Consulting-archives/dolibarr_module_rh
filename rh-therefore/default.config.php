<?php
	define('ROOT','/var/www/CPro/dolibarr-rh/htdocs/');
	define('HTTP','http://127.0.0.1/CPro/dolibarr-rh/htdocs/');

	if(defined('INC_FROM_CRON_SCRIPT')) {
		include(ROOT."master.inc.php");
	}
	elseif(!defined('INC_FROM_DOLIBARR')) {
		include(ROOT."main.inc.php");
	}

	define('THEREFORE_READ','http://srvtherefore/TWA/Client/TheGetDoc.aspx?CtgryNo=[categorie]&Id_Dolibarr=[id]');
	define('THEREFORE_LOADER','//SRVTHEREFORE/Scan/Loader');
	define('THEREFORE_USER','dolibarr2');
    define('THEREFORE_PASSWORD','dolibar2013');
	define('THEREFORE_GROUP','groupecpro');
	