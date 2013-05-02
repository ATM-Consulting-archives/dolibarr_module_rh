<?php
require('../config.php');
require('../lib/ressource.lib.php');
global $conf;

if(isset($_REQUEST['type'])) {
		
		//echo $_REQUEST['type'];
		/*$TRessource = array('');
		$ATMdb =new TPDOdb;
		
		$sqlReq="SELECT rowid,libelle, numId FROM ".MAIN_DB_PREFIX."rh_ressource WHERE entity=".$conf->entity."
		AND fk_rh_ressource_type=".$_REQUEST['type'];
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$TRessource[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('libelle').' '.$ATMdb->Get_field('numId');
			}*/
		
		$TRessource = getRessource($_REQUEST['type']);
		echo json_encode($TRessource);
		
		exit();
	}
	