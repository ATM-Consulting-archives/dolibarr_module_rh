<?php
/*
 * RH Gère les codes analytiques des utilisateurs
 */ 
class TRH_analytique_user extends TObjetStd {
	function __construct() { /* declaration */
		global $conf;
		$ATMdb=new Tdb;
		
		parent::set_table(MAIN_DB_PREFIX.'rh_analytique_user');
		parent::add_champs('code','type=chaine;');
		parent::add_champs('pourcentage','type=entier;');
		parent::add_champs('fk_user,entity','type=entier;index;');
		
		parent::_init_vars();
		parent::start();
		
		$this->pourcentage=100;
		
	}

	function save(&$db) {
		global $conf;
		$this->entity = $conf->entity;
		
		parent::save($db);
	}
}
