<?php

function ressourcePrepareHead(&$obj, $type='type-ressource') {
	
	switch ($type) {
		case 'type-ressource':
				return array(
					array(DOL_URL_ROOT_ALT.'/ressource/typeRessource.php?id='.$obj->getId(), 'Fiche','fiche')
				);
			
			break;
		case 'ressource':
				return array(
					array(DOL_URL_ROOT_ALT.'/ressource/ressource.php?id='.$obj->getId(), 'Fiche','fiche')
					,array(DOL_URL_ROOT_ALT.'/ressource/attribution.php?id='.$obj->getId(), 'Attribution','attribution')
					//,array(DOL_URL_ROOT_ALT.'/ressource/evenement.php?id='.$obj->getId(), 'Evénement','evenement')
					,array(DOL_URL_ROOT_ALT.'/ressource/calendrierRessource.php?id='.$obj->getId(), 'Calendrier','calendrier')
				);
			
			break;
	
		
		
	}
	
	
}
	