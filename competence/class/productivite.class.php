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
	
	static function get_key_val_fields() {
			
		global $db;
		
		$TIndices = array(
						0=>""
						,"login"=>"login"
						,'date_indice'=>'date_indice'
						);
		
		$sql = "SELECT rowid, indice ";
		$sql.= "FROM ".MAIN_DB_PREFIX."rh_productivite ";
		$resql = $db->query($sql);
		
		while($res = $db->fetch_object($resql)) {
			
			$TIndices[$res->rowid] = $res->indice;
			
		}
		
		return $TIndices;
		
	}
	
	static function get_libelle_indice($id_indice) {
		
		global $db;
		
		$sql = "SELECT indice ";
		$sql.= "FROM ".MAIN_DB_PREFIX."rh_productivite ";
		$sql.= "WHERE rowid = ".$id_indice;
		
		$resql = $db->query($sql);
		
		$res = $db->fetch_object($resql);
		
		return $res->indice;
		
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
	
	function delete(&$ATMdb, $fk_productivite) {
		
		// On récupère les chiffres réalisés par cet utilisateur sur cet indice pour les supprimer
		$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_productivite_indice 
		WHERE fk_user=".$this->fk_user." AND fk_productivite=".$fk_productivite;
		
		$TChiffres_user = TRequeteCore::get_keyval_by_sql($ATMdb, $sql, 'rowid', 'rowid');
		
		$ATMdb->Execute($sql);
		foreach ($TChiffres_user as $id_chiffre) {
			
			$obj = new TRH_productiviteIndice;
			$obj->load($ATMdb, $id_chiffre);
			$obj->delete($ATMdb);
			
		}
		
		parent::delete($ATMdb);
		
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
	
	function existeIndice() {
		
		global $db;
		
		$sql = 'SELECT rowid
				FROM '.MAIN_DB_PREFIX.'rh_productivite_indice
				WHERE fk_user = '.$this->fk_user.'
				AND fk_productivite = '.$this->fk_productivite.'
				AND MONTH(date_indice) = '.date('m', $this->date_indice).'
				AND YEAR(date_indice) = '.date('Y', $this->date_indice).'
				LIMIT 1';
		
		$resql = $db->query($sql);
		$res = $db->fetch_object($resql);
		
		return ($res->rowid > 0);
		
	}

	function save(&$ATMdb) {
		
		if(!$this->existeIndice()) parent::save($ATMdb);
		
	}

}