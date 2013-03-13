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

	$o=new TRH_Compteur;
	$o->init_db_by_vars($ATMdb);
	
	////// 1er juin, tous les congés de l'année N sont remis à 0, et sont transférés vers le compteur congés N-1
	$juin=date("dm");
	if($juin=="0106"){
		//on transfère les jours N-1 non pris vers jours report
		$sqlTransfert="UPDATE llx_rh_compteur SET reportCongesNM1=acquisExerciceNM1+acquisAncienneteNM1+acquisHorsPeriodeNM1";
		$ATMdb->Execute($sqlTransfert);
		
		//on transfère les jours acquis N vers N-1
		$sqlTransfert2="UPDATE llx_rh_compteur SET acquisExerciceNM1=acquisExerciceN, acquisAncienneteNM1=acquisAncienneteN,acquisHorsPeriodeNM1=acquisHorsPeriodeN";
		$ATMdb->Execute($sqlTransfert2);
		
		//on remet à 0 les jours année courante
		//L'ancienneté devra normalement être gérée manuellement. 
		$sqlRaz="UPDATE llx_rh_compteur SET acquisExerciceN=0, acquisHorsPeriodeN=0";
		$ATMdb->Execute($sqlRaz);
		
	}
	
	
	
	//on incrémente les années
	$annee=date("dm");
	if($annee=="0101"){
		//on transfère les jours N-1 non pris vers jours report
		$sqlAnnee="UPDATE llx_rh_compteur SET anneeN=anneN+1, anneeNM1=anneNM1+1";
		$ATMdb->Execute($sqlAnnee);	
	}
