<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 * 
 */
 	define('INC_FROM_CRON_SCRIPT', true);
	
	require('../config.php');
	require('../class/valideur.class.php');
	require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
	
	global $db;

	$ATMdb=new Tdb;
	$ATMdb->db->debug=true;

	$o=new TRH_valideur_groupe;
	$o->init_db_by_vars($ATMdb);
	
	
	$ATMdb->Execute("ALTER TABLE `llx_user` ADD code_analytique INT DEFAULT 0");
	
	$extrafields = new ExtraFields($db);

	$extrafields->addExtraField ('CODE_ANA', 'Code analytique', 'int', 0, 10, 'user', 0, 0);