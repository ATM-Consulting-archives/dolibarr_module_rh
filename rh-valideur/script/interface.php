<?php

define('INC_FROM_CRON_SCRIPT', true);
require('../config.php');

//Interface qui renvoie toutes les lignes de notes de frais étant classées comme "comptabilisées"
$ATMdb=new Tdb;

$get = isset($_REQUEST['get'])?$_REQUEST['get']:'ndf';

_get($ATMdb, $get);

function _get(&$ATMdb, $case) {
	switch ($case) {
		case 'ndf':
			__out(_ndf($ATMdb,$_REQUEST['date_debut'], $_REQUEST['date_fin'], $_REQUEST['type'], $_REQUEST['entity']));
			break;
		case 'situation_perso':
			__out( _situation_perso($ATMdb,$_REQUEST['fk_user']));	
			break;
		case 'situation_pro':
			__out( _situation_pro($ATMdb,$_REQUEST['fk_user']));	
			break;
	}
}

function _ndf(&$ATMdb, $date_debut, $date_fin, $type, $entity){
	global $langs, $db, $user, $conf;
	
	$langs->load('ndfp@ndfp');
	$langs->load('main');
	
	$TabNdf=array();
	
	$date_debut=explode("/", $date_debut);
	$date_debut=date('Y-m-d',mktime(0, 0, 0, $date_debut[1], $date_debut[0], $date_debut[2]));
	$date_fin=explode("/", $date_fin);
	$date_fin=date('Y-m-d',mktime(0, 0, 0, $date_fin[1], $date_fin[0], $date_fin[2]));
	
	/**----***********************----**/
	/**----** Ligne de l'entité **----**/
	/**----***********************----**/
	
	$sql = "SELECT
			e.label as 'label'
			FROM ".MAIN_DB_PREFIX."entity as e
			WHERE e.rowid IN (0,".$entity.")";
	
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TabNdf[]=$ATMdb->Get_field('label');
	}
	
	/**----**********************----**/
	/**----** Lignes de débit **----**/
	/**----**********************----**/
	
	$sql = "SELECT
			t.accountancy_code
			,CAST(SUM(l.total_ht) as DECIMAL(16,2)) as 'total_ht'
			
			FROM ".MAIN_DB_PREFIX."ndfp_det as l
				LEFT JOIN ".MAIN_DB_PREFIX."ndfp as n ON n.rowid = l.fk_ndfp
				LEFT JOIN ".MAIN_DB_PREFIX."c_exp as t ON t.rowid = l.fk_exp
			
			WHERE n.statut = 1
			AND n.entity IN (".$entity.")
			AND n.type LIKE '".$type."'
			AND (n.datef>='".$date_debut."' AND n.datef<='".$date_fin."')
			GROUP BY t.accountancy_code";
	
	if(isset($_REQUEST['DEBUG'])) {
		print $sql;
	}
	
	$ATMdb2=new Tdb;
	$ndf_exist=0;
			
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$code_compta		=	$ATMdb->Get_field('accountancy_code');
		$total_ht			=	$ATMdb->Get_field('total_ht');
		
		$line = array('NDF', date('dmy'), 'OD', $code_compta, 'G', '', '', 'NOTE DE FRAIS '.date('m').'/'.date('Y'), 'V', date('dmy'), 'D', $total_ht, 'N', '', '', 'EUR', '', '');
		$TabNdf[]=$line;
		
		$sql_anal = "SELECT
						l.rowid
						, l.total_ht as 'total_ht'
						, a.code as 'code_analytique'
						, a.pourcentage as 'pourcentage'
					
					FROM ".MAIN_DB_PREFIX."ndfp_det as l
						LEFT JOIN ".MAIN_DB_PREFIX."ndfp as n ON n.rowid = l.fk_ndfp
						LEFT JOIN ".MAIN_DB_PREFIX."c_exp as t ON t.rowid = l.fk_exp
						LEFT JOIN ".MAIN_DB_PREFIX."rh_analytique_user as a ON a.fk_user = n.fk_user
					
					WHERE n.statut = 1
					AND n.entity IN (".$entity.")
					AND n.type LIKE '".$type."'
					AND (n.datef>='".$date_debut."' AND n.datef<='".$date_fin."')
					AND t.accountancy_code = ".$code_compta."
					
		";
		
		if(isset($_REQUEST['DEBUG'])) {
			print $sql_anal;
		}
		
		$ATMdb2->Execute($sql_anal);
		while($ATMdb2->Get_line()) {
			$code_analytique	=	$ATMdb2->Get_field('code_analytique');
			$pourcentage		=	$ATMdb2->Get_field('pourcentage');
			$total_ht			=	$ATMdb2->Get_field('total_ht');
			$total_ht			=	round($total_ht*($pourcentage/100),2);
			
			if(!empty($code_analytique)) {
				$line = array('NDF', date('dmy'), 'OD', $code_compta, 'A', $code_analytique, '', 'NOTE DE FRAIS '.date('m').'/'.date('Y'), 'V', date('dmy'), 'D', $total_ht, 'N', '', '', 'EUR', '', '');
				$TabNdf[]=$line;
				
			}
			
		}
		
		$ndf_exist=1;
	}
	
	/**----**********************----**/
	/**----**** Ligne de TVA ****----**/
	/**----**********************----**/
	
	if($ndf_exist){
		$sql = "SELECT CAST(SUM(n.total_tva) as DECIMAL(16,2)) as 'total_tva'
					FROM ".MAIN_DB_PREFIX."ndfp as n
					WHERE n.statut = 1
					AND n.entity IN (".$entity.")
					AND n.type LIKE '".$type."'
					AND (n.datef>='".$date_debut."' AND n.datef<='".$date_fin."')";
		
		if(isset($_REQUEST['DEBUG'])) {
			print $sql;
		}
		
		$ATMdb->Execute($sql);
		while($ATMdb->Get_line()) {
			$total_tva_ndf	=	$ATMdb->Get_field('total_tva');
			
			$line = array('NDF', date('dmy'), 'OD', '445660', 'G', '', '', 'NOTE DE FRAIS '.date('m/Y'), 'V', date('dmy'), 'D', $total_tva_ndf, 'N', '', '', 'EUR', '', '');
			$TabNdf[]=$line;
		}
	}
	
	/**----**********************----**/
	/**----** Lignes de crédit **----**/
	/**----**********************----**/
	
	$sql = "SELECT
					n.ref as 'ref'
					,CAST(n.total_ttc as DECIMAL(16,2)) as 'total_ttc'
					,n.datee as 'datef'
					,e.COMPTE_TIERS as 'compte_tiers'
					,u.login as 'login'
					,u.firstname as 'firstname'
					,u.name as 'lastname'
					FROM ".MAIN_DB_PREFIX."ndfp as n
					LEFT JOIN ".MAIN_DB_PREFIX."user as u ON u.rowid = n.fk_user
						LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as e ON u.rowid = e.fk_object
				WHERE n.statut = 1
				AND n.entity IN (".$entity.")
				AND n.type LIKE '".$type."'
				AND (n.datef>='".$date_debut."' AND n.datef<='".$date_fin."')
				GROUP BY n.rowid";
	
	if(isset($_REQUEST['DEBUG'])) {
		print $sql;
	}
	
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$ref			=	$ATMdb->Get_field('ref');
		$compte_tiers	=	$ATMdb->Get_field('compte_tiers');
		
		if(isset($_REQUEST['withLogin'])) {
			$compte_tiers.=" (".$ATMdb->Get_field('firstname').' '.$ATMdb->Get_field('lastname').")";
		}
		
		$mois_ndf		=	substr($ATMdb->Get_field('datef'), 5, 2);
		$annee_ndf		=	substr($ATMdb->Get_field('datef'), 0, 4);
    	$datef_ndf		=	substr($ATMdb->Get_field('datef'), 8, 2).substr($ATMdb->Get_field('datef'), 5, 2).substr($ATMdb->Get_field('datef'), 2, 2);
    	$total_ttc_ndf	=	$ATMdb->Get_field('total_ttc');
		
		$line = array('NDF', $datef_ndf, 'OD', '425902', 'X', $compte_tiers, $ref, 'NOTE DE FRAIS '.$mois_ndf.'/'.$annee_ndf, 'V', date('dmy'), 'C', $total_ttc_ndf, 'N', '', '', 'EUR', '', '');
		$TabNdf[]=$line;
	}
	
	return $TabNdf;
}

