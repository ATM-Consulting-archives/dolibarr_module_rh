<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 * 
 */
 	define('INC_FROM_CRON_SCRIPT', true);
	
	require('../config.php');
	require('../class/competence.class.php');
	require('../class/formation.class.php');
	require('../class/productivite.class.php');
	

	$ATMdb=new TPDOdb;
	$ATMdb->db->debug=true;
	
	//Competence
	$o=new TRH_ligne_cv;
	$o->init_db_by_vars($ATMdb);
	
	$p=new TRH_formation_cv;
	$p->init_db_by_vars($ATMdb);
	
	$q=new TRH_competence_cv;
	$q->init_db_by_vars($ATMdb);
	
	$r=new TRH_remuneration;
	$r->init_db_by_vars($ATMdb);
	
	$r=new TRH_remunerationPrime;
	$r->init_db_by_vars($ATMdb);
	
	$r=new TRH_dif;
	$r->init_db_by_vars($ATMdb);
	
	// Productivité
	$r=new TRH_productivite;
	$r->init_db_by_vars($ATMdb);
	
	$r=new TRH_productiviteUser;
	$r->init_db_by_vars($ATMdb);
	
	$r=new TRH_productiviteIndice;
	$r->init_db_by_vars($ATMdb);
	
	
	//Formation
	$r=new TRH_formation_plan;
	$r->init_db_by_vars($ATMdb);
	
	$r=new TRH_formation_section;
	$r->init_db_by_vars($ATMdb);
	
	$r=new TRH_formation;
	$r->init_db_by_vars($ATMdb);
	
	$r=new TRH_formation_type_priorite;
	$r->init_db_by_vars($ATMdb);
	
	$r=new TRH_formation_session;
	$r->init_db_by_vars($ATMdb);
	
	$r=new TRH_formation_participant;
	$r->init_db_by_vars($ATMdb);
	
	$r=new TRH_formation_participant_statut;
	$r->init_db_by_vars($ATMdb);