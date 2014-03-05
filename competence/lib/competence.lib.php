<?php

function competencePrepareHead(&$obj, $type='competence') {
	global $user;
	switch ($type) {
		case 'competence':
				return array(
					array(dol_buildpath('/competence/rechercheCompetence.php?id='.$obj->getId()."&action=view",1), 'Fiche','fiche')
				);
				break;
		
		case 'planFormation':
				return array(
					array(dol_buildpath('/competence/planFormation.php?id='.$obj->getId()."&action=view",1), 'Fiche','fiche')
				);
				break;
		
	}
}
