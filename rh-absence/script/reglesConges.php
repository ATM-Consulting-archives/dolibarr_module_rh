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
	$juin=date("dM");
	if($juin=="0106"){
		//on transfère les jours N-1 non pris vers jours report
		$sqlTransfert="UPDATE llx_rh_compteur SET reportCongesNm1=acquisExerciceNM1";
		$ATMdb->Execute($sqlTransfert);
		
		//on transfère les jours acquis N vers N-1
		$sqlTransfert2="UPDATE llx_rh_compteur SET acquisExerciceNm1=acquisExerciceN";
		$ATMdb->Execute($sqlTransfert2);
		
		//on remet à 0 les jours année courante
		$sqlRaz="UPDATE llx_rh_compteur SET acquisExerciceN=0";
		$ATMdb->Execute($sqlRaz);
		
	}
	
	/////chaque mois, les congés année N sont incrémentés de 2,08
	$jour=date("d");
	if($jour=="01"){
		//on remet à 0 les jours année courante
		$sqlIncr="UPDATE llx_rh_compteur SET acquisExerciceN=acquisExerciceN+2.08";
		$ATMdb->Execute($sqlIncr);
	}
	
