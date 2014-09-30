<?php

class TRH_productivite extends TObjetStd {
	function __construct() { 
		
		parent::set_table(MAIN_DB_PREFIX.'rh_productivite');
		parent::add_champs('date_objectif','type=date;');
		parent::add_champs('indice','type=float;');
		
		parent::add_champs('label');
		
		parent::add_champs('entity','type=entier;');
		
		parent::_init_vars();
		parent::start();
	}

}


class TRH_productiviteUser extends TObjetStd {
	function __construct() { 
		
		parent::set_table(MAIN_DB_PREFIX.'rh_productivite_user');
		parent::add_champs('date_objectif','type=date;');
		parent::add_champs('indice','type=float;');
		
		parent::add_champs('objectif');
		
		
		parent::add_champs('fk_user,fk_productivite','type=entier;index;');
		
		parent::_init_vars();
		parent::start();
	}


	function load_by_user(&$ATMdb, $fk_user, $fk_productivite){
		$sql="SELECT rowid FROM ".$this->get_table()." 
		WHERE fk_user=".$fk_user." AND fk_productivite=".$fk_productivite;
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
		parent::add_champs('indice','type=float;');
			
		parent::add_champs('fk_user,fk_productivite,entity','type=entier;');
		
		parent::_init_vars();
		parent::start();
	}


}