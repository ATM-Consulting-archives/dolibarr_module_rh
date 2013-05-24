<?php

function absencePrepareHead(&$obj, $type='absence') {
	global $user;
	switch ($type) {
		case 'absence':
			return array(
				array(DOL_URL_ROOT_ALT.'/absence/absence.php?id='.$obj->getId()."&action=view", 'Fiche','fiche')
				,array(DOL_URL_ROOT_ALT.'/absence/calendrierAbsence.php?idUser='.$user->id.'&id='.$obj->getId(), 'Calendrier','calendrier')
			);
			break;
		case 'absenceCreation':
			return array(
				array(DOL_URL_ROOT_ALT.'/absence/absence.php?action=new', 'Fiche','fiche')
			);
			break;
		
		
	}
}

function compteurPrepareHead(&$obj, $type='absence') {
	global $user;
	switch ($type) {
		
		case 'compteur':
			//eif($user->rights->absence->myactions->modifierParamGlobalConges=="1"){
			return array(
			array(DOL_URL_ROOT_ALT.'/absence/compteur.php?action=view', 'Compteur de '.$user->lastname,'compteur')
			//,array(DOL_URL_ROOT_ALT.'/absence/adminCompteur.php?action=view', 'Administration générale congés','adminconges')
			);
			break;
	}
}

function adminCompteurPrepareHead(&$obj, $type='compteur') {
	global $user;
	switch ($type) {
		
		case 'compteur':
			return array(
			array(DOL_URL_ROOT_ALT.'/absence/adminCompteur.php', 'Compteur de congés','compteur')
			);
			break;				
	}
}

function adminCongesPrepareHead(&$obj, $type='compteur') {
	global $user;
	switch ($type) {
		
		case 'compteur':
			return array(
			array(DOL_URL_ROOT_ALT.'/absence/adminConges.php?action=view', 'Données générales des congés','adminconges')
			);
			break;
	}
}

function adminRecherchePrepareHead(&$obj, $type='recherche') {
	global $user;
	switch ($type) {
		
		case 'recherche':
			return array(
				array(DOL_URL_ROOT_ALT.'/absence/rechercheAbsence.php', 'Recherche Absence','recherche')
			);
			break;
	}
}

function edtPrepareHead(&$obj, $type='absence') {
	global $user;

	switch ($type) {
		
		case 'emploitemps':
				
			return array(
				array(DOL_URL_ROOT_ALT.'/absence/emploitemps.php?&fk_user='.$user->id, 'Emploi du temps','emploitemps')
			   ,array(DOL_URL_ROOT_ALT.'/absence/joursferies.php?&fk_user='.$user->id, 'Jours fériés ou non travaillés','joursferies')
			   //,array(DOL_URL_ROOT_ALT.'/absence/pointage.php?&fk_user='.$user->id, 'Pointage Collaborateurs','pointage')
			);
			break;
				
	}
}

function reglePrepareHead(&$obj, $type='regle') {
	global $user;

	switch ($type) {
		case 'regle':
			return array(
				array(DOL_URL_ROOT_ALT.'/absence/regleAbsence.php?fk_user='.$user->id, 'Règles des absences','regle')
			);
			break;
		case 'import':
			return array(
				array(DOL_URL_ROOT_ALT.'/ressource/documentRegle.php', 'Fiche','fiche')
			);
			break;
	}
}

//fonction qui permet d'enregistrer le libellé d'une absence suivant son type
function saveLibelle($type){
	switch($type){
		case 'rttcumule':
			return "RTT cumulé";
		break;
		case 'rttnoncumule':
			return "RTT non cumulé";
		break;
		case 'conges':
			return "Absence congés";
		break;
		case 'maladiemaintenue':
			return "Absence maladie maintenue";
		break;
		case 'maladienonmaintenue':
			return "Absence maladie non maintenue";
		break;
		case 'maternite':
			return "Absence maternité";
		break;
		case 'pathologie':
			return "Absence pathologie";
		break;
		
		case 'paternite':
			return "Absence paternité";
		break;
		case 'chomagepartiel':
			return "Absence Chômage partiel";
		break;
		case 'nonremuneree':
			return "Absence non rémunérée";
		break;
		case 'accidentdetravail':
			return "Absence accident du travail";
		break;
		case 'maladieprofessionnelle':
			return "Absence maladie Professionnelle";
		break;
		case 'congeparental':
			return "Absence Congés parental";
		break;
		case 'accidentdetrajet':
			return "Absence Accident trajet";
		break;
		case 'mitempstherapeutique':
			return "Absence Mi-temps thérapeutique";
		break;
		case 'mariage':
			return "Mariage";
		break;
		case 'deuil':
			return "Deuil";
		break;
		case 'naissanceadoption':
			return "Naissance ou adoption";
		break;
		case 'enfantmalade':
			return "Enfant malade";
		break;
		case 'demenagement':
			return "Déménagement";
		break;
		case 'cours':
			return "Cours";
		break;
		case 'preavis':
			return "Absence préavis";
		break;
		case 'demenagement':
			return "Déménagement";
		break;
		case 'rechercheemploi':
			return "Absence recherche emploi";
		break;
		case 'miseapied':
			return "Absence mise à pied";
		break;
		case 'nonjustifiee':
			return "Absence non justifiée";
		break;
		
	}
}

