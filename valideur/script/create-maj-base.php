<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 * 
 */
   if(!defined('INC_FROM_DOLIBARR')) {
       define('INC_FROM_CRON_SCRIPT', true);
       require('../config.php');
    }
    
    global $db,$langs; // pour les require from init mod
    
	dol_include_once('/valideur/class/valideur.class.php');
	dol_include_once('/valideur/class/analytique_user.class.php');
	dol_include_once('/core/class/extrafields.class.php');
	
	$ATMdb=new TPDOdb;
	$ATMdb->db->debug=true;

	$o=new TRH_valideur_groupe;
	$o->init_db_by_vars($ATMdb);
	$o=new TRH_analytique_user;
	$o->init_db_by_vars($ATMdb);
	$o=new TRH_valideur_object;
	$o->init_db_by_vars($ATMdb);
	
	$ATMdb->Execute("ALTER TABLE `llx_ndfp` ADD alertLevel INT DEFAULT 1");
	
	//$ATMdb->Execute("ALTER TABLE `llx_user` ADD code_analytique INT DEFAULT 0");
	
	$extrafields = new ExtraFields($db);
	
	$extrafields->addExtraField('DDN', 'Date de naissance', 'date', 0, 10, 'user', 0, 0);
	$extrafields->addExtraField('SIT_FAM', 'Situation de famille', 'varchar', 0, 150, 'user', 0, 0);
	$extrafields->addExtraField('NB_ENF_CHARGE', 'Nombre d\'enfants à charge', 'int', 0, 10, 'user', 0, 0);
	$extrafields->addExtraField('DDA', 'Date d\'ancienneté', 'date', 0, 10, 'user', 0, 0);
	$extrafields->addExtraField('COMPTE_TIERS', 'Compte tiers', 'varchar', 0, 10, 'user', 0, 0);
	$extrafields->addExtraField('ldap_entity_login', 'Entité par défaut (transverse mdoe)', 'int', 0, 10, 'user', 0, 0);
	
	$extrafields->addExtraField('HORAIRE', 'Horaire contractuel', 'varchar', 0, 150, 'user', 0, 0);
	$extrafields->addExtraField('STATUT', 'Statut', 'varchar', 0, 150, 'user', 0, 0);
	$extrafields->addExtraField('NIVEAU', 'Niveau de classification', 'varchar', 0, 150, 'user', 0, 0);
	$extrafields->addExtraField('CONTRAT', 'Contrat', 'varchar', 0, 150, 'user', 0, 0);
	$extrafields->addExtraField('FONCTION', 'Fonction', 'varchar', 0, 150, 'user', 0, 0);
	
	
	
