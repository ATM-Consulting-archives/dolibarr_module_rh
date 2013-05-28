<?php
/*
 * RH Gère les codes analytiques des utilisateurs
 */ 
class TRH_analytique_user extends TObjetStd {
	function __construct() { /* declaration */
		global $conf;
		$ATMdb=new Tdb;
		
		parent::set_table(MAIN_DB_PREFIX.'rh_analytique_user');
		parent::add_champs('fk_code','type=entier;');
		parent::add_champs('pourcentage','type=entier;');
		parent::add_champs('fk_user,entity','type=entier;index;');
		
		parent::_init_vars();
		parent::start();
		
		$this->TAnalytiqueUser = array();
		
		$this->TAnalytique = array();
		
	}
	
	function loadListAnalytique(&$ATMdb){
		$this->TAnalytique = array();
		$sqlReq="SELECT rowid, code FROM ".MAIN_DB_PREFIX."rh_analytique";
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TAnalytique[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('code');
			}
	}

	function loadListAnalytiqueUser(&$ATMdb, $user_id){
		$this->TAnalytiqueUser = array();
		$sqlReq="SELECT a.code AS 'rowid', a.code AS 'code'
			 FROM ".MAIN_DB_PREFIX."rh_analytique as a,
				LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (u.rowid=a.fk_user)
			 WHERE u.rowid=".$user_id;
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TAnalytiqueUser[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('code');
			}
	}

	function save(&$db) {
		global $conf;
		$this->entity = $conf->entity;
		
		parent::save($db);
	}
}