//fonction qui permet de renvoyer le code de l'absence
function saveCodeTypeAbsence(&$ATMdb, $type){
	global $conf;
	$sql="SELECT codeAbsence FROM `".MAIN_DB_PREFIX."rh_type_absence` WHERE typeAbsence LIKE '".$type."' AND entity IN (0,".$conf->entity.")";
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		return $ATMdb->Get_field('codeAbsence');
	}	
}

//fonction permettant de retourner le libelle de l'état de l'absence (à Valider...)
function saveLibelleEtat($etat){
	switch($etat){
		case 'Avalider':
			return "En attente de validation";
		break;
		case 'Acceptee':
			return "Acceptée";
		break;
		case 'Refusee':
			return "Refusée";
		break;

	}
}





//arrondi variable float à 2 virgules
function round2Virgule($variable){
	if($variable==0){
		return "0";
	}else return round($variable,2);
}

//retourne la date au format "d/m/Y"
function php2dmy($phpDate){
    return date("d/m/Y", $phpDate);
}


//fonction permettant l'envoi de mail
function mailConges(&$absence){
		
	$from = USER_MAIL_SENDER;
	

	$ATMdb=new Tdb;
	
	/*
	 * Mail destinataire
	 */
	$sql="SELECT * FROM `".MAIN_DB_PREFIX."user` WHERE rowid=".$absence->fk_user." AND entity IN (0,".$conf->entity.")";
	$ATMdb->Execute($sql);
	$ATMdb->Get_line();
	
	$sendto=$ATMdb->Get_field('email');
	$name=$ATMdb->Get_field('name');
	$firstname=$ATMdb->Get_field('firstname');
		

	$TBS=new TTemplateTBS();
	if($absence->etat=='Avalider'){
		$subject = "Création d'une demande de congés";
		$message = $TBS->render(DOL_DOCUMENT_ROOT_ALT.'/absence/tpl/mail.absence.creation.tpl.php'
			,array()
			,array(
				'absence'=>array(
					'nom'=>$name
					,'prenom'=>$firstname
					,'date_debut'=>php2dmy($absence->date_debut)
					,'date_fin'=>php2dmy($absence->date_fin)
					,'libelle'=>$absence->libelle
					,'libelleEtat'=>$absence->libelleEtat
				)
				)
		);
	}else if($absence->etat=='Validee'){
		$subject = "Acceptation de votre demande de congés";
		$message = $TBS->render(DOL_DOCUMENT_ROOT_ALT.'/absence/tpl/mail.absence.acceptation.tpl.php'
			,array()
			,array(
				'absence'=>array(
					'nom'=>$name
					,'prenom'=>$firstname
					,'date_debut'=>php2dmy($absence->date_debut)
					,'date_fin'=>php2dmy($absence->date_fin)
					,'libelle'=>$absence->libelle
					,'libelleEtat'=>$absence->libelleEtat
				)
				)
		);
	}
	else if($absence->etat=='Refusee'){
		$subject = "Refus de votre demande de congés";
		$message = $TBS->render(DOL_DOCUMENT_ROOT_ALT.'/absence/tpl/mail.absence.refus.tpl.php'
			,array()
			,array(
				'absence'=>array(
					'nom'=>$name
					,'prenom'=>$firstname
					,'date_debut'=>php2dmy($absence->date_debut)
					,'date_fin'=>php2dmy($absence->date_fin)
					,'libelle'=>$absence->libelle
					,'libelleEtat'=>$absence->libelleEtat
				)
				)
		);
	}
	$mail = new TReponseMail($from,$sendto,$subject,$message);
    (int)$result = $mail->send();
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

