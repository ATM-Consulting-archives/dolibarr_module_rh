<?php
	//require('./class/evenement.class.php');
	
class TRH_Ressource extends TObjetStd {
	function __construct() { /* declaration */
		parent::set_table(MAIN_DB_PREFIX.'rh_ressource');
		parent::add_champs('libelle','type=chaine;');
		parent::add_champs('numId','type=chaine;');
		parent::add_champs('date_achat, date_vente, date_garantie','type=date;');
		
		//types énuméré
		parent::add_champs('statut','type=chaine;');
		
		//clé étrangere : groupe propriétaire
		parent::add_champs('fk_proprietaire','type=chaine;index;');
		//clé étrangère : société
		parent::add_champs('fk_soc,entity','type=entier;index;');//fk_soc_leaser
		//clé étrangère : type de la ressource
		parent::add_champs('fk_rh_ressource_type','type=chaine;index;');
		//clé étrangère : ressource associé
		parent::add_champs('fk_rh_ressource','type=entier;index;');
		
		parent::_init_vars();
		parent::start();
		
		$this->TField=array();
		$this->ressourceType=new TRH_Ressource_type;

		$ATMdb=new Tdb;
		
		$Tab = TRequeteCore::get_id_from_what_you_want($ATMdb, MAIN_DB_PREFIX.'rh_ressource_type', array());
		
		//chargement d'une liste de tout les types de ressources
		$temp = new TRH_Ressource_type;
		$this->TType = array();
		foreach($Tab as $k=>$id){
			$temp->load($ATMdb, $id);
			$this->TType[$temp->getId()] = $temp->libelle;
		}
		$this->TBail = array('bail'=>'Bail','immobilisation'=>'Immobilisation');
		
		$this->TRessource = array('');
		$this->TEvenement = array();
		
		$this->TAgence = array();
		$sqlReq="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup ";
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TAgence[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('nom');
			}
		$this->TTVA = array();
		$this->TContratAssocies = array(); 	//tout les objets rh_contrat_ressource liés à la ressource
		$this->TContratExaustif = array(); 	//tout les objets contrats
		$this->TListeContrat = array(); 	//liste des id et libellés de tout les contrats
		$sqlReq="SELECT rowid, libelle FROM ".MAIN_DB_PREFIX."rh_contrat ";
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TListeContrat[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('libelle');
			}
	}
	

	
	function load(&$ATMdb, $id) {
		global $conf;
		parent::load($ATMdb, $id);

		$this->load_ressource_type($ATMdb);
		//chargement d'une liste de toutes les ressources (pour le combo "ressource associé")
		$sqlReq="SELECT rowid,libelle FROM ".MAIN_DB_PREFIX."rh_ressource WHERE rowid!=".$this->getId()."
		AND entity=".$conf->entity;
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TRessource[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('libelle');
			}	
	}
	
	/**
	 * charge des infos sur les évenements associés à cette ressource dans le tableau TEvenements[]
	 * Seulement les evenements du type spécifié.
	 */
	function load_evenement(&$ATMdb, $type=array('emprunt')){
		global $conf;
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."rh_evenement WHERE fk_rh_ressource=".$this->getId();
		$sql.=" AND ( 0 ";
		foreach ($type as $value) {
			 $sql.= "OR type LIKE '".$value."' ";
		}
		$sql .= ") AND entity=".$conf->entity." ORDER BY date_fin";
		$ATMdb->Execute($sql);
		$Tab=array();
		while($ATMdb->Get_line()){
			$Tab[]=$ATMdb->Get_field('rowid');
		}
		$this->TEvenement = array();
		foreach($Tab as $k=>$id) {
			$this->TEvenement[$k] = new TRH_Evenement ;
			$this->TEvenement[$k]->load($ATMdb, $id);
		}
		
	}
	
	
	/**
	 * charge tout les contrats associé à cette ressource.
	 */
	function load_contrat(&$ATMdb){
		global $conf;
		$this->TContratExaustif = array();
		foreach($this->TListeContrat as $k=>$id) {
			$this->TContratExaustif[$k] = new TRH_Contrat ;
			$this->TContratExaustif[$k]->load($ATMdb, $k);
		}
		
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."rh_contrat_ressource WHERE fk_rh_ressource=".$this->getId()."
		AND entity=".$conf->entity;
		$ATMdb->Execute($sql);
		$Tab=array();
		while($ATMdb->Get_line()){
			$Tab[]=$ATMdb->Get_field('rowid');
		}
		$this->TContratAssocies = array();
		foreach($Tab as $k=>$id) {
			$this->TContratAssocies[$id] = new TRH_Contrat_Ressource;
			$this->TContratAssocies[$id]->load($ATMdb, $id);
		}
		
		$this->TTVA = array();
		$sqlReq="SELECT rowid, taux FROM ".MAIN_DB_PREFIX."c_tva WHERE fk_pays=".$conf->global->MAIN_INFO_SOCIETE_PAYS[0];
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TTVA[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('taux');
			}
		
	}
	

