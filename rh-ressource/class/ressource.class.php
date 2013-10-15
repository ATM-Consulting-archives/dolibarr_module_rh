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
		
		//clé étrangere : groupes propriétaire et utilisatrice
		parent::add_champs('fk_utilisatrice','type=entier;index;');	//groupe : pointe sur llx_usergroup
		parent::add_champs('fk_entity_utilisatrice','type=entier;index;');	//fk_entity_utilisatrice : pointe sur llx_entity
		parent::add_champs('fk_proprietaire,entity','type=entier;index;');//fk_propriétaire pointe sur llx_entity
		parent::add_champs('fk_loueur','type=entier;index;');//fk_loueur pointe sur llx_societe
		
		//clé étrangère : type de la ressource
		parent::add_champs('fk_rh_ressource_type','type=entier;index;');
		//clé étrangère : ressource associé
		parent::add_champs('fk_rh_ressource','type=entier;index;');
		
		parent::_init_vars();
		parent::start();
		
		$this->TField=array();
		$this->ressourceType=new TRH_Ressource_type;

		$this->TType = array();
		$this->TBail = array('location'=>'Location','immobilisation'=>'Immobilisation');
		
		$this->TRessource = array('');
		$this->TEvenement = array();
		
		$this->TAgence = array('');
		$this->TFournisseur = array('');
		$this->TTVA = array();
		$this->TContratAssocies = array(); 	//tout les objets rh_contrat_ressource liés à la ressource
		$this->TContratExaustif = array(); 	//tout les objets contrats
		$this->TListeContrat = array(); 	//liste des id et libellés de tout les contrats
		$this->TEntity = array();
	}
	
	function load_liste_type_ressource(&$ATMdb){
		//chargement d'une liste de tout les types de ressources
		$temp = new TRH_Ressource_type;
		$Tab = TRequeteCore::get_id_from_what_you_want($ATMdb, MAIN_DB_PREFIX.'rh_ressource_type', array());
		$this->TType = array('');
		foreach($Tab as $k=>$id){
			$temp->load($ATMdb, $id);
			$this->TType[$temp->getId()] = $temp->libelle;
		}
		
	}
	
	function load_agence(&$ATMdb){
		global $conf;
		$this->TAgence = array('');
		$sqlReq="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup WHERE entity IN (0,".$conf->entity.")";
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TAgence[$ATMdb->Get_field('rowid')] = htmlentities($ATMdb->Get_field('nom'), ENT_COMPAT , 'ISO8859-1');
			}
		
		$this->TFournisseur = array('');
		$sql="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."societe";
		$ATMdb->Execute($sql);
		while($ATMdb->Get_line()) {
			$this->TFournisseur[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('nom');
			}

		
	}
	
	function load_liste_entity(&$ATMdb){
		global $conf;
		
		$sql="SELECT rowid,label FROM ".MAIN_DB_PREFIX."entity WHERE 1";
		$ATMdb->Execute($sql);
		while($ATMdb->Get_line()) {
			$this->TEntity[$ATMdb->Get_field('rowid')] = htmlentities($ATMdb->Get_field('label'), ENT_COMPAT , 'ISO8859-1');
			}
		
		
	}
	
	function load_by_numId(&$ATMdb, $numId){
		$sqlReq="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_ressource WHERE numId='".$numId."'";
		$ATMdb->Execute($sqlReq);
		if ($ATMdb->Get_line()) {
			return $this->load($ATMdb, $ATMdb->Get_field('rowid'));
		}
		return false;
	}
	
	function load(&$ATMdb, $id, $annexe=true) {
		global $conf;
		parent::load($ATMdb, $id);

		$this->load_ressource_type($ATMdb);
	
		if($annexe) {
			
			//chargement d'une liste de toutes les ressources (pour le combo "ressource associé")
			// AA à supprimer et mettre cette horreur ailleur
				$sqlReq="SELECT rowid,libelle, numId FROM ".MAIN_DB_PREFIX."rh_ressource WHERE rowid!=".$this->getId()." ORDER BY fk_rh_ressource_type, numId";
				$ATMdb->Execute($sqlReq);
				while($ATMdb->Get_line()) {
					$this->TRessource[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('numId').' '.$ATMdb->Get_field('libelle');
				}	
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
		$sql .= ")  ORDER BY date_fin";
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
		AND entity IN (0,".$conf->entity.")";
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
		// AA c'est un contrat ça ? (outre le fait que je ne comprends pas toutes ces notions de contrats)
		$this->TTVA = array();
		$sqlReq="SELECT rowid, taux FROM ".MAIN_DB_PREFIX."c_tva WHERE fk_pays=".$conf->global->MAIN_INFO_SOCIETE_PAYS[0];
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TTVA[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('taux');
			}
		
		$this->TListeContrat = array(); 	//liste des id et libellés de tout les contrats
		$sqlReq="SELECT rowid, libelle FROM ".MAIN_DB_PREFIX."rh_contrat WHERE fk_rh_ressource_type =".$this->fk_rh_ressource_type;
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TListeContrat[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('libelle');
			}
	}
	
	/**
	 * Retourne une liste de type ATM des contrats associés à la ressource
	 */
	function liste_contrat(&$ATMdb){
		global $user, $conf;
		$r = new TListviewTBS('lol');
		$sql="SELECT DISTINCT a.rowid as 'ID',  c.rowid as 'IDContrat' , c.libelle as 'Libellé',
			DATE(c.date_debut) as 'Date début', DATE(c.date_fin) as 'Date fin', a.commentaire as 'Commentaire'
			FROM ".MAIN_DB_PREFIX."rh_contrat_ressource as a
			LEFT JOIN ".MAIN_DB_PREFIX."rh_contrat as c ON (a.fk_rh_contrat = c.rowid)
			LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (a.fk_rh_ressource = r.rowid)
			WHERE a.fk_rh_ressource=".$this->getId();
		$TOrder = array('Date début'=>'ASC');
		if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
		if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
		
		$res = $r->render($ATMdb, $sql, array(
			'limit'=>array(
				'page'=>$page
				,'nbLine'=>'30'
			)
			,'link'=>array(
				'ID'=>'<a href="?id='.$this->getId().'&idAssoc=@ID@">@val@</a>'
				,'Libellé'=>'<a href="contrat.php?id=@IDContrat@">@val@</a>'
			)
			,'translate'=>array()
			,'hide'=>array('DateCre', 'IDContrat')
			,'type'=>array(
				'Date début'=>'date'
				,'Date fin'=>'date'
				)
			,'liste'=>array(
				'titre'=>'Liste des contrats associés'
				,'image'=>img_picto('','title.png', '', 0)
				,'picto_precedent'=>img_picto('','previous.png', '', 0)
				,'picto_suivant'=>img_picto('','next.png', '', 0)
				,'noheader'=> (int)isset($_REQUEST['socid'])
				,'messageNothing'=>"Il n'y a aucun contrat à afficher"
				,'order_down'=>img_picto('','1downarrow.png', '', 0)
				,'order_up'=>img_picto('','1uparrow.png', '', 0)
			)
			,'orderBy'=>$TOrder
		));
		return $res;
	}

	/**
	 * La fonction renvoie le rowid de l'user qui a la ressource à la date T, 0 sinon.
	 */
	function isEmpruntee(&$ATMdb, $jour){ // AA bizarrement, oui j'ai toujours aimé le Franglais
		global $conf;
		
		// AA Par contre je la function peut se résumer en une seule requete
		
		$sql = "SELECT u.rowid, e.date_debut as 'debut', e.date_fin as 'fin'
				FROM ".MAIN_DB_PREFIX."user as u
				LEFT JOIN ".MAIN_DB_PREFIX."rh_evenement as e ON (e.fk_user = u.rowid)
				LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (e.fk_rh_ressource = r.rowid)
				WHERE r.rowid =".$this->getId()."
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
	 * retourne le timestamp d'une chaine au format jj/mm/aaaa
	 * Utile pour la comparaison.
	 */
	function strToTimestamp($chaine){
		$a = strptime ($chaine, "%d/%m/%Y"); // AA snif je viens d'apprendre une fonction et c'est pas tout les jours ;)
		$timestamp = mktime(0,0,0,substr($chaine, 3,2),substr($chaine,0,2), substr($chaine, 6,4));
		//$timestamp = mktime(0, 0, 0, $a['tm_mon']+1, $a['tm_mday'], $a['tm_year']+1900);
		return $timestamp;
	}
	
	
	/**
	 * La fonction renvoie vrai si les nouvelles date proposé pour un emprunt se chevauchent avec d'autres.
	 */
	function nouvelEmpruntSeChevauche(&$ATMdb,  $idRessource, $newEmprunt){
		global $conf;
		$sqlReq="SELECT date_debut,date_fin FROM ".MAIN_DB_PREFIX."rh_evenement WHERE fk_rh_ressource=".$idRessource."
		AND type='emprunt' AND rowid != ".$newEmprunt['idEven']; 
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			if ($this->dateSeChevauchent($this->strToTimestamp($newEmprunt['date_debut'])
										,$this->strToTimestamp($newEmprunt['date_fin'])
										,$this->strToTimestamp(date("d/m/Y",strtotime($ATMdb->Get_field('date_debut'))))
										,$this->strToTimestamp(date("d/m/Y",strtotime($ATMdb->Get_field('date_fin'))))))
				{
				return true;}}
		return false;
	}
	
	/**
	 * les dates demandés sont au format timeStamp
	 * @return true si chevauchement; false sinon.
	 */
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
			$this->add_champs($field->code, 'type=chaine;');
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
		$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_contrat_ressource WHERE fk_rh_ressource=".$this->getId();
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
		
		//on supprime aussi les évenements associés
		$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_evenement WHERE fk_rh_ressource=".$this->getId();
		$Tab = array();
		$temp = new TRH_Evenement;
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
		parent::add_champs('supprimable','type=entier;');
				
		parent::_init_vars();
		parent::start();
		$this->TField=array();
		$this->TType=array('chaine'=>'Texte','entier'=>'Entier','float'=>'Float',"liste"=>'Liste','date'=>'Date', "checkbox"=>'Case à cocher');
	}
	
	
	function load_by_code(&$ATMdb, $code){
		$sqlReq="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_ressource_type WHERE code='".$code."'";
		$ATMdb->Execute($sqlReq);
		
		if ($ATMdb->Get_line()) {
			$this->load($ATMdb, $ATMdb->Get_field('rowid'));
			return true;
		}
		return false;
	}
	
	/**
	 * Attribut les champs directement, pour créer les types par défauts par exemple. 
	 */
	function chargement(&$db, $libelle, $code, $supprimable){
		$this->load_by_code($db, $code);
		$this->libelle = $libelle;
		$this->code = $code;
		$this->supprimable = $supprimable;
		$this->save($db);
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
		if (count($Tab)>0) return true;
		return false;

	}
	
	function load_field(&$ATMdb) {
		global $conf;
		$sqlReq="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_ressource_field WHERE fk_rh_ressource_type=".$this->getId()." ORDER BY ordre ASC;";
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
			$sqlReq="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_ressource_field WHERE fk_rh_ressource_type=".$this->getId();
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
		
		$this->code = TRH_Ressource_type::code_format(empty($this->code) ? $this->libelle : $this->code);
		
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
		parent::add_champs('inliste','type=chaine;'); //varchar booléen : oui/non si le champs sera dans la liste de Ressource.
		parent::add_champs('fk_rh_ressource_type,entity','type=entier;index;');
		
		$this->TListe = array();
		parent::_init_vars();
		parent::start();
		
	}
	
	function load_by_code(&$db, $code){
		$sqlReq="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_ressource_field WHERE code='".$code."'";
		$db->Execute($sqlReq);
		
		if ($db->Get_line()) {
			$this->load($db, $db->Get_field('rowid'));
			return true;
		}
		return false;
	}
	
	
	function chargement(&$db, $libelle, $code, $type, $obligatoire, $ordre, $options, $supprimable, $fk_rh_ressource_type, $inliste = "non"){
		$this->load_by_code($db, $code);	
		$this->libelle = $libelle;
		$this->code = $code;
		$this->type = $type;
		$this->obligatoire = $obligatoire;
		$this->ordre = $ordre;
		$this->options = $options;
		$this->supprimable = $supprimable;
		$this->inliste = $inliste;
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
		if (empty($this->supprimable)){$this->supprimable = 0;}
		parent::save($db);
	}

	function delete(&$ATMdb) {
		global $conf;
		
		//on supprime le champs que si il est par défault.
		if (! $this->supprimable){
			parent::delete($ATMdb);	
			return true;
		}
		else {return false;}
		
		
	}

}
	
