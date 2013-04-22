<?php

function absencePrepareHead(&$obj, $type='absence') {
	global $user;
	switch ($type) {
		case 'absence':
				return array(
					array(DOL_URL_ROOT_ALT.'/absence/absence.php?id='.$obj->getId()."&action=view", 'Fiche','fiche')
					,array(DOL_URL_ROOT_ALT.'/absence/calendrierAbsence.php?idUser='.$obj->fk_user.'&id='.$obj->getId(), 'Calendrier','calendrier')
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
				if($user->rights->absence->myactions->modifierParamGlobalConges=="1"){
					return array(
					array(DOL_URL_ROOT_ALT.'/absence/compteur.php', 'Compteur','compteur')
					,array(DOL_URL_ROOT_ALT.'/absence/adminCompteur.php?action=view', 'Administration générale congés','adminconges')
					);
					break;
				}else{
					return array(
					array(DOL_URL_ROOT_ALT.'/absence/compteur.php?id='.$obj->getId()."&action=view", 'Compteur','compteur')
					);
				}
				
	}
}

function edtPrepareHead(&$obj, $type='absence') {
	global $user;

	switch ($type) {
		
		case 'emploitemps':
				
				return array(
					array(DOL_URL_ROOT_ALT.'/absence/emploitemps.php?id='.$obj->fk_user."&action=view&fk_user=".$user->id, 'Emploi du temps','emploitemps')
				   ,array(DOL_URL_ROOT_ALT.'/absence/joursferies.php?&fk_user='.$user->id, 'Jours non travaillés','joursferies')
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
	}
}

//fonction qui permet d'enregistrer le libellé d'une absence suivant son type
function saveLibelle($type){
	switch($type){
		case 'rttcumule':
			return "RTT Cumulé";
		break;
		case 'rttnoncumule':
			return "RTT Non Cumulé";
		break;
		case 'conges':
			return "Congés";
		break;
		case 'maladiemaintenue':
			return "Maladie Maintenue";
		break;
		case 'maladienonmaintenue':
			return "Maladie Non Maintenue";
		break;
		case 'maternite':
			return "Maternité";
		break;
		case 'paternite':
			return "Paternité";
		break;
		case 'chomagepartiel':
			return "Chômage Partiel";
		break;
		case 'nonremuneree':
			return "Non rémunérée";
		break;
		case 'accidentdetravail':
			return "Accident de travail";
		break;
		case 'maladieprofessionnelle':
			return "Maladie Professionnelle";
		break;
		case 'congeparental':
			return "Congé Parental";
		break;
		case 'accidentdetrajet':
			return "Accident de trajet";
		break;
		case 'mitempstherapeutique':
			return "Mi-temps thérapeutique";
		break;
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
		case 'Enregistree':
			return "Enregistrée dans la paie";
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
	$sql="SELECT * FROM `".MAIN_DB_PREFIX."user` where rowid=".$absence->fk_user;//AND entity=".$conf->entity;
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



