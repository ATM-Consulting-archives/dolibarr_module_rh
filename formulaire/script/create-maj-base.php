<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 * 
 */
 	define('INC_FROM_CRON_SCRIPT', FALSE);
	
	require('../config.php');
	require('../class/groupeformulaire.class.php');
	require('../class/type_poste.class.php');
	
	$ATMdb=new TPDOdb;
	$ATMdb->db->debug=true;

	$o=new TGroupeFormulaire;
	$o->init_db_by_vars($ATMdb);
	
	$o=new TRH_fichePoste;
	$o->init_db_by_vars($ATMdb);