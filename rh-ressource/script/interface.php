<?php

define('INC_FROM_CRON_SCRIPT', true);
set_time_limit(0);
require('../config.php');
require('../lib/ressource.lib.php');

//Interface qui renvoie les emprunts de ressources d'un utilisateur
$ATMdb=new TPDOdb;

$get = isset($_REQUEST['get'])?$_REQUEST['get']:'emprunt';

_get($ATMdb, $get);

function _get(&$ATMdb, $case) {
	switch (strtolower($case)) {
		case 'emprunt':
			__out( _emprunt($ATMdb, $_REQUEST['fk_user'], $_REQUEST['date_debut'], $_REQUEST['date_fin']));
			break;
		case 'orange':
			__out(_exportOrange($ATMdb, $_REQUEST['date_debut'], $_REQUEST['date_fin'], $_REQUEST['entity']));
			//print_r(_exportOrange($ATMdb, $_REQUEST['date_debut'], $_REQUEST['date_fin'], $_REQUEST['entity']));
			break;
		default:
			__out(_exportVoiture($ATMdb, $_REQUEST['date_debut'], $_REQUEST['date_fin'], $_REQUEST['entity'],
						$_REQUEST['fk_fournisseur'], $_REQUEST['idTypeRessource'] , $_REQUEST['idImport'] ));
			break;
		
		
	}
}

