<?php
require('../config.php');
require('../lib/absence.lib.php');
global $conf,$user;

if(isset($_REQUEST['user'])) {
		
		$TCompteur = array();
		$ATMdb =new TPDOdb;
		$congePrec =array();
		$sql="SELECT * FROM `".MAIN_DB_PREFIX."rh_compteur` where fk_user=".$_REQUEST['user'];
		
		$ATMdb->Execute($sql);
		while($ATMdb->Get_line()) {
			$TCompteur['mensuel']=round2Virgule($ATMdb->Get_field('rttAcquisMensuel'));
			$TCompteur['annuelCumule']=round2Virgule($ATMdb->Get_field('rttAcquisAnnuelCumule'));
			$TCompteur['annuelNonCumule']=round2Virgule($ATMdb->Get_field('rttAcquisAnnuelNonCumule'));
			
			$congePrec['acquisEx']=$ATMdb->Get_field('acquisExerciceNM1');
			$congePrec['acquisAnc']=$ATMdb->Get_field('acquisAncienneteNM1');
			$congePrec['acquisHorsPer']=$ATMdb->Get_field('acquisHorsPeriodeNM1');
			$congePrec['reportConges']=$ATMdb->Get_field('reportCongesNM1');
			$congePrec['congesPris']=$ATMdb->Get_field('congesPrisNM1');
		}

		$congePrecTotal=$congePrec['acquisEx']+$congePrec['acquisAnc']+$congePrec['acquisHorsPer']+$congePrec['reportConges'];
		$TCompteur['reste']=round2Virgule($congePrecTotal-$congePrec['congesPris']);
		
		echo json_encode($TCompteur);
		
		exit();
}
	