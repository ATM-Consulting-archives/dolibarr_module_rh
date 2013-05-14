<?php
class TRH_Evenement  extends TObjetStd {
	
	function __construct(){
		parent::set_table(MAIN_DB_PREFIX.'rh_evenement');
		parent::add_champs('date_debut, date_fin','type=date;');
		parent::add_champs('fk_rh_ressource','type=entier;index;');	
		parent::add_champs('fk_user,entity','type=entier;index;');
		parent::add_champs('motif','type=chaine;');
		parent::add_champs('commentaire','type=chaine;');
		
		//type : accident, répération, emprunt, appel ou facture
		parent::add_champs('type','type=chaine;');

		//pour le wdCalendar
		parent::add_champs('color','type=chaine;');
		parent::add_champs('isAllDayEvent','type=entier;');
		parent::add_champs('subject','type=chaine;');
		
		
		//pour un accident, une réparation
		parent::add_champs('responsabilite','type=entier;');
		parent::add_champs('coutTTC','type=float;');
		parent::add_champs('coutEntrepriseTTC','type=float;');
		parent::add_champs('TVA','type=entier;'); //indice de la TVA dans le tableau $this->TTVA
		parent::add_champs('coutEntrepriseHT','type=float;');
		
		parent::add_champs('litreEssence','type=float;'); //pour des pleins d'essences
		parent::add_champs('kilometrage','type=entier;');
		
		//pour un appel
		parent::add_champs('appelHeure','type=chaine;');
		parent::add_champs('appelNumero','type=chaine;');
		parent::add_champs('appelDureeReel','type=chaine;');
		parent::add_champs('appelDureeFacturee','type=chaine;');
		parent::add_champs('fk_facture','type=entier;index');
		
		//pour une facture
		parent::add_champs('numFacture','type=chaine;');
		parent::add_champs('compteFacture','type=chaine;');
		parent::add_champs('numContrat','type=chaine;');
		parent::add_champs('fk_contrat','type=entier;index');
		
		parent::_init_vars();
		parent::start();

		
		$this->TType = array(
			'all'=>''
			,'accident'=>'Accident'
			,'reparation'=>'Réparation'
			,'facture'=>'Facture'
			,'divers'=>'Divers'
		);	
			
	}

	function load_liste(&$ATMdb){
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
		$sqlReq="SELECT rowid, firstname, name FROM ".MAIN_DB_PREFIX."user WHERE entity=".$conf->entity;
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TUser[$ATMdb->Get_field('rowid')] = htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1')." ".htmlentities($ATMdb->Get_field('name'), ENT_COMPAT , 'ISO8859-1'); 
			}
	}

	function load_liste_type(&$ATMdb, $idRessourceType){
		global $conf;
		$sqlReq="SELECT rowid, liste_evenement_value, liste_evenement_key FROM ".MAIN_DB_PREFIX."rh_ressource_type 
		WHERE rowid=".$idRessourceType." AND entity=".$conf->entity;
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$keys = explode(';', $ATMdb->Get_field('liste_evenement_key'));
			$values = explode(';', $ATMdb->Get_field('liste_evenement_value'));
			foreach ($values as $key=>$value) {
				if (!empty($value)){
					$this->TType[$keys[$key]] = $values[$key];
				}
			}
		}
		
		
	}
	
	function save(&$db) {
		global $conf;
		$this->entity = $conf->entity;
		
		if ($this->date_fin < $this->date_debut) {
			$this->date_fin = $this->date_debut;
		}
		
		$sqlReq="SELECT rowid, libelle FROM ".MAIN_DB_PREFIX."rh_ressource 
		WHERE rowid=".$this->fk_rh_ressource." AND entity=".$conf->entity;
		$db->Execute($sqlReq);
		while($db->Get_line()) {
			$nom = $db->Get_field('libelle');
		}
			
		$this->load_liste($db);
		$this->load_liste_type($db, $this->fk_rh_ressource_type);
		
		switch($this->type){
			case 'accident':
				$this->color= 8; 
				break;
			case 'reparation':
				$this->color= 11;
				break;
			case 'appel' :
				$this->color= 14;
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
		
		if ($this->type=='emprunt'){
			$this->subject = "[ ".$nom." ] Utilisé par ".$this->TUser[$this->fk_user];
		}
		else {
			$this->subject = "[ ".$nom." ] ".$this->TType[$this->type]." : ".$this->motif;
		}
		
		$this->isAllDayEvent = 1;
		if (empty($this->coutEntrepriseHT)) {$this->coutEntrepriseHT = ($this->coutEntrepriseTTC)*(1-(0.01*$this->TTVA[$this->TVA]));}
		parent::save($db);
		
	}
	
}	