function _exportVoiture(&$ATMdb, $date_debut, $date_fin, $entity, $fk_fournisseur, $idTypeRessource, $idImport){
	$TLignes = array();
	if(isset($_REQUEST['DEBUG'])) {echo $idImport.'<br>';}
						
	
	//$idImport = false;
	$date_debut=explode("/", $date_debut);
	$date_debut=date('Y-m-d',mktime(0, 0, 0, $date_debut[1], $date_debut[0], $date_debut[2]));
	$date_fin=explode("/", $date_fin);
	$date_fin=date('Y-m-d',mktime(0, 0, 0, $date_fin[1], $date_fin[0], $date_fin[2]));
	
	$idVoiture = getIdType('voiture');
	
	/**----***********************----**/
	/**----** Ligne de l'entité **----**/
	/**----***********************----**/
	
	$sql = "SELECT
			e.label as 'label'
			FROM ".MAIN_DB_PREFIX."entity as e
			WHERE e.rowid IN (0,".$entity.")";
			
	if(isset($_REQUEST['DEBUG'])) {
		print $sql;
	}
	
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TLignes[]=$ATMdb->Get_field('label');
	}
	
	/**----***********************----**/
	/**----** Lignes de débit **----**/
	/**----***********************----**/
	
	$sql="SELECT CAST(SUM(e.coutEntrepriseTTC) as DECIMAL(16,2)) as coutEntrepriseTTC, 
				CAST(SUM(e.coutEntrepriseHT) as DECIMAL(16,2)) as coutEntrepriseHT, 
				e.type, e.date_facture, 
				DATE_FORMAT(e.date_debut, '%d%m%y') as date_debut, 
				DATE_FORMAT(e.date_debut, '%m') as mois_date_debut, 
				DATE_FORMAT(e.date_debut, '%Y') as annee_date_debut, 
				r.typeVehicule, u.name, u.firstname, e.entity, t.codecomptable, 
				ue.COMPTE_TIERS
	FROM ".MAIN_DB_PREFIX."rh_evenement as e
	LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (r.rowid=e.fk_rh_ressource)
	LEFT JOIN ".MAIN_DB_PREFIX."rh_type_evenement as t ON (e.type=t.code)
	LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (u.rowid=e.fk_user)
		LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON (u.rowid = ue.fk_object)
	WHERE t.fk_rh_ressource_type = ".$idTypeRessource."
	AND (e.date_debut<='".$date_fin."' AND e.date_debut>='".$date_debut."')
	AND e.entity = ".$entity."
	AND e.fk_fournisseur =".$fk_fournisseur;	
	if ($idImport){ $sql .= " AND e.idImport = '".$idImport."' ";}
	$sql .= " GROUP BY t.codecomptable";
	
	if(isset($_REQUEST['DEBUG'])) {
		print $sql;
	}
	
	$ATMdb2=new TPDOdb;
			
	$ATMdb->Execute($sql);
	while($row = $ATMdb->Get_line()) {
		$montant = $row->coutEntrepriseHT;
		$sens = 'D';
		$code_compta = $row->codecomptable;
		$type_compte = 'G';
		
		$TLignes[] = array(
			'RES'
			,date('dmy', date2ToInt($row->date_facture))
			,'FF'
			,$code_compta
			,$type_compte
			,''
			,''
			,'RESSOURCE '.date('m/Y')
			,'V'
			,date('dmy')
			,$sens
			,$montant
			,'N'
			,''
			,''
			,'EUR'
		);
		
		$sql_anal="SELECT e.rowid
				, e.coutEntrepriseTTC as coutEntrepriseTTC , e.date_facture
				, (e.coutEntrepriseHT * IFNULL(a.pourcentage,100) / 100) as coutEntrepriseHT
				, a.code as 'code_analytique'
				, a.pourcentage as 'pourcentage'
				,u.firstname,u.name,u.rowid as 'fk_user'
		FROM ".MAIN_DB_PREFIX."rh_evenement as e
		LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (r.rowid=e.fk_rh_ressource)
		LEFT JOIN ".MAIN_DB_PREFIX."rh_type_evenement as t ON (e.type=t.code)
		LEFT JOIN ".MAIN_DB_PREFIX."rh_analytique_user as a ON (e.fk_user=a.fk_user)
		LEFT JOIN ".MAIN_DB_PREFIX."user as u ON u.rowid=e.fk_user
		WHERE t.fk_rh_ressource_type = ".$idTypeRessource."
		AND (e.date_debut<='".$date_fin."' AND e.date_debut>='".$date_debut."')
		AND e.entity = ".$entity."
		AND e.fk_fournisseur =".$fk_fournisseur;
		if ($idImport){ $sql_anal .= " AND e.idImport = '".$idImport."' ";}
		$sql_anal .= " AND t.codecomptable = '".$code_compta."'";
		
		if(isset($_REQUEST['DEBUG'])) {
			print $sql_anal;
		}
    		$ATMdb2->Execute($sql_anal);
		$TabAna=array();		
		while($ATMdb2->Get_line()) {

			$code_anal = $ATMdb2->Get_field('code_analytique');
			$total_anal = $ATMdb2->Get_field('coutEntrepriseHT');
//print_r($code_anal);
 		
 			if( isset( $_REQUEST['withLogin'] ) /*&& empty( $code_anal )*/ ) {
				$code_anal .= ' <a href="'.HTTP.'custom/valideur/analytique.php?fk_user='.$ATMdb2->Get_field('fk_user').'">'. $ATMdb2->Get_field('firstname').' '.$ATMdb2->Get_field('name') ."</a>";
			} 
			
			if(isset($_REQUEST['DEBUG'])) {
				print "$code_anal=$total_anal<br/>";
			}
			if(!isset($TabAna[$code_anal])) $TabAna[$code_anal]=0;
			$TabAna[$code_anal]+=$total_anal;
			/*$TabAna[] = array(
				$code_anal
				,number_format($ATMdb2->Get_field('total_ht'),2,'.','' )
			);*/
		}
    
    $nbElement = count($TabAna);
		$total_partiel = 0;$cpt=0;
		foreach($TabAna as $code_analytique=>$total_ht_anal /*$ana*/) {
			//list($code_analytique,$total_ht_anal)=$ana ;
			
			if(isset($_REQUEST['DEBUG'])) {
                                print "<b>$code_analytique=$total_ht_anal</b><br/>";
                        }

			$total_ht_anal = round($total_ht_anal,2);

			if($cpt==$nbElement-1) $total_ht_anal = $montant - $total_partiel;
 			$total_partiel+=$total_ht_anal;
          
          		$type_compte 		= 	'A';
					
					$TLignes[] = array(
						'RES'
						,date('dmy', date2ToInt($row->date_facture))
						,'FF'
						,$code_compta
						,$type_compte
						,$code_analytique
						,''
						,'RESSOURCE '.date('m/Y')
						,'V'
						,date('dmy')
						,$sens
						,number_format($total_ht_anal,2,'.','')
						,'N'
						,''
						,''
						,'EUR'
					);
			 $cpt++;
		}
    
     /* 
		$nb_parts=0;
		$new_code_compta=0;
		$ATMdb2->Execute($sql_anal);
		while($row2 = $ATMdb2->Get_line()) {
			$ligne_id_new = $row2->rowid;
			
			if($new_code_compta){
				if($ligne_id_new!=$ligne_id_old){
					array_pop($TLignes);
					
					$type_compte 		= 	'A';
					$montant_anal		=	number_format($montant-($montant_anal*($nb_parts-1)),2);
					
					$TLignes[] = array(
						'RES'
						,date('dmy')
						,'FF'
						,$code_compta
						,$type_compte
						,$code_analytique
						,''
						,'RESSOURCE '.date('m/Y')
						,'V'
						,date('dmy')
						,$sens
						,$montant_anal
						,'N'
						,''
						,''
						,'EUR'
					);
					
					$nb_parts=0;
					$new_code_compta=0;
				}
			}
			
			$ligne_id_old		=	$ligne_id_new;
			$type_compte 		= 	'A';
			$code_analytique	=	$row2->code_analytique;
			$pourcentage		=	$row2->pourcentage;
			$montant 			= 	$row2->coutEntrepriseHT;
			$montant_anal		=	number_format($montant*($pourcentage/100),2);
			
			if(!empty($code_analytique)) {
				$TLignes[] = array(
					'RES'
					,date('dmy')
					,'FF'
					,$code_compta
					,$type_compte
					,$code_analytique
					,''
					,'RESSOURCE '.date('m/Y')
					,'V'
					,date('dmy')
					,$sens
					,$montant_anal
					,'N'
					,''
					,''
					,'EUR'
				);
				$nb_parts++;
			}
			
			$new_code_compta=1;
		}

		array_pop($TLignes);
		
		$type_compte = 'A';
		$montant_anal =	number_format($montant-($montant_anal*($nb_parts-1)),2);
		
		$TLignes[] = array(
			'RES'
			,date('dmy')
			,'FF'
			,$code_compta
			,$type_compte
			,$code_analytique
			,''
			,'RESSOURCE '.date('m/Y')
			,'V'
			,date('dmy')
			,$sens
			,$montant_anal
			,'N'
			,''
			,''
			,'EUR'
		);
		  */
		$ressource_exist=1;
	}

	/**----**********************----**/
	/**----**** Ligne de TVA ****----**/
	/**----**********************----**/
	
	if($ressource_exist){
		$sql="SELECT CAST(SUM(e.coutEntrepriseTTC) as DECIMAL(16,2)) as coutEntrepriseTTC, 
					CAST(SUM(e.coutEntrepriseHT) as DECIMAL(16,2)) as coutEntrepriseHT , e.date_facture
		FROM ".MAIN_DB_PREFIX."rh_evenement as e
		LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (r.rowid=e.fk_rh_ressource)
		LEFT JOIN ".MAIN_DB_PREFIX."rh_type_evenement as t ON (e.type=t.code)
		WHERE t.fk_rh_ressource_type = ".$idTypeRessource." ";
		
		if ($idTypeRessource==$idVoiture){$sql .= "AND r.typeVehicule = 'VP' ";}
		
		$sql .= "AND (e.date_debut<='".$date_fin."' AND e.date_debut>='".$date_debut."')
		AND e.entity = ".$entity."
		AND e.fk_fournisseur =".$fk_fournisseur;
		if ($idImport){ $sql .= " AND e.idImport = '".$idImport."' ";}
		
		if(isset($_REQUEST['DEBUG'])) {
			print $sql;
		}
		
		$ATMdb->Execute($sql);
		while($row = $ATMdb->Get_line()) {
			$total_tva	=	$ATMdb->Get_field('coutEntrepriseTTC') - $ATMdb->Get_field('coutEntrepriseHT');
			
			$line = array('RES', date('dmy', date2ToInt($row->date_facture)), 'FF', '445660', 'G', '', '', 'RESSOURCE '.date('m/Y'), 'V', date('dmy'), 'D', $total_tva, 'N', '', '', 'EUR', '', '');
			$TLignes[]=$line;
		}
	}
	
	/**----***********************----**/
	/**----** Lignes de crédit **----**/
	/**----***********************----**/
	
	$TLoueurs = array();
	$sql="SELECT rowid, code_fournisseur FROM ".MAIN_DB_PREFIX."societe";
	$ATMdb->Execute($sql);
	while($row = $ATMdb->Get_line()) {
		$TLoueurs[$row->rowid] = $row->code_fournisseur;
	}
	
	$TEntity = array();
	$sql="SELECT rowid, label FROM ".MAIN_DB_PREFIX."entity";
	$ATMdb->Execute($sql);
	while($row = $ATMdb->Get_line()) {
		$TEntity[$row->rowid] = substr($row->label,0,13);
	}
	
	$idTotal = getIdSociete($ATMdb, 'total');
	
	$sql="SELECT CAST(e.coutEntrepriseTTC as DECIMAL(16,2)) as coutEntrepriseTTC, 
				CAST(e.coutEntrepriseHT as DECIMAL(16,2)) as coutEntrepriseHT, type, e.date_facture, 
				DATE_FORMAT(e.date_debut, '%d%m%y') as date_debut, 
				DATE_FORMAT(e.date_debut, '%m') as mois_date_debut, 
				DATE_FORMAT(e.date_debut, '%Y') as annee_date_debut, 
				r.typeVehicule, t.codecomptable, r.fk_loueur, e.fk_fournisseur, 
				r.fk_entity_utilisatrice
	FROM ".MAIN_DB_PREFIX."rh_evenement as e
	LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (r.rowid=e.fk_rh_ressource)
	LEFT JOIN ".MAIN_DB_PREFIX."rh_type_evenement as t ON (e.type=t.code)
	WHERE t.fk_rh_ressource_type = ".$idTypeRessource."
	AND (e.date_debut<='".$date_fin."' AND e.date_debut>='".$date_debut."')
	AND e.fk_fournisseur =".$fk_fournisseur."
	AND e.entity = ".$entity;
	if ($idImport){ $sql .= " AND e.idImport = '".$idImport."'";}
	
	if(isset($_REQUEST['DEBUG'])) {
		print $sql;
	}
	
	
	
	$ATMdb->Execute($sql);
	$TCredits = array();
	
	while($row = $ATMdb->Get_line()) {
		$date = $row->date_debut;
		$date_mois = $row->mois_date_debut;
		$date_annee = $row->annee_date_debut;
		//un VU : on prend le HT
		//un VP on prend le TTC
		if ($idTypeRessource==$idVoiture){
			$montant = (strtoupper($row->typeVehicule) == 'VP') ? $row->coutEntrepriseTTC : $row->coutEntrepriseHT;}
		else {
			$montant = $row->coutEntrepriseTTC;}
		$sens = 'C';
		$code_compta = '425902';
		$type_compte = 'X';
		
		//if($row->fk_entity_utilisatrice==$entity || $row->$fk_fournisseur==$idTotal){
			$compte_tiers=$TLoueurs[$fk_fournisseur];
		/*}else{
			$compte_tiers=$TEntity[$entity];
		}*/
	
		if (empty($TCredits[$compte_tiers])){
			$TCredits[$compte_tiers] = array(
				'RES'
				,date('dmy', date2ToInt($row->date_facture))
				,'FF'
				,$code_compta
				,$type_compte
				,$compte_tiers
				,''
				,'RESSOURCE '.date('m/Y')
				,'V'
				,date('dmy')
				,$sens
				,$montant
				,'N'
				,''
				,''
				,'EUR'
			);
		}
		else {
			$TCredits[$compte_tiers][11] += $montant;
		}
		/*$TLignes[] = array(
			'RES'
			,date('dmy')
			,'FF'
			,$code_compta
			,$type_compte
			,$compte_tiers
			,''
			,'RESSOURCE '.date('m/Y')
			,'V'
			,date('dmy')
			,$sens
			,$montant
			,'N'
			,''
			,''
			,'EUR'
			);*/
	}
	
	foreach ($TCredits as $key => $value) {
		$TLignes[] = $value;
	}
	
	return $TLignes;
	
}


