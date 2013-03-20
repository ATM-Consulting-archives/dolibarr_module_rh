<?php
class TRH_Ressource_Regle  extends TObjetStd {
	
	function __construct(){
		parent::set_table(MAIN_DB_PREFIX.'rh_ressource_regle');
		
		parent::add_champs('objet','type=chaine;');
		parent::add_champs('choixApplication','type=chaine;');
		parent::add_champs('periode','type=chaine;');
		parent::add_champs('dureeH','type=chaine;');
		parent::add_champs('dureeM','type=chaine;');
		
		parent::add_champs('fk_user','type=entier;index;');
		parent::add_champs('fk_usergroup','type=entier;index;');
		parent::add_champs('fk_rh_ressource_type, entity','type=entier;index;');
		
		parent::_init_vars();
		parent::start();
		
		$this->choixApplication = 'all';
		
		$this->TPeriode = array('mois'=>'Mois', 'annee'=>'Année');
		$this->TObjet = array('limInterne'=>'Limite interne', 'limExterne'=>'Limite externe');
		$this->TUser = array();
		$this->TGroup  = array();
		$this->TChoixApplication = array(
			'all'=>'Tous'
			,'group'=>'Groupe'
			,'user'=>'Utilisateur'
		);
	}
	
	function load_liste(&$ATMdb){
		global $conf;
		
		//chargement d'une liste de toutes les types de ressources
		/*$this->TRessourceType = array();
		$sqlReq="SELECT rowid,libelle FROM ".MAIN_DB_PREFIX."rh_ressource_type WHERE entity=".$conf->entity;
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TRessourceType[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('libelle');
			}*/
		
		//LISTE DE GROUPES
		$this->TGroup  = array();
		$sqlReq="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup WHERE entity=".$conf->entity;
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TGroup[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('nom');
			}
		
		//LISTE DE USERS
		$this->TUser = array();
		$sqlReq="SELECT rowid, firstname, name FROM ".MAIN_DB_PREFIX."user WHERE entity=".$conf->entity;
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TUser[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('firstname').' '.$ATMdb->Get_field('name');
			}
		
	}
	
	function load(&$ATMdb, $id) {
		//global $conf;
		parent::load($ATMdb, $id);
	}
	
	
	
	function save(&$ATMdb) {
		global $conf;
		$this->entity = $conf->entity;
		
		switch ($this->choixApplication){
			case 'all':$this->fk_user = NULL;$this->fk_usergroup=NULL;break;
			case 'user':$this->fk_usergroup = NULL;break;
			case 'group':$this->fk_user = NULL;break;
			default : echo'pbchoixapplication';break;				
		}
		$this->dureeM = substr($this->dureeM,0, 2);
		$this->dureeH = substr($this->dureeH,0, 2);
		parent::save($ATMdb);
	}
	
	
	
	
	
}	