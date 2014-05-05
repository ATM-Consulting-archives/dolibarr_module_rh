#!/usr/bin/php
<?php
	define('INC_FROM_CRON_SCRIPT', true);
	chdir(__DIR__);
	
	require('../../config.php');
	require('../../class/absence.class.php');
	require('../../class/pointeuse.class.php');
	
	
	
	$ATMdb=new TPDOdb;

	$sqlReqUser="SELECT DISTINCT rowid FROM ".MAIN_DB_PREFIX."user WHERE rowid NOT IN ( SELECT fk_user from ".MAIN_DB_PREFIX."rh_compteur)";
	$ATMdb->Execute($sqlReqUser);
	$Tab=array();
	while($ATMdb->Get_line()) {
				$Tab[]=$ATMdb->Get_field('rowid');		
	}
	
	if(!empty($Tab)){
		
		foreach ($Tab as $idUserC) {
			$o=new TRH_Compteur;
			$o->initCompteur($ATMdb, $idUserC);
			$o->save($ATMdb);
		}
	}
	
	
	
	$sqlReq="SELECT DISTINCT rowid FROM ".MAIN_DB_PREFIX."user WHERE rowid NOT IN ( SELECT fk_user from ".MAIN_DB_PREFIX."rh_absence_emploitemps)";
	$ATMdb->Execute($sqlReq);
	$Tab=array();
	while($ATMdb->Get_line()) {
				$Tab[]=$ATMdb->Get_field('rowid');		
	}
	
	if(!empty($Tab)){
		
		foreach ($Tab as $idUserC) {
			$r=new TRH_EmploiTemps;
			$r->initCompteurHoraire($ATMdb, $idUserC);
			$r->save($ATMdb);
		}
	}
