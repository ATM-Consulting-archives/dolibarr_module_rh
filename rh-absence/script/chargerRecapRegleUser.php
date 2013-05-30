<?php
require('../config.php');
require('../lib/absence.lib.php');


if(isset($_REQUEST['idUser'])) {
		
		$ATMdb =new TPDOdb;
		global $conf,$user;
		$sql="SELECT DISTINCT r.typeAbsence, r.`nbJourCumulable`, r. `restrictif`, 
		r.fk_user, r.fk_usergroup, r.choixApplication
		FROM ".MAIN_DB_PREFIX."usergroup_user as g, ".MAIN_DB_PREFIX."rh_absence_regle as r
		WHERE r.choixApplication Like 'user' AND r.fk_user=".$_REQUEST['idUser']."
		OR (r.choixApplication Like 'all')
		OR (r.choixApplication Like 'group' AND r.fk_usergroup=g.fk_usergroup AND g.fk_user=".$_REQUEST['idUser'].") 
		AND r.entity IN (0,".$conf->entity.")
		ORDER BY r.nbJourCumulable";

		$ATMdb->Execute($sql);
		$TRegle = array();
		$k=0;
		while($ATMdb->Get_line()) {
			$TRegle[$k]['libelle']= saveLibelle($ATMdb->Get_field('typeAbsence'));
			$TRegle[$k]['nbJourCumulable']= $ATMdb->Get_field('nbJourCumulable');
			$TRegle[$k]['restrictif']= $ATMdb->Get_field('restrictif');
			$k++;
		}
		
		echo json_encode($TRegle);
		
		exit();
}
	