	/**
	 * La fonction renvoie le rowid de l'user qui a la ressource à la date T, 0 sinon.
	 */
	function isEmpruntee(&$ATMdb, $jour){
		global $conf;
		$sql = "SELECT u.rowid, e.date_debut as 'debut', e.date_fin as 'fin'
				FROM ".MAIN_DB_PREFIX."user as u
				LEFT JOIN ".MAIN_DB_PREFIX."rh_evenement as e ON (e.fk_user = u.rowid)
				LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (e.fk_rh_ressource = r.rowid)
				WHERE e.entity=".$conf->entity."
				AND r.rowid =".$this->getId()."
				AND e.type='emprunt'";
		$ATMdb->Execute($sql);
		$Tab=array();
		while($ATMdb->Get_line()){
			if ( date("Y-m-d",strtotime($ATMdb->Get_field('debut'))) <= $jour  
				&& date("Y-m-d",strtotime($ATMdb->Get_field('fin'))) >= $jour ){
				$Tab[]=$ATMdb->Get_field('rowid');	
			}
			
		}
		if (! empty($Tab)){
			return $Tab[0];}
		else {
			return 0;}
	}
	
	
	/**
	 * La fonction renvoie vrai si les nouvelles date proposé pour un emprunt se chevauchent avec d'autres.
	 */
	
	function nouvelEmpruntSeChevauche(&$ATMdb, $newEmprunt, $idRessource){
		global $conf;
		$sqlReq="SELECT date_debut,date_fin FROM ".MAIN_DB_PREFIX."rh_evenement WHERE fk_rh_ressource=".$idRessource."
		AND type='emprunt' AND entity=".$conf->entity." AND rowid != ".$newEmprunt['idEven'];
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			if ($this->dateSeChevauchent($newEmprunt['date_debut'], $newEmprunt['date_fin'],date("d/m/Y",strtotime($ATMdb->Get_field('date_debut'))), date("d/m/Y",strtotime($ATMdb->Get_field('date_fin'))) ))
				{
				return true;
				}
		}
		return false;
	}
	
	
	function dateSeChevauchent($d1d, $d1f, $d2d, $d2f){
		if (  ( ($d1d>=$d2d) && ($d1d<=$d2f) ) || ( ($d1f>=$d2d)  && ($d1f<=$d2f) )  ) 
			{return true;}
		return false;	
	}

	function load_ressource_type(&$ATMdb) {
		//on prend le type de ressource associé
		$Tab = TRequeteCore::get_id_from_what_you_want($ATMdb, MAIN_DB_PREFIX.'rh_ressource_type', array('rowid'=>$this->fk_rh_ressource_type));
		$this->ressourceType->load($ATMdb, $Tab[0]);
		$this->fk_rh_ressource_type = $this->ressourceType->getId();
		
		//on charge les champs associés au type.
		$this->init_variables($ATMdb);
		
	}
	
	function init_variables(&$ATMdb) {
		foreach($this->ressourceType->TField as $field) {
			$this->add_champs($field->code, 'type=chaine');
		}
		$this->init_db_by_vars($ATMdb);
		parent::load($ATMdb, $this->getId());
	}
	
	function save(&$db) {
		global $conf;
		$this->entity = $conf->entity;
		
		//$this->setStatut($db, date("Y-m-d"));
		
		//on transforme les champs sensés être entier en int
		foreach($this->ressourceType->TField as $k=>$field) {
			if ($field->type=='entier'){
				$this->{$field->code} = (int) ($this->{$field->code});
			}
		}
		
		parent::save($db);
	}
	
	function delete(&$ATMdb){
		global $conf;
		
		//avant de supprimer le contrat, on supprime les liaisons contrat-ressource associés.
		$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_contrat_ressource WHERE entity=".$conf->entity."
		AND fk_rh_ressource=".$this->getId();
		$Tab = array();
		$temp = new TRH_Contrat_Ressource;
		$ATMdb->Execute($sql);
		while($ATMdb->Get_line()) {
			$Tab[] = $ATMdb->Get_field('rowid');
			}
		foreach ($Tab as $key => $id) {
			$temp->load($ATMdb, $id);
			$temp->delete($ATMdb);
		}
		
		
		parent::delete($ATMdb);
		
		
	}
}