function _exportOrange(&$ATMdb, $date_debut, $date_fin, $entity){
	$TabLigne = array();
	
	$sql="SELECT totalIFact, totalEFact, totalFact, natureRefac, montantRefac, name, firstname, COMPTE_TIERS
	FROM ".MAIN_DB_PREFIX."rh_evenement as e
	LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (u.rowid=e.fk_user)
	LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as c ON (c.fk_object = e.fk_user)
	WHERE e.entity=".$entity."
	AND e.type='factTel' 
	AND (e.date_debut<='".$date_fin."' AND e.date_debut>='".$date_debut."')";
	//echo $sql.'<br>';
	$ATMdb->Execute($sql);
	while($row = $ATMdb->Get_line()) {
		$total = number_format($row->totalIFact+$row->totalEFact+$row->montantRefac, 2);
		if ($total>0){
			$TabLigne[] = array(
				'user'=>htmlentities($row->firstname.' '.$row->name, ENT_COMPAT , 'ISO8859-1')
				,'comptetiers'=>$row->COMPTE_TIERS
				,'int'=>number_format($row->totalIFact,2)
				,'ext'=>number_format($row->totalEFact,2)
				,'naturerefact'=>$row->natureRefac
				,'montantrefact'=>$row->montantRefac != 0 ? number_format($row->montantRefac, 2) : ''
				,'total'=>$total
				);
		}
	}
	
	
	$ATMdb->close();
	return $TabLigne;
}


