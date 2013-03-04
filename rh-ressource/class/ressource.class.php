<?php

class TRH_Ressource extends TObjetStd {
	function __construct() { /* declaration */
		parent::set_table(MAIN_DB_PREFIX.'rh_ressource');
		parent::add_champs('libelle','type=chaine;');
		parent::add_champs('date_achat','type=date;');
		
		//types énuméré
		parent::add_champs('bail','type=chaine;');
		parent::add_champs('statut','type=chaine;');
		
		//clé étrangère : société
		parent::add_champs('fk_soc,entity','type=entier;index;');//fk_soc_leaser
		//clé étrangère : type de la ressource
		parent::add_champs('fk_rh_ressource_type','type=chaine;index;');
		//clé étrangère : ressource associé
		parent::add_champs('fk_rh_ressource','type=entier;index;');
		
		parent::_init_vars();
		parent::start();
		
		$this->TField=array();
		$this->ressourceType=new TRH_Ressource_type;

		$ATMdb=new Tdb;
		
		$Tab = TRequeteCore::get_id_from_what_you_want($ATMdb, MAIN_DB_PREFIX.'rh_ressource_type', array());
		
		//chargement d'une liste de tout les types de ressources
		$temp = new TRH_Ressource_type;
		$this->TType = array();
		foreach($Tab as $k=>$id){
			$temp->load($ATMdb, $id);
			$this->TType[$temp->getId()] = $temp->libelle;
		}
		$this->TBail = array('bail'=>'Bail','immo'=>'Immo');
		$this->TStatut = array('nonattribuée'=>'Non attribuée','attribuée'=>'Attribuée');
		
		$this->TRessource = array('');
		
		}
	
	function load(&$ATMdb, $id) {
		parent::load($ATMdb, $id);
		$this->load_ressource_type($ATMdb);
		
		//chargement d'une liste de toutes les ressources (pour le combo "ressource associé")
		$sqlReq="SELECT rowid,libelle FROM ".MAIN_DB_PREFIX."rh_ressource where rowid!=".$this->getId();
		$ATMdb->Execute($sqlReq);
		
		while($ATMdb->Get_line()) {
			
			$this->TRessource[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('libelle');
			}
		
	}
	
	function load_ressource_type(&$ATMdb) {
		//on prend le type de ressource associé
		$Tab = TRequeteCore::get_id_from_what_you_want($ATMdb, MAIN_DB_PREFIX.'rh_ressource_type', array('rowid'=>$this->fk_rh_ressource_type));
		$this->ressourceType->load($ATMdb, $Tab[0]);
		$this->fk_rh_ressource_type = $this->ressourceType->getId();
		
		//on charge les champs associés au type.
		$this->init_variables($ATMdb);
		
	}
	
	function init_variables(&$ATMdb) {
		foreach($this->ressourceType->TField as $field) {
			$this->add_champs($field->code);
		}
		$this->init_db_by_vars($ATMdb);
		parent::load($ATMdb, $this->getId());
	}
	
	function save(&$db) {
		global $conf;
		$this->entity = $conf->entity;
		
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
		$this->TType=array('chaine'=>'Texte','entier'=>'Entier','float'=>'Float',"liste"=>'Liste',"checkbox"=>'Case à cocher');
	}
	

	function load(&$ATMdb, $id) {
		parent::load($ATMdb, $id);
		$this->load_field($ATMdb);
	}
	
	/**
	 * Renvoie true si ce type est utilisé par une des ressources.
	 */
	function isUsedByRessource(&$ATMdb){
		$Tab = TRequeteCore::get_id_from_what_you_want($ATMdb, MAIN_DB_PREFIX.'rh_ressource', array('fk_rh_ressource_type'=>$this->getId()));
		$taille = count($Tab);
		if ($taille>0) return true;
		return false;

	}
	
	function load_field(&$ATMdb) {
		$sqlReq="SELECT rowid FROM llx_rh_ressource_field WHERE fk_rh_ressource_type=".$this->getId()." ORDER BY ordre ASC;";
		$ATMdb->Execute($sqlReq);
		
		$Tab = array();
		while($ATMdb->Get_line()) {
			$Tab[]= $ATMdb->Get_field('rowid');
		}
		
		$this->TField=array();
		foreach($Tab as $k=>$id) {
			$this->TField[$k]=new TRH_Ressource_field;
			$this->TField[$k]->load($ATMdb, $id);
		}
	}
	
	function addField(&$ATMdb, $TNField) {
		$k=count($this->TField);
		$this->TField[$k]=new TRH_Ressource_field;
		$this->TField[$k]->set_values($TNField);
		
		$p=new TRH_Ressource;				
		$p->add_champs($TNField['code'] ,"type='".$TNField['type']."'" );
		$p->init_db_by_vars($ATMdb);
					
		return $k;
	}
	
	function delField(&$ATMdb, $id){
		$toDel = new TRH_Ressource_field;
		$toDel->load($ATMdb,$id);
		$toDel->delete($ATMdb);
	}
	
	function save(&$db) {
		global $conf;
		
		$this->entity = $conf->entity;
		$this->code = TRH_Ressource_type::code_format(empty($this->code) ? $this->libelle : $this->code);
		
		parent::save($db);
		
		foreach($this->TField as $field) {
			$field->fk_rh_ressource_type = $this->getId();
			$field->save($db);
		}
		
	}	
	
	static function code_format($s){
		$r=""; $s = strtolower($s);
		$nb=strlen($s);
		for($i = 0; $i < $nb; $i++){
			if(ctype_alnum($s[$i])){
				$r.=$s[$i];			
			}
		} // for
		return $r;
	}
		
}

class TRH_Ressource_field extends TObjetStd {
	function __construct() { /* declaration */
		parent::set_table(MAIN_DB_PREFIX.'rh_ressource_field');
		parent::add_champs('code,libelle','type=chaine;');
		parent::add_champs('type','type=chaine;');
		parent::add_champs('obligatoire','type=entier;');
		parent::add_champs('ordre','type=entier');
		parent::add_champs('fk_rh_ressource_type','type=entier;index;');
		
		parent::_init_vars();
		parent::start();
		
	}
	
	function save(&$db) {
		global $conf;
		
		$this->code = TRH_Ressource_type::code_format(empty($this->code) ? $this->libelle : $this->code);
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
	
	
