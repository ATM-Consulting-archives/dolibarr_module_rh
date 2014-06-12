<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 * 
 */
 	define('INC_FROM_CRON_SCRIPT', true);
	
	require('../config.php');
	require('../class/absence.class.php');
	require('../class/pointeuse.class.php');
	require('../class/ticket.class.php');
	
	$ATMdb=new TPDOdb;
	$ATMdb->db->debug=true;

	$o=new TRH_declarationTemps;
	$o->init_db_by_vars($ATMdb);

	$o=new TRH_Pointeuse;
	$o->init_db_by_vars($ATMdb);

	$o=new TRH_TicketResto;
	$o->init_db_by_vars($ATMdb);
	
	$o=new TRH_Compteur;
	$o->init_db_by_vars($ATMdb);
	
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
	
	
	
	
	
	$p=new TRH_Absence;
	$p->init_db_by_vars($ATMdb);
	

	$sqlReq="DELETE FROM ".MAIN_DB_PREFIX."rh_admin_compteur WHERE 1";
	$ATMdb->Execute($sqlReq);
	
	$q=new TRH_AdminCompteur;
	$q->init_db_by_vars($ATMdb);
	$q->congesAcquisMensuelInit=2.08;
	$q->rttCumuleInitCadreCpro=8;
	$q->rttCumuleInitCadreCproInfo=9;
	$q->date_rttClotureInit=strtotime(DATE_RTT_CLOTURE);
	$q->date_congesClotureInit=strtotime(DATE_CONGES_CLOTURE);
	$q->save($ATMdb);
	
	

	$r=new TRH_EmploiTemps;
	$r->init_db_by_vars($ATMdb);
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

	
	
	$s=new TRH_JoursFeries;
	$s->init_db_by_vars($ATMdb);
	
	
	
	$t=new TRH_RegleAbsence;
	$t->init_db_by_vars($ATMdb);
	
	$u=new TRH_TypeAbsence;
	$u->init_db_by_vars($ATMdb);
	
