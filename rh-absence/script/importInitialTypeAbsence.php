<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 * 
 */
 	define('INC_FROM_CRON_SCRIPT', true);
	
	require('../config.php');
	require('../class/absence.class.php');
	
	$ATMdb=new Tdb;
	$ATMdb->db->debug=true;

	$u=new TRH_TypeAbsence;
	$u->init_db_by_vars($ATMdb);
	global $conf;
	
	/*$this->TTypeAbsenceAdmin = array('rttcumule'=>'RTT cumulé','rttnoncumule'=>'RTT non cumulé', 
		'conges' => 'Absence congés', 'maladiemaintenue' => 'Absence maladie maintenue', 
		'maladienonmaintenue'=>'Absence maladie non maintenue','maternite'=>'Absence maternité', 'paternite'=>'Absence paternité', 
		'chomagepartiel'=>'Absence Chômage partiel','nonremuneree'=>'Absence non rémunérée','accidentdetravail'=>'Absence accident du travail',
		'maladieprofessionnelle'=>'Absence maladie professionnelle', 
		'congeparental'=>'Absence Congés parental', 'accidentdetrajet'=>'Absence Accident trajet',
		'mitempstherapeutique'=>'Absence Mi-temps thérapeutique', 'pathologie'=>'Absence pathologie','mariage'=>'Mariage',
		'deuil'=>'Deuil','naissanceadoption'=>'Naissance ou adoption', 'enfantmalade'=>'Enfant malade', 'demenagement'=>'Déménagement',
		'cours'=>'Cours', 'preavis'=>'Absence préavis','rechercheemploi'=>'Absence recherche emploi', 
		'miseapied'=>'Absence mise à pied', 'nonjustifiee'=>'Absence non justifiée'  
		);
		
		$this->TTypeAbsenceUser = array('rttcumule'=>'RTT cumulé','rttnoncumule'=>'RTT non cumulé', 
		'conges' => 'Absence congés', 'paternite'=>'Absence paternité', 
		'nonremuneree'=>'Absence non rémunérée', 'mariage'=>'Mariage',
		'deuil'=>'Deuil','naissanceadoption'=>'Naissance ou adoption', 'enfantmalade'=>'Enfant malade', 'demenagement'=>'Déménagement',
		 );*/

 	$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_type_absence (rowid,typeAbsence, libelleAbsence, codeAbsence, admin, entity)
	VALUES 
	
	(1,'rttcumule','RTT cumulé','','0','".$conf->entity."')
	,(2,'rttnoncumule','RTT non cumulé','','0','".$conf->entity."')
	,(3,'conges','Absence paternité','','0','".$conf->entity."')
	,(4,'paternite','Cours','','0','".$conf->entity."')
	,(5,'nonremuneree','Absence non rémunérée','','0','".$conf->entity."')
	,(6,'mariage','Mariage','','0','".$conf->entity."')
	,(7,'deuil','Deuil','','0','".$conf->entity."')
	,(8,'naissanceadoption','Naissance ou adoption','','0','".$conf->entity."')
	,(9,'enfantmalade','Enfant malade','','0','".$conf->entity."')
	,(10,'demenagement','Déménagement','','0','".$conf->entity."')
	
	
	,(11,'maladiemaintenue','Absence maladie maintenue','','1','".$conf->entity."')
	,(12,'maladienonmaintenue','Absence maladie non maintenue','','1','".$conf->entity."')
	,(13,'maternite','Absence maternité','','1','".$conf->entity."')
	,(14,'chomagepartiel','Absence Chômage partiel','','1','".$conf->entity."')
	,(15,'accidentdetravail','Absence accident du travail','','1','".$conf->entity."')
	,(16,'maladieprofessionnelle','Absence maladie professionnelle','','1','".$conf->entity."')
	,(17,'congeparental','Absence Congés parental','','1','".$conf->entity."')
	,(18,'accidentdetrajet','Absence Accident trajet','','1','".$conf->entity."')
	,(19,'mitempstherapeutique','Absence Mi-temps thérapeutique','','1','".$conf->entity."')
	,(20,'pathologie','Absence pathologie','','1','".$conf->entity."')
	,(21,'cours','Cours','','1','".$conf->entity."')
	,(22,'preavis','Absence préavis','','1','".$conf->entity."')
	,(23,'rechercheemploi','Absence recherche emploi','','1','".$conf->entity."')
	,(24,'miseapied','Absence mise à pied','','1','".$conf->entity."')
	,(55,'nonjustifiee','Absence non justifiée','','1','".$conf->entity."')		
	
	";

	$ATMdb->Execute($sql);

	