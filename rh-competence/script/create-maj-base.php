<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 * 
 */
 	define('INC_FROM_CRON_SCRIPT', true);
	
	require('../config.php');
	require('../class/competence.class.php');

	$ATMdb=new Tdb;
	$ATMdb->db->debug=true;

	$o=new TRH_ligne_cv;
	$o->init_db_by_vars($ATMdb);
	
	$p=new TRH_competence_cv;
	$p->init_db_by_vars($ATMdb);
	
	
	
	
	
	
	