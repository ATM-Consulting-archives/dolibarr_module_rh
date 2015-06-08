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
		parent::add_champs('nbjours,is_weak','type=entier;');			//nbjours avant alerte
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
			$this->TUser[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('firstname')." ".$ATMdb->Get_field('lastname');
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
	
	static function isValideur(&$ATMdb, $fk_user, $fk_usergroup=0, $excludePointeur=false) {
		
		$sql=" SELECT count(*) as 'nb'
 			FROM `".MAIN_DB_PREFIX."rh_valideur_groupe`
			WHERE fk_user=".$fk_user;
			
		if($fk_usergroup>0) $sql.=" AND fk_usergroup=".$fk_usergroup;
		 
		$sql.=" AND type='Conges' ";
		if($excludePointeur) $sql.=" AND pointeur !=1 ";

//print $sql;
		$ATMdb->Execute($sql);
		
		$ATMdb->Get_line();
		
		if($ATMdb->Get_field('nb')>0) return true;
		else return false;
			
		
	}
	
	static function isStrong(&$PDOdb, $fk_user, $type, $entity)
	{
		$sql = 'SELECT * FROM '.MAIN_DB_PREFIX.'rh_valideur_groupe WHERE fk_user = '.(int) $fk_user.' AND type = "'.$type.'" AND entity = '.(int) $entity;
		$PDOdb->Execute($sql);
		
		$line = $PDOdb->Get_line();
		if ($line && !$line->is_weak) return true;
		else return false;		
	}
	
}

class TRH_valideur_object extends TObjetStd
{
	function __construct() 
	{
		global $conf;
		
		$PDOdb=new TPDOdb;
		
		parent::set_table(MAIN_DB_PREFIX.'rh_valideur_object');
		parent::add_champs('type','type=chaine;'); // type de l'objet validé (NDFP, ABS)
		parent::add_champs('fk_user,fk_object,entity','type=entier;index;');
		
		parent::_init_vars();
		parent::start();
		
		$this->TType = array(
			'NDFP'=>'Note de frais'
			,'ABS'=>'Absence'
		);
	}
	
	/*
	 * Renvois false s'il existe des valideurs qui non pas encore validé
	 */
	static function checkAllAccepted(&$PDOdb, $type, $fk_object)
	{
		$sql = 'SELECT * FROM '.MAIN_DB_PREFIX.'rh_valideur_groupe';
		$sql.= ' WHERE fk_user NOT IN (SELECT fk_user FROM '.MAIN_DB_PREFIX.'rh_valideur_object WHERE type="'.$type.'" AND fk_object='.(int) $fk_object.')';
		
		$PDOdb->Execute($sql);
		
		if ($PDOdb->Get_line()) return false;
		else return true;
	}

	static function deleteChildren(&$PDOdb, $type, $fk_object)
	{
		$sql = 'DELETE FROM '.MAIN_DB_PREFIX.'rh_valideur_object WHERE type="'.$type.'" AND fk_object='.(int) $fk_object;
		return $PDOdb->Execute($sql);
	}
	
	static function addLink(&$PDOdb, $entity, $fk_user, $fk_object, $type)
	{
		$TRH_valideur_object = new TRH_valideur_object;
		
		$TRH_valideur_object->fk_user = $fk_user;
		$TRH_valideur_object->fk_object = $fk_object;
		$TRH_valideur_object->entity = $entity;
		$TRH_valideur_object->type = $type;
		$TRH_valideur_object->save($PDOdb);
		
		return $TRH_valideur_object;
	}
	
	static function alreadyAcceptedByThisUser(&$PDOdb, $entity, $fk_user, $fk_object, $type)
	{
		$sql = 'SELECT * FROM '.MAIN_DB_PREFIX.'rh_valideur_object WHERE type = "'.$type.'" AND fk_object = '.$fk_object.' AND fk_user = '.$fk_user.' AND entity = '.$entity;
		$PDOdb->Execute($sql);
		
		if ($PDOdb->Get_line()) return true;
		else return false;
	}
}
