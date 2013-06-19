<?php

function ressourcePrepareHead(&$obj, $type='type-ressource',&$param=null) {
	global $user;
	
	switch ($type) {
		case 'type-ressource':
				return array(
					array(DOL_URL_ROOT_ALT.'/ressource/typeRessource.php?id='.$obj->getId(), 'Fiche','fiche')
					,array(DOL_URL_ROOT_ALT.'/ressource/typeRessourceField.php?id='.$obj->getId(), 'Champs','field')
					,($obj->code == 'telephone') ? array(DOL_URL_ROOT_ALT.'/ressource/typeRessourceRegle.php?id='.$obj->getId(), 'Règles','regle'): null
					,array(DOL_URL_ROOT_ALT.'/ressource/typeRessourceEvenement.php?id='.$obj->getId(), 'Evénements','event')
				);
			
			break;
		case 'ressource':
				return array(
					array(DOL_URL_ROOT_ALT.'/ressource/ressource.php?id='.$obj->getId(), 'Fiche','fiche')
					,($obj->fk_rh_ressource == 0)  ? array(DOL_URL_ROOT_ALT.'/ressource/attribution.php?id='.$obj->getId(), 'Attribution','attribution'):null
					,array(DOL_URL_ROOT_ALT.'/ressource/evenement.php?id='.$obj->getId(), 'Evénement','evenement')
					,$user->rights->ressource->ressource->viewResourceCalendar?array(DOL_URL_ROOT_ALT.'/ressource/calendrierRessource.php?id='.$obj->getId().'&fiche=true', 'Calendrier','calendrier'):''
					,array(DOL_URL_ROOT_ALT.'/ressource/document.php?id='.$obj->getId(), 'Fichiers joints','document')
					,$user->rights->ressource->ressource->viewFilesRestricted?array(DOL_URL_ROOT_ALT.'/ressource/documentConfidentiel.php?id='.$obj->getId(), 'Fichiers confidentiels','documentConfidentiel'):''
					,array(DOL_URL_ROOT_ALT.'/ressource/contratRessource.php?id='.$obj->getId(), 'Contrats','contrats')
				);
			
			break;
		case 'contrat':
				return array(
					array(DOL_URL_ROOT_ALT.'/ressource/contrat.php?id='.$obj->getId(), 'Fiche','fiche')
					,array(DOL_URL_ROOT_ALT.'/ressource/documentContrat.php?id='.$obj->getId(), 'Fichiers joints','document')
				);
			
			break;
		case 'evenement':
				return array(
					array(DOL_URL_ROOT_ALT.'/ressource/evenement.php?id='.$param->getId().'&idEven='.$obj->getId().'&action=view', 'Fiche','fiche')
					,array(DOL_URL_ROOT_ALT.'/ressource/documentEvenement.php?id='.$param->getId().'&idEven='.$obj->getId(), 'Fichiers joints','document')
				);
			
			break;
		case 'import':
				return array(
					array(DOL_URL_ROOT_ALT.'/ressource/documentSupplier.php', 'Fiche','fiche')
				);
			
			break;
		default :
				return array();
			break;
	}
}

/**
 * Affiche un tableau avec le numId et le libellé de la ressource
 */
function printLibelle($ressource){
	
	print getLibelle($ressource);
	
}

function getLibelle($ressource){
	return '<table class="border" style="width:100%">
		<tr>
			<td style="width:20%">Numéro Id</td>
			<td>'.$ressource->numId.'</td>
		</tr>
		<tr>
			<td>Libellé</td>
			<td><a href="ressource.php?id='.$ressource->getId().'">'.$ressource->libelle.'</a> </td>
		</tr>
	</table><br>';
}

/**
 * Retourne la liste des types d'événement associé à un type de ressource
 */
