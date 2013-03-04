<?php

function ressourcePrepareHead(&$obj) {
	return array(
		array(DOL_URL_ROOT_ALT.'/ressource/typeRessource.php?id='.$obj->getId(), 'Fiche','fiche')
	);
	
}
	