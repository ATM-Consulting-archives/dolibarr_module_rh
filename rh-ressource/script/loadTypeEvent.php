<?php
require('../config.php');
global $conf;

if(isset($_REQUEST['type'])) {
		
		//echo $_REQUEST['type'];
		$TEvent = array(
			'all'=>''
			,'accident'=>'Accident'
			,'reparation'=>'Réparation'
			,'facture'=>'Facture'
		);	
		$ATMdb =new TPDOdb;
		

		$sqlReq="SELECT rowid, liste_evenement_value, liste_evenement_key FROM ".MAIN_DB_PREFIX."rh_ressource_type 
		WHERE rowid=".$_REQUEST['type']." AND entity=".$conf->entity;
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$keys = explode(';', $ATMdb->Get_field('liste_evenement_key'));
			$values = explode(';', $ATMdb->Get_field('liste_evenement_value'));
			foreach ($values as $i=>$value) {
				if (!empty($value)){
					$TEvent[$keys[$i]] = $values[$i];
				}
			}
		}
		
		echo json_encode($TEvent);
		
		exit();
	}
	