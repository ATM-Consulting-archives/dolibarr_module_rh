<?php

class TRH_Ressource extends TObjetStd {
	function __construct() { /* declaration */
		parent::set_table(MAIN_DB_PREFIX.'rh_ressource');
		parent::add_champs('libelle','type=chaine;');
		parent::add_champs('date_achat','type=date;');
		
		//types énuméré
		parent::add_champs('bail','type=entier;');
		parent::add_champs('statut','type=entier;');
		
		//clé étrangère : société
		parent::add_champs('fk_soc,entity','type=entier;index;');//fk_soc_leaser
		//clé étrangère : type de la ressource
		parent::add_champs('fk_rh_ressource_type','type=chaine;index;');
		
		parent::_init_vars();
		parent::start();
		
		$this->TField=array();
		
		$this->ressourceType=new TRH_Ressource_type;
	}
	
	function load(&$ATMdb, $id) {
		parent::load($ATMdb, $id);
		//load_ressource_type
		//$this->load_field($ATMdb);
		$this->load_ressource_type($ATMdb);
	}
	
	function load_ressource_type($ATMdb) {
		//on prend le type de ressource associé	
		$Tab = TRequeteCore::get_id_from_what_you_want($ATMdb, MAIN_DB_PREFIX.'rh_ressource_type',  array(getId()=>'fk_rh_ressource_type'));
		$this->ressourceType = $Tab[0];
		//on charge les champs associés au type.
		$this->ressourceType->load_field($ATMdb);
		$this->init_variables();
	}
	
	function init_variables() {
		foreach($this->ressourceType->TField as $field) {
			$this->add_champs($field->code);
		}		
		$this->_init_vars();
		
		foreach($this->TField as $field) {
			$this->{$field->code} = $field->valeur;
		}
	}
	
	function save(&$db) {
		parent::save($db);
	}
}


class TRH_Ressource_type extends TObjetStd {
	function __construct() { /* declaration */
		parent::set_table(MAIN_DB_PREFIX.'rh_ressource_type');
		parent::add_champs('libelle,code','type=chaine;');
		parent::add_champs('entity','type=entier;index;');
				
		parent::_init_vars();
		parent::start();
		$this->TField=array();
	}
	

	function load(&$ATMdb, $id) {
		parent::load($ATMdb, $id);
		$this->load_field($ATMdb);
	}
	
	function load_field(&$ATMdb) {
		$Tab = TRequeteCore::get_id_from_what_you_want($ATMdb, MAIN_DB_PREFIX.'rh_ressource_field', array('fk_rh_ressource_type'=>$this->getId()));
		$this->TField=array();
		foreach($Tab as $k=>$id) {
			$this->TField[$k]=new TRH_Ressource_field;
			$this->TField[$k]->load($ATMdb, $id);
		}
		
	}
	function addField($TNField) {
		$k=count($this->TField);
		$this->TField[$k]=new TRH_Ressource_field;
		$this->TField[$k]->set_values($TNField);
		
		return $k;
	}
	function save(&$db) {
		global $conf;
		
		$this->entity = $conf->entity;
		parent::save($db);
		
		foreach($this->TField as $field) {
			$field->fk_rh_ressource_type = $this->getId();
			$field->save($db);
		}
		
	}	
		
}

class TRH_Ressource_field extends TObjetStd {
	function __construct() { /* declaration */
		parent::set_table(MAIN_DB_PREFIX.'rh_ressource_field');
		parent::add_champs('code,libelle','type=chaine;');
		parent::add_champs('type','type=entier;');
		parent::add_champs('obligatoire','type=entier;');
		parent::add_champs('fk_rh_ressource_type','type=entier;index;');
		
		parent::_init_vars();
		parent::start();
		
	}
	
	function save(&$db) {
		global $conf;
		
		$this->entity = $conf->entity;
		parent::save($db);
	}
}
	
class TRH_Emprunt  extends TObjetStd {
	
	function __construct(){
		parent::set_table(MAIN_DB_PREFIX.'rh_emprunt');
		parent::add_champs('libelle','type=chaine;');
		
		//dates de l'emprunt
		parent::add_champs('date_debut','type=date;');
		parent::add_champs('date_fin','type=date;');
		
		//Un emprunt est un lien entre une ressource et un utilisateur
		//TODO : fk_user ?
		parent::add_champs('fk_user,entity','type=entier;index;');
		parent::add_champs('fk_rh_ressource,entity','type=entier;index;');
	}
	
	
}	

/*
 * Classes d'associations
 * 
 */

class TRH_Ressource_Import  extends TObjetStd {
	
	function __construct(){
		parent::set_table(MAIN_DB_PREFIX.'rh_association_ressourceimport');
		parent::add_champs('libelle','type=chaine;');
		
		parent::add_champs('fk_rh_import,entity','type=entier;index;');
		parent::add_champs('fk_rh_ressource,entity','type=entier;index;');
	}
	
}	
	
	