<?php

class TRH_TicketResto extends TObjetStd {
	function __construct() { 
		parent::set_table(MAIN_DB_PREFIX.'rh_ticketresto');
		parent::add_champs('nombre','type=int;');
		parent::add_champs('date_distribution','type=date;index');
		parent::add_champs('fk_user','type=entier;');	//utilisateur concerné
		parent::add_champs('entity','type=int;');
		
		
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
	
}