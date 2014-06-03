<?php

class TRH_TicketResto extends TObjetStd {
	function __construct() { 
		parent::set_table(MAIN_DB_PREFIX.'rh_ticketresto');
		parent::add_champs('nbTicket','type=entier;');
		parent::add_champs('date_distribution','type=date;index');
		parent::add_champs('montant,partpatron','type=entier;');	//utilisateur concerné
		parent::add_champs('entity,fk_user','type=entier;index;');
		
		parent::add_champs('code_produit,code_client,pointlivraison,niveau1,niveau2,matricule,nomcouv,nomtitre,raisonsociale,cp,ville,rscarnet,cpcarnet','type=chaine;');
		
		parent::_init_vars();
		parent::start();	
		
		$this->choixApplication = 'all';
		
		$this->TUser = array();
		$this->TGroup  = array();
		$this->TChoixApplication = array(
			'all'=>'Tous'
			,'group'=>'Groupe'
			,'user'=>'Utilisateur'
		);
	}
	
	function loadByUserDate(&$ATMdb, $fk_user, $date_distribution) {
		
		$ATMdb->Execute("SELECT rowid FROM ".$this->get_table()." WHERE fk_user=".$fk_user." AND date_distribution='".$date_distribution."'"  );
		if($obj=$ATMdb->Get_line()) {
			return $this->load($ATMdb, $obj->rowid);
		}
		else {
			return false;
		}
		
	}
	
	static function isNDFforDay(&$ATMdb, $date, $fk_user) {
		global $conf;
		
		$ATMdb->Execute("SELECT count(*) as nb 
		FROM ".MAIN_DB_PREFIX."ndfp_det nd LEFT JOIN ".MAIN_DB_PREFIX."ndfp n ON (nd.fk_ndfp=n.rowid)
		WHERE n.fk_user=".$fk_user." AND nd.fk_exp IN (".$conf->global->RH_NDF_TICKET_RESTO.") AND nd.dated<='".$date."' AND nd.datef>='".$date."'");
		$obj = $ATMdb->Get_line();
		
		return ($obj->nb!=0);
		
	}
	
	static function getTicketFor(&$ATMdb, $date_debut, $date_fin, $idGroup=0, $fk_user=0) {
		$Tab=array();
		$TAbsence = TRH_Absence::getPlanning($ATMdb, $idGroup, $fk_user, $date_debut, $date_fin);	
		
		foreach($TAbsence as $fk_user=>$TAbs) {
			
			$presence = 0;
			$ndf = 0;
			
			foreach($TAbs as $date=>$row) {
				
				$presence+=	$row['presence_jour_entier'];	
				if(	$row['presence_jour_entier'] ) {
					
					$ndf+=	TRH_TicketResto::isNDFforDay($ATMdb, $date, $fk_user);
					
				}
				
				
				
			}
			
			$Tab[$fk_user]=array(
				'presence'=>$presence
				,'ndf'=>$ndf
			);
		}
		
		
		return $Tab;
		
	}
	static function getHistory(&$ATMdb, $fk_user) {
		$Tab=array();
			
		$TId = $ATMdb->ExecuteAsArray("SELECT rowid FROM ".MAIN_DB_PREFIX.'rh_ticketresto WHERE fk_user='.$fk_user." ORDER BY date_distribution DESC");
		
		foreach($TId as $row) {
			
			$t=new TRH_TicketResto;
			$t->load($ATMdb, $row->rowid);
			
			$Tab[] = $t;
		}
			
			
		return $Tab;
	}
}