function getTypeEvent($idTypeRessource = 0){
	global $conf;
	$TEvent = array();
	
	$sql="SELECT rowid, code, libelle FROM ".MAIN_DB_PREFIX."rh_type_evenement 
	WHERE (fk_rh_ressource_type=".$idTypeRessource." OR fk_rh_ressource_type=0) ORDER BY fk_rh_ressource_type";
	$ATMdb =new TPDOdb;
	$ATMdb->Execute($sql);
	while($row = $ATMdb->Get_line()) {
		$TEvent[$row->code] = $row->libelle;	
	}
	$ATMdb->close();
	return $TEvent;
}

/**
 * Renvoie un tableau de id=>libelle des ressources de type spécifié. Par défaut toute les ressources.
 */
function getRessource($idTypeRessource = 0){
	global $conf;
	$TRessource = array(0=>'');
	$ATMdb =new TPDOdb;
	
	$sqlReq="SELECT rowid,libelle, numId FROM ".MAIN_DB_PREFIX."rh_ressource WHERE 1 ";
	if ($idTypeRessource>0){$sqlReq.= " AND fk_rh_ressource_type=".$idTypeRessource;}
	$ATMdb->Execute($sqlReq);
	while($ATMdb->Get_line()) {
		$TRessource[$ATMdb->Get_field('rowid')] = htmlentities($ATMdb->Get_field('libelle').' '.$ATMdb->Get_field('numId'), ENT_COMPAT , 'ISO8859-1');
		}
	$ATMdb->close();
	return $TRessource;
}

/**
 * Retourne l'ID du type de ressource correspondant à 'code', false si code pas trouvé.
 */
function getIdType($code){
	global $conf;
	$ATMdb =new TPDOdb;
	$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_ressource_type 
		WHERE code= '".$code."'";
	$ATMdb->Execute($sql);
	$id = false;
	if ($ATMdb->Get_line()) {$id = $ATMdb->Get_field('rowid');}
	$ATMdb->close();
	return $id;
}

/**
 * Renvoie un tableau $numId=>$rowid des ressources du type spécifié.
 */
function getIDRessource(&$ATMdb, $idType=0){
	global $conf;
	$TRessource = array();
	
	$sql="SELECT rowid, numId  FROM ".MAIN_DB_PREFIX."rh_ressource
	 WHERE fk_rh_ressource_type=".$idType." 
	 AND entity IN (0, ".$conf->entity.")";
	// echo $sql.'<br>';
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TRessource[$ATMdb->Get_field('numId')] = $ATMdb->Get_field('rowid');
	}
	return $TRessource;
}

/**
 * Renvoie un tableau $id=> nom des users
 * $inEntity à vrai ne renvoie que les User de l'entité courante
 * $avecAll à vrai rajoute une ligne Tous
 */
function getUsers($avecAll = false, $inEntity = true){
	global $conf;
	$TUser = $avecAll ? array(0=>'Tous') : array() ;
	$ATMdb =new TPDOdb;
	
	$sqlReq = "SELECT rowid,name, firstname FROM ".MAIN_DB_PREFIX."user";
	if ($inEntity){$sqlReq .= " WHERE entity IN (0,".$conf->entity.") ";} 
	$sqlReq.= " ORDER BY name, firstname ";
	
	$ATMdb->Execute($sqlReq);
	while($ATMdb->Get_line()) {
		$TUser[$ATMdb->Get_field('rowid')] = htmlentities($ATMdb->Get_field('firstname').' '.$ATMdb->Get_field('name'), ENT_COMPAT , 'ISO8859-1');
		}
	$ATMdb->close();
	return $TUser;
	
}

/**
 * renvoie une liste des groupes $id=>nom
 */
function getGroups(){
	global $conf;
	$TGroups = array();
	$ATMdb =new TPDOdb;
	
	$sqlReq="SELECT rowid,nom FROM ".MAIN_DB_PREFIX."usergroup WHERE entity IN (0,".$conf->entity.")";
	
	$ATMdb->Execute($sqlReq);
	while($ATMdb->Get_line()) {
		$TGroups[$ATMdb->Get_field('rowid')] = htmlentities($ATMdb->Get_field('nom'), ENT_COMPAT , 'ISO8859-1');
		}
	return $TGroups;
	
}


