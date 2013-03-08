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
	
	
	//COMPTEUR MENSUEL
	////// 1er mars, tous les rtt de l'année N sont remis à 0 pour ceux qui les accumulent par mois
	$mars=date("dM");
	if($mars=="0103"){
		//on remet à 0 les compteurs
		$sqlRaz='UPDATE llx_rh_compteur SET rttAcquis=0, rttPris=0 WHERE typeAcquisition="Mensuel"';
		$ATMdb->Execute($sqlRaz);
	}
	
	
	/////chaque mois, les rtt sont incrémentés de 1 pour ceux qui les accumulent par mois
	$jour=date("d");
	if($jour=="01"){
		//on incrémente de 1
		$sqlIncr='UPDATE llx_rh_compteur SET rttAcquisMensuel=rttAcquisMensuel+1 WHERE typeAcquisition="Mensuel"';
		$ATMdb->Execute($sqlIncr);
	}
	
	
	
	//COMPTEUR ANNUEL
	////// 1er mars, tous les rtt de l'année N sont données à ceux qui les accumulent par année
	if($mars=="0103"){
		//on remet à 0 les compteurs
		$sqlTransfert='UPDATE llx_rh_compteur SET rttAcquisAnnuelCumule=5, rttAcquisAnnuelNonCumule=7, rttPris=0  WHERE typeAcquisition="Annuel"';
		$ATMdb->Execute($sqlTransfert);
	}
	
	
	
	
