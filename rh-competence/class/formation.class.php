<?php

class TRH_formation_plan extends TObjetStd {
	function __construct() {
		
		parent::set_table(MAIN_DB_PREFIX.'rh_formation_plan');
		parent::add_champs('date_debut,date_fin','type=date;');
		parent::add_champs('libelle','type=chaine');
		parent::add_champs('description','type=chaine');
		parent::add_champs('budget','type=float');
		parent::add_champs('entity','type=entier;');
		
		parent::_init_vars();
		parent::start();
	}
}

class TRH_formation_section extends TObjetStd {
	function __construct() {
		
		parent::set_table(MAIN_DB_PREFIX.'rh_formation_section');
		parent::add_champs('libelle','type=chaine');
		parent::add_champs('description','type=chaine');
		parent::add_champs('budget','type=float');
		parent::add_champs('fk_usergroup, fk_formation_plan','type=entier;');
		parent::add_champs('entity','type=entier;');
		
		parent::_init_vars();
		parent::start();
	}
}

class TRH_formation extends TObjetStd {
	function __construct() {
		
		parent::set_table(MAIN_DB_PREFIX.'rh_formation');
		parent::add_champs('libelle','type=chaine');
		parent::add_champs('description','type=chaine');
		parent::add_champs('budget','type=float');
		parent::add_champs('budget_consomme','type=float');
		parent::add_champs('fk_priorite, fk_formation_section','type=entier;');
		parent::add_champs('entity','type=entier;');
		
		parent::_init_vars();
		parent::start();
	}
}

class TRH_formation_type_priorite extends TObjetStd {
	function __construct() {
		
		parent::set_table(MAIN_DB_PREFIX.'rh_formation_type_priorite');
		parent::add_champs('libelle','type=chaine');
		
		parent::_init_vars();
		parent::start();
	}
}

class TRH_formation_session extends TObjetStd {
	function __construct() {
		
		parent::set_table(MAIN_DB_PREFIX.'rh_formation_session');
		parent::add_champs('date_debut,date_fin','type=date;');
		parent::add_champs('libelle','type=chaine');
		parent::add_champs('description','type=chaine');
		parent::add_champs('budget','type=float');
		parent::add_champs('budget_consomme','type=float');
		parent::add_champs('fk_formation','type=entier;');
		parent::add_champs('entity','type=entier;');
		
		parent::_init_vars();
		parent::start();
	}
}

class TRH_formation_participant extends TObjetStd {
	function __construct() {
		
		parent::set_table(MAIN_DB_PREFIX.'rh_formation_participant');
		parent::add_champs('description','type=chaine');
		parent::add_champs('fk_statut, fk_formation_session, fk_user, fk_responsable','type=entier;');
		parent::add_champs('entity','type=entier;');
		
		parent::_init_vars();
		parent::start();
	}
}

class TRH_formation_participant_statut extends TObjetStd {
	function __construct() {
		
		parent::set_table(MAIN_DB_PREFIX.'rh_formation_participant_statut');
		parent::add_champs('libelle','type=chaine');
		parent::add_champs('entity','type=entier;');
		
		parent::_init_vars();
		parent::start();
	}
}