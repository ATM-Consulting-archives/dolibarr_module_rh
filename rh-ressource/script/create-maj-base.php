<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 * 
 */
	require('../config.php');
	require('../class/ressource.class.php');

	$ATMdb=new Tdb;
	$ATMdb->db->debug=true;

	$o=new TRH_Ressource_type;
	$o->init_db_by_vars($ATMdb);
	
	$p=new TRH_Ressource_field;
	$p->init_db_by_vars($ATMdb);
	
