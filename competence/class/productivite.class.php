<?php

class TRH_productivite extends TObjetStd {
	function __construct() { 
		
		parent::set_table(MAIN_DB_PREFIX.'rh_productivite');
		parent::add_champs('date_objectif','type=date;');
		parent::add_champs('objectifIndice1,objectifIndice2,objectifIndice3','type=float;');
		
		parent::add_champs('objectif');
		
		
		parent::add_champs('fk_user','type=entier;');
		parent::add_champs('entity','type=entier;');
		
		parent::_init_vars();
		parent::start();
	}


	function load_by_user(&$ATMdb, $fk_user){
		$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_productivite 
		WHERE fk_user=".$fk_user;
		$ATMdb->Execute($sql);
		if ($ATMdb->Get_line()) {
			return $this->load($ATMdb, $ATMdb->Get_field('rowid'));
		}
		return false;
		
	}

}


class TRH_productiviteIndice extends TObjetStd {
	function __construct() { 
		
		parent::set_table(MAIN_DB_PREFIX.'rh_productivite_indice');
		parent::add_champs('date_indice','type=date;');
		parent::add_champs('indice1,indeice2,indice3','type=float;');
			
		parent::add_champs('fk_user,fk_productivite,entity','type=entier;');
		
		parent::_init_vars();
		parent::start();
	}


	function load_by_user(&$ATMdb, $fk_user){
		$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_productivite 
		WHERE fk_user=".$fk_user;
		$ATMdb->Execute($sql);
		if ($ATMdb->Get_line()) {
			return $this->load($ATMdb, $ATMdb->Get_field('rowid'));
		}
		return false;
		
	}

}