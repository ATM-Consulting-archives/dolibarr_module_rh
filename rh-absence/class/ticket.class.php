<?php

class TRH_TicketResto extends TObjetStd {
	function __construct() { 
		parent::set_table(MAIN_DB_PREFIX.'rh_ticketresto');
		parent::add_champs('nbTicket','type=entier;');
		parent::add_champs('date_distribution','type=date;index');
		parent::add_champs('montant,partpatron','type=entier;');	//utilisateur concernÃ©
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
	
	static function isNDFforDay(&$ATMdb, $date, $fk_user, $withSuspicisous=false) {
		global $conf;
		/* Note repas */
		$sql = "SELECT  DISTINCT n.ref 
		FROM ".MAIN_DB_PREFIX."ndfp_det nd LEFT JOIN ".MAIN_DB_PREFIX."ndfp n ON (nd.fk_ndfp=n.rowid)
		WHERE n.fk_user=".$fk_user." AND nd.fk_exp IN (".$conf->global->RH_NDF_TICKET_RESTO.") ";
		
		if($withSuspicisous) {
			$sql .= " AND ((nd.dated<='".$date."' AND nd.datef>='".$date."') OR (nd.datec LIKE '".$date."%') ) ";
		}
		else{
			$sql .= " AND nd.dated<='".$date."' AND nd.datef>='".$date."'";
		}

//var_dump($sql);       print '<br/>';
		
		$ATMdb->Execute($sql);
		$Tab=array();

                while($obj = $ATMdb->Get_line()) {
                        $Tab[] = $obj->ref;
                }
		

		/*Note invité*/
		$sql = "SELECT DISTINCT n.ref 
		FROM ".MAIN_DB_PREFIX."ndfp_det nd 
			INNER JOIN ".MAIN_DB_PREFIX."ndfp n ON (nd.fk_ndfp=n.rowid)
			INNER JOIN ".MAIN_DB_PREFIX."ndfp_det_link_user ndl ON (nd.rowid=ndl.fk_ndfpdet)
		WHERE ndl.fk_user=".$fk_user." AND nd.fk_exp IN (".$conf->global->RH_NDF_TICKET_RESTO.") ";
		
		if($withSuspicisous) {
			$sql .= " AND ((nd.dated<='".$date."' AND nd.datef>='".$date."') OR (nd.datec LIKE '".$date."%') ) ";
		}
		else{
			$sql .= " AND nd.dated<='".$date."' AND nd.datef>='".$date."'";
		}
//var_dump($sql);	print '<br/>';
		$ATMdb->Execute($sql);
//		$Tab=array();

		while($obj = $ATMdb->Get_line()) {
			$Tab[] = $obj->ref;
		}		

		return $Tab;		
	}
	
	static function getTicketFor(&$ATMdb, $date_debut, $date_fin, $idGroup=0, $fk_user=0) {
		$Tab=array();
		$TAbsence = TRH_Absence::getPlanning($ATMdb, $idGroup, $fk_user, $date_debut, $date_fin);	
		
		foreach($TAbsence as $fk_user=>$TAbs) {
			
			$presence = $ndf = $ndf_with_suspicious = 0;
			
			$TRefSuspisious = array();
			foreach($TAbs as $date=>$row) {
				
				$presence+=	$row['presence_jour_entier'];	
				if(	$row['presence_jour_entier'] ) {
					
					$TRefNDF = TRH_TicketResto::isNDFforDay($ATMdb, $date, $fk_user);
					$ndf+=count( $TRefNDF );

					$TRefNDF = TRH_TicketResto::isNDFforDay($ATMdb, $date, $fk_user, true);
					$ndf_with_suspicious+=count( $TRefNDF );

					$TRefSuspisious = array_merge($TRefSuspisious, $TRefNDF);
				}
				
				
				
			}
			
			$Tab[$fk_user]=array(
				'presence'=>$presence
				,'ndf'=>$ndf
				,'ndf_suspicious'=>$ndf_with_suspicious - $ndf
				, 'TRefSuspisious'=>$TRefSuspisious
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
