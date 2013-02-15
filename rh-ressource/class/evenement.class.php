<?php


class TRH_Evenement  extends TObjetStd {
	
	function __construct(){
		parent::set_table(MAIN_DB_PREFIX.'rh_evenement');
		parent::add_champs('libelle','type=chaine;');
		parent::add_champs('date','type=date;');
		parent::add_champs('type, motif','type=chaine;');
		
		parent::add_champs('montant_HT, TVA','type=float;');
		
		//Un evenement est lié à une ressource et un utilisateur
		//TODO : fk_user ?
		parent::add_champs('fk_user,entity','type=entier;index;');
		parent::add_champs('fk_rh_ressource,entity','type=entier;index;');
	}
		
}	
	
	
/**
 * classe qui fait la liaison n-n entre un contrat et une ressource.
 */
class TRH_Ressource_Contrat  extends TObjetStd {
	
	function __construct(){
		parent::set_table(MAIN_DB_PREFIX.'rh_ressource_contrat');
		parent::add_champs('libelle','type=chaine;');
		
		parent::add_champs('fk_rh_contrat,entity','type=entier;index;');
		parent::add_champs('fk_rh_ressource,entity','type=entier;index;');
	}
		
}	
	
	?>