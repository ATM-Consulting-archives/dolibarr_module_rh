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
				
		case 'productivite_user':
				return array(
					array(dol_buildpath('/competence/productivite_user_fiche.php?id='.$obj->getId()."&fk_user=".$_REQUEST['fk_user']."&action=view",1), 'Fiche','fiche')
				);
				break;
				
		case 'chiffre_user':
				return array(
					array(dol_buildpath('/competence/productivite_user_indice.php?id='.$obj->getId()."&fk_user=".$_REQUEST['fk_user']."&fk_productivite=".$_REQUEST['fk_productivite']."&action=view",1), 'Fiche','fiche')
				);
				break;
		
	}
}
