<?php

class TRH_fichePoste extends TObjetStd {
	function __construct() { 
		
		parent::set_table(MAIN_DB_PREFIX.'rh_fiche_poste');
		parent::add_champs('type_poste','type=chaine');
		parent::add_champs('numero_convention','type=chaine;');
		parent::add_champs('descriptif', 'type=text;');
		
		parent::add_champs('entity','type=entier;');
		
		parent::_init_vars();
		parent::start();
		
		$this->setChild('TRH_grilleSalaire','fk_type_poste');
		
	}

}

class TRH_grilleSalaire extends TObjetStd {
	function __construct() { 
		
		parent::set_table(MAIN_DB_PREFIX.'rh_grille_salaire');
		parent::add_champs('fk_type_poste','type=chaine;');
		parent::add_champs('salaire_min,salaire_max,salaire_conventionnel,salaire_constate','type=float;');
		
		parent::add_champs('entity,nb_annees_anciennete','type=entier;');
		
		parent::_init_vars();
		parent::start();
	}

}