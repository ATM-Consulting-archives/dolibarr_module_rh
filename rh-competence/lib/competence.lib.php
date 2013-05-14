<?php

function competencePrepareHead(&$obj, $type='competence') {
	global $user;
	switch ($type) {
		case 'competence':
				return array(
					array(DOL_URL_ROOT_ALT.'/competence/rechercheCompetence.php?id='.$obj->getId()."&action=view", 'Fiche','fiche')
				);
				break;

	}
}
