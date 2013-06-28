<?php

define('INC_FROM_CRON_SCRIPT', true);
require('../config.php');
//require('../lib/ressource.lib.php');
global $conf;
$ATMdb=new TPDOdb;



$fk_fournisseur = (isset($_REQUEST['fk_fournisseur'])) ? intval($_REQUEST['fk_fournisseur']) : 9;
$mode_retour = (isset($_REQUEST['mode_retour'])) ? $_REQUEST['mode_retour'] : 'json' ;


//chargement des voitures
$TFacture = array('Tous'=>'Tous');
$sql = "SELECT DISTINCT idImport
	FROM ".MAIN_DB_PREFIX."rh_evenement
	WHERE fk_fournisseur =".$fk_fournisseur."
	AND idImport IS NOT NULL";


$ATMdb->Execute($sql);
while($row = $ATMdb->Get_line()) {
	$TFacture[$row->idImport] = $row->idImport;
}
if(isset($_REQUEST['DEBUG'])) {
	echo $sql.'<br>';
	print_r($TFacture);
}


if ($mode_retour=='json'){
	echo json_encode($TFacture);}
else{
	__out($TFacture);}

exit();