function _emprunt(&$ATMdb, $userId, $date_debut, $date_fin){
	global $user, $conf;
	
	$TabEmprunt=array();
	
	//on transforme la date du format timestamp en 2013-01-20
	//$timestamp = mktime(0,0,0,substr($date_debut, 3,2),substr($date_debut, 0,2), substr($date_debut, 6,4));
	$date_debut = date("Y-m-d", $date_debut);
	//$timestamp = mktime(0,0,0,substr($date_fin, 3,2),substr($date_fin, 0,2), substr($date_fin, 6,4));
	$date_fin = date("Y-m-d", $date_fin);
	
	$sql="SELECT libelle, numId	
	FROM ".MAIN_DB_PREFIX."rh_evenement as e
	LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (r.rowid=e.fk_rh_ressource)
	WHERE e.entity=".$conf->entity."
	AND e.fk_user=".$userId."
	AND (date_debut<='".$date_fin."' AND date_fin>='".$date_debut."')";
	
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TabEmprunt[]=array(
			'nom'=>$ATMdb->Get_field('libelle').' - '.$ATMdb->Get_field('numId')
			,'date_debut'=>$ATMdb->Get_field('date_debut')
			,'date_fin'=>$ATMdb->Get_field('date_fin')
		);
	}
	
	$ATMdb->close();
	return $TabEmprunt;
}

/**
 * prend un format 2013-03-19 00:00:00 et renvoie un timestamp
 */
function date2ToInt($chaine){
	return mktime(0,0,0,substr($chaine,5,2),substr($chaine,8,2),substr($chaine,0,4));
}

