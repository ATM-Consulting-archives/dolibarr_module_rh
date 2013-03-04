<?php
class TRH_Contrat  extends TObjetStd {
	
	function __construct(){
		parent::set_table(MAIN_DB_PREFIX.'rh_contrat');
		parent::add_champs('libelle','type=chaine;');
		parent::add_champs('date_debut, date_fin','type=date;');
		//parent::add_champs('duree, kilometrage ','type=entier;');
		
		parent::add_champs('bail','type=chaine;');
		parent::add_champs('TVA','type=entier;');
		parent::add_champs('loyer_TTC','type=float;');
		
		//Un evenement est lié à une ressource et deux tiers (agence utilisatrice et fournisseur)
		parent::add_champs('fk_tier_utilisateur,entity','type=entier;index;');
		parent::add_champs('fk_tier_fournisseur','type=entier;index;');
		parent::add_champs('fk_rh_ressource','type=entier;index;');
		
		parent::_init_vars();
		parent::start();
		
		$this->TBail = array('immo'=>'Immo', 'location'=>'Location');
		
		$ATMdb=new Tdb;
		
		//chargement d'une liste de tout les types de ressources
		$this->TTypeRessource = array();
		$sqlReq="SELECT rowid, libelle FROM ".MAIN_DB_PREFIX."rh_ressource_type";
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TTypeRessource[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('libelle');
			}
		
		
		//chargement d'une liste de touts les tiers (pour le combo "tiers")
		$this->TTiers = array();
		$sqlReq="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."societe";
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TTiers[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('nom');
			}
		
		//chargement d'une liste de touts les tiers (pour le combo "tiers")
		$this->TAgence = array();
		$sqlReq="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup";
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TAgence[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('nom');
			}
		
		//chargement d'une liste de touts les tiers (pour le combo "tiers")
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
	
}	
	
/*
 * Classes d'associations
 * 
 */

class TRH_Contrat_Ressource  extends TObjetStd {
	
	function __construct(){
		parent::set_table(MAIN_DB_PREFIX.'rh_association_ressourceimport');
		parent::add_champs('commentaire','type=chaine;');
		
		parent::add_champs('fk_rh_contrat,entity','type=entier;index;');
		parent::add_champs('fk_rh_ressource,entity','type=entier;index;');
		
		parent::_init_vars();
		parent::start();
	}
	
}	
	