<?php
class TRH_Evenement  extends TObjetStd {
	
	function __construct(){
		parent::set_table(MAIN_DB_PREFIX.'rh_evenement');
		parent::add_champs('date_debut, date_fin','type=date;');
		
		parent::add_champs('fk_rh_ressource','type=entier;index;');	
		parent::add_champs('fk_user,entity','type=entier;index;');
		parent::add_champs('fk_tier,entity','type=entier;index;');
		
		//type : accident, répération, ou emprunt
		parent::add_champs('type','type=chaine;');

		//pour le wdCalendar
		parent::add_champs('color','type=chaine;');
		parent::add_champs('isAllDayEvent','type=entier;');
		parent::add_champs('location','type=chaine;');
		parent::add_champs('subject','type=chaine;');
		parent::add_champs('description','type=chaine;');
		parent::add_champs('recurringrule','type=chaine;');
		
		//pour un évenement
		parent::add_champs('motif','type=chaine;');
		parent::add_champs('coutHT','type=float;');
		parent::add_champs('coutEntrepriseHT','type=float;');
		parent::add_champs('TVA','type=entier;');
		
		parent::_init_vars();
		parent::start();

		
		$this->TType = array('accident'=>'Accident', 'reparation'=>'Réparation');
		
		$ATMdb=new Tdb;
		
		//chargement d'une liste de touts les tiers (pour le combo "tiers")
		$this->TTiers = array();
		$sqlReq="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."societe";
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TTiers[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('nom');
			}
		
		//chargement d'une liste de touts les tiers (pour le combo "agence utilisatrice")
		$this->TAgence = array();
		$sqlReq="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup";
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TAgence[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('nom');
			}
		global $conf;
		//chargement d'une liste de touts les TVA (pour le combo "TVA")
		$this->TTVA = array();
		$sqlReq="SELECT rowid, taux FROM ".MAIN_DB_PREFIX."c_tva WHERE fk_pays=".$conf->global->MAIN_INFO_SOCIETE_PAYS[0];
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TTVA[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('taux');
			}
		
		//chargement d'une liste de touts les users (pour le combo "Utilisateur")
		$this->TUser = array();
		$sqlReq="SELECT rowid, firstname, name FROM ".MAIN_DB_PREFIX."user";
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TUser[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('firstname').' '.$ATMdb->Get_field('name');
			}
		
	}

	
	function save(&$db) {
		global $conf;
		$this->entity = $conf->entity;
		
		if ($this->date_fin < $this->date_debut) {
			$this->date_fin = $this->date_debut;
		}
		$temp = new TRH_Ressource;
		$temp->load($db, $this->fk_rh_ressource);
		
		if ($this->type=='emprunt'){
			$this->color = 1 ; //couleur rouge
			$this->subject = "[ ".$temp->libelle." ] Utilisé par ".$this->TUser[$this->fk_user];
		}
		else {
			$this->color = 6 ; //couleur verte moche
			$this->subject = "[ ".$temp->libelle." ] ".$this->TType[$this->type]." : ".$this->motif;
	
		}
		
		$this->isAllDayEvent = 1;
		
		//$this->description = "";
		
		
		
		parent::save($db);
	}
	
}	