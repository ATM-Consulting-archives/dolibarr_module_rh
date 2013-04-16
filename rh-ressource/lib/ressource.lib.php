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
					,array(DOL_URL_ROOT_ALT.'/ressource/calendrierRessource.php?id='.$obj->getId(), 'Calendrier','calendrier')
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
	