/**
 * si le choix limite est cohérant avec la colonne, on affiche la valeur
 */
function afficheOuPas($val, $choixLimite,$colonne){
	if ($colonne==$choixLimite){return intToString($val);}
	return '';
}

	
/**
 * renvoie 'Tous' si choixApplication='all', renvoie val sinon. 
 */
function stringTous($val, $choixApplication){
	if ($choixApplication == 'all') return 'Tous';
	else return $val;
}

/**
 * Transforme un nombre de minute (entier) en jolie chaine de caractère donnant l'heure
 * @return une string
 */
function intToString($val = 0){
	$h = intval($val/60);
	if ($h < 10){$h = '0'.$h;}
	$m = $val%60;
	if ($m < 10){$m = '0'.$m;}
	if ($h==0 && $m==0){return '00:00';}
	return $h.':'.$m;
}

/**
 * Donnant le nombre d'heure correspondant à $val minutes
 * @return une string
 */
function intToHour($val){
	$h = intval($val/60);
	if ($h < 10){$h = '0'.$h;}
	return $h;
}
/**
 * Donne le modulo 60 de $val minutes
 * @return une string
 */
function intToMinute($val){
	$m = $val%60;
	if ($m < 10){$m = '0'.$m;}
	return $m;
}

/**
 * f(heure, minutes) => minutes
 */
function timeToInt($h, $m){
	return intval($h)*60+intval($m);
}


/**
 * Charge les règles pour chacun des utilisateurs
 */
function load_limites_telephone(&$ATMdb, $TGroups, $TRowidUser){
	$default = 359940; //consideration conso infinie : 99H
	$TLimites = array();
	foreach ($TRowidUser as $id) {		
		$TLimites[$id] = array(
			'lim'=>$default
			,'limInterne' => $default	//en sec
			,'limExterne' => $default	//en sec
			,'dataIllimite' => false
			,'dataIphone' => false
			,'mailforfait'=> false
			,'smsIllimite'=> false
			,'data15Mo'=> false
			,'natureRefac'=>''
			,'montantRefac'=>0
			);
	}
	

	/*echo '<br><br><br>';
foreach ($TLimites as $key => $value) {
	echo $key.' ';	
	print_r($value);
	echo '<br>';*/


	$sql="SELECT fk_user, fk_usergroup, choixApplication, dureeInt, dureeExt,duree,
		dataIllimite, dataIphone, smsIllimite, mailforfait, data15Mo, natureRefac, montantRefac 
		FROM ".MAIN_DB_PREFIX."rh_ressource_regle
		";
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		if ($ATMdb->Get_field('choixApplication')=='user'){
			modifierLimites($TLimites, $ATMdb->Get_field('fk_user')
				, $ATMdb->Get_field('duree')
				, $ATMdb->Get_field('dureeInt')
				, $ATMdb->Get_field('dureeExt')
				, $ATMdb->Get_field('dataIllimite')
				, $ATMdb->Get_field('dataIphone')
				, $ATMdb->Get_field('mailforfait')
				, $ATMdb->Get_field('smsIllimite')
				, $ATMdb->Get_field('data15Mo')
				, $ATMdb->Get_field('natureRefac')
				, $ATMdb->Get_field('montantRefac')
				);
			}
		else if ($ATMdb->Get_field('choixApplication')=='group'){
			if (empty($TGroups[$ATMdb->Get_field('fk_usergroup')]))
				{$message .= 'Groupe n°'.$ATMdb->Get_field('fk_usergroup').' inexistant.<br>';}
			else{
				foreach ($TGroups[$ATMdb->Get_field('fk_usergroup')] as $members) {
					modifierLimites($TLimites, $members
						, $ATMdb->Get_field('duree')
						, $ATMdb->Get_field('dureeInt')
						, $ATMdb->Get_field('dureeExt')
						, $ATMdb->Get_field('dataIllimite')
						, $ATMdb->Get_field('dataIphone')
						, $ATMdb->Get_field('mailforfait')
						, $ATMdb->Get_field('smsIllimite')
						, $ATMdb->Get_field('data15Mo')
						, $ATMdb->Get_field('natureRefac')
						, $ATMdb->Get_field('montantRefac')
						
						);
					}
				}
			}
		else if ($ATMdb->Get_field('choixApplication')=='all'){
			foreach ($TRowidUser as $idUser) {
				modifierLimites($TLimites, $idUser
					, $ATMdb->Get_field('duree')
					, $ATMdb->Get_field('dureeInt')
					, $ATMdb->Get_field('dureeExt')
					, $ATMdb->Get_field('dataIllimite')
					, $ATMdb->Get_field('dataIphone')
					, $ATMdb->Get_field('mailforfait')
					, $ATMdb->Get_field('smsIllimite')
					, $ATMdb->Get_field('data15Mo')
					, $ATMdb->Get_field('natureRefac')
					, $ATMdb->Get_field('montantRefac')
					);
				}
			}
		}
	return $TLimites;
}


