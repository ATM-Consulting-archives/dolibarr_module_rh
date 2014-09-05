<?php

function pointeusePrepareHead() {
	global $langs;
	
	return array(
		array(dol_buildpath('/absence/pointeuse.php',1), $langs->trans('PunchClock'),'fiche')
	);
}


function absencePrepareHead(&$obj, $type='absence') {
	global $user, $langs;
	
	switch ($type) {
		case 'absence':
			return array(
				array(dol_buildpath('/absence/absence.php?id='.$obj->getId(),1)."&action=view", $langs->trans('Card'),'fiche')
				,array(dol_buildpath('/absence/calendrierAbsence.php?idUser='.$user->id.'&id='.$obj->getId(),1), $langs->trans('Calendar'),'calendrier')
			);
			break;
		case 'presence':
			return array(
				array(dol_buildpath('/absence/presence.php?id='.$obj->getId()."&action=view",1), $langs->trans('Card'),'fiche')
				,array(dol_buildpath('/absence/calendrierAbsence.php?idUser='.$user->id.'&id='.$obj->getId(),1), $langs->trans('Calendar'),'calendrier')
			);
			break;
		case 'absenceCreation':
			return array(
				array(dol_buildpath('/absence/absence.php?action=new',1), $langs->trans('Card'),'fiche')
			);
			break;
		
		
	}
}


function compteurPrepareHead(&$obj, $type='absence', $nomUser, $prenomUser) {
	global $user, $langs;
	
	switch ($type) {
		
		case 'compteur':
			//eif($user->rights->absence->myactions->modifierParamGlobalConges=="1"){
			return array(
			array(dol_buildpath('/absence/compteur.php?action=view',1), $langs->trans('CounterOf') . ' ' . $nomUser . ' ' . $prenomUser, 'compteur')
			//,array(dol_buildpath('/absence/adminCompteur.php?action=view', 'Administration générale congés','adminconges')
			);
			break;
	}
}

function adminCompteurPrepareHead(&$obj, $type='compteur') {
	global $user, $langs;
	switch ($type) {
		
		case 'compteur':
			return array(
			array(dol_buildpath('/absence/adminCompteur.php',1), $langs->trans('HolidayCounter'), 'compteur')
			);
			break;				
	}
}

function adminCongesPrepareHead($type='compteur') {
	global $user;
	switch ($type) {
		
		case 'compteur':
			return array(
				array(dol_buildpath('/absence/adminConges.php',1), $langs->trans('GlobalHolidaysData'),'adminconges')
				,array(dol_buildpath('/absence/typeAbsence.php',1), $langs->trans('AbsencesTypes'),'typeabsence')
				,array(dol_buildpath('/absence/typePresence.php',1), $langs->trans('PresencesTypes'),'typepresence')
			);
			break;
	}
}

function adminRecherchePrepareHead(&$obj, $type='recherche') {
	global $user;
	switch ($type) {
		
		case 'recherche':
			return array(
				array(dol_buildpath('/absence/rechercheAbsence.php',1), $langs->trans('SearchAbsence'),'recherche')
			);
			break;
		case 'planning':
			return array(
				array(dol_buildpath('/absence/rechercheAbsence.php',1), $langs->trans('SearchAbsence'),'recherche')
			);
			break;
	}
}

function edtPrepareHead(&$obj, $type='absence') {
	global $user;

	switch ($type) {
		
		case 'emploitemps':
				
			return array(
				array(dol_buildpath('/absence/emploitemps.php?&fk_user='.$user->id,1), $langs->trans('Schedule'),'emploitemps')
			   ,array(dol_buildpath('/absence/joursferies.php?&fk_user='.$user->id,1), $langs->trans('HolidaysOrNoWorkingDays'),'joursferies')
			   //,array(dol_buildpath('/absence/pointage.php?&fk_user='.$user->id, 'Pointage Collaborateurs','pointage')
			);
			break;
				
	}
}

