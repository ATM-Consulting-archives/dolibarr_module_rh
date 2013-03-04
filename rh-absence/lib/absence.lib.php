<?php

function absencePrepareHead(&$obj, $type='absence') {
	
	switch ($type) {
		case 'absence':
				return array(
					array(DOL_URL_ROOT_ALT.'/absence/absence.php?id='.$obj->getId(), 'Fiche','fiche')
				);
			
			break;
		
	}
	
	
}
	