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
	
	?><table class="border" style="width:100%">
		<tr>
			<td>Numéro Id</td>
			<td><? echo $ressource->numId ;?></td>
		</tr>
		<tr>
			<td>Libellé</td>
			<td><? echo $ressource->libelle ;?></td>
		</tr>
	</table><br><?
	
}

function getTypeEvent($idTypeRessource){
	global $conf;
	$TEvent = array(
		'all'=>''
		,'accident'=>'Accident'
		,'reparation'=>'Réparation'
		,'facture'=>'Facture'
	);	
	$ATMdb =new TPDOdb;
	
	$sqlReq="SELECT rowid, liste_evenement_value, liste_evenement_key FROM ".MAIN_DB_PREFIX."rh_ressource_type 
	WHERE rowid=".$idTypeRessource." AND entity=".$conf->entity;
	$ATMdb->Execute($sqlReq);
	while($ATMdb->Get_line()) {
		$keys = explode(';', $ATMdb->Get_field('liste_evenement_key'));
		$values = explode(';', $ATMdb->Get_field('liste_evenement_value'));
		foreach ($values as $i=>$value) {
			if (!empty($value)){
				$TEvent[$keys[$i]] = $values[$i];
			}
		}
	}
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
	return $TUser;
	
}
	