class TRH_Ressource_type extends TObjetStd {
	function __construct() { /* declaration */
		parent::set_table(MAIN_DB_PREFIX.'rh_ressource_type');
		parent::add_champs('libelle,code','type=chaine;');
		parent::add_champs('entity','type=entier;index;');
		parent::add_champs('supprimable','type=entier');
		parent::add_champs('liste_evenement_value','type=chaine;');
		parent::add_champs('liste_evenement_key','type=chaine;');
				
		parent::_init_vars();
		parent::start();
		$this->TField=array();
		$this->TType=array('chaine'=>'Texte','entier'=>'Entier','float'=>'Float',"liste"=>'Liste',"checkbox"=>'Case à cocher');
	}
	
	function chargement($libelle, $code, $supprimable, $liste_evenement_value, $liste_evenement_key){
		$this->libelle = $libelle;
		$this->code = $code;
		$this->supprimable = $supprimable;
		$this->liste_evenement_value = $liste_evenement_value;
		$this->liste_evenement_key = $liste_evenement_key;
	}
	
	function load(&$ATMdb, $id) {
		parent::load($ATMdb, $id);
		$this->load_field($ATMdb);
	}
	
	/**
	 * Renvoie true si ce type est utilisé par une des ressources.
	 */
	function isUsedByRessource(&$ATMdb){
		$Tab = TRequeteCore::get_id_from_what_you_want($ATMdb, MAIN_DB_PREFIX.'rh_ressource', array('fk_rh_ressource_type'=>$this->getId()));
		$taille = count($Tab);
		if ($taille>0) return true;
		return false;

	}
	
	function load_field(&$ATMdb) {
		global $conf;
		$sqlReq="SELECT rowid FROM llx_rh_ressource_field WHERE fk_rh_ressource_type=".$this->getId()." AND entity=".$conf->entity." ORDER BY ordre ASC;";
		$ATMdb->Execute($sqlReq);
		
		$Tab = array();
		while($ATMdb->Get_line()) {
			$Tab[]= $ATMdb->Get_field('rowid');
		}
		
		$this->TField=array();
		foreach($Tab as $k=>$id) {
			$this->TField[$k]=new TRH_Ressource_field;
			$this->TField[$k]->load($ATMdb, $id);
		}
	}
	
	function addField(&$ATMdb, $TNField) {
		$k=count($this->TField);
		$this->TField[$k]=new TRH_Ressource_field;
		$this->TField[$k]->set_values($TNField);
		
		$p=new TRH_Ressource;				
		$p->add_champs($TNField['code'] ,'type=chaine' );
		$p->init_db_by_vars($ATMdb);
					
		return $k;
	}
	
	function delField(&$ATMdb, $id){
		$toDel = new TRH_Ressource_field;
		$toDel->load($ATMdb,$id);
		return $toDel->delete($ATMdb);
	}
	
