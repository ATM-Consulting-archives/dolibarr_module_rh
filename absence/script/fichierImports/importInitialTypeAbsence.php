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
	global $conf, $langs;
	
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
	
	(1,'rttcumule','" . $langs->trans('CumulatedDayOff') . "','0930','0','".$conf->entity."', 'jour')
	,(2,'rttnoncumule','" . $langs->trans('NonCumulatedDayOff') . "','0940','0','".$conf->entity."', 'jour')
	,(3,'conges','" . $langs->trans('HolidaysAbsence') . "','0950','0','".$conf->entity."', 'jour')
	,(4,'paternite','" . $langs->trans('PaternityAbsence') . "','0963','0','".$conf->entity."', 'heure')
	,(5,'nonremuneree','" . $langs->trans('HolidayAbsenceWithoutBalance') . "','0980','0','".$conf->entity."', 'heure')
	,(6,'mariage','" . $langs->trans('Mariage') . "','2000','0','".$conf->entity."', 'jour')
	,(7,'deuil','" . $langs->trans('Mourning') . "','2010','0','".$conf->entity."', 'jour')
	,(8,'naissanceadoption','" . $langs->trans('BornOrAdoption') . "','2020','0','".$conf->entity."', 'jour')
	,(9,'enfantmalade','" . $langs->trans('SickChild') . "','2030','0','".$conf->entity."', 'jour')
	,(10,'demenagement','" . $langs->trans('Moving') . "','2040','0','".$conf->entity."', 'jour')
	
	
	,(11,'maladiemaintenue','" . $langs->trans('SicknessAbsenceMaintained') . "','0960','1','".$conf->entity."', 'heure')
	,(12,'maladienonmaintenue','" . $langs->trans('SicknessAbsenceNonMaintained') . "','0961','1','".$conf->entity."', 'heure')
	,(13,'maternite','" . $langs->trans('MaternityAbsence') . "','0962','1','".$conf->entity."', 'heure')
	,(14,'chomagepartiel','" . $langs->trans('PartialUnemploymentAbsence') . "','0970','1','".$conf->entity."', 'heure')
	,(15,'accidentdetravail','" . $langs->trans('WorkAccidentAbsence') . "','0990','1','".$conf->entity."', 'heure')
	,(16,'maladieprofessionnelle','" . $langs->trans('ProSicknessAbsence') . "','1000','1','".$conf->entity."', 'heure')
	,(17,'congeparental','" . $langs->trans('HolidayParentalAbsence') . "','1010','1','".$conf->entity."', 'heure')
	,(18,'accidentdetrajet','" . $langs->trans('RoadAccidentAbsence') . "','1040','1','".$conf->entity."', 'heure')
	,(19,'mitempstherapeutique','" . $langs->trans('TherapeuticMidTimeAbsence') . "','1070','1','".$conf->entity."', 'heure')
	,(20,'pathologie','" . $langs->trans('PathologyAbsence') . "','0964','1','".$conf->entity."', 'heure')
	,(21,'cours','" . $langs->trans('SessionAbsence') . "','','1','".$conf->entity."', '')
	,(22,'preavis','" . $langs->trans('PreparedAbsence') . "','1020','1','".$conf->entity."', 'heure')
	,(23,'rechercheemploi','" . $langs->trans('SearchJobAbsence') . "','1030','1','".$conf->entity."', 'heure')
	,(24,'miseapied','" . $langs->trans('WarningAbsence') . "','1050','1','".$conf->entity."', 'heure')
	,(25,'nonjustifiee','" . $langs->trans('NoJustifiedAbsence') . "','1060','1','".$conf->entity."', 'heure')	
	
	,(26,'cppartiel','" . $langs->trans('HolidayPartialTime') . "','0951','0','".$conf->entity."', 'heure')		
	
	";

	$ATMdb->Execute($sql);
	
	$sql="UPDATE ".MAIN_DB_PREFIX."rh_type_absence SET libelleAbsence='" . $langs->trans('HolidayAbsenceWithoutBalance') . "' WHERE typeAbsence='nonremuneree'";
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
	'cppartiel', '" . $langs->trans('HolidayPartialTime') . "', '0951', '0', 'jour', '0', NULL, 'non')";
	$ATMdb->Execute($sql);
	
	$sql="UPDATE ".MAIN_DB_PREFIX."rh_type_absence SET decompteNormal='oui' WHERE codeAbsence!='0951'";
	$ATMdb->Execute($sql);
	
	$sql="UPDATE ".MAIN_DB_PREFIX."rh_type_absence SET decompteNormal='non' WHERE codeAbsence='0951'";
	$ATMdb->Execute($sql);
	
	
	
	
	

	