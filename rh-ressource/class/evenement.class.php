<?php
class TRH_Evenement  extends TObjetStd {
	
	function __construct(){
		parent::set_table(MAIN_DB_PREFIX.'rh_evenement');
		parent::add_champs('date_debut, date_fin','type=date;');
		
		parent::add_champs('fk_rh_ressource','type=entier;index;');
		//parent::add_champs('fk_rh_ressource_type','type=entier;index;');	
		parent::add_champs('fk_user,entity','type=entier;index;');

		//pour le wdCalendar
		parent::add_champs('color','type=chaine;');
		parent::add_champs('isAllDayEvent','type=entier;');
		parent::add_champs('location','type=chaine;');
		parent::add_champs('subject','type=chaine;');
		parent::add_champs('description','type=chaine;');
		parent::add_champs('recurringrule','type=chaine;');
		
		//pour un évenement
		parent::add_champs('type, motif','type=chaine;');
		parent::add_champs('montant_HT, TVA','type=float;');
		

		parent::_init_vars();
		parent::start();

		
		
		
		$ATMdb=new Tdb;
		//chargement d'une liste de tout les types de ressources
		/*$this->TTypeRessource = array();
		$sqlReq="SELECT rowid, libelle FROM ".MAIN_DB_PREFIX."rh_ressource_type";
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TTypeRessource[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('libelle');
			} 
		*/
		//chargement d'une liste de touts les tiers (pour le combo "Utilisateur")
		$this->TUser = array();
		$sqlReq="SELECT rowid, name FROM ".MAIN_DB_PREFIX."user";
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TUser[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('name');
			}
		
		//chargement d'une liste de toutes les ressources selon le type choisi
		/*$this->TRessource = array();
		$sqlReq="SELECT rowid,libelle FROM ".MAIN_DB_PREFIX."rh_ressource WHERE fk_rh_ressource_type=".$this->fk_rh_ressource_type."";
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TRessource[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('libelle');
			}*/
		
			
	}
	function load(&$db, $id){
		parent::load($db, $id);
		//chargement d'une liste de toutes les ressources selon le type choisi
		/*$sqlReq="SELECT rowid,libelle FROM ".MAIN_DB_PREFIX."rh_ressource where fk_rh_ressource_type=".$this->fk_rh_ressource_type."";
		$this->TRessource = array();
		$db->Execute($sqlReq);
		while($db->Get_line()) {
			$this->TRessource[$db->Get_field('rowid')] = $db->Get_field('libelle');
			}
		*/
	}
	
	function save(&$db) {
		global $conf;
		$this->entity = $conf->entity;
		
		parent::add_champs('color','type=chaine;');
		parent::add_champs('isAllDayEvent','type=entier;');
		parent::add_champs('location','type=chaine;');
		parent::add_champs('subject','type=chaine;');
		parent::add_champs('description','type=chaine;');
		parent::add_champs('recurringrule','type=chaine;');
	
		$this->color = 1;
		$this->isAllDayEvent = 1;
		$this->location = "nowhere";
		$this->subject = "Emprunt de la ressource";
		$this->description = "pdpom";
		
		
		
		parent::save($db);
	}
	
}	