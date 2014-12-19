<?php
class TRH_Evenement  extends TObjetStd {
	
	function __construct(){
		parent::set_table(MAIN_DB_PREFIX.'rh_evenement');
		parent::add_champs('date_debut, date_fin','type=date;');
		parent::add_champs('fk_rh_ressource','type=entier;index;');	
		parent::add_champs('fk_user,entity','type=entier;index;');
		parent::add_champs('motif','type=chaine;');
		parent::add_champs('commentaire, refexterne','type=chaine;');
		parent::add_champs('confidentiel','type=chaine;'); // booleen varchar: oui/non
		
		//type : accident, répération, emprunt, appel,  facture, ...
		parent::add_champs('type','type=chaine;');
		parent::add_champs('fk_fournisseur','type=chaine;');  //fournisseur : pointe sur llx_societe

		//pour le wdCalendar
		parent::add_champs('color','type=chaine;');
		parent::add_champs('isAllDayEvent','type=entier;');
		
		//pour un accident, une réparation
		parent::add_champs('responsabilite','type=entier;');
		parent::add_champs('coutTTC','type=float;');
		parent::add_champs('coutEntrepriseTTC','type=float;');
		parent::add_champs('TVA','type=entier;'); //indice de la TVA dans le tableau $this->TTVA
		parent::add_champs('coutEntrepriseHT','type=float;');
		
		parent::add_champs('litreEssence','type=float;'); //pour des pleins d'essences
		parent::add_champs('kilometrage','type=entier;');
		parent::add_champs('tiersImplique','type=chaine;'); // booleen varchar: oui/non
		
		//pour une facture téléphonique
		parent::add_champs('duree, dureeI, dureeE','type=entier;'); //durée de consommation
		parent::add_champs('appels','type=chaine;');
		parent::add_champs('totalIFact, totalEFact','type=float;'); //montant facturé en interne et en externe
		parent::add_champs('totalFact','type=float;'); //montant facturé en général
		parent::add_champs('montantRefac','type=float;'); //montant refacturé
		parent::add_champs('natureRefac','type=chaine;'); //nature refacturée
		
		//pour une facture
		parent::add_champs('date_facture','type=date;');
		parent::add_champs('idImport','type=chaine;'); //sera le nom du fichier d'import d'où provient la facture.
		parent::add_champs('numFacture','type=chaine;');
		parent::add_champs('compteFacture','type=chaine;');
		
		parent::_init_vars();
		parent::start();

		
		$this->TType = array();	
		$this->TResponsabilite = array('0%', '50%', '100%');
			
	}

