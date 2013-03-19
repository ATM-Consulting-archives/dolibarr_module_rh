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
	$Tab=array();
	while($ATMdb->Get_line()) {
				$Tab[]=$ATMdb->Get_field('rowid');		
	}
	
	if(!empty($Tab)){
		
		foreach ($Tab as $idUserC) {
			$o=new TRH_Compteur;
			$o->initCompteur($idUserC, $ATMdb);
			$o->save($ATMdb);
		}
	}
	
	
	
	
	
	$p=new TRH_Absence;
	$p->init_db_by_vars($ATMdb);
	
	
	
	
	
	
	$q=new TRH_AdminCompteur;
	$q->init_db_by_vars($ATMdb);
	$q->congesAcquisMensuelInit='2.08';
	$q->date_rttClotureInit=strtotime('2013-06-01 00:00:00');
	$q->date_congesClotureInit=strtotime('2013-03-01 00:00:00');
	$q->save($ATMdb);
	
	
	
	
	
	
	$r=new TRH_EmploiTemps;
	$r->init_db_by_vars($ATMdb);
	$sqlReq="SELECT DISTINCT rowid FROM llx_user WHERE rowid NOT IN ( SELECT fk_user from llx_rh_absence_emploitemps)";
	$ATMdb->Execute($sqlReq);
	$Tab=array();
	while($ATMdb->Get_line()) {
				$Tab[]=$ATMdb->Get_field('rowid');		
	}
	
	if(!empty($Tab)){
		
		foreach ($Tab as $idUserC) {
			$r=new TRH_EmploiTemps;
			$r->initCompteurHoraire($idUserC, $ATMdb);
			$r->save($ATMdb);
		}
	}

	
	
	$s=new TRH_JoursFeries;
	$s->init_db_by_vars($ATMdb);