<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 * 
 */
    if(!defined('INC_FROM_DOLIBARR')) {
        
        define('INC_FROM_CRON_SCRIPT', true);
        require('../config.php');
        
    }
    
    global $db,$langs;
    
	
	dol_include_once('/absence/class/absence.class.php');
	dol_include_once('/absence/class/pointeuse.class.php');
	dol_include_once('/absence/class/ticket.class.php');
	 
	$langs->load('absence@absence');
	
    if(!class_exists('modAbsence')) dol_include_once('/absence/core/modules/modAbsence.class.php');
    Tools::setVersion($db, 'modAbsence');
    
	$ATMdb=new TPDOdb;
	$ATMdb->db->debug=true;

	$o=new TRH_declarationTemps;
	$o->init_db_by_vars($ATMdb);

	$o=new TRH_Pointeuse;
	$o->init_db_by_vars($ATMdb);
	
	$o=new TRH_TicketResto;
	$o->init_db_by_vars($ATMdb);


	$o=new TRH_Compteur;
	$o->init_db_by_vars($ATMdb);
	
	$o=new TRH_CompteurLog;
	$o->init_db_by_vars($ATMdb);
	
	$sqlReqUser="SELECT DISTINCT rowid FROM ".MAIN_DB_PREFIX."user WHERE rowid NOT IN ( SELECT fk_user from ".MAIN_DB_PREFIX."rh_compteur)";
	$ATMdb->Execute($sqlReqUser);
	$Tab=array();
	while($ATMdb->Get_line()) {
				$Tab[]=$ATMdb->Get_field('rowid');		
	}
	
	if(!empty($Tab)){
		
		foreach ($Tab as $idUserC) {
			$o=new TRH_Compteur;
			$o->initCompteur($ATMdb, $idUserC);
			$o->save($ATMdb);
		}
	}
	
	
	
	
	
	$p=new TRH_Absence;
	$p->init_db_by_vars($ATMdb);
	

	$sqlReq="DELETE FROM ".MAIN_DB_PREFIX."rh_admin_compteur WHERE 1";
	$ATMdb->Execute($sqlReq);
	
	$q=new TRH_AdminCompteur;
	$q->init_db_by_vars($ATMdb);
	$q->congesAcquisMensuelInit=2.08;
	$q->rttCumuleInit=8;
	$q->date_rttClotureInit=strtotime(DATE_RTT_CLOTURE);
	$q->date_congesClotureInit=strtotime(DATE_CONGES_CLOTURE);
	$q->save($ATMdb);
	
	

	$r=new TRH_EmploiTemps;
	$r->init_db_by_vars($ATMdb);
	$sqlReq="SELECT DISTINCT rowid FROM ".MAIN_DB_PREFIX."user WHERE rowid NOT IN ( SELECT fk_user from ".MAIN_DB_PREFIX."rh_absence_emploitemps)";
	$ATMdb->Execute($sqlReq);
	$Tab=array();
	while($ATMdb->Get_line()) {
				$Tab[]=$ATMdb->Get_field('rowid');		
	}
	
	if(!empty($Tab)){
		
		foreach ($Tab as $idUserC) {
			$r=new TRH_EmploiTemps;
			$r->initCompteurHoraire($ATMdb, $idUserC);
			$r->save($ATMdb);
		}
	}

	
	
	$s=new TRH_JoursFeries;
	$s->init_db_by_vars($ATMdb);
	
	
	
	$t=new TRH_RegleAbsence;
	$t->init_db_by_vars($ATMdb);
	
	$u=new TRH_TypeAbsence;
	$u->init_db_by_vars($ATMdb);
	

	$ATMdb->Execute("SELECT count(*) as nb FROM ".MAIN_DB_PREFIX."rh_type_absence");
	$ATMdb->Get_line();
	$nb = $ATMdb->Get_field('nb');
	if($nb==0) {

		$ATMdb->Execute("
		
INSERT INTO `llx_rh_type_absence` (`rowid`, `date_cre`, `date_maj`, `typeAbsence`, `libelleAbsence`, `codeAbsence`, `admin`, `unite`, `entity`, `decompteNormal`, `isPresence`, `date_hourStart`, `date_hourEnd`, `colorId`) VALUES
(1, '0000-00-00 00:00:00', '2014-01-03 11:40:01', 'rttcumule', '" . $langs->trans('CumulatedDayOff') . "', '0930', '0', 'jour', '1', 'oui', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(3, '0000-00-00 00:00:00', '2014-01-03 11:40:01', 'conges', '" . $langs->trans('HolidaysAbsence') . "', '0950', '0', 'jour', '1', 'oui', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(4, '0000-00-00 00:00:00', '2014-01-03 11:40:01', 'paternite', '" . $langs->trans('PaternityAbsence') . "', '0963', '0', 'heure', '1', 'oui', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(5, '0000-00-00 00:00:00', '2014-01-03 11:40:01', 'nonremuneree', '" . $langs->trans('HolidayAbsenceWithoutBalance') . "', '0980', '0', 'heure', '1', 'oui', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(6, '0000-00-00 00:00:00', '2014-01-03 11:40:01', 'mariage', '" . $langs->trans('Mariage') . "', '2000', '0', 'jour', '1', 'oui', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(7, '0000-00-00 00:00:00', '2014-01-03 11:40:01', 'deuil', '" . $langs->trans('Mourning') . "', '2010', '0', 'jour', '1', 'oui', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(8, '0000-00-00 00:00:00', '2014-01-03 11:40:01', 'naissanceadoption', '" . $langs->trans('BornOrAdoption') . "', '2020', '0', 'jour', '1', 'oui', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(9, '0000-00-00 00:00:00', '2014-01-03 11:40:01', 'enfantmalade', '" . $langs->trans('SickChild') . "', '2030', '0', 'jour', '1', 'oui', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(10, '0000-00-00 00:00:00', '2014-01-03 11:40:01', 'demenagement', '" . $langs->trans('Moving') . "', '2040', '0', 'jour', '1', 'oui', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(11, '0000-00-00 00:00:00', '2014-01-03 11:40:01', 'maladiemaintenue', '" . $langs->trans('SicknessAbsenceMaintained') . "', '0960', '1', 'heure', '1', 'oui', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(12, '0000-00-00 00:00:00', '2014-01-03 11:40:01', 'maladienonmaintenue', '" . $langs->trans('SicknessAbsenceNonMaintained') . "', '0961', '1', 'heure', '1', 'oui', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(13, '0000-00-00 00:00:00', '2014-01-03 11:40:01', 'maternite', '" . $langs->trans('MaternityAbsence') . "', '0962', '1', 'heure', '1', 'oui', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(14, '0000-00-00 00:00:00', '2014-01-03 11:40:01', 'chomagepartiel', '" . $langs->trans('PartialUnemploymentAbsence') . "', '0970', '1', 'heure', '1', 'oui', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(15, '0000-00-00 00:00:00', '2014-01-03 11:40:01', 'accidentdetravail', '" . $langs->trans('WorkAccidentAbsence') . "', '0990', '1', 'heure', '1', 'oui', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(16, '0000-00-00 00:00:00', '2014-01-03 11:40:01', 'maladieprofessionnelle', '" . $langs->trans('ProSicknessAbsence') . "', '1000', '1', 'heure', '1', 'oui', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(17, '0000-00-00 00:00:00', '2014-01-03 11:40:01', 'congeparental', '" . $langs->trans('HolidayParentalAbsence') . "', '1010', '1', 'heure', '1', 'oui', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(18, '0000-00-00 00:00:00', '2014-01-03 11:40:01', 'accidentdetrajet', '" . $langs->trans('RoadAccidentAbsence') . "', '1040', '1', 'heure', '1', 'oui', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(19, '0000-00-00 00:00:00', '2014-01-03 11:40:01', 'mitempstherapeutique', '" . $langs->trans('TherapeuticMidTimeAbsence') . "', '1070', '1', 'heure', '1', 'oui', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(20, '0000-00-00 00:00:00', '2014-01-03 11:40:01', 'pathologie', '" . $langs->trans('PathologyAbsence') . "', '0964', '1', 'heure', '1', 'oui', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(21, '0000-00-00 00:00:00', '2014-01-03 11:40:01', 'cours', '" . $langs->trans('SessionAbsence') . "', NULL, '1', 'jour', '1', 'oui', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(22, '0000-00-00 00:00:00', '2014-01-03 11:40:01', 'preavis', '" . $langs->trans('PreparedAbsence') . "', '1020', '1', 'heure', '1', 'oui', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(23, '0000-00-00 00:00:00', '2014-01-03 11:40:01', 'rechercheemploi', '" . $langs->trans('SearchJobAbsence') . "', '1030', '1', 'heure', '1', 'oui', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(24, '0000-00-00 00:00:00', '2014-01-03 11:40:01', 'miseapied', '" . $langs->trans('WarningAbsence') . "', '1050', '1', 'heure', '1', 'oui', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(25, '0000-00-00 00:00:00', '2014-01-03 11:40:01', 'nonjustifiee', '" . $langs->trans('NoJustifiedAbsence') . "', '1060', '1', 'heure', '1', 'oui', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(26, '0000-00-00 00:00:00', '2014-01-03 11:40:01', 'cppartiel', '" . $langs->trans('HolidayPartialTime') . "', '0951', '0', 'jour', '1', 'non', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(27, '2014-01-03 11:32:49', '2014-01-03 11:40:01', 'rttnoncumule', '" . $langs->trans('NonCumulatedDayOff') . "', '0950', '0', 'jour', '1', 'oui', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(28, '2014-01-03 11:33:36', '2014-02-11 11:47:13', '3.8 matin', '" . $langs->trans('3/8Morning') . "', '3000', '0', 'jour', '1', 'oui', '1', '2014-02-11 01:33:00', '2014-02-11 01:33:00', '5'),
(29, '2014-01-03 11:34:10', '2014-02-11 11:47:13', '3.8 journee', '" . $langs->trans('3/8Daytime') . "', '3010', '0', 'jour', '1', 'oui', '1', '2014-02-11 01:33:00', '2014-02-11 01:33:00', '15'),
(30, '2014-01-03 11:34:37', '2014-02-11 11:47:13', '3.8 soiree', '" . $langs->trans('3/8Evening') . "', '3020', '0', 'jour', '1', 'oui', '1', '2014-02-11 01:33:00', '2014-02-11 01:33:00', '14'),
(31, '2014-01-03 11:34:37', '2014-02-11 11:47:13', 'recup', '" . $langs->trans('Recuperation') . "', '4000', '0', 'jour', '1', 'oui', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL);

		");
	}

