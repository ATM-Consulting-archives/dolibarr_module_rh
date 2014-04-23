<?php

function pointeusePrepareHead() {
		return array(
			array(dol_buildpath('/absence/pointeuse.php',1), 'Pointeuse','fiche')
		);
}


function absencePrepareHead(&$obj, $type='absence') {
	global $user;
	switch ($type) {
		case 'absence':
			return array(
				array(dol_buildpath('/absence/absence.php?id='.$obj->getId(),1)."&action=view", 'Fiche','fiche')
				,array(dol_buildpath('/absence/calendrierAbsence.php?idUser='.$user->id.'&id='.$obj->getId(),1), 'Calendrier','calendrier')
			);
			break;
		case 'presence':
			return array(
				array(dol_buildpath('/absence/presence.php?id='.$obj->getId()."&action=view",1), 'Fiche','fiche')
				,array(dol_buildpath('/absence/calendrierAbsence.php?idUser='.$user->id.'&id='.$obj->getId(),1), 'Calendrier','calendrier')
			);
			break;
		case 'absenceCreation':
			return array(
				array(dol_buildpath('/absence/absence.php?action=new',1), 'Fiche','fiche')
			);
			break;
		
		
	}
}


function compteurPrepareHead(&$obj, $type='absence', $nomUser, $prenomUser) {
	global $user;
	switch ($type) {
		
		case 'compteur':
			//eif($user->rights->absence->myactions->modifierParamGlobalConges=="1"){
			return array(
			array(dol_buildpath('/absence/compteur.php?action=view',1), 'Compteur de '.$nomUser." ".$prenomUser,'compteur')
			//,array(dol_buildpath('/absence/adminCompteur.php?action=view', 'Administration générale congés','adminconges')
			);
			break;
	}
}

function adminCompteurPrepareHead(&$obj, $type='compteur') {
	global $user;
	switch ($type) {
		
		case 'compteur':
			return array(
			array(dol_buildpath('/absence/adminCompteur.php',1), 'Compteur de congés','compteur')
			);
			break;				
	}
}

function adminCongesPrepareHead($type='compteur') {
	global $user;
	switch ($type) {
		
		case 'compteur':
			return array(
				array(dol_buildpath('/absence/adminConges.php',1), 'Données générales des congés','adminconges')
				,array(dol_buildpath('/absence/typeAbsence.php',1), "Types d'absences",'typeabsence')
				,array(dol_buildpath('/absence/typePresence.php',1), "Types de présences",'typepresence')
			);
			break;
	}
}

function adminRecherchePrepareHead(&$obj, $type='recherche') {
	global $user;
	switch ($type) {
		
		case 'recherche':
			return array(
				array(dol_buildpath('/absence/rechercheAbsence.php',1), 'Recherche Absence','recherche')
			);
			break;
		case 'planning':
			return array(
				array(dol_buildpath('/absence/rechercheAbsence.php',1), 'Recherche Absence','recherche')
			);
			break;
	}
}

function edtPrepareHead(&$obj, $type='absence') {
	global $user;

	switch ($type) {
		
		case 'emploitemps':
				
			return array(
				array(dol_buildpath('/absence/emploitemps.php?&fk_user='.$user->id,1), 'Emploi du temps','emploitemps')
			   ,array(dol_buildpath('/absence/joursferies.php?&fk_user='.$user->id,1), 'Jours fériés ou non travaillés','joursferies')
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
				array(dol_buildpath('/absence/regleAbsence.php?fk_user='.$user->id,1), 'Règles des absences','regle')
			);
			break;
		case 'import':
			return array(
				array(dol_buildpath('/ressource/documentRegle.php',1), 'Fiche','fiche')
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
			return "Absence congés sans solde";
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
		case 'cppartiel':
			return "CP à temps partiel";
		break;
		
	}
}

//fonction qui permet de renvoyer le code de l'absence
function saveCodeTypeAbsence(&$ATMdb, $type){
	global $conf;
	$sql="SELECT codeAbsence FROM `".MAIN_DB_PREFIX."rh_type_absence` WHERE typeAbsence LIKE '".$type."'";
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
		case 'Validee':
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
function mailConges(&$absence){
		
	$from = USER_MAIL_SENDER;
	

	$ATMdb=new TPDOdb;
	
	/*
	 * Mail destinataire
	 */
	$sql="SELECT * FROM `".MAIN_DB_PREFIX."user` WHERE rowid=".$absence->fk_user;
	$ATMdb->Execute($sql);
	if($ATMdb->Get_line()){
		$sendto=$ATMdb->Get_field('email');
		$name=$ATMdb->Get_field('lastname');
		$firstname=$ATMdb->Get_field('firstname');
	}
		

	$TBS=new TTemplateTBS();
	if($absence->etat=='Avalider'){
		$subject = "Création d'une demande de congés";
		$message = $TBS->render(dol_buildpath('/absence/tpl/mail.absence.creation.tpl.php')
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
		$subject = "Acceptation de votre demande de congés";
		$message = $TBS->render(dol_buildpath('/absence/tpl/mail.absence.acceptation.tpl.php')
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
		$subject = "Refus de votre demande de congés";
		$message = $TBS->render(dol_buildpath('/absence/tpl/mail.absence.refus.tpl.php')
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
    (int)$result = $mail->send(true, 'utf-8');
	return 1;	
}

//fonction permettant la récupération
function mailCongesValideur(&$ATMdb, &$absence){
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
			envoieMailValideur($ATMdb, $absence, $idVal);
		}
	}
	
}


//fonction permettant l'envoi de mail aux valideurs de la demande d'absence
function envoieMailValideur(&$ATMdb, &$absence, $idValideur){
		
	$from = USER_MAIL_SENDER;

	$sql="SELECT * FROM `".MAIN_DB_PREFIX."user` WHERE rowid=".$absence->fk_user;
	$ATMdb->Execute($sql);
	if($ATMdb->Get_line()){
		$name=$ATMdb->Get_field('lastname');
		$firstname=$ATMdb->Get_field('firstname');
	}
		
	/*
	 * Mail destinataire
	 */
	$sql="SELECT * FROM `".MAIN_DB_PREFIX."user` WHERE rowid=".$idValideur;
	$ATMdb->Execute($sql);
	if($ATMdb->Get_line()){
		$nameValideur=$ATMdb->Get_field('lastname');
		$firstnameValideur=$ATMdb->Get_field('firstname');
		$sendto=$ATMdb->Get_field('email');
	}	

		
	$TBS=new TTemplateTBS();

	$subject = "Nouvelle demande d'absence à valider";
	$message = $TBS->render(dol_buildpath('/absence/tpl/mail.absence.creationValideur.tpl.php')
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
    (int)$result = $mail->send(true, 'utf-8');
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

