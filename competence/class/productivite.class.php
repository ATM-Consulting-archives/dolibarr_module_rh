<?php

class TRH_productivite extends TObjetStd {
	function __construct() { 
		
		parent::set_table(MAIN_DB_PREFIX.'rh_productivite');
		parent::add_champs('date_objectif','type=date;');
		parent::add_champs('objectif','type=float;');
		
		parent::add_champs('indice');
		
		parent::add_champs('entity','type=entier;');
		
		parent::_init_vars();
		parent::start();
	}

}


class TRH_productiviteUser extends TObjetStd {
	function __construct() { 
		
		parent::set_table(MAIN_DB_PREFIX.'rh_productivite_user');
		parent::add_champs('date_objectif','type=date;');
		parent::add_champs('indice');
		
		parent::add_champs('objectif','type=float;');
		
		
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
	
	static function existe_indice_user($id_productivite, $id_user) {
		
		global $db;
		
		$sql = "SELECT rowid ";
		$sql.= "FROM ".MAIN_DB_PREFIX."rh_productivite_user ";
		$sql.= "WHERE fk_productivite = ".$id_productivite." ";
		$sql.= "AND fk_user = ".$id_user;
		$resql = $db->query($sql);
		
		if($resql) {
			while($res = $db->fetch_object($resql))
				return true;
		}
		
		return false;
		
	}
	
	static function get_array_indices_user($id_user) {
			
		global $db;
		
		$TIndicesuser = array();
		
		$sql = "SELECT DISTINCT indice ";
		$sql.= "FROM ".MAIN_DB_PREFIX."rh_productivite_indice ";
		$sql.= "WHERE fk_user = ".$id_user;
		$resql = $db->query($sql);
		
		while($res = $db->fetch_object($resql)) {
			
			$TIndicesuser[] = $res->indice;
			
		}
		
		return $TIndicesuser;
		
	}

}


class TRH_productiviteIndice extends TObjetStd {
	function __construct() { 
		
		parent::set_table(MAIN_DB_PREFIX.'rh_productivite_indice');
		parent::add_champs('date_indice','type=date;');
		parent::add_champs('indice');
		
		parent::add_champs('chiffre_realise','type=float;');
			
		parent::add_champs('fk_user,fk_productivite,entity','type=entier;');
		
		parent::_init_vars();
		parent::start();
	}


}