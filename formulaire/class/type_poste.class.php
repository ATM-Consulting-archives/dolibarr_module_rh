<?php

class TRH_fichePoste extends TObjetStd {
	function __construct() { 
		
		parent::set_table(MAIN_DB_PREFIX.'rh_fiche_poste');
		parent::add_champs('type_poste','type=chaine');
		parent::add_champs('numero_convention,descriptif','type=chaine');
		
		parent::add_champs('entity','type=entier;');
		
		parent::_init_vars();
		parent::start();
		
		$this->setChild('TRH_grilleSalaire','fk_type_poste');
		
	}

}

class TRH_grilleSalaire extends TObjetStd {
	function __construct() { 
		
		parent::set_table(MAIN_DB_PREFIX.'rh_grille_salaire');
		parent::add_champs('nb_annees_anciennete,fk_type_poste','type=chaine');
		parent::add_champs('montant','type=chaine');
		
		parent::add_champs('entity','type=entier;');
		
		parent::_init_vars();
		parent::start();
	}

}