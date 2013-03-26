<?php
class ActionsAbsence
{
	 
     /** Overloading the doActions function : replacing the parent's function with the one below 
      *  @param      parameters  meta datas of the hook (context, etc...) 
      *  @param      object             the object you want to process (an invoice if you are in invoice module, a propale in propale's module, etc...) 
      *  @param      action             current action (if set). Generally create or edit or null 
      *  @return       void 
      */ 
      
    function formObjectOptions($parameters, &$object, &$action, $idUser, $conf, $dateC, $hookmanager) 
    { 
        global $db,$user;
		$annee=date('Y');
		$anneePrec=$annee-1;
		$sqlcompteur="INSERT INTO llx_rh_compteur (rowid, date_cre, entity, fk_user, acquisExerciceN, 
		acquisAncienneteN, acquisHorsPeriodeN, anneeN, acquisExerciceNM1, acquisAncienneteNM1, acquisHorsPeriodeNM1, reportCongesNM1, congesPrisNM1
		,anneeNM1, rttPris, rttTypeAcquisition, rttAcquisMensuelInit, rttAcquisAnnuelCumuleInit, rttAcquisAnnuelNonCumuleInit
		,rttAcquisMensuel, rttAcquisAnnuelCumule, rttAcquisAnnuelNonCumule, rttannee, nombreCongesAcquisMensuel, date_congesCloture, date_rttCloture) 
		VALUES('".$parameters['idUser']."', '" .$parameters['dateC']."','".$parameters['conf']."','".$parameters['idUser']."','6', '1',
		 '0', '".$annee."', '25', '1', '0', '0', '4','".$anneePrec."', '0', 'Annuel', '0', '5', '7', '0', '5', '7', '".$annee."', '2.08', '2013-06-01 00:00:00', '2013-03-01 00:00:00' )";
		$db->query($sqlcompteur);
		
		$sqledt="INSERT INTO  llx_rh_absence_emploitemps (rowid, date_cre, entity, fk_user,
		lundiam,lundipm,mardiam,mardipm,mercrediam,mercredipm,jeudiam,jeudipm, vendrediam, vendredipm, samediam, samedipm, dimancheam, dimanchepm,
		date_lundi_heuredam, date_lundi_heurefam, date_lundi_heuredpm, date_lundi_heurefpm, 
		date_mardi_heuredam, date_mardi_heurefam, date_mardi_heuredpm, date_mardi_heurefpm, 
		date_mercredi_heuredam, date_mercredi_heurefam, date_mercredi_heuredpm, date_mercredi_heurefpm, 
		date_jeudi_heuredam, date_jeudi_heurefam, date_jeudi_heuredpm, date_jeudi_heurefpm, 
		date_vendredi_heuredam, date_vendredi_heurefam, date_vendredi_heuredpm, date_vendredi_heurefpm, 
		date_samedi_heuredam, date_samedi_heurefam, date_samedi_heuredpm, date_samedi_heurefpm, 
		date_dimanche_heuredam, date_dimanche_heurefam, date_dimanche_heuredpm, date_dimanche_heurefpm) 
		
		VALUES('".$parameters['idUser']."', '" .$parameters['dateC']."','".$parameters['conf']."','".$parameters['idUser']."',
		'1','1',	'1','1',	'1','1',	'1','1', 	'1','1',	'0','0',	'0','0',
		'2013-06-01 9:00:00','2013-06-01 12:15:00','2013-06-01 14:00:00','2013-06-01 18:00:00', 
		'2013-06-01 9:00:00','2013-06-01 12:15:00','2013-06-01 14:00:00','2013-06-01 18:00:00',
		'2013-06-01 9:00:00','2013-06-01 12:15:00','2013-06-01 14:00:00','2013-06-01 18:00:00',
		'2013-06-01 9:00:00','2013-06-01 12:15:00','2013-06-01 14:00:00','2013-06-01 18:00:00',
		'2013-06-01 9:00:00','2013-06-01 12:15:00','2013-06-01 14:00:00','2013-06-01 18:00:00',
		'2013-06-01 0:00:00','2013-06-01 0:00:00','2013-06-01 0:00:00','2013-06-01 0:00:00',
		'2013-06-01 0:00:00','2013-06-01 0:00:00','2013-06-01 0:00:00','2013-06-01 0:00:00')";
		
		echo $sqledt;
		
		$db->query($sqledt);
		
		return 1;
	}

}