	function delete(&$ATMdb) {
		global $conf;
		if ($this->supprimable){
			//on supprime les champs associés à ce type
			$sqlReq="SELECT rowid FROM llx_rh_ressource_field WHERE fk_rh_ressource_type=".$this->getId()." AND entity=".$conf->entity;
			$ATMdb->Execute($sqlReq);
			$Tab = array();
			while($ATMdb->Get_line()) {
				$Tab[]= $ATMdb->Get_field('rowid');
			}
			$temp = new TRH_Ressource_field;
			foreach ($Tab as $k => $id) {
				$temp->load($ATMdb, $id);
				$temp->delete($ATMdb);
			}
			//puis on supprime le type
			parent::delete($ATMdb);
			return true;
		}
		else {return false;}
		
	}
	function save(&$db) {
		global $conf;
		
		$this->entity = $conf->entity;
		$this->code = TRH_Ressource_type::code_format(empty($this->code) ? $this->libelle : $this->code);
		
		//on transforme la liste des évenements en liste valide pour être des clés d'un tableau
		$temp = array();
		foreach (explode(';', $this->liste_evenement_value) as $value) {
			$temp[] = TRH_Ressource_type::code_format($value);
		}
		$this->liste_evenement_key = implode(';',$temp);
		
		parent::save($db);
		
		foreach($this->TField as $field) {
			$field->fk_rh_ressource_type = $this->getId();
			$field->save($db);
		}
		
	}	
	
	static function code_format($s){
		$r=""; $s = strtolower($s);
		$nb=strlen($s);
		for($i = 0; $i < $nb; $i++){
			if(ctype_alnum($s[$i])){
				$r.=$s[$i];			
			}
		} // for
		return $r;
	}
		
}

class TRH_Ressource_field extends TObjetStd {
	function __construct() { /* declaration */
		parent::set_table(MAIN_DB_PREFIX.'rh_ressource_field');
		parent::add_champs('code,libelle','type=chaine;');
		parent::add_champs('type','type=chaine;');
		parent::add_champs('obligatoire','type=entier;');
		parent::add_champs('ordre','type=entier;');
		parent::add_champs('options','type=chaine;');
		parent::add_champs('supprimable','type=entier;');
		parent::add_champs('fk_rh_ressource_type,entity','type=entier;index;');
		
		$this->TListe = array();
		parent::_init_vars();
		parent::start();
		
	}
	
	function chargement(&$db, $libelle, $code, $type, $obligatoire, $ordre, $options, $supprimable, $fk_rh_ressource_type){
		$this->libelle = $libelle;
		$this->code = $code;
		$this->type = $type;
		$this->obligatoire = $obligatoire;
		$this->ordre = $ordre;
		$this->options = $options;
		$this->supprimable = $supprimable;
		$this->fk_rh_ressource_type = $fk_rh_ressource_type;
		
		
		$this->save($db);
	}
	
	function load(&$ATMdb, $id){
		parent::load($ATMdb, $id);
		$this->TListe = array();
		foreach (explode(";",$this->options) as $key => $value) {
			$this->TListe[$value] = $value;
		}
	}
	
	function save(&$db) {
		global $conf;
		
		$this->code = TRH_Ressource_type::code_format(empty($this->code) ? $this->libelle : $this->code);
		
		$this->entity = $conf->entity;
		$this->supprimable = 1;
		parent::save($db);
	}

	function delete(&$ATMdb) {
		global $conf;
		
		//on supprime le champs que si il est par défault.
		if ($this->supprimable){
			parent::delete($ATMdb);	
			return true;
		}
		else {return false;}
		
		
	}

}
	
/*
 * Classes d'associations
 * 
 */

class TRH_Ressource_Import  extends TObjetStd {
	
	function __construct(){
		parent::set_table(MAIN_DB_PREFIX.'rh_association_ressourceimport');
		parent::add_champs('libelle','type=chaine;');
		
		parent::add_champs('fk_rh_import','type=entier;index;');
		parent::add_champs('fk_rh_ressource,entity','type=entier;index;');
	}
	
}	
	
	
