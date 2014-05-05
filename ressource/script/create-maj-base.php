<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 * 
 */
 	define('INC_FROM_CRON_SCRIPT', true);
	
	require('../config.php');
	require('../class/ressource.class.php');
	require('../class/contrat.class.php');
	require('../class/evenement.class.php');
	require('../class/regle.class.php');

	$ATMdb=new TPDOdb;
	$ATMdb->db->debug=true;

	$o=new TRH_Ressource_type;
	$o->init_db_by_vars($ATMdb);
	
	$p=new TRH_Ressource_field;
	$p->init_db_by_vars($ATMdb);
	
	$p=new TRH_Ressource;
	$p->init_db_by_vars($ATMdb);
	
	$p=new TRH_Contrat;
	$p->init_db_by_vars($ATMdb);
	
	$p=new TRH_Contrat_Ressource;
	$p->init_db_by_vars($ATMdb);
	
	$p=new TRH_Evenement;
	$p->init_db_by_vars($ATMdb);
	//ALTER table customer modify Addr char(30)
	$sqlReq="ALTER TABLE ".MAIN_DB_PREFIX."rh_evenement MODIFY appels LONGTEXT";
	$ATMdb->Execute($sqlReq);
	
	$p=new TRH_Type_Evenement;
	$p->init_db_by_vars($ATMdb);
	
	$p=new TRH_Ressource_Regle;
	$p->init_db_by_vars($ATMdb);