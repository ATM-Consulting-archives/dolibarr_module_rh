#!/usr/bin/php
<?php
/*
 * Script Mettant à jour les attributions chaque nuit
 * 
 */
 	define('INC_FROM_CRON_SCRIPT', true);
	require('../../config.php');
	
	if(defined('AUTOMATIC_ATTRIBUTION_USER_ENTITY_ON_RESSOURCE') && AUTOMATIC_ATTRIBUTION_USER_ENTITY_ON_RESSOURCE ) {
		
		$date=date('Y-m-d');
		
		$ATMdb=new TPDOdb;
		$ATMdb->Execute("SELECT rowid FROM ".MAIN_DB_PREFIX."rh_evenement WHERE date_debut<='$date' && date_fin>='$date'");
		$Tab = $ATMdb->Get_All();
		
		foreach($Tab as $row) {
			
			$e=new TRH_Evenement;
			$e->load($ATMdb, $row->rowid);
			
			$e->save($ATMdb);
			
		}
		
		
	}
	else {
		exit('Attribution auto désactivée');
	}
