#!/usr/bin/php
<?php
/*
 * SCRIPT 3 à exécuter
 * 
 */
	$sapi_type = php_sapi_name();
        $script_file = basename(__FILE__);
        $path=dirname(__FILE__).'/';
        // Test if batch mode
        if (substr($sapi_type, 0, 3) != 'cli') {
            echo "Error: ".$script_file." you must use PHP for CLI mode.\n";
                exit(-1);
        }

 	define('INC_FROM_CRON_SCRIPT', true);
	
	chdir(__DIR__);
	
	require('../../config.php');
	require('../../class/absence.class.php');

	$ATMdb=new TPDOdb;
	$ATMdb->db->debug=true;

	$o=new TRH_Compteur;
	$o->init_db_by_vars($ATMdb);
	
	
	//on récupère la date de fin de cloture des RTT
	$k=0;
	$sqlReqCloture="SELECT fk_user, date_rttCloture, rttAcquisAnnuelCumuleInit, rttAcquisAnnuelNonCumuleInit FROM `".MAIN_DB_PREFIX."rh_compteur`";
	$ATMdb->Execute($sqlReqCloture);
	$Tab=array();
	while($ATMdb->Get_line()) {
			$Tab[$ATMdb->Get_field('fk_user')]['date_rttCloture'] = $ATMdb->Get_field('date_rttCloture');
			$Tab[$ATMdb->Get_field('fk_user')]['rttAcquisAnnuelCumuleInit'] = $ATMdb->Get_field('rttAcquisAnnuelCumuleInit');
			$Tab[$ATMdb->Get_field('fk_user')]['rttAcquisAnnuelNonCumuleInit'] = $ATMdb->Get_field('rttAcquisAnnuelNonCumuleInit');
			$Tab[$ATMdb->Get_field('fk_user')]['fk_user'] = $ATMdb->Get_field('fk_user');
	}

	foreach($Tab as $TabRtt )
	{
	   	//echo $idUser." ".$dateCloture. "<br/>";
		$date=strtotime($TabRtt['date_rttCloture']);
		$date=strtotime('+1day',$date);
		
		
		$dateMD=date("dm",$date);

		//on reporte les RTT pour ceux pour qui c'est autorisé
		if($mars==$dateMD){
			//on remet à 5 et à 7 des rtt cumules/noncumules les compteurs par exemple, dépend de ce qui est entré sur le compteur
			$sqlTransfert='UPDATE '.MAIN_DB_PREFIX.'rh_compteur 
			SET rttNonCumuleReportNM1=rttNonCumuleTotal,  rttCumuleReportNM1=rttCumuleTotal
			WHERE fk_user ='.$TabRtt['fk_user']." AND reportRtt='1'";
			$ATMdb->Execute($sqlTransfert);
		}	
		
		
		//COMPTEUR MENSUEL
		////// 1er mars, tous les rtt de l'année N sont remis à 0 pour ceux qui les accumulent par mois, sauf si reportRtt=1
		$mars=date("dm");
		if($mars==$dateMD){
			//on remet à 0 les compteurs
			$sqlRaz="UPDATE ".MAIN_DB_PREFIX."rh_compteur 
			SET  rttCumulePris=0, rttNonCumulePris=0, rttCumuleAcquis=0, rttNonCumuleAcquis=0
			WHERE rttTypeAcquisition='Mensuel' 
			AND reportRtt!='1' 
			AND fk_user =".$TabRtt['fk_user'];
			$ATMdb->Execute($sqlRaz);
		}
		

		//COMPTEUR ANNUEL
		////// 1er mars, tous les rtt de l'année N sont donnés à ceux qui les accumulent par année
		if($mars==$dateMD){
			//on remet à 5 et à 7 des rtt cumules/noncumules les compteurs par exemple, dépend de ce qui est entré sur le compteur
			$sqlTransfert='UPDATE '.MAIN_DB_PREFIX.'rh_compteur 
			SET rttCumulePris=0, rttNonCumulePris=0, 
			rttCumuleAcquis=rttAcquisAnnuelCumuleInit ,rttNonCumuleAcquis=rttAcquisAnnuelNonCumuleInit
			WHERE rttTypeAcquisition="Annuel" 
			AND fk_user ='.$TabRtt['fk_user'];
			$ATMdb->Execute($sqlTransfert);
		}	
		
		
		//on recalcule les totaux des RTT
		if($mars==$dateMD){
			//on remet à 5 et à 7 des rtt cumules/noncumules les compteurs par exemple, dépend de ce qui est entré sur le compteur
			$sqlTransfert='UPDATE '.MAIN_DB_PREFIX.'rh_compteur 
			SET rttCumuleTotal=rttCumuleAcquis+rttCumuleReportNM1-rttCumulePris,  
			rttNonCumuleTotal=rttNonCumuleAcquis+rttNonCumuleReportNM1-rttNonCumulePris
			WHERE fk_user ='.$TabRtt['fk_user'].' AND rttTypeAcquisition="Annuel"';
			$ATMdb->Execute($sqlTransfert);
		}
			    
	}
		
	
	/////chaque mois, les rtt sont incrémentés de 1 pour ceux qui les accumulent par mois
	$jour=date("d");
	if($jour=="01"){
		$sqlMois="SELECT fk_user, rttAcquisMensuelInit 
		FROM `".MAIN_DB_PREFIX."rh_compteur` 
		WHERE rttTypeAcquisition='Mensuel'";
		$ATMdb->Execute($sqlMois);
		$Tab=array();
		while($ATMdb->Get_line()) {
				$Tab[$ATMdb->Get_field('fk_user')]['rttAcquisMensuelInit'] = $ATMdb->Get_field('rttAcquisMensuelInit');
				$Tab[$ATMdb->Get_field('fk_user')]['fk_user'] = $ATMdb->Get_field('fk_user');
		}

		foreach($Tab as $TabMois){
			//on incrémente de 1 par exemple, suivant ce qui est donné dans la base
			$sqlIncr='UPDATE '.MAIN_DB_PREFIX.'rh_compteur 
			SET rttCumuleAcquis=rttCumuleAcquis+'.$TabMois['rttAcquisMensuelInit'].' 
			WHERE rttTypeAcquisition="Mensuel" 
			AND fk_user='.$TabMois['fk_user'];
			$ATMdb->Execute($sqlIncr);
		}
		
	}


	//on incrémente les années
	$annee=date("dm");
	if($annee=="0101"){
		//on transfère les jours N-1 non pris vers jours report
		$sqlAnnee="UPDATE ".MAIN_DB_PREFIX."rh_compteur SET rttannee=rttannee+1";
		$ATMdb->Execute($sqlAnnee);
	}
	
	$ATMdb->close();
