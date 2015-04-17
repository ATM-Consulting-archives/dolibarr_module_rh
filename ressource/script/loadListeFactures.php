<?php

define('INC_FROM_CRON_SCRIPT', true);
require('../config.php');
//require('../lib/ressource.lib.php');
global $conf;
$ATMdb=new TPDOdb;

$fk_fournisseur = (isset($_REQUEST['fk_fournisseur'])) ? intval($_REQUEST['fk_fournisseur']) : 9;
$mode_retour = (isset($_REQUEST['mode_retour'])) ? $_REQUEST['mode_retour'] : 'json' ;

dol_include_once('/ressource/lib/ressource.lib.php');
$TFacture = getFactures($ATMdb, $fk_fournisseur);

if ($mode_retour=='json'){
	echo json_encode($TFacture);}
else{
	__out($TFacture);}

exit();
