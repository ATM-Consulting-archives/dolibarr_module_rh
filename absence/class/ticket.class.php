<?php

class TRH_TicketResto extends TObjetStd {
	function __construct() {
		global $langs;
		
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
			'all'=> $langs->trans('AllThis')
			,'group'=> $langs->trans('ApplicationChoiceGroup')
			,'user'=> $langs->trans('ApplicationChoiceUser')
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
	
	static function isNDFforDay(&$ATMdb, $date, $fk_user, $withSuspicisous=false) {
		global $conf;
		
		$sql = "SELECT count(*) as nb 
		FROM ".MAIN_DB_PREFIX."ndfp_det nd LEFT JOIN ".MAIN_DB_PREFIX."ndfp n ON (nd.fk_ndfp=n.rowid)
		WHERE n.fk_user=".$fk_user." AND nd.fk_exp IN (".$conf->global->RH_NDF_TICKET_RESTO.") ";
		
		if($withSuspicisous) {
			$sql .= " AND ((nd.dated<='".$date."' AND nd.datef>='".$date."') OR (nd.datec LIKE '".$date."%') ) ";
		}
		else{
			$sql .= " AND nd.dated<='".$date."' AND nd.datef>='".$date."'";
		}
		
		$ATMdb->Execute($sql);
		$obj = $ATMdb->Get_line();
		
		if($obj->nb>0) return true;
		
		
		$sql = "SELECT count(*) as nb 
		FROM ".MAIN_DB_PREFIX."ndfp_det nd 
				INNER JOIN ".MAIN_DB_PREFIX."ndfp_det_link_user ndl ON (nd.rowid=ndl.fk_ndfpdet)
		WHERE ndl.fk_user=".$fk_user." AND nd.fk_exp IN (".$conf->global->RH_NDF_TICKET_RESTO.") ";
		
		if($withSuspicisous) {
			$sql .= " AND ((nd.dated<='".$date."' AND nd.datef>='".$date."') OR (nd.datec LIKE '".$date."%') ) ";
		}
		else{
			$sql .= " AND nd.dated<='".$date."' AND nd.datef>='".$date."'";
		}
		
		$ATMdb->Execute($sql);
		$obj = $ATMdb->Get_line();
		
		if($obj->nb>0) return true;
		
		return false;
		
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
					
					$ndf_with_suspicious+=TRH_TicketResto::isNDFforDay($ATMdb, $date, $fk_user, true);
				}
				
				
				
			}
			
			$Tab[$fk_user]=array(
				'presence'=>$presence
				,'ndf'=>$ndf
				,'ndf_suspicious'=>$ndf_with_suspicious - $ndf
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