function reglePrepareHead(&$obj, $type='regle') {
	global $user;

	switch ($type) {
		case 'regle':
			return array(
				array(dol_buildpath('/absence/regleAbsence.php?fk_user='.$user->id,1), $langs->trans('AbsencesRules'),'regle')
			);
			break;
		case 'import':
			return array(
				array(dol_buildpath('/ressource/documentRegle.php',1), $langs->trans('Card'),'fiche')
			);
			break;
	}
}

//fonction qui permet d'enregistrer le libellé d'une absence suivant son type
function saveLibelle($type){ //TODO deprecated
	global $langs;
	
	switch($type){
		case 'rttcumule':
			return $langs->trans('CumulatedDayOff');
		break;
		case 'rttnoncumule':
			return $langs->trans('NonCumulatedDayOff');
		break;
		case 'conges':
			return $langs->trans('HolidaysAbsence');
		break;
		case 'maladiemaintenue':
			return $langs->trans('SicknessAbsenceMaintained');
		break;
		case 'maladienonmaintenue':
			return $langs->trans('SicknessAbsenceNonMaintained');
		break;
		case 'maternite':
			return $langs->trans('MaternityAbsence');
		break;
		case 'pathologie':
			return $langs->trans('PathologyAbsence');
		break;
		case 'paternite':
			return $langs->trans('PaternityAbsence');
		break;
		case 'chomagepartiel':
			return $langs->trans('PartialUnemploymentAbsence');
		break;
		case 'nonremuneree':
			return $langs->trans('HolidayAbsenceWithoutBalance');
		break;
		case 'accidentdetravail':
			return $langs->trans('WorkAccidentAbsence');
		break;
		case 'maladieprofessionnelle':
			return $langs->trans('ProSicknessAbsence');
		break;
		case 'congeparental':
			return $langs->trans('HolidayParentalAbsence');
		break;
		case 'accidentdetrajet':
			return $langs->trans('RoadAccidentAbsence');
		break;
		case 'mitempstherapeutique':
			return $langs->trans('TherapeuticMidTimeAbsence');
		break;
		case 'mariage':
			return $langs->trans('Mariage');
		break;
		case 'deuil':
			return $langs->trans('Mourning');
		break;
		case 'naissanceadoption':
			return $langs->trans('BornOrAdoption');
		break;
		case 'enfantmalade':
			return $langs->trans('SickChild');
		break;
		case 'demenagement':
			return $langs->trans('Moving');
		break;
		case 'cours':
			return $langs->trans('SessionAbsence');
		break;
		case 'preavis':
			return $langs->trans('PreparedAbsence');
		break;
		case 'rechercheemploi':
			return $langs->trans('SearchJobAbsence');
		break;
		case 'miseapied':
			return $langs->trans('WarningAbsence');
		break;
		case 'nonjustifiee':
			return $langs->trans('NoJustifiedAbsence');
		break;
		case 'cppartiel':
			return $langs->trans('HolidayPartialTime');
		break;
		
	}
}

//fonction qui permet de renvoyer le code de l'absence
function saveCodeTypeAbsence(&$ATMdb, $type){ // TODO deprecated
	$ta = new TRH_TypeAbsence;
	$ta->load_by_type($ATMdb, $type);
	
	return $ta->codeAbsence;					
}

//fonction permettant de retourner le libelle de l'état de l'absence (à Valider...)
function saveLibelleEtat($etat){
	switch($etat){
		case 'Avalider':
			return $langs->trans('WaitingValidation');
		break;
		case 'Validee':
			return $langs->trans('Accepted');
		break;
		case 'Refusee':
			return $langs->trans('Refused');
		break;

	}
}





//arrondi variable float à 2 virgules
function round2Virgule($variable){
	if($variable==0){
		return '0';
	}else {
		return number_format($variable,2,'.','');
	} 
}

//retourne la date au format "d/m/Y"
function php2dmy($phpDate){
    return date("d/m/Y", $phpDate);
}


