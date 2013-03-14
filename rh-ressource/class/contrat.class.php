<?php
class TRH_Contrat  extends TObjetStd {
	
	function __construct(){
		global $conf;
		parent::set_table(MAIN_DB_PREFIX.'rh_contrat');
		parent::add_champs('libelle','type=chaine;');
		parent::add_champs('date_debut, date_fin','type=date;');
		//parent::add_champs('duree, kilometrage ','type=entier;');
		
		parent::add_champs('bail','type=chaine;');
		parent::add_champs('TVA','type=entier;');
		parent::add_champs('loyer_TTC','type=float;');
		
		//Un evenement est lié à une ressource et deux tiers (agence utilisatrice et fournisseur)
		parent::add_champs('fk_tier_utilisateur,entity','type=entier;index;');
		parent::add_champs('fk_rh_ressource_type','type=entier;index;');
		
		parent::_init_vars();
		parent::start();
		
		$this->TBail = array('bail'=>'Bail','immobilisation'=>'Immobilisation');
		
		$ATMdb=new Tdb;
		

		$this->TTypeRessource = array();
		$this->TAgence = array();
		$this->TTVA = array();
		$this->TRessource = array();
		
	}
	
	function load_liste(&$ATMdb){
		global $conf;
		//chargement des listes pour les combos
		
		$this->TTypeRessource = array();
		$sqlReq="SELECT rowid, libelle FROM ".MAIN_DB_PREFIX."rh_ressource_type WHERE entity=".$conf->entity;
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TTypeRessource[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('libelle');
			}
		
		//chargement d'une liste de touts les groupes
		$this->TAgence = array();
		$sqlReq="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup WHERE entity=".$conf->entity;
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TAgence[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('nom');
			}
		
		//chargement d'une liste de toutes les TVA
		$this->TTVA = array();
		$sqlReq="SELECT rowid, taux FROM ".MAIN_DB_PREFIX."c_tva";
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TTVA[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('taux');
			}
		
	}
	
	function save(&$db) {
		global $conf;
		$this->entity = $conf->entity;
		parent::save($db);
	}
	
	function delete(&$ATMdb){
		global $conf;
		//avant de supprimer le contrat, on supprime les liaisons contrat-ressource associés.
		$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_contrat_ressource WHERE entity=".$conf->entity."
		AND fk_rh_contrat=".$this->getId();
		$Tab = array();
		$temp = new TRH_Contrat_Ressource;
		$ATMdb->Execute($sql);
		while($ATMdb->Get_line()) {
			$Tab[] = $ATMdb->Get_field('rowid');
			}
		foreach ($Tab as $key => $id) {
			$temp->load($ATMdb, $id);
			$temp->delete($ATMdb);
		}
		
		
		parent::delete($ATMdb);
		
		
	}
	
	
}	
	
/*
 * Classes d'associations
 * 
 */

class TRH_Contrat_Ressource  extends TObjetStd {
	
	function __construct(){
		parent::set_table(MAIN_DB_PREFIX.'rh_contrat_ressource');
		parent::add_champs('commentaire','type=chaine;');
		
		parent::add_champs('fk_rh_contrat,entity','type=entier;index;');
		parent::add_champs('fk_rh_ressource','type=entier;index;');
		
		parent::_init_vars();
		parent::start();
	}
	
	function save(&$db) {
		global $conf;
		$this->entity = $conf->entity;
		parent::save($db);
	}
	
	
	
	
	
}	
	