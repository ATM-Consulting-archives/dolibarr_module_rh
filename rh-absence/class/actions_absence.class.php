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
		,rttAcquisMensuel, rttAcquisAnnuelCumule, rttAcquisAnnuelNonCumule, rttannee, nombreCongesAcquisMensuel) 
		VALUES('".$parameters['idUser']."', '" .$parameters['dateC']."','".$parameters['conf']."','".$parameters['idUser']."','6', '1',
		 '0', '".$annee."', '25', '1', '0', '0', '4','".$anneePrec."', '0', 'Annuel', '0', '5', '7', '0', '5', '7', '".$annee."', '2.08' )";
		$db->query($sqlcompteur);
		return 1;
	}

}