function _situation_perso(&$ATMdb, $userId){
	global $user, $conf;
		
	$TabRecapSituationPerso=array();
	
	$sql="SELECT e.DDN as 'ddn', e.SIT_FAM as 'situation_famille', e.NB_ENF_CHARGE as 'nb_enfants'
	FROM ".MAIN_DB_PREFIX."user_extrafields as e
	WHERE e.fk_object=".$userId;
	
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$ddn=$ATMdb->Get_field('ddn');
		$TabRecapSituationPerso['ddn']=substr($ddn,8,2)."/".substr($ddn,5,2)."/".substr($ddn,0,4);
		$TabRecapSituationPerso['situation_famille']=$ATMdb->Get_field('situation_famille');
		$TabRecapSituationPerso['nb_enfants']=$ATMdb->Get_field('nb_enfants');
	}
	
	return $TabRecapSituationPerso;
	
}

function _situation_pro(&$ATMdb, $userId){
	global $user, $conf;
	
	$TabRecapSituationPro=array();
	
	$sql="SELECT u.job as 'fonction'
	FROM ".MAIN_DB_PREFIX."user as u
	WHERE u.rowid=".$userId;
	
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TabRecapSituationPro['fonction']=$ATMdb->Get_field('fonction');
	}
	
	$sql="SELECT r.date_entreeEntreprise
	FROM ".MAIN_DB_PREFIX."rh_remuneration as r
	WHERE r.fk_user=".$userId;
	
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$date_anciennete=$ATMdb->Get_field('date_entreeEntreprise');
		$TabRecapSituationPro['date_anciennete']=substr($date_anciennete,8,2)."/".substr($date_anciennete,5,2)."/".substr($date_anciennete,0,4);
	}
	
	$sql="SELECT e.HORAIRE as 'horaire', e.STATUT as 'statut', e.NIVEAU as 'niveau', e.CONTRAT as 'contrat'
	FROM ".MAIN_DB_PREFIX."user_extrafields as e
	WHERE e.fk_object=".$userId;
	
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TabRecapSituationPro['horaire']=$ATMdb->Get_field('horaire');
		$TabRecapSituationPro['statut']=$ATMdb->Get_field('statut');
		$TabRecapSituationPro['niveau']=$ATMdb->Get_field('niveau');
		$TabRecapSituationPro['contrat']=$ATMdb->Get_field('contrat');
	}
	
	$sql="SELECT e.label
	FROM ".MAIN_DB_PREFIX."entity as e
	LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (u.entity=e.rowid)
	WHERE u.rowid=".$userId;
	
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TabRecapSituationPro['affectation']=$ATMdb->Get_field('label');
	}
	
	return $TabRecapSituationPro;
	
}
