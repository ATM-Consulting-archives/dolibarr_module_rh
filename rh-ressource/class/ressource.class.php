<?php

class TRH_Ressource extends TObjetStd {
	function __construct() { /* declaration */
		
		parent::set_table(MAIN_DB_PREFIX.'rh_ressource');
		parent::add_champs('type,libelle','type=chaine;');
		parent::add_champs('date_achat','type=date;');
		
		//types énuméré
		parent::add_champs('bail','type=entier;');
		parent::add_champs('statut','type=entier;');
		
		//clé étrangères
		parent::add_champs('fk_soc,entity','type=entier;index;');//fk_soc_leaser
		
		parent::_init_vars();
		parent::start();
		
		$this->TField=array();
		
	}
	
	function load(&$ATMdb, $id) {
		
		parent::load($ATMdb, $id);
		
		$this->load_field($ATMdb);
		
	}
	
	function load_field(&$ATMdb) {

		$Tab = TRequeteCore::get_id_from_what_you_want(&$db, MAIN_DB_PREFIX.'rh_ressource_field', array('fk_rh_ressource'=>$this->getId()));
		foreach($Tab as $k=>$id) {
			$this->TField[$k]=new TRH_Ressource_field;
			$this->TField[$k]->load($ATMdb, $id);
			
		}
		
		$this->init_variables();
	}
	function init_variables() {
		foreach($this->TField as $field) {
			$this->{$field->nom} = $field->valeur;
			
		}		
		
	}
	function save(&$ATMdb) {
		parent::save($db);
		
		$this->save_field($ATMdb);
	}
	function save_field(&$ATMdb) {
		foreach($this->TField as &$field) {
			$field->fk_rh_ressource = $this->getId();
			$field->valeur = $this->{$field->nom};
			$field->save($ATMdb);
		}
	}
}

class TRH_Ressource_field extends TObjetStd {
	function __construct() { /* declaration */
		
		parent::set_table(MAIN_DB_PREFIX.'rh_ressource_field');
		parent::add_champs('type,nom,valeur','type=chaine;');
		parent::add_champs('fk_rh_ressource','type=entier;index;');//fk_soc_leaser
		
		parent::_init_vars();
		parent::start();
		
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
	
	