<?php
/*
 * RH Gère les valideur d'un group et leur type
 */ 
class TRH_valideur_groupe extends TObjetStd {
	function __construct() { /* declaration */
		global $conf;
		$ATMdb=new Tdb;
		
		parent::set_table(MAIN_DB_PREFIX.'rh_valideur_groupe');
		parent::add_champs('type','type=chaine;');				//type de valideur
		parent::add_champs('nbjours','type=entier;');			//nbjours avant alerte
		parent::add_champs('fk_user,fk_usergroup,entity','type=entier;index;');	//utilisateur ou groupe concerné
		
		parent::_init_vars();
		parent::start();
		
		$this->TType = array(
			'NDFP'=>'Note de frais'
			,'Ressource'=>'Ressources'
		);
		
		//chargement d'une liste de tous les groupes
		$this->TGroup = array();
		$sqlReq="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup";
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TGroup[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('nom');
			}
		
		//chargement d'une liste de tous les utilisateurs
		$this->TUser = array();
		$sqlReq="SELECT rowid, firstname, name FROM ".MAIN_DB_PREFIX."user";
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TUser[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('firstname')." ".$ATMdb->Get_field('name');
			}
		
	}

	function save(&$db) {
		global $conf;
		$this->entity = $conf->entity;
		
		parent::save($db);
	}
}
