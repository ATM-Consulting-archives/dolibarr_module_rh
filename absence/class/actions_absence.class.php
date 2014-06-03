<?php
class ActionsAbsence
{
	 
     /** Overloading the doActions function : replacing the parent's function with the one below 
      *  @param      parameters  meta datas of the hook (context, etc...) 
      *  @param      object             the object you want to process (an invoice if you are in invoice module, a propale in propale's module, etc...) 
      *  @param      action             current action (if set). Generally create or edit or null 
      *  @return       void 
      */ 
      
    function formObjectOptions($parameters, &$object, &$action, $idUser) 
    {
    	 
        global $db,$user, $conf;
		$annee=date('Y');
		$anneePrec=$annee-1;
		
		$sql="SELECT * FROM ".MAIN_DB_PREFIX."rh_admin_compteur";
		$result = $db->query($sql);
		if($result)
		{
			$obj = $db->fetch_object($sql);
		    if($obj)
		    {
				$congesAcquisMensuelInit=$obj->congesAcquisMensuelInit;
				
				$date_congesClotureInit =	$obj->date_congesClotureInit;
				$date_rttClotureInit =$obj->date_rttClotureInit;
			}
		}
		
	/*	$compteur = new TRH_Compteur;
		$compteur->initCompteur($ATMdb, $idUser)
		*/
		
		// A la création d'un nouvel utilisateur, on lui affecte un compteur initial et un emploi du temps
		// TODO Object !
		$sqlcompteur="INSERT INTO ".MAIN_DB_PREFIX."rh_compteur (rowid, date_cre, entity, fk_user, acquisExerciceN, 
		acquisAncienneteN, acquisHorsPeriodeN, anneeN, acquisExerciceNM1, acquisAncienneteNM1, acquisHorsPeriodeNM1, reportCongesNM1, congesPrisNM1
		,anneeNM1, rttTypeAcquisition, rttAcquisMensuelInit, rttAcquisAnnuelCumuleInit, rttAcquisAnnuelNonCumuleInit
		, rttannee, nombreCongesAcquisMensuel, date_congesCloture, date_rttCloture
		,rttAcquisMensuelTotal, dureeN, congesResteNM1, dureeNM1, rttMetier, rttCumulePris, 
		rttNonCumulePris, rttCumuleReportNM1, rttNonCumuleReportNM1, rttCumuleTotal, 
		rttNonCumuleTotal, rttCumuleAcquis, rttNonCumuleAcquis) 
		VALUES('".$parameters['idUser']."', '" .$parameters['dateC']."','".$conf->entity."','".$parameters['idUser']."','0', '0',
		 '0', '".$annee."', '25', '0', '0', '0', '0','".$anneePrec."', 'Annuel', '0', '5', '7', '".$annee."', ".$congesAcquisMensuelInit.", 
		 '".$date_congesClotureInit."', '".$date_rttClotureInit."' 
		 ,'0', '0', '0', '0', 'noncadre37cpro', '0', '0', '0', '0', '5', '7', '5', '7')";
		
		$db->query($sqlcompteur);
		
		$sqledt="INSERT INTO  ".MAIN_DB_PREFIX."rh_absence_emploitemps (rowid, date_cre, entity, fk_user,
		lundiam,lundipm,mardiam,mardipm,mercrediam,mercredipm,jeudiam,jeudipm, vendrediam, vendredipm, samediam, samedipm, dimancheam, dimanchepm,
		date_lundi_heuredam, date_lundi_heurefam, date_lundi_heuredpm, date_lundi_heurefpm, 
		date_mardi_heuredam, date_mardi_heurefam, date_mardi_heuredpm, date_mardi_heurefpm, 
		date_mercredi_heuredam, date_mercredi_heurefam, date_mercredi_heuredpm, date_mercredi_heurefpm, 
		date_jeudi_heuredam, date_jeudi_heurefam, date_jeudi_heuredpm, date_jeudi_heurefpm, 
		date_vendredi_heuredam, date_vendredi_heurefam, date_vendredi_heuredpm, date_vendredi_heurefpm, 
		date_samedi_heuredam, date_samedi_heurefam, date_samedi_heuredpm, date_samedi_heurefpm, 
		date_dimanche_heuredam, date_dimanche_heurefam, date_dimanche_heuredpm, date_dimanche_heurefpm, tempsHebdo, societeRtt) 
		
		VALUES('".$parameters['idUser']."', '" .$parameters['dateC']."','".$parameters['conf']."','".$parameters['idUser']."',
		'1','1',	'1','1',	'1','1',	'1','1', 	'1','1',	'0','0',	'0','0',
		'2013-06-01 8:15:00','2013-06-01 12:00:00','2013-06-01 14:00:00','2013-06-01 17:45:00', 
		'2013-06-01 8:15:00','2013-06-01 12:00:00','2013-06-01 14:00:00','2013-06-01 17:45:00', 
		'2013-06-01 8:15:00','2013-06-01 12:00:00','2013-06-01 14:00:00','2013-06-01 17:45:00', 
		'2013-06-01 8:15:00','2013-06-01 12:00:00','2013-06-01 14:00:00','2013-06-01 17:45:00', 
		'2013-06-01 8:15:00','2013-06-01 12:00:00','2013-06-01 14:00:00','2013-06-01 17:15:00', 
		'2013-06-01 0:00:00','2013-06-01 0:00:00','2013-06-01 0:00:00','2013-06-01 0:00:00',
		'2013-06-01 0:00:00','2013-06-01 0:00:00','2013-06-01 0:00:00','2013-06-01 0:00:00', 37, ".$conf->entity.")";
		

		$db->query($sqledt);
		
		return 1;
	}

}