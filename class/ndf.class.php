<?php

class TRH_Ndf extends TObjetStd {
	function __construct() { /* declaration */
		
		parent::set_table(MAIN_DB_PREFIX.'rh_ndf');
		parent::add_champs('statut','type=enum('A valider','Refusé','Bon à payer')');
		parent::add_champs('formulaire','type=enum(('Standard','Formation'))');
		parent::add_champs('fk_rh_utilisateur','type=entier');
		
		parent::_init_vars();
		parent::start();
		
		$this->TField=array();
		
	}
	
	function load(&$ATMdb, $id) {
		parent::load($ATMdb, $id);
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
			$field->fk_rh_ndf = $this->getId();
			$field->valeur = $this->{$field->nom};
			$field->save($ATMdb);
		}
	}
}

class TRH_Ligne extends TObjetStd {
	function __construct() { /* declaration */
		
		parent::set_table(MAIN_DB_PREFIX.'rh_ligne');
		parent::add_champs('date','type=date');
		parent::add_champs('montant_ttc','type=float');
		parent::add_champs('tva','type=float');
		parent::add_champs('fk_rh_ndf','type=entier');
		parent::add_champs('fk_rh_compta','type=entier');
		
		parent::_init_vars();
		parent::start();
		
		$this->TField=array();
		
	}
	
	function load(&$ATMdb, $id) {
		parent::load($ATMdb, $id);
	}
	
	function init_variables() {
		foreach($this->TField as $field) {
			$this->{$field->nom} = $field->valeur;
		}		
		
	}
	function save(&$ATMdb) {
		parent::save($db);
	}
	
	function save_field(&$ATMdb) {
		foreach($this->TField as &$field) {
			$field->fk_rh_ligne = $this->getId();
			$field->valeur = $this->{$field->nom};
			$field->save($ATMdb);
		}
	}
}

class TRH_Validateur extends TObjetStd {
	function __construct() { /* declaration */
		
		parent::set_table(MAIN_DB_PREFIX.'rh_validateur');
		
		parent::_init_vars();
		parent::start();
		
		$this->TField=array();
		
	}
	
	function load(&$ATMdb, $id) {
		parent::load($ATMdb, $id);
	}
	
	function init_variables() {
		foreach($this->TField as $field) {
			$this->{$field->nom} = $field->valeur;
		}		
		
	}
	function save(&$ATMdb) {
		parent::save($db);
	}
	
	function save_field(&$ATMdb) {
		foreach($this->TField as &$field) {
			$field->fk_rh_validateur = $this->getId();
			$field->valeur = $this->{$field->nom};
			$field->save($ATMdb);
		}
	}
}

class TRH_Commentaire extends TObjetStd {
	function __construct() { /* declaration */
		
		parent::set_table(MAIN_DB_PREFIX.'rh_commentaire');
		parent::add_champs('texte','type=chaine');
		parent::add_champs('date','type=date');
		parent::add_champs('fk_rh_ndf','type=entier');
		parent::add_champs('fk_rh_validateur','type=entier');
		
		parent::_init_vars();
		parent::start();
		
		$this->TField=array();
		
	}
	
	function load(&$ATMdb, $id) {
		parent::load($ATMdb, $id);
	}
	
	function init_variables() {
		foreach($this->TField as $field) {
			$this->{$field->nom} = $field->valeur;
		}		
		
	}
	function save(&$ATMdb) {
		parent::save($db);
	}
}

class TRH_Comptabilite extends TObjetStd {
	function __construct() { /* declaration */
		
		parent::set_table(MAIN_DB_PREFIX.'rh_compta');
		parent::add_champs('default_TVA','type=float');
		
		parent::_init_vars();
		parent::start();
		
		$this->TField=array();
		
	}
	
	function load(&$ATMdb, $id) {
		parent::load($ATMdb, $id);
	}
	
	function init_variables() {
		foreach($this->TField as $field) {
			$this->{$field->nom} = $field->valeur;
		}		
		
	}
	function save(&$ATMdb) {
		parent::save($db);
	}
	
	function save_field(&$ATMdb) {
		foreach($this->TField as &$field) {
			$field->fk_rh_compta = $this->getId();
			$field->valeur = $this->{$field->nom};
			$field->save($ATMdb);
		}
	}
}

class TRH_Trajet extends TObjetStd {
	function __construct() { /* declaration */
		
		parent::set_table(MAIN_DB_PREFIX.'rh_trajet');
		parent::add_champs('depart','type=chaine');
		parent::add_champs('arrive','type=chaine');
		parent::add_champs('km','type=float');
		parent::add_champs('puissance_fiscale','type=entier');
		parent::add_champs('fk_rh_ligne','type=entier');
		
		parent::_init_vars();
		parent::start();
		
		$this->TField=array();
		
	}
	
	function load(&$ATMdb, $id) {
		parent::load($ATMdb, $id);
	}
	
	function init_variables() {
		foreach($this->TField as $field) {
			$this->{$field->nom} = $field->valeur;
		}		
		
	}
	function save(&$ATMdb) {
		parent::save($db);
	}
}