	function load_liste(&$ATMdb){
		global $conf;
		
		//chargement d'une liste de touts les TVA (pour le combo "TVA")
		$this->TTVA = array();
		$sqlReq="SELECT rowid, taux FROM ".MAIN_DB_PREFIX."c_tva WHERE fk_pays=".$conf->global->MAIN_INFO_SOCIETE_PAYS[0].' AND active=1';
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TTVA[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('taux');
			}
		
		//chargement d'une liste de touts les users (pour le combo "Utilisateur")
		$this->TUser = array();
		$sqlReq="SELECT rowid, firstname, name FROM ".MAIN_DB_PREFIX."user WHERE entity IN (0,".$conf->entity.") ORDER BY name, firstname";
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TUser[$ATMdb->Get_field('rowid')] = htmlentities($ATMdb->Get_field('firstname')." ".strtoupper($ATMdb->Get_field('name')), ENT_COMPAT , 'ISO8859-1'); 
			}
	}

	function load_liste_type($idRessourceType){
		global $conf;
		$this->TType = getTypeEvent($idRessourceType);		
		
	}
	static function listTypeEvent(&$ATMdb, $id_ressource) {
		$TEvent = array();
	
		$sql="SELECT rowid, code, libelle FROM ".MAIN_DB_PREFIX."rh_type_evenement 
		WHERE (fk_rh_ressource_type=".(int)$idTypeRessource." OR fk_rh_ressource_type=0) ORDER BY fk_rh_ressource_type";
		
		$ATMdb->Execute($sql);
		
		while($row = $ATMdb->Get_line()) {
			$TEvent[$row->code] = $row->libelle;	
		}
		
		return $TEvent;
	}
	function save(&$db) {
		global $conf;
		//si l'entité n'est pas encore renseigné, on met celle de l'entité courante.
		$this->entity = (empty($this->entity)) ? $conf->entity : $this->entity ;
		
		if ($this->date_fin < $this->date_debut) {
			$this->date_fin = $this->date_debut;
		}
		
		if (empty($this->confidentiel)){$this->confidentiel = 'oui';}
		
		/*$sqlReq="SELECT rowid, libelle FROM ".MAIN_DB_PREFIX."rh_ressource 
		WHERE rowid=".$this->fk_rh_ressource;
		$db->Execute($sqlReq);
		while($db->Get_line()) {
			$nom = $db->Get_field('libelle');
		}*/
			
		$this->load_liste($db);
		$this->TType =  TRH_Evenement::listTypeEvent($db, $this->fk_rh_ressource_type);
		
		switch($this->type){
			case 'accident':
				$this->color= 8; 
				break;
			case 'reparation':
				$this->color= 11;
				break;
			case 'facture':
				$this->color= 17; 
				break;
			case 'emprunt' :
			 	$this->color= 6; //bleu-vert clair vif
				break;
			default :
			 	$this->color= 1; //couleur rouge
				break;
		}
		
		/*if ($this->type=='emprunt'){
			$this->subject = "[ ".$nom." ] Utilisé par ".$this->TUser[$this->fk_user];
		}
		else {
			$this->subject = "[ ".$nom." ] ".$this->TType[$this->type]." : ".$this->motif;
		}*/
		
		$this->attributionAuto($db);
		
		$this->isAllDayEvent = 1;
		//if (empty($this->coutEntrepriseHT)) {
			$this->coutEntrepriseHT =  round($this->coutEntrepriseTTC * (100/($this->TTVA[$this->TVA] + 100)),2); 
		//}
		parent::save($db);
		
	}
	
	function attributionAuto(&$ATMdb){
		global $conf, $db, $TGroupeAutomaticAttributionByAnalytique;
		
		if(defined('AUTOMATIC_ATTRIBUTION_USER_ENTITY_ON_RESSOURCE') && AUTOMATIC_ATTRIBUTION_USER_ENTITY_ON_RESSOURCE 
		&& $this->type == 'emprunt'
		&& $this->date_fin>=strtotime(date('Y-m-d'))
		&& $this->date_debut<=strtotime(date('Y-m-d'))
		){
			
			$ressource = new TRH_Ressource();
			$ressource->load($ATMdb, $this->fk_rh_ressource);
			
			$utilisateur = new User($db);
			
			
			if($conf->multicompany->transverse_mode){
				$utilisateur->fetch_optionals($this->fk_user, array('ldap_entity_login'=>'ldap_entity_login'));
				$ressource->fk_entity_utilisatrice = $utilisateur->array_options['options_ldap_entity_login'];
			}
			elseif($conf->multicompany){
				$ressource->fk_entity_utilisatrice = $utilisateur->entity;
			}
			
			if(isset($TGroupeAutomaticAttributionByAnalytique)) {
		
				$ressource->fk_utilisatrice = $this->get_AAGroupe($ATMdb, $TGroupeAutomaticAttributionByAnalytique);
				
			}
			
			//print_r($utilisateur);
			$ressource->save($ATMdb);
		}
	}
	
	function get_AAGroupe(&$ATMdb, $TGroupeAutomaticAttributionByAnalytique) {
		global $db;
		
				dol_include_once('/valideur/class/analytique_user.class.php');
				dol_include_once('/user/class/usergroup.class.php');
		
				$TAnal = TRH_analytique_user::getUserAnalytique($ATMdb, $this->fk_user);
				
				foreach($TGroupeAutomaticAttributionByAnalytique as $mask=>$groupName) {
					
					foreach($TAnal as $anal) { 
						//print "{$anal->code} $mask => $groupName<br />";
						if(strpos($anal->code, $mask)===0) { // si le début correspond
							
							$grp=new UserGroup($db);
							$grp->fetch('',$groupName);
						
							
							
							return $grp->id;
						}
						
					}
					
				}
			return false;
	}
}	



class TRH_Type_Evenement  extends TObjetStd {
	
	function __construct(){
		parent::set_table(MAIN_DB_PREFIX.'rh_type_evenement');
		parent::add_champs('libelle, code, codecomptable, supprimable','type=chaine;');
		parent::add_champs('fk_rh_ressource_type','type=entier;index;');	
		
		parent::_init_vars();
		parent::start();
		
	}
	
	function load_by_code(&$db, $code){
		$sqlReq="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_type_evenement WHERE code='".$code."'";
		$db->Execute($sqlReq);
		
		if ($db->Get_line()) {
			$this->load($db, $db->Get_field('rowid'));
			return true;
		}
		return false;
	}
	
	/**
	 * Attribut les champs directement, pour créer les types par défauts par exemple. 
	 */
	function chargement(&$db, $libelle, $code, $codecomptable, $supprimable, $fk_rh_ressource_type){
		if (empty($code)){$this->code = TRH_Ressource_type::code_format($libelle);}
		else $this->code = $code;
		$this->load_by_code($db, $this->code);
		$this->libelle = $libelle;
		$this->codecomptable = $codecomptable;
		$this->supprimable = $supprimable;
		$this->fk_rh_ressource_type = $fk_rh_ressource_type;
		$this->save($db);
	}
	function save(&$db) {
		if (empty($this->supprimable)) {$this->supprimable = 'vrai';}
		if (empty($this->fk_rh_ressource_type)){$this->fk_rh_ressource_type = 0;}
		parent::save($db);
	}


}


class TRH_Evenement_appel  extends TObjetStd {
	
	function __construct(){
		
		parent::set_table(MAIN_DB_PREFIX.'rh_evenement_appel');
		parent::add_champs('idImport','type=chaine;index;');
		parent::add_champs('compte_facture,num_gsm,nom_abonne,num_facture','type=chaine;');
		parent::add_champs('date_facture','type=date;');
		parent::add_champs('num_abonne','type=chaine;');
		parent::add_champs('date_appel','type=date;');
		parent::add_champs('num_appele','type=chaine;');
		parent::add_champs('volume_facture,volume_reel','type=datetime;');
		parent::add_champs('type_appel','type=chaine;');
		parent::add_champs('montant_euros_ht','type=float;');
		
		parent::_init_vars();
		parent::start();
		
	}
}