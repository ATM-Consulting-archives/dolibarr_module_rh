#!/usr/bin/php
<?php
/*
 * Script envoyant un mail au validateur chaque jour si besoin pour le notifier des notes de frais à valider
 * 
 */
 	define('INC_FROM_CRON_SCRIPT', true);
	
	chdir(__DIR__);
	
	require('../config.php');
	require('../class/absence.class.php');
	require('../lib/absence.lib.php');
	
	$ATMdb=new TPDOdb;
	$langs->load('mails');
	
	/*$sql = "SELECT DISTINCT u.rowid, u.name,u.firstname,u.email, v.level
	FROM ".MAIN_DB_PREFIX."user u LEFT JOIN  llx_rh_valideur_groupe v ON (v.fk_user=u.rowid)
	WHERE u.email!='' 
	AND v.type='Conges'
	";
	$ATMdb->Execute($sql);
	$TValideur = array();
	while($ATMdb->Get_line()) {
		
		$TValideur[] = array(
			'id'=>$ATMdb->Get_field('rowid')
			,'lastname'=>$ATMdb->Get_field('lastname')
			,'firstname'=>$ATMdb->Get_field('firstname')
			,'email'=>$ATMdb->Get_field('email')
			
		);
		
	}

	
	foreach($TValideur as $valideur) {
		_mail_valideur($ATMdb, $valideur['id'],$valideur['firstname'],$valideur['lastname'], $valideur['email'] );
	}*/
	$sql = "SELECT rowid
	FROM ".MAIN_DB_PREFIX."rh_absence 
	WHERE etat like 'Avalider'
	";
	$ATMdb->Execute($sql);
	$TAbsences = array();
	while($ATMdb->Get_line()) {
		$TAbsences[]=$ATMdb->Get_field('rowid');
	}
	
	foreach($TAbsences as $id){
		$absence->load($ATMdb, $id);
		mailCongesValideur($ATMdb,$absence);
	}
	
	
	return 1;
	
	
