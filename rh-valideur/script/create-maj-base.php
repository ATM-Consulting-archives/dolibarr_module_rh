<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 * 
 */
 	define('INC_FROM_CRON_SCRIPT', true);
	
	require('../config.php');
	require('../class/valideur.class.php');

	$ATMdb=new Tdb;
	$ATMdb->db->debug=true;

	$o=new TRH_valideur_groupe;
	$o->init_db_by_vars($ATMdb);