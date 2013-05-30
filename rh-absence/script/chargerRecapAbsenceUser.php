<?php
require('../config.php');
require('../lib/absence.lib.php');
global $conf,$user;

if(isset($_REQUEST['idUser'])) {
		
		$ATMdb =new TPDOdb;
		global $conf;
		$sql="SELECT DATE_FORMAT(date_debut, '%d/%m/%Y') as 'dateD', 
		DATE_FORMAT(date_fin, '%d/%m/%Y')  as 'dateF', libelle, libelleEtat 
		FROM `".MAIN_DB_PREFIX."rh_absence` WHERE fk_user=".$_REQUEST['idUser']." 
		GROUP BY date_cre LIMIT 0,10";

		$ATMdb->Execute($sql);
		$TRecap=array();
		$k=0;
		while($ATMdb->Get_line()) {		
			$TRecap[$k]['date_debut']=$ATMdb->Get_field('dateD');
			$TRecap[$k]['date_fin']=$ATMdb->Get_field('dateF');
			$TRecap[$k]['libelle']=$ATMdb->Get_field('libelle');
			$TRecap[$k]['libelleEtat']=$ATMdb->Get_field('libelleEtat');
			$k++;
		}
		
		echo json_encode($TRecap);
		
		exit();
}
	