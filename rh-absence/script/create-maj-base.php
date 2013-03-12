<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 * 
 */
 	define('INC_FROM_CRON_SCRIPT', true);
	
	require('../config.php');
	require('../class/absence.class.php');

	$ATMdb=new Tdb;
	$ATMdb->db->debug=true;

	$o=new TRH_Compteur;
	$o->init_db_by_vars($ATMdb);
	
	$sqlReqUser="SELECT DISTINCT rowid FROM llx_user WHERE rowid NOT IN ( SELECT fk_user from llx_rh_compteur)";
	$ATMdb->Execute($sqlReqUser);
	while($ATMdb->Get_line()) {
				$Tab[]=$ATMdb->Get_field('rowid');		
	}
	foreach ($Tab as $idUserC) {
		$o=new TRH_Compteur;
		$o->initCompteur($idUserC, $ATMdb);
		$o->save($ATMdb);
	}
				
	
	
	$p=new TRH_Absence;
	$p->init_db_by_vars($ATMdb);
	
