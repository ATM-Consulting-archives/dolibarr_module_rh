<?php
class TRH_Ressource_Regle  extends TObjetStd {
	
	function __construct(){
		parent::set_table(MAIN_DB_PREFIX.'rh_ressource_regle');
		
		parent::add_champs('choixApplication','type=chaine;');
		parent::add_champs('choixLimite','type=chaine;');
		
		//valeurs
		parent::add_champs('duree','type=entier;');
		parent::add_champs('dureeInt','type=entier;');
		parent::add_champs('dureeExt','type=entier;');
		parent::add_champs('natureDeduire','type=chaine;');
		parent::add_champs('montantDeduire','type=float;');
		parent::add_champs('dataIllimite','type=chaine;'); //booléen
		parent::add_champs('dataIphone','type=chaine;'); //booléen
		parent::add_champs('mailforfait','type=chaine;'); //booléen
		parent::add_champs('smsIllimite','type=chaine;'); //booléen
		parent::add_champs('data15Mo','type=chaine;'); //booléen
		parent::add_champs('carteJumelle','type=chaine;'); //booléen
		parent::add_champs('numeroExclus','type=chaine;');
		
		parent::add_champs('fk_user','type=entier;');
		parent::add_champs('fk_usergroup','type=entier;');
		parent::add_champs('fk_rh_ressource_type, entity','type=entier;index;');
		
		$this->choixApplication = 'all';
		$this->choixLimite = 'extint';
		$this->TUser = array();
		$this->TGroup  = array();
		$this->TChoixLimite = array(
			'gen'=>'Générale'
			,'extint'=>'Interne/Externe'
		);
		$this->TChoixApplication = array(
			'all'=>'Tous'
			,'group'=>'Groupe'
			,'user'=>'Utilisateur'
		);
		
		parent::_init_vars();
		parent::start();
	}
	
	function load_liste(&$ATMdb){
		global $conf;
		
		//LISTE DE GROUPES
		$this->TGroup  = array();
		$sqlReq="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup WHERE entity=".$conf->entity;
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TGroup[$ATMdb->Get_field('rowid')] = htmlentities($ATMdb->Get_field('nom'), ENT_COMPAT , 'ISO8859-1');
			}
		
		//LISTE DE USERS
		$this->TUser = array();
		$sqlReq="SELECT rowid, firstname, name FROM ".MAIN_DB_PREFIX."user WHERE entity=".$conf->entity;
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TUser[$ATMdb->Get_field('rowid')] = htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1')." ".htmlentities($ATMdb->Get_field('name'), ENT_COMPAT , 'ISO8859-1');
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
			case 'all':$this->fk_user = 0;$this->fk_usergroup=0;break;
			case 'user':$this->fk_usergroup = NULL;break;
			case 'group':$this->fk_user = NULL;break;
			default : break;				
		}
		
		switch ($this->choixLimite){
			case 'gen':
				$this->dureeInt = 0;
				$this->dureeExt = 0;
				break;
			case 'extint':
				$this->duree = 0;
				break;
			default : break;
		}
		
		parent::save($ATMdb);
	}
	
	
}	



