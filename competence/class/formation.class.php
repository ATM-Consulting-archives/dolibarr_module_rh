<?php

class TRH_formation_plan extends TObjetStd {
	function __construct() {
		
		parent::set_table(MAIN_DB_PREFIX.'rh_formation_plan');
		parent::add_champs('date_debut,date_fin','type=date;');
		parent::add_champs('libelle','type=chaine');
		parent::add_champs('description','type=chaine');
		parent::add_champs('budget','type=float;');
		parent::add_champs('entity','type=entier;');
		
		parent::_init_vars();
		parent::start();
		
		$this->date_debut =strtotime( date('Y-01-01') );
		$this->date_fin =strtotime( date('Y-12-31') );
		
		$this->libelle = 'Plan de formation '.date('Y');
		
	}
	
	//Retourne la liste des Formation associées au plan
	function getListeFormation(&$ATMdb){
		global $user, $conf;
		
		$r = new TListviewTBS('listeFormation');
		
		$sql = "SELECT f.rowid AS 'ID', f.fk_formation_plan AS 'idPlan', f.libelle AS 'Intitule', f.description AS 'Description', f.budget AS 'Budget', f.budget_consomme AS 'Consomme', ftp.libelle AS 'LibelleType'
				FROM ".MAIN_DB_PREFIX."rh_formation AS f 
					LEFT JOIN ".MAIN_DB_PREFIX."rh_formation_type_priorite AS ftp ON (f.fk_priorite = ftp.rowid)
				WHERE f.fk_formation_plan = ".$this->getId()."
				ORDER BY ftp.rowid ASC";
		
		$res = $r->render($ATMdb, $sql, array(
			'limit'=>array(
				'nbLine'=>'30'
			)
			,'link'=>array(
				'Intitule'=>'<a href="formation.php?id=@ID@&idPlan=@idPlan@">@val@</a>'
			)
			,'translate'=>array()
			,'hide'=>array('ID','idPlan')
			,'liste'=>array(
				'titre'=>'Liste des formations'
				,'image'=>img_picto('','title.png', '', 0)
				,'picto_precedent'=>img_picto('','previous.png', '', 0)
				,'picto_suivant'=>img_picto('','next.png', '', 0)
				,'noheader'=> (int)isset($_REQUEST['socid'])
				,'messageNothing'=>"Il n'y a aucunes formations à afficher"
				,'order_down'=>img_picto('','1downarrow.png', '', 0)
				,'order_up'=>img_picto('','1uparrow.png', '', 0)
			)
		));
		
		return $res;
	}
}

class TRH_formation_section extends TObjetStd {
	function __construct() {
		
		parent::set_table(MAIN_DB_PREFIX.'rh_formation_section');
		parent::add_champs('libelle','type=chaine');
		parent::add_champs('description','type=chaine');
		parent::add_champs('budget','type=float');
		parent::add_champs('fk_usergroup, fk_formation_plan','type=entier;');
		parent::add_champs('entity','type=entier;');
		
		parent::_init_vars();
		parent::start();
	}
}

class TRH_formation extends TObjetStd {
	function __construct() {
		
		parent::set_table(MAIN_DB_PREFIX.'rh_formation');
		parent::add_champs('libelle','type=chaine');
		parent::add_champs('description','type=chaine');
		parent::add_champs('budget','type=float');
		parent::add_champs('budget_consomme','type=float');
		parent::add_champs('fk_priorite, fk_formation_plan','type=entier;');
		parent::add_champs('entity','type=entier;');
		
		parent::_init_vars();
		parent::start();
	}
	
	//Retourne la liste des Sessions associées à la formation
	function getListeSessionFormation($ATMdb){
		global $user, $conf;
		
		$r = new TListviewTBS('listeSessionFormation');
		
		$sql = "SELECT fs.rowid AS 'ID', fs.libelle AS 'Intitule', fs.description AS 'Description', fs.budget AS 'Budget', fs.budget_consomme AS 'Consomme'
				FROM ".MAIN_DB_PREFIX."rh_formation_session AS fs
				WHERE fs.fk_formation = ".$this->getId();
		
		$res = $r->render($ATMdb, $sql, array(
			'limit'=>array(
				'nbLine'=>'30'
			)
			,'link'=>array(
				'Libellé'=>'<a href="sessionFormation.php?id=@ID@">@val@</a>'
			)
			,'translate'=>array()
			,'hide'=>array('ID')
			,'liste'=>array(
				'titre'=>'Liste des Sessions de Formation'
				,'image'=>img_picto('','title.png', '', 0)
				,'picto_precedent'=>img_picto('','previous.png', '', 0)
				,'picto_suivant'=>img_picto('','next.png', '', 0)
				,'noheader'=> (int)isset($_REQUEST['socid'])
				,'messageNothing'=>"Il n'y a aucunes session à afficher"
				,'order_down'=>img_picto('','1downarrow.png', '', 0)
				,'order_up'=>img_picto('','1uparrow.png', '', 0)
			)
		));
		
		return $res;
	}
}

class TRH_formation_type_priorite extends TObjetStd {
	function __construct() {
		
		parent::set_table(MAIN_DB_PREFIX.'rh_formation_type_priorite');
		parent::add_champs('libelle','type=chaine');
		
		parent::_init_vars();
		parent::start();
	}
}

class TRH_formation_session extends TObjetStd {
	function __construct() {
		
		parent::set_table(MAIN_DB_PREFIX.'rh_formation_session');
		parent::add_champs('date_debut,date_fin','type=date;');
		parent::add_champs('libelle','type=chaine');
		parent::add_champs('description','type=chaine');
		parent::add_champs('budget','type=float');
		parent::add_champs('budget_consomme','type=float');
		parent::add_champs('fk_formation','type=entier;');
		parent::add_champs('entity','type=entier;');
		
		parent::_init_vars();
		parent::start();
	}
}

class TRH_formation_participant extends TObjetStd {
	function __construct() {
		
		parent::set_table(MAIN_DB_PREFIX.'rh_formation_participant');
		parent::add_champs('description','type=chaine');
		parent::add_champs('fk_statut, fk_formation_session, fk_user, fk_responsable','type=entier;');
		parent::add_champs('entity','type=entier;');
		
		parent::_init_vars();
		parent::start();
	}
}

class TRH_formation_participant_statut extends TObjetStd {
	function __construct() {
		
		parent::set_table(MAIN_DB_PREFIX.'rh_formation_participant_statut');
		parent::add_champs('libelle','type=chaine');
		parent::add_champs('entity','type=entier;');
		
		parent::_init_vars();
		parent::start();
	}
}