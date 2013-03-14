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
	
	
	//on récupère la date de fin de cloture des RTT
	$k=0;
	$sqlReqCloture="SELECT fk_user, date_rttCloture FROM `llx_rh_compteur`";
	$ATMdb->Execute($sqlReqCloture);
	$Tab=array();
	while($ATMdb->Get_line()) {
				$Tab[$ATMdb->Get_field('fk_user')] = $ATMdb->Get_field('date_rttCloture');
	}

	foreach($Tab as $idUser => $dateCloture )
	{
	   	//echo $idUser." ".$dateCloture. "<br/>";
		$date=strtotime($dateCloture);
		$dateMD=date("dm",$date);

		//COMPTEUR MENSUEL
		////// 1er mars, tous les rtt de l'année N sont remis à 0 pour ceux qui les accumulent par mois
		$mars=date("dm");
		if($mars==$dateMD){
			//on remet à 0 les compteurs
			$sqlRaz='UPDATE llx_rh_compteur SET rttAcquisMensuel=0, rttPris=0 WHERE rttTypeAcquisition="Mensuel"';
			$ATMdb->Execute($sqlRaz);
		}
		

		//COMPTEUR ANNUEL
		////// 1er mars, tous les rtt de l'année N sont données à ceux qui les accumulent par année
		if($mars==$dateMD){
			//on remet à 5 et à 7 des rtt cumules/noncumules les compteurs
			$sqlTransfert='UPDATE llx_rh_compteur SET rttAcquisAnnuelCumule=5, rttAcquisAnnuelNonCumule=7, rttPris=0  WHERE rttTypeAcquisition="Annuel"';
			$ATMdb->Execute($sqlTransfert);
		}	
			    
	}
		
	
	/////chaque mois, les rtt sont incrémentés de 1 pour ceux qui les accumulent par mois
	$jour=date("d");
	if($jour=="01"){
		//on incrémente de 1
		$sqlIncr='UPDATE llx_rh_compteur SET rttAcquisMensuel=rttAcquisMensuel+1 WHERE rttTypeAcquisition="Mensuel"';
		$ATMdb->Execute($sqlIncr);
	}


	//on incrémente les années
	$annee=date("dm");
	if($annee=="0101"){
		//on transfère les jours N-1 non pris vers jours report
		$sqlAnnee="UPDATE llx_rh_compteur SET rttannee=rttannee+1";
		$ATMdb->Execute($sqlAnnee);
	}
	
