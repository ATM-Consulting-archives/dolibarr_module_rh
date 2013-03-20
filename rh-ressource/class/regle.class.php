<?php
class TRH_Ressource_Regle  extends TObjetStd {
	
	function __construct(){
		parent::set_table(MAIN_DB_PREFIX.'rh_ressource_regle');
		
		parent::add_champs('objet','type=chaine;');
		parent::add_champs('montant','type=chaine;');
		
		parent::add_champs('duree','type=chaine;');
		
		parent::add_champs('fk_user','type=entier;index;');
		parent::add_champs('fk_group','type=entier;index;');
		parent::add_champs('fk_rh_ressource_type, entity','type=entier;index;');
		
		parent::_init_vars();
		parent::start();
		
		$this->TDuree = array('Mois'=>'mois');
		$this->TObjet = array('limInterne'=>'Limite interne', 'limExterne'=>'Limite externe');
		$this->TUser = array('user1');
		$this->TGroup  = array('groupe1');
		$this->TRessourceType = array();
	}
	
	
	function load(&$ATMdb, $id) {
		global $conf;
		parent::load($ATMdb, $id);

		//chargement d'une liste de toutes les ressources (pour le combo "ressource associé")
		$sqlReq="SELECT rowid,libelle FROM ".MAIN_DB_PREFIX."rh_ressource_type WHERE entity=".$conf->entity;
		$this->TRessourceType = array();
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TRessourceType[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('libelle');
			}	
	}
	
	
	
	function save(&$ATMdb) {
		global $conf;
		$this->entity = $conf->entity;
		parent::save($ATMdb);
	}
	
	
	
	
	
}	