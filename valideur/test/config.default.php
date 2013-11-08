<?php


require_once dirname(__FILE__).'/../../../dolibarr-rh/htdocs/master.inc.php';
define('ROOT','/var/www/dolibarr-rh/htdocs/');
define('COREROOT','/var/www/core/');
define('COREHTTP','http://127.0.0.1/core/');
ini_set('display_errors', 'on');
define('DB_HOST','localhost');
define('DB_NAME','dolibarrdebian');
define('DB_USER','root');
define('DB_PASS','root');
define('DB_DRIVER','mysqli');
define('MAIN_DB_PREFIX', 'llx_');
define('DOL_PACKAGE', true);
define('USE_TBS', true);
require(COREROOT.'inc.core.php');
define('DOL_ADMIN_USER', 'admin');