<?php
/*
 * Script exportant toutes les lignes de notes de frais étant classées comme "comptabilisées"
 * 
 */
 	define('INC_FROM_CRON_SCRIPT', true);
	require('../config.php');
	
	global $db, $langs;
	
	$langs->load('ndfp@ndfp');
	$langs->load('main');
	
	$sql = "SELECT";
	$sql.= " n.ref,";
	$sql.= " l.datef,";
	$sql.= " t.code_compta,";
	$sql.= " t.label,";
	$sql.= " CONCAT(CAST(l.total_ttc as DECIMAL(16,2)), '€') as 'total_ttc',";
	$sql.= " u.code_analytique";
	
    $sql.= " FROM ".MAIN_DB_PREFIX."ndfp_det as l";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."ndfp as n ON n.rowid = l.fk_ndfp";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."c_exp as t ON t.rowid = l.fk_exp";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."user as u ON u.rowid = n.fk_user";
    $sql.= " WHERE n.statut = 1";
	$sql.= " GROUP BY l.rowid";
	
	$resql=$db->query($sql);
	
	$fichier = fopen("../tpl/compta.txt", "w+"); 
	
	if ($resql){
        $num = $db->num_rows($resql);
        $m = 0;
        if ($num){
            while ($m < $num){
                $obj = $db->fetch_object($resql);

                if ($obj){
					$ref				=	$obj->ref;
					$datef				=	dol_print_date($obj->datef,"day");
					$code_compta		=	$obj->code_compta;
					$label				=	$obj->label;
					$total_ttc			=	$obj->total_ttc;
					$code_analytique	=	$obj->code_analytique;
					
					$line = array($ref, $datef, $code_compta,  html_entity_decode($langs->trans($label)), $total_ttc, $code_analytique);
					$line_implode=implode("\t", $line);
					fputs($fichier, $line_implode);
					fputs($fichier, "\n");
				}
                $m++;
            }
        }
    }else{
        $error++;
        dol_print_error($db);
    }
    
	$sql = "SELECT";
	$sql.= " CONCAT(CAST(SUM(l.total_ttc) as DECIMAL(16,2)), '€') as 'total_ttc'";
	
    $sql.= " FROM ".MAIN_DB_PREFIX."ndfp_det as l";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."ndfp as n ON n.rowid = l.fk_ndfp";
    $sql.= " WHERE n.statut = 1";
	
	$resql_sum=$db->query($sql);
	
	if ($resql_sum){
        $obj_sum = $db->fetch_object($resql_sum);
		
        if ($obj_sum){
			$total_ttc	=	$obj_sum->total_ttc;
		}
    }else{
        $error++;
        dol_print_error($db);
    }
    
	$line = array("Note de Frais", $total_ttc, $code_analytique);
	$line_implode=implode("\t", $line);
	fputs($fichier, $line_implode);
	fputs($fichier, "\n");
	
	fclose($fichier);
	