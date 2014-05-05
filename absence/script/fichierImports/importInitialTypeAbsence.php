<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 * 
 */
 	define('INC_FROM_CRON_SCRIPT', true);
	
	require('../../config.php');
	require('../../class/absence.class.php');
	
	$ATMdb=new TPDOdb;
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

 	$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_type_absence (rowid,typeAbsence, libelleAbsence, codeAbsence, admin, entity, unite)
	VALUES 
	
	(1,'rttcumule','RTT cumulé','0930','0','".$conf->entity."', 'jour')
	,(2,'rttnoncumule','RTT non cumulé','0940','0','".$conf->entity."', 'jour')
	,(3,'conges','Absence congés','0950','0','".$conf->entity."', 'jour')
	,(4,'paternite','Absence paternité','0963','0','".$conf->entity."', 'heure')
	,(5,'nonremuneree','Absence congés sans solde','0980','0','".$conf->entity."', 'heure')
	,(6,'mariage','Mariage','2000','0','".$conf->entity."', 'jour')
	,(7,'deuil','Deuil','2010','0','".$conf->entity."', 'jour')
	,(8,'naissanceadoption','Naissance ou adoption','2020','0','".$conf->entity."', 'jour')
	,(9,'enfantmalade','Enfant malade','2030','0','".$conf->entity."', 'jour')
	,(10,'demenagement','Déménagement','2040','0','".$conf->entity."', 'jour')
	
	
	,(11,'maladiemaintenue','Absence maladie maintenue','0960','1','".$conf->entity."', 'heure')
	,(12,'maladienonmaintenue','Absence maladie non maintenue','0961','1','".$conf->entity."', 'heure')
	,(13,'maternite','Absence maternité','0962','1','".$conf->entity."', 'heure')
	,(14,'chomagepartiel','Absence Chômage partiel','0970','1','".$conf->entity."', 'heure')
	,(15,'accidentdetravail','Absence accident du travail','0990','1','".$conf->entity."', 'heure')
	,(16,'maladieprofessionnelle','Absence maladie professionnelle','1000','1','".$conf->entity."', 'heure')
	,(17,'congeparental','Absence Congés parental','1010','1','".$conf->entity."', 'heure')
	,(18,'accidentdetrajet','Absence Accident trajet','1040','1','".$conf->entity."', 'heure')
	,(19,'mitempstherapeutique','Absence Mi-temps thérapeutique','1070','1','".$conf->entity."', 'heure')
	,(20,'pathologie','Absence pathologie','0964','1','".$conf->entity."', 'heure')
	,(21,'cours','Cours','','1','".$conf->entity."', '')
	,(22,'preavis','Absence préavis','1020','1','".$conf->entity."', 'heure')
	,(23,'rechercheemploi','Absence recherche emploi','1030','1','".$conf->entity."', 'heure')
	,(24,'miseapied','Absence mise à pied','1050','1','".$conf->entity."', 'heure')
	,(25,'nonjustifiee','Absence non justifiée','1060','1','".$conf->entity."', 'heure')	
	
	,(26,'cppartiel','CP à temps partiel','0951','0','".$conf->entity."', 'heure')		
	
	";

	$ATMdb->Execute($sql);
	
	$sql="UPDATE ".MAIN_DB_PREFIX."rh_type_absence SET libelleAbsence='Absence congés sans solde' WHERE typeAbsence='nonremuneree'";
	$ATMdb->Execute($sql);
	
	$sql="ALTER TABLE ".MAIN_DB_PREFIX."rh_type_absence ADD  codeMotif VARCHAR(20)";
	$ATMdb->Execute($sql);
	
	$sql="UPDATE ".MAIN_DB_PREFIX."rh_type_absence SET codeMotif='MA' WHERE codeAbsence='960'";
	$ATMdb->Execute($sql);
	$sql="UPDATE ".MAIN_DB_PREFIX."rh_type_absence SET codeMotif='MA' WHERE codeAbsence='961'";
	$ATMdb->Execute($sql);
	$sql="UPDATE ".MAIN_DB_PREFIX."rh_type_absence SET codeMotif='MT' WHERE codeAbsence='962'";
	$ATMdb->Execute($sql);
	$sql="UPDATE ".MAIN_DB_PREFIX."rh_type_absence SET codeMotif='MP' WHERE codeAbsence='963'";
	$ATMdb->Execute($sql);
	$sql="UPDATE ".MAIN_DB_PREFIX."rh_type_absence SET codeMotif='CHP' WHERE codeAbsence='970'";
	$ATMdb->Execute($sql);
	$sql="UPDATE ".MAIN_DB_PREFIX."rh_type_absence SET codeMotif='CS' WHERE codeAbsence='980'";
	$ATMdb->Execute($sql);
	$sql="UPDATE ".MAIN_DB_PREFIX."rh_type_absence SET codeMotif='AT' WHERE codeAbsence='990'";
	$ATMdb->Execute($sql);
	$sql="UPDATE ".MAIN_DB_PREFIX."rh_type_absence SET codeMotif='ATR' WHERE codeAbsence='1040'";
	$ATMdb->Execute($sql);
	$sql="UPDATE ".MAIN_DB_PREFIX."rh_type_absence SET codeMotif='MAP' WHERE codeAbsence='1000'";
	$ATMdb->Execute($sql);
	$sql="UPDATE ".MAIN_DB_PREFIX."rh_type_absence SET codeMotif='MAP' WHERE codeAbsence='1000'";
	$ATMdb->Execute($sql);
	$sql="UPDATE ".MAIN_DB_PREFIX."rh_type_absence SET codeMotif='MAP' WHERE codeAbsence='1000'";
	$ATMdb->Execute($sql);
	$sql="UPDATE ".MAIN_DB_PREFIX."rh_type_absence SET codeMotif='MTT' WHERE codeAbsence='1070'";
	$ATMdb->Execute($sql);
	
	
	$sql="UPDATE ".MAIN_DB_PREFIX."rh_type_absence SET codeAbsence='0930' WHERE codeAbsence='930'";
	$ATMdb->Execute($sql);
	$sql="UPDATE ".MAIN_DB_PREFIX."rh_type_absence SET codeAbsence='0940' WHERE codeAbsence='940'";
	$ATMdb->Execute($sql);
	$sql="UPDATE ".MAIN_DB_PREFIX."rh_type_absence SET codeAbsence='0950' WHERE codeAbsence='950'";
	$ATMdb->Execute($sql);
	$sql="UPDATE ".MAIN_DB_PREFIX."rh_type_absence SET codeAbsence='0960' WHERE codeAbsence='960'";
	$ATMdb->Execute($sql);
	$sql="UPDATE ".MAIN_DB_PREFIX."rh_type_absence SET codeAbsence='0961' WHERE codeAbsence='961'";
	$ATMdb->Execute($sql);
	$sql="UPDATE ".MAIN_DB_PREFIX."rh_type_absence SET codeAbsence='0962' WHERE codeAbsence='962'";
	$ATMdb->Execute($sql);
	$sql="UPDATE ".MAIN_DB_PREFIX."rh_type_absence SET codeAbsence='0963' WHERE codeAbsence='963'";
	$ATMdb->Execute($sql);
	$sql="UPDATE ".MAIN_DB_PREFIX."rh_type_absence SET codeAbsence='0964' WHERE codeAbsence='964'";
	$ATMdb->Execute($sql);
	$sql="UPDATE ".MAIN_DB_PREFIX."rh_type_absence SET codeAbsence='0970' WHERE codeAbsence='970'";
	$ATMdb->Execute($sql);
	$sql="UPDATE ".MAIN_DB_PREFIX."rh_type_absence SET codeAbsence='0980' WHERE codeAbsence='980'";
	$ATMdb->Execute($sql);
	$sql="UPDATE ".MAIN_DB_PREFIX."rh_type_absence SET codeAbsence='0990' WHERE codeAbsence='990'";
	$ATMdb->Execute($sql);
	
	
	$sql="ALTER TABLE ".MAIN_DB_PREFIX."rh_type_absence ADD  decompteNormal VARCHAR(20)";
	$ATMdb->Execute($sql);
	
	$sql="INSERT INTO `llx_rh_type_absence` 
	(`rowid`, `date_cre`, `date_maj`, `typeAbsence`, `libelleAbsence`, `codeAbsence`, `admin`, `unite`, `entity`, 
	`codeMotif`, `decompteNormal`) VALUES ('26', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 
	'cppartiel', 'CP à temps partiel', '0951', '0', 'jour', '0', NULL, 'non')";
	$ATMdb->Execute($sql);
	
	$sql="UPDATE ".MAIN_DB_PREFIX."rh_type_absence SET decompteNormal='oui' WHERE codeAbsence!='0951'";
	$ATMdb->Execute($sql);
	
	$sql="UPDATE ".MAIN_DB_PREFIX."rh_type_absence SET decompteNormal='non' WHERE codeAbsence='0951'";
	$ATMdb->Execute($sql);
	
	
	
	
	

	