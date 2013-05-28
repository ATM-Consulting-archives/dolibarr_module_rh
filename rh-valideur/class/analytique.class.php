<?php
/*
 * RH Gère les codes analytiques
 */ 
class TRH_analytique extends TObjetStd {
	function __construct() { /* declaration */
		global $conf;
		$ATMdb=new Tdb;
		
		parent::set_table(MAIN_DB_PREFIX.'rh_analytique');
		parent::add_champs('code','type=chaine;');
		parent::add_champs('entity','type=entier;index;');
		
		parent::_init_vars();
		parent::start();
	}

	function save(&$db) {
		global $conf;
		$this->entity = $conf->entity;
		
		parent::save($db);
	}
}