//fonction permettant l'envoi de mail
function mailConges(&$absence,$presence=false){
	global $db, $lang;		

	$from = USER_MAIL_SENDER;
	

	$ATMdb=new TPDOdb;
	
	/*
	 * Mail destinataire
	 */
	$user = new User($db);	
	$user->fetch($absence->fk_user);

	$sendto=$user->email;
	$name=$user->lastname;
	$firstname=$user->firstname;
		

	$TBS=new TTemplateTBS();
	if($absence->etat=='Avalider'){
		
		if(!$presence){
			$subject = $langs->trans('HolidayRequestCreation');
			$tpl = dol_buildpath('/absence/tpl/mail.absence.creation.tpl.php');
		}
		else{
			$subject = $langs->trans('PresenceRequestCreation');
			$tpl = dol_buildpath('/absence/tpl/mail.presence.creation.tpl.php');
		}
		
		$message = $TBS->render($tpl
			,array()
			,array(
				'absence'=>array(
					'nom'=> htmlentities($name, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1')
					,'prenom'=> htmlentities($firstname, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1')
					,'date_debut'=> php2dmy($absence->date_debut)
					,'date_fin'=>php2dmy($absence->date_fin)
					,'libelle'=>htmlentities($absence->libelle, ENT_COMPAT | ENT_HTML401, 'UTF-8')
					,'libelleEtat'=>htmlentities($absence->libelleEtat, ENT_COMPAT | ENT_HTML401, 'UTF-8')
					
				)
				)
		);
	}else if($absence->etat=='Validee'){
		if(!$presence){
			$subject = $langs->trans('HolidayRequestAcceptance');
			$tpl = dol_buildpath('/absence/tpl/mail.absence.acceptation.tpl.php');
		}
		else{
			$subject = $langs->trans('PresenceRequestAcceptance');
			$tpl = dol_buildpath('/absence/tpl/mail.presence.acceptation.tpl.php');
		}
		
		$message = $TBS->render($tpl
			,array()
			,array(
				'absence'=>array(

					/*'nom'=>utf8_encode($name)
					,'prenom'=>utf8_encode($firstname)
					,'date_debut'=>php2dmy($absence->date_debut)
					,'date_fin'=>php2dmy($absence->date_fin)
					,'libelle'=>utf8_encode($absence->libelle)
					,'libelleEtat'=>utf8_encode($absence->libelleEtat)
					*/'nom'=> htmlentities($name, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1')
                                        ,'prenom'=> htmlentities($firstname, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1')
                                        ,'date_debut'=> php2dmy($absence->date_debut)
                                        ,'date_fin'=>php2dmy($absence->date_fin)
                                        ,'libelle'=>htmlentities($absence->libelle, ENT_COMPAT | ENT_HTML401, 'UTF-8')
                                        ,'libelleEtat'=>htmlentities($absence->libelleEtat, ENT_COMPAT | ENT_HTML401, 'UTF-8')
					,'commentaireValideur'=>utf8_encode($absence->commentaireValideur)
				)
				)
		);
	}
	else if($absence->etat=='Refusee'){
		if(!$presence){
			$subject = $langs->trans('HolidayRequestDenied');
			$tpl = dol_buildpath('/absence/tpl/mail.absence.refus.tpl.php');
		}
		else{
			$subject = $langs->trans('PresenceRequestDenied');
			$tpl = dol_buildpath('/absence/tpl/mail.presence.refus.tpl.php');
		}
		
		$message = $TBS->render($tpl
			,array()
			,array(
				'absence'=>array(
					/*'nom'=>utf8_encode($name)
					,'prenom'=>utf8_encode($firstname)
					,'date_debut'=>php2dmy($absence->date_debut)
					,'date_fin'=>php2dmy($absence->date_fin)
					,'libelle'=>utf8_encode($absence->libelle)
					,'libelleEtat'=>utf8_encode($absence->libelleEtat)
					*/'nom'=> htmlentities($name, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1')
                                        ,'prenom'=> htmlentities($firstname, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1')
                                        ,'date_debut'=> php2dmy($absence->date_debut)
                                        ,'date_fin'=>php2dmy($absence->date_fin)
                                        ,'libelle'=>htmlentities($absence->libelle, ENT_COMPAT | ENT_HTML401, 'UTF-8')
                                        ,'libelleEtat'=>htmlentities($absence->libelleEtat, ENT_COMPAT | ENT_HTML401, 'UTF-8')
					,'commentaireValideur'=>utf8_encode($absence->commentaireValideur)
				)
			)
		);
	}
	
	
	$mail = new TReponseMail($from,$sendto,$subject,$message);
	
	$result = $mail->send(true, 'utf-8');
	
	return 1;	
}

//fonction permettant la récupération
function mailCongesValideur(&$ATMdb, &$absence,$presence=false){
	//on récupèreles ids des groupes auxquels appartient l'utilisateur
	$sql="SELECT fk_usergroup FROM ".MAIN_DB_PREFIX."usergroup_user 
	WHERE fk_user= ".$absence->fk_user;

	$ATMdb->Execute($sql);
	$TGValideur=array();
	while($ATMdb->Get_line()){
		$TGValideur[]=$ATMdb->Get_field('fk_usergroup');
	}
	
	//on récupère tous les ids des collaborateurs à qui on devra envoyer un mail lors de la création d'une absence (valideurs des groupes précédents)
	$sql="SELECT fk_user FROM ".MAIN_DB_PREFIX."rh_valideur_groupe 
	WHERE type LIKE 'Conges' AND fk_usergroup IN(".implode(',', $TGValideur).") AND pointeur=0 AND level=".$absence->niveauValidation." AND fk_user!=".$absence->fk_user;
	
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()){
		$TValideur[]=$ATMdb->Get_field('fk_user');
	}
	
	if(!empty($TValideur)){
		foreach($TValideur as $idVal){
			envoieMailValideur($ATMdb, $absence, $idVal,$presence);
		}
	}
	
}


//fonction permettant l'envoi de mail aux valideurs de la demande d'absence
function envoieMailValideur(&$ATMdb, &$absence, $idValideur,$presence=false){
	global $db, $langs;
		
	$from = USER_MAIL_SENDER;

	$user = new User($db);  
    $user->fetch($absence->fk_user);

    $name=$user->lastname;
    $firstname=$user->firstname;

	/*
	 * Mail destinataire
	 */

	$userV = new User($db);  
        $userV->fetch($idValideur);

        $nameValideur=$userV->lastname;
        $firstnameValideur=$userV->firstname;
	$sendto = $userV->email;

	$TBS=new TTemplateTBS();
	
	if(!$presence){
		$subject = $langs->trans('NewAbsenceRequestWaitingValidation');
		$tpl = dol_buildpath('/absence/tpl/mail.absence.creationValideur.tpl.php');
	}
	else{
		$subject = $langs->trans('NewPresenceRequestWaitingValidation');
		$tpl = dol_buildpath('/absence/tpl/mail.presence.creationValideur.tpl.php');
	}
	
	$message = $TBS->render($tpl
		,array()
		,array(
			'absence'=>array(
				'nom'=>htmlentities($name, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1')
				,'prenom'=>htmlentities($firstname, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1')
				,'valideurNom'=>htmlentities($nameValideur, ENT_COMPAT | ENT_HTML401, 'UTF-8')
				,'valideurPrenom'=>htmlentities($firstnameValideur, ENT_COMPAT | ENT_HTML401, 'UTF-8')
				,'date_debut'=>php2dmy($absence->date_debut)
				,'date_fin'=>php2dmy($absence->date_fin)
				,'libelle'=>htmlentities($absence->libelle, ENT_COMPAT | ENT_HTML401, 'UTF-8')
				,'libelleEtat'=>htmlentities($absence->libelleEtat, ENT_COMPAT | ENT_HTML401, 'UTF-8')
			)
		)
	);
	
	$mail = new TReponseMail($from,$sendto,$subject,$message);
    	
	$result = $mail->send(true, 'utf-8');
	
	return 1;
}

function supprimerAccent($chaine){
	$chaine = strtr($chaine,"ÀÂÄÇÈÉÊËÌÎÏÑÒÔÕÖÙÛÜ","AAACEEEEIIINOOOOUUU");
	$chaine = strtr($chaine,"àáâãäåçèéêëìíîïñòóôõöùúûüýÿ","aaaaaaceeeeiiiinooooouuuuyy");
	return $chaine;
}

//permet d'additionner deux heures ensemble
function additionnerHeure($dureeTotale, $dureeDiff){
	list($heureT, $minuteT) = explode(':', $dureeTotale);
	//echo "heureT : ".$heureT." minutesT : ".$minuteT;
	list($heureD, $minuteD) = explode(':', $dureeDiff);
	
	$heureT=$heureT+$heureD;
	$minuteT=$minuteT+$minuteD;
	
	while($minuteT>60){
		$minuteT-=60;
		$heureT+=1;
	}
	
	return $heureT.":".$minuteT;
}

		
//donne la différence entre 2 heures (respecter l'ordre début et fin)
function difheure($heuredeb,$heurefin)
	{
		
		$hd=explode(":",$heuredeb);
		$hf=explode(":",$heurefin);
		$hd[0]=(int)($hd[0]);$hd[1]=(int)($hd[1]);$hd[2]=(int)($hd[2]);
		$hf[0]=(int)($hf[0]);$hf[1]=(int)($hf[1]);$hf[2]=(int)($hf[2]);
		if($hf[2]<$hd[2]){$hf[1]=$hf[1]-1;$hf[2]=$hf[2]+60;}
		if($hf[1]<$hd[1]){$hf[0]=$hf[0]-1;$hf[1]=$hf[1]+60;}
		if($hf[0]<$hd[0]){$hf[0]=$hf[0]+24;}
		return (($hf[0]-$hd[0]).":".($hf[1]-$hd[1]).":".($hf[2]-$hd[2]));
	}



function horaireMinuteEnCentieme($horaire){
	list($heure, $minute) = explode(':', $horaire);	
	$horaireCentieme=$heure+$minute/60;
	return $horaireCentieme;
}

//retourne la date au format "Y-m-d H:i:s"
function php2Date($phpDate){
    return date("Y-m-d H:i:s", $phpDate);
}
function getHistoryCompteurForUser($fk_user,$id_absence,$duree=null,$type=null, $etat=null) {
global $compteurCongeResteCurrentUser,$ATMdb;

	if(!isset($ATMdb_getHistoryCompteurForUser)) $ATMdb_getHistoryCompteurForUser=new TPDOdb;

	if(!isset($compteurCongeResteCurrentUser)) {
		
		$compteur =new TRH_Compteur;
		$compteur->load_by_fkuser($ATMdb_getHistoryCompteurForUser, $fk_user);

		$congePrecTotal = $compteur->acquisExerciceNM1 + $compteur->acquisAncienneteNM1 + $compteur->acquisHorsPeriodeNM1 + $compteur->reportCongesNM1;
		$compteurCongeResteCurrentUser = $congePrecTotal - $compteur->congesPrisNM1;
		
	}
		
	if(is_null($duree) || is_null($etat) || is_null($type)) {
		$absence = new TRH_Absence;
		$absence->load($ATMdb_getHistoryCompteurForUser, $id_absence);
		
		$duree = $absence->duree;
		$etat = $absence->etat;
		$type = $absence->type;
	}
		
	if($etat!='Refusee' && $duree>0 && ($type=='conges' || $type=='cppartiel')) {
		$compteurCongeResteCurrentUser+=$duree;
		return $compteurCongeResteCurrentUser;
		//return '<div align="right">'.number_format($compteurCongeResteCurrentUser,2,',',' ').'</div>';
	}
	else {
		return 0;
	}
	
}
