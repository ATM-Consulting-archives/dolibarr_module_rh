<?php
class TRH_Contrat  extends TObjetStd {
	
	function __construct(){
		parent::set_table(MAIN_DB_PREFIX.'rh_contrat');
		parent::add_champs('libelle','type=chaine;');
		parent::add_champs('date','type=date;');
		parent::add_champs('duree, kilometrage','type=entier;');
		
		parent::add_champs('bail','type=chaine;');
		parent::add_champs('montant_HT, TVA','type=float;');
		
		//Un evenement est lié à une ressource et deux tiers (agence utilisatrice et fournisseur)
		parent::add_champs('fk_tier_utilisateur,entity','type=entier;index;');
		parent::add_champs('fk_tier_fournisseur,entity','type=entier;index;');
		parent::add_champs('fk_rh_ressource,entity','type=entier;index;');
		
		parent::_init_vars();
		parent::start();
		
	}
	
	
}	
	
/*
 * Classes d'associations
 * 
 */

class TRH_Contrat_Ressource  extends TObjetStd {
	
	function __construct(){
		parent::set_table(MAIN_DB_PREFIX.'rh_association_ressourceimport');
		parent::add_champs('commentaire','type=chaine;');
		
		parent::add_champs('fk_rh_contrat,entity','type=entier;index;');
		parent::add_champs('fk_rh_ressource,entity','type=entier;index;');
		
		parent::_init_vars();
		parent::start();
	}
	
}	
	