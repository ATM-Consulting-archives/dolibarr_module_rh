<?php
/*
 * RH Gère les codes analytiques des utilisateurs
 */ 
class TRH_analytique_user extends TObjetStd {
	function __construct() { /* declaration */
		global $conf;
		
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
	
	static function getUserAnalytique(&$ATMdb, $fk_user) {
		$sql= "SELECT rowid, code, pourcentage
		FROM ".MAIN_DB_PREFIX."rh_analytique_user
			WHERE fk_user = ".$fk_user;
			
		$ATMdb->Execute($sql);
		
		return $ATMdb->Get_All();	
	}
}
