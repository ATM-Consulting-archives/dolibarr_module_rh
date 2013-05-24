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

function getRessource($idTypeRessource = 0){
	global $conf;
	$TRessource = array('');
	$ATMdb =new TPDOdb;
	
	$sqlReq="SELECT rowid,libelle, numId FROM ".MAIN_DB_PREFIX."rh_ressource WHERE entity=".$conf->entity;
	if ($idTypeRessource>0){$sqlReq.= " AND fk_rh_ressource_type=".$idTypeRessource;}
	$ATMdb->Execute($sqlReq);
	while($ATMdb->Get_line()) {
		$TRessource[$ATMdb->Get_field('rowid')] = htmlentities($ATMdb->Get_field('libelle').' '.$ATMdb->Get_field('numId'), ENT_COMPAT , 'ISO8859-1');
		}
	$ATMdb->close();
	return $TRessource;
}

/**
 * Retourne l'ID du type code
 */
function getIdType($type){
	global $conf;
	$ATMdb =new TPDOdb;
	$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_ressource_type 
		WHERE entity IN (0,".$conf->entity.")
	 	AND code= '".$type."'";
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$id = $ATMdb->Get_field('rowid');
		}
	$ATMdb->close();
	return $id;
}


function getIDRessource(&$ATMdb, $idType){
	global $conf;
	$TRessource = array();
	
	$sql="SELECT rowid, numId  FROM ".MAIN_DB_PREFIX."rh_ressource
	 WHERE fk_rh_ressource_type=".$idType." 
	 AND entity in (0, ".$conf->entity.")";
	// echo $sql.'<br>';
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TRessource[$ATMdb->Get_field('numId')] = $ATMdb->Get_field('rowid');
	}
	return $TRessource;
}


function getUsers(){
	global $conf;
	$TUser = array();
	$ATMdb =new TPDOdb;
	
	$sqlReq="SELECT rowid,name, firstname FROM ".MAIN_DB_PREFIX."user WHERE entity=".$conf->entity;
	
	$ATMdb->Execute($sqlReq);
	while($ATMdb->Get_line()) {
		$TUser[$ATMdb->Get_field('rowid')] = htmlentities($ATMdb->Get_field('firstname').' '.$ATMdb->Get_field('name'), ENT_COMPAT , 'ISO8859-1');
		}
	$ATMdb->close();
	return $TUser;
	
}

function getGroups(){
	global $conf;
	$TGroups = array();
	$ATMdb =new TPDOdb;
	
	$sqlReq="SELECT rowid,nom FROM ".MAIN_DB_PREFIX."usergroup WHERE entity=".$conf->entity;
	
	$ATMdb->Execute($sqlReq);
	while($ATMdb->Get_line()) {
		$TGroups[$ATMdb->Get_field('rowid')] = htmlentities($ATMdb->Get_field('nom'), ENT_COMPAT , 'ISO8859-1');
		}
	return $TGroups;
	
}
	

function stringTous($val, $choixApplication){
	echo $choixApplication;
	if ($choixApplication == 'all') return 'Tous';
	else return $val;
}

function intToString($val = 0){
	$h = intval($val/60);
	if ($h < 10){$h = '0'.$h;}
	$m = $val%60;
	if ($m < 10){$m = '0'.$m;}
	if ($h==0 && $m==0){return '';}
	return $h.':'.$m;
}

function intToHour($val){
	$h = intval($val/60);
	if ($h < 10){$h = '0'.$h;}
	return $h;
}
function intToMinute($val){
	$m = $val%60;
	if ($m < 10){$m = '0'.$m;}
	return $m;
}

function timeToInt($h, $m){
	return intval($h)*60+intval($m);
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
	