function modifierLimites(&$TLimites, $fk_user, $gen,  $int, $ext, $dataIll = false, $dataIphone = false, $mail = false, $smsIll = false, $data15Mo= false, $natureRefac = false, $montantRefac = 0){
	if (($TLimites[$fk_user]['limInterne'] > $int*60)){
		$TLimites[$fk_user]['limInterne'] = $int*60;
	}
	if (($TLimites[$fk_user]['limExterne'] > $ext*60)) {
		$TLimites[$fk_user]['limExterne'] = $ext*60;
	}
	if ($TLimites[$fk_user]['lim'] > ($gen*60)){
		$TLimites[$fk_user]['lim'] = $gen*60;
	}
	
	$TLimites[$fk_user]['dataIllimite'] =$dataIll;
	$TLimites[$fk_user]['dataIphone'] =$dataIphone;
	$TLimites[$fk_user]['mailforfait']=$mail;
	$TLimites[$fk_user]['smsIllimite']=$smsIll;
	$TLimites[$fk_user]['data15Mo']=$data15Mo;
	if ($natureRefac){
		if (!empty($TLimites[$fk_user]['natureRefac'])){$TLimites[$fk_user]['natureRefac'] .= " ; ";}	
		$TLimites[$fk_user]['natureRefac'] .= $natureRefac;
		$TLimites[$fk_user]['montantRefac'] += $montantRefac;
		}
		
	return;
}




function send_mail_resources($subject, $message){
	global $langs;
	
	$langs->load('mails');
	
	$from = USER_MAIL_SENDER;
	$sendto = USER_MAIL_RECEIVER;

	$mail = new TReponseMail($from,$sendto,$subject,$message);
	
	dol_syslog("Ressource::sendmail content=$from,$sendto,$subject,$message", LOG_DEBUG);
	
    (int)$result = $mail->send(true, 'utf-8');
	return (int)$result;
}
	
	

/**
 * La fonction renvoie le rowid de l'user qui a la ressource $idRessource à la date $jour, 0 sinon.
 * $jour a la forme
 */
function ressourceIsEmpruntee(&$ATMdb, $idRessource, $jour){
		global $conf;
		$sql = "SELECT e.fk_user, e.date_debut , e.date_fin
				FROM ".MAIN_DB_PREFIX."rh_evenement as e
				LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (e.fk_rh_ressource=r.rowid OR e.fk_rh_ressource=r.fk_rh_ressource) 
				WHERE e.type='emprunt'
				AND r.rowid = ".$idRessource."
				AND e.entity IN (0, ".$conf->entity.") 
				AND e.date_debut<='".$jour."' AND e.date_fin >= '".$jour."' 
				";
				
		$ATMdb->Execute($sql);
		if ($ATMdb->Get_line()){
			return $ATMdb->Get_field('fk_user');
		}
		return 0;
}	
	
	