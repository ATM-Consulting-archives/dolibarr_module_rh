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
		parent::add_champs('coutHT','type=float;');
		parent::add_champs('coutEntrepriseHT','type=float;');
		parent::add_champs('TVA','type=entier;');
		
		//pour un appel
		parent::add_champs('appelHeure','type=chaine;');
		parent::add_champs('appelNumero','type=chaine;');
		parent::add_champs('appelDureeReel','type=chaine;');
		parent::add_champs('appelDureeFacturee','type=chaine;');
		parent::add_champs('fk_facture','type=entier;index');
		
		//pour une facture
		parent::add_champs('numFacture','type=chaine;');
		parent::add_champs('compteFacture','type=chaine;');		
		
		parent::_init_vars();
		parent::start();

		
		$this->TType = array(
			'all'=>''
			 ,'accident'=>'Accident'
			,'reparation'=>'Réparation'
			,'appel'=>'Appel'
			,'facture'=>'Facture'
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
		$sqlReq="SELECT rowid, firstname, name FROM ".MAIN_DB_PREFIX."user";
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TUser[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('firstname').' '.$ATMdb->Get_field('name');
			}
	}

	function load_liste_type(&$ATMdb, $ressource){
		global $conf;
		$sqlReq="SELECT rowid, liste_evenement_value, liste_evenement_key FROM ".MAIN_DB_PREFIX."rh_ressource_type 
		WHERE rowid=".$ressource->fk_rh_ressource_type." AND entity=".$conf->entity;
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
	function delete(&$db){
		parent::delete($db);
		
		$temp = new TRH_Ressource;
		$temp->load($db, $this->fk_rh_ressource);
		$temp->save($db);	//ça met le statut de la ressource à jour
		
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
		
		parent::save($db);
		$temp->save($db);	//ça met le statut de la ressource liée à jour
	}
	
}	