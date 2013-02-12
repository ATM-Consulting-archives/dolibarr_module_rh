<?php
class TRH_Contrat  extends TObjetStd {
	
	function __construct(){
		parent::set_table(MAIN_DB_PREFIX.'rh_contrat');
		parent::add_champs('libelle','type=chaine;');
		parent::add_champs('date','type=date;');
		parent::add_champs('duree, kilometrage','type=entier;');
		
		parent::add_champs('montant_HT, TVA','type=float;');
		
		//Un evenement est lié à une ressource et deux tiers (agence utilisatrice et fournisseur)
		//TODO fk_tier existe-t-il ?
		parent::add_champs('fk_tier,entity','type=entier;index;');
		parent::add_champs('fk_tier,entity','type=entier;index;');
		parent::add_champs('fk_rh_ressource,entity','type=entier;index;');
	}
	
	
}	
	
?>