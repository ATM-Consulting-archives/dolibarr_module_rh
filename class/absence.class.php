<?php

//TRH_ABSENCE
//classe pour la définition d'une absence 
class TRH_Absence extends TObjetStd {
	function __construct() { /* declaration */
		
		parent::set_table(MAIN_DB_PREFIX.'rh_absence');
		parent::add_champs('type','type=enum('rtt','conge_paye','maladie','deuil');');				//type de congé
		parent::add_champs('date_debut,date_fin','type=date;');	//dates debut fin de congés
		parent::add_champs('duree','type=entier;');				//duree en demi-journees
		parent::add_champs('commentaire','type=chaine;');		//commentaire
		parent::add_champs('fk_utilisateur','type=entier;');	//utilisateur concerné
		
		parent::_init_vars();
		parent::start();
		
		$this->TField=array();
		
	}
	
	function load(&$ATMdb, $id) {
		
		parent::load($ATMdb, $id);
		
		$this->load_field($ATMdb);
		
	}
	
	function load_field(&$ATMdb) {

		$Tab = TRequeteCore::get_id_from_what_you_want(&$db, MAIN_DB_PREFIX.'rh_ressource_field', array('fk_rh_ressource'=>$this->getId()));
		foreach($Tab as $k=>$id) {
			$this->TField[$k]=new TRH_Ressource_field;
			$this->TField[$k]->load($ATMdb, $id);
			
		}
		
		$this->init_variables();
	}
	function init_variables() {
		foreach($this->TField as $field) {
			$this->{$field->nom} = $field->valeur;
			
		}		
		
	}
	function save(&$ATMdb) {
		parent::save($db);
		
		$this->save_field($ATMdb);
	}
	function save_field(&$ATMdb) {
		foreach($this->TField as &$field) {
			$field->fk_rh_ressource = $this->getId();
			$field->valeur = $this->{$field->nom};
			$field->save($ATMdb);
		}
	}
}


//définition de la classe pour la notion de pointage
class TRH_Pointage extends TObjetStd {
	function __construct() { /* declaration */
		
		parent::set_table(MAIN_DB_PREFIX.'rh_pointage');
		parent::add_champs('date','type=date;');		//date de pointage
		parent::add_champs('present','type=entier'); 	//collaborateur présent ou non
		
		parent::_init_vars();
		parent::start();
		
	}	
		
}


//TODO  A terminer de definir...
//Définiton classe d'export vers la comptabilité + export bilan social individuel annuel 
class TRH_Export extends TObjetStd {
	function __construct() { /* declaration */
		
		parent::set_table(MAIN_DB_PREFIX.'rh_export');
		parent::add_champs('date','type=date;');		//date de l'export
		parent::add_champs('nb_rtt','type=entier'); 	//nombre de Rtt à décompter
		parent::add_champs('nb_conge_paye','type=entier'); 	//nombre de congés payés à décompter
		parent::add_champs('nb_absence_autre','type=entier'); 	//nombre d'absences de type autres (deuil, maladie etc...) à décompter
		parent::add_champs('fk_utilisateur','type=entier;');	//utilisateur concerné
		
		parent::_init_vars();
		parent::start();
		
	}	
		
}


//définition de la table pour l'enregistrement des jours non travaillés dans l'année (fériés etc...)
class TRH_Jour_non_travaille extends TObjetStd {
	function __construct() { /* declaration */
		
		parent::set_table(MAIN_DB_PREFIX.'rh_jour_non_travaille');
		parent::add_champs('date','type=date;');		//date du jour non travaillé
		
		parent::_init_vars();
		parent::start();
		
	}	
		
}







	