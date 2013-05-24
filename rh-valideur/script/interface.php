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

function _ndf(&$ATMdb, $date_debut, $date_fin, $type,$entity){
	global $langs, $db, $user, $conf;
	
	$TabNdf=array();
	
	$sql = "SELECT";
	$sql.= " e.label";
    $sql.= " FROM ".MAIN_DB_PREFIX."entity as e";
    $sql.= " WHERE e.rowid IN (0,".$conf->entity.")";
	
	$resql=$db->query($sql);
	if ($resql){
        $obj = $db->fetch_object($resql);
        if ($obj){
			$label = $obj->label;
		}
    }else{
        $error++;
        dol_print_error($db);
    }
	
	$k=0;
	$TabNdf[$k]=$label;
	$k++;
	
	$date_debut=explode("/", $date_debut);
	$date_debut=date('Y-m-d',mktime(0, 0, 0, $date_debut[1], $date_debut[0], $date_debut[2]));
	$date_fin=explode("/", $date_fin);
	$date_fin=date('Y-m-d',mktime(0, 0, 0, $date_fin[1], $date_fin[0], $date_fin[2]));
	
	$langs->load('ndfp@ndfp');
	$langs->load('main');
	
	$sql = "SELECT";
	$sql.= " n.rowid as 'NDF_ID',";
	$sql.= " n.ref,";
	$sql.= " n.datee as 'datef_ndf',";
	$sql.= " l.datef,";
	$sql.= " t.accountancy_code,";
	$sql.= " t.label,";
	$sql.= " v.taux as 'tva',";
	$sql.= " CAST(l.total_ht as DECIMAL(16,2)) as 'total_ht',";
	$sql.= " CAST(l.total_ttc as DECIMAL(16,2)) as 'total_ttc',";
	$sql.= " e.code_analytique,";
	$sql.= " e.COMPTE_TIERS";
	
    $sql.= " FROM ".MAIN_DB_PREFIX."ndfp_det as l";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."ndfp as n ON n.rowid = l.fk_ndfp";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."c_exp as t ON t.rowid = l.fk_exp";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."user as u ON u.rowid = n.fk_user";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as e ON u.rowid = e.fk_object";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."c_tva as v ON v.rowid = l.fk_tva";
    $sql.= " WHERE n.statut = 1";
	$sql.= " AND n.entity IN (0,".$entity.")";
	$sql.= " AND n.type LIKE '".$type."'";
	$sql.= " AND (n.datef>='".$date_debut."' AND n.datef<='".$date_fin."')";
	$sql.= " ORDER BY n.rowid";
	
	if(isset($_REQUEST['DEBUG'])) {
		print $sql;
	}
	
	$resql=$db->query($sql);
	if ($resql){
        $num = $db->num_rows($resql);
        $m = 0;
        if ($num){
            while ($m < $num){
                $obj = $db->fetch_object($resql);

                if ($obj){
                	// Si l'on a parcouru toutes les lignes d'une note de frais
                	if(($obj->ref!=$ref)&&(isset($ref))){
                		$sql_ndf = "SELECT";
                		$sql_ndf.= " CAST(SUM(l.total_ht) as DECIMAL(16,2)) as 'total_ht',";
						$sql_ndf.= " CAST(SUM(l.total_tva) as DECIMAL(16,2)) as 'total_tva',";
						$sql_ndf.= " CAST(SUM(l.total_ttc) as DECIMAL(16,2)) as 'total_ttc',";
						$sql_ndf.= " n.datee as 'datef'";
						
					    $sql_ndf.= " FROM ".MAIN_DB_PREFIX."ndfp_det as l";
						$sql_ndf.= " LEFT JOIN ".MAIN_DB_PREFIX."ndfp as n ON n.rowid = l.fk_ndfp";
					    $sql_ndf.= " WHERE n.statut = 1";
					    $sql_ndf.= " AND n.rowid =".$NDF_ID;
						
						$resql_ndf=$db->query($sql_ndf);
						
						if ($resql_ndf){
					        $obj_ndf = $db->fetch_object($resql_ndf);
							
					        if ($obj_ndf){
					        	$mois_ndf		=	substr($obj_ndf->datef, 5, 2);
								$annee_ndf		=	substr($obj_ndf->datef, 0, 4);
					        	$datef_ndf		=	substr($obj_ndf->datef, 8, 2).substr($obj_ndf->datef, 5, 2).substr($obj_ndf->datef, 2, 2);
					        	$total_ht_ndf	=	$obj_ndf->total_ht;
								$total_ttc_ndf	=	$obj_ndf->total_ttc;
								$total_tva_ndf	=	$obj_ndf->total_tva;
							}
					    }else{
					        $error++;
					        dol_print_error($db);
					    }
					    
					    $line = array('ND', $datef_ndf, 'OD', '425900', 'G', $compte_tiers, $ref, 'NOTE DE FRAIS '.$mois_ndf.'/'.$annee_ndf, 'V', date('dmy'), 'D', $total_tva_ndf, 'N', $ref, '', '', 'EUR', '');
						$TabNdf[$k]=$line;
						$k++;
						$line = array('ND', $datef_ndf, 'OD', '425900', 'G', $compte_tiers, $ref, 'NOTE DE FRAIS '.$mois_ndf.'/'.$annee_ndf, 'V', date('dmy'), 'C', $total_ttc_ndf, 'N', $ref, '', '', 'EUR', '');
						$TabNdf[$k]=$line;
						$k++;
                	}
                	
                	$NDF_ID				=	$obj->NDF_ID;
					$ref				=	$obj->ref;
					$mois				=	substr($obj->datef, 5, 2);
					$annee				=	substr($obj->datef, 0, 4);
					$mois_ndf			=	substr($obj->datef_ndf, 5, 2);
					$annee_ndf			=	substr($obj->datef_ndf, 0, 4);
					$datef				=	substr($obj->datef, 8, 2).substr($obj->datef, 5, 2).substr($obj->datef, 2, 2);
					$code_compta		=	$obj->accountancy_code;
					$label				=	$obj->label;
					$tva				=	$obj->tva;
					$total_ht			=	$obj->total_ht;
					$total_ttc			=	$obj->total_ttc;
					$code_analytique	=	$obj->CODE_ANA;
					$compte_tiers		=	$obj->COMPTE_TIERS;
					
					$line = array('ND', $datef, 'OD', $code_compta, 'G', $compte_tiers, $ref, 'NOTE DE FRAIS '.$mois_ndf.'/'.$annee_ndf, 'V', date('dmy'), 'D', $total_ht, 'N', $ref, '', '', 'EUR', '');
					$TabNdf[$k]=$line;
					$k++;
				}
                $m++;
            }

			$sql_ndf = "SELECT";
    		$sql_ndf.= " CAST(SUM(l.total_ht) as DECIMAL(16,2)) as 'total_ht',";
			$sql_ndf.= " CAST(SUM(l.total_tva) as DECIMAL(16,2)) as 'total_tva',";
			$sql_ndf.= " CAST(SUM(l.total_ttc) as DECIMAL(16,2)) as 'total_ttc',";
			$sql_ndf.= " n.datee as 'datef'";
			
		    $sql_ndf.= " FROM ".MAIN_DB_PREFIX."ndfp_det as l";
			$sql_ndf.= " LEFT JOIN ".MAIN_DB_PREFIX."ndfp as n ON n.rowid = l.fk_ndfp";
		    $sql_ndf.= " WHERE n.statut = 1";
		    $sql_ndf.= " AND n.rowid =".$NDF_ID;
			
			$resql_ndf=$db->query($sql_ndf);
			
			if ($resql_ndf){
		        $obj_ndf = $db->fetch_object($resql_ndf);
				
		        if ($obj_ndf){
		        	$mois_ndf		=	substr($obj_ndf->datef, 5, 2);
					$annee_ndf		=	substr($obj_ndf->datef, 0, 4);
		        	$datef_ndf		=	substr($obj_ndf->datef, 8, 2).substr($obj_ndf->datef, 5, 2).substr($obj_ndf->datef, 2, 2);
		        	$total_ht_ndf	=	$obj_ndf->total_ht;
					$total_ttc_ndf	=	$obj_ndf->total_ttc;
					$total_tva_ndf	=	$obj_ndf->total_tva;
				}
		    }else{
		        $error++;
		        dol_print_error($db);
		    }
		    
		    $line = array('ND', $datef_ndf, 'OD', '425900', 'G', $compte_tiers, $ref, 'NOTE DE FRAIS '.$mois_ndf.'/'.$annee_ndf, 'V', date('dmy'), 'D', $total_tva_ndf, 'N', $ref, '', '', 'EUR', '');
			$TabNdf[$k]=$line;
			$k++;
			$line = array('ND', $datef_ndf, 'OD', '425900', 'G', $compte_tiers, $ref, 'NOTE DE FRAIS '.$mois_ndf.'/'.$annee_ndf, 'V', date('dmy'), 'C', $total_ttc_ndf, 'N', $ref, '', '', 'EUR', '');
			$TabNdf[$k]=$line;
        }
    }else{
        $error++;
        dol_print_error($db);
    }
	
	return $TabNdf;
}

function _situation_perso(&$ATMdb, $userId){
	global $user, $conf;
		
	$TabRecapSituationPerso=array();
	
	$sql="SELECT e.DDN as 'ddn', e.SIT_FAM as 'situation_famille', e.NB_ENF_CHARGE as 'nb_enfants'
	FROM ".MAIN_DB_PREFIX."user_extrafields as e 
		LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (e.fk_object = u.rowid)
	WHERE u.entity=".$conf->entity."
	AND u.rowid=".$userId;
	
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TabRecapSituationPerso['ddn']=dol_print_date($ATMdb->Get_field('ddn'));
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
	WHERE u.entity=".$conf->entity."
	AND u.rowid=".$userId;
	
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TabRecapSituationPro['fonction']=$ATMdb->Get_field('fonction');
	}
	
	return $TabRecapSituationPro;
	
}
