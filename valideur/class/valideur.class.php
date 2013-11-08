<?php
/*
 * RH Gère les valideur d'un group et leur type
 */ 
class TRH_valideur_groupe extends TObjetStd {
	function __construct() { /* declaration */
		global $conf;
		$ATMdb=new TPDOdb;
		
		parent::set_table(MAIN_DB_PREFIX.'rh_valideur_groupe');
		parent::add_champs('type','type=chaine;');				//type de valideur
		parent::add_champs('nbjours','type=entier;');			//nbjours avant alerte
		parent::add_champs('montant','type=float;');			//montant avant alerte
		parent::add_champs('fk_user,fk_usergroup,entity,validate_himself,pointeur,level','type=entier;index;');	//utilisateur ou groupe concerné
		
		parent::_init_vars();
		parent::start();
		
		$this->TType = array(
			'NDFP'=>'Note de frais'
			,'Conges'=>'Conges'
		);
		
		$this->TValidate_himself=array(0=>'Non',1=>'Oui');
		$this->TValidate_pointeur=array(0=>'Non',1=>'Oui');
		
		$this->TGroup = array();
		
		// TODO AA arg encore un ! :(
		//chargement d'une liste de tous les utilisateurs
		$this->TUser = array();
		$sqlReq="SELECT rowid, firstname, lastname FROM ".MAIN_DB_PREFIX."user";
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TUser[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('firstname')." ".$ATMdb->Get_field('name');
			}
		
	}

	//chargement d'une liste de tous les groupes
	function loadListGroup(&$ATMdb,$user_id){
		$this->TGroup = array();
		$sqlReq="SELECT g.rowid AS 'rowid', g.nom AS 'nom'
			 FROM ".MAIN_DB_PREFIX."usergroup g, 
			".MAIN_DB_PREFIX."usergroup_user a
			 WHERE a.fk_usergroup=g.rowid AND a.fk_user=".$user_id;
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TGroup[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('nom');
			}
	}

	function save(&$db) {
		global $conf;
		$this->entity = $conf->entity;
		
		parent::save($db);
	}
}
