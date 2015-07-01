<?php

define('INC_FROM_CRON_SCRIPT', true);
set_time_limit(0);
ini_set('memory_limit','1024M');

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
			//__out(_exportOrange($ATMdb, $_REQUEST['date_debut'], $_REQUEST['date_fin'], $_REQUEST['entity']));
			__out(_exportOrange2($ATMdb, $_REQUEST['date_debut'], $_REQUEST['date_fin'], $_REQUEST['entity'], $_REQUEST['idImport']));
			//print_r(_exportOrange($ATMdb, $_REQUEST['date_debut'], $_REQUEST['date_fin'], $_REQUEST['entity']));
			break;
		case 'autocomplete':
			__out(_autocomplete($ATMdb,$_REQUEST['fieldcode'],$_REQUEST['term']));
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
				r.typeVehicule, u.name as 'lastname', u.firstname, e.entity, t.codecomptable, 
				ue.COMPTE_TIERS, e.idImport,e.numFacture, r.fk_rh_ressource
	FROM ".MAIN_DB_PREFIX."rh_evenement as e
	LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (r.rowid=e.fk_rh_ressource)
	LEFT JOIN ".MAIN_DB_PREFIX."rh_type_evenement as t ON (e.type=t.code)
	LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (u.rowid=e.fk_user)
		LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON (u.rowid = ue.fk_object)
	WHERE (e.date_debut<='".$date_fin."' AND e.date_debut>='".$date_debut."')
	AND e.entity = ".$entity."
	AND e.fk_fournisseur =".$fk_fournisseur;	
	if ($idImport){ $sql .= " AND e.idImport = '".$idImport."' ";}
	$sql .= " GROUP BY e.numFacture, t.codecomptable";
	
	if(isset($_REQUEST['DEBUG'])) {
		print "SQL Débit ".$sql;
	}
	
	$ATMdb2=new TPDOdb;
			
	$ATMdb->Execute($sql); $montantTotal=0;
	while($row = $ATMdb->Get_line()) {
		$montant = $row->coutEntrepriseHT;
		$montantTotal+= $row->coutEntrepriseTTC;
		$sens = 'D';
		$code_compta = $row->codecomptable;
		$type_compte = 'G';
		$numeroFacture = $row->numFacture;
		
		$TLignes[] = array(
			'numFacture'=>$numeroFacture
			,'codeJournal'=>'RES'
			,'datePiece'=>date('dmy', date2ToInt($row->date_facture))
			,'typePiece'=> 'FF'
			,'compteGeneral'=> $code_compta
			,'typeCompte'=> $type_compte
			,'codeAnalytique'=> ''
			,'nom'=>''
			,'prenom'=>''
			,'referenceEcriture' => ''
			,'libelleEcriture'=> 'RESSOURCE '.date('m/Y')
			,'modePaiement'=> 'V'
			,'dateEcheance'=> date('dmy')
			,'sens'=> $sens
			,'montant'=>  $montant
			,'typeEcriture'=> 'N'
			,'numeroPiece'=> ''
			,'devise'=>'EUR'
			,'idImport'=>$row->idImport
			
		);
if(isset($_REQUEST['DEBUG'])) print 'MontantTotal = '. $montantTotal.'<br />';		
		/*
		 * Exploitation de l'analytique
		 */
		 
		$sql_anal="SELECT DISTINCT e.rowid
				, e.coutEntrepriseTTC as coutEntrepriseTTC , e.date_facture
				, (e.coutEntrepriseHT * IFNULL(a.pourcentage,100) / 100) as coutEntrepriseHT
				, a.code as 'code_analytique'
				, a.pourcentage as 'pourcentage'
				,u.firstname,u.name as 'lastname',u.rowid as 'fk_user'
				,e.idImport,e.numFacture
				,r.fk_rh_ressource
		FROM ".MAIN_DB_PREFIX."rh_evenement as e
		LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (r.rowid=e.fk_rh_ressource)
		LEFT JOIN ".MAIN_DB_PREFIX."rh_type_evenement as t ON (e.type=t.code)
		LEFT JOIN ".MAIN_DB_PREFIX."rh_analytique_user as a ON (e.fk_user=a.fk_user)
		LEFT JOIN ".MAIN_DB_PREFIX."user as u ON u.rowid=e.fk_user
		WHERE (e.date_debut<='".$date_fin."' AND e.date_debut>='".$date_debut."')
		AND e.entity = ".$entity."
		AND e.fk_fournisseur =".$fk_fournisseur;
		if ($idImport){ $sql_anal .= " AND e.idImport = '".$idImport."' ";}
		$sql_anal .= " AND t.codecomptable = '".$code_compta."' AND e.numFacture='".$numeroFacture."'";
		
		if(isset($_REQUEST['DEBUG'])) {
			print $sql_anal;
		}
    		$ATMdb2->Execute($sql_anal);
		$TabAna=array();	$TUser=array();	
		while($ATMdb2->Get_line()) {

			$code_anal = $ATMdb2->Get_field('code_analytique');
			$total_anal = $ATMdb2->Get_field('coutEntrepriseHT');
			$fk_user =  $ATMdb2->Get_field('fk_user');
//print_r($code_anal);

			$TUser[$code_anal][$fk_user]=array(
					'nom' => ' <a href="'.HTTP.'custom/valideur/analytique.php?fk_user='.$ATMdb2->Get_field('fk_user').'">'. $ATMdb2->Get_field('lastname') ." (".$ATMdb2->Get_field('fk_rh_ressource').")</a>"
					,'prenom' => $ATMdb2->Get_field('firstname')
			);
 						
			if(isset($_REQUEST['DEBUG'])) {
				print "$code_anal=$total_anal<br/>";
			}
			if(!isset($TabAna[$code_anal][$fk_user])) $TabAna[$code_anal][$fk_user]=0;
			$TabAna[$code_anal][$fk_user]+=$total_anal;
			/*$TabAna[] = array(
				$code_anal
				,number_format($ATMdb2->Get_field('total_ht'),2,'.','' )
			);*/
		}
    
	    	$nbElement = count($TabAna, COUNT_RECURSIVE );
		$total_partiel = 0;$cpt=0;
		foreach($TabAna as $code_analytique=>$TAnal_user /*$ana*/) {
			
			
			foreach($TAnal_user as $fk_user=>$total_ht_anal) {
			if(isset($_REQUEST['DEBUG'])) {
                                print "<b>$code_analytique=$total_ht_anal</b><br/>";
                        }

			$total_ht_anal = round($total_ht_anal,2);

			if($cpt==$nbElement-1) $total_ht_anal = $montant - $total_partiel;
	 			$total_partiel+=$total_ht_anal;
	          
	          		$type_compte 		= 	'A';
						
						$TLignes[] = array(
							'numFacture'=>$row->numFacture
							,'codeJournal'=>'RES'
							,'datePiece'=>date('dmy', date2ToInt($row->date_facture))
							,'typePiece'=> 'FF'
							,'compteGeneral'=> $code_compta
							,'typeCompte'=> $type_compte
							,'codeAnalytique'=> $code_analytique
							,'nom'=>$TUser[$code_analytique][$fk_user]['nom']
							,'prenom'=>$TUser[$code_analytique][$fk_user]['prenom']
							,'referenceEcriture' => ''
							,'libelleEcriture'=> 'RESSOURCE '.date('m/Y')
							,'modePaiement'=> 'V'
							,'dateEcheance'=> date('dmy')
							,'sens'=> $sens
							,'montant'=>  number_format($total_ht_anal,2,'.','')
							,'typeEcriture'=> 'N'
							,'numeroPiece'=> ''
							,'devise'=>'EUR'
							,'idImport'=>$row->idImport
							
								
						);
				 $cpt++;				
			}
			
			//list($code_analytique,$total_ht_anal)=$ana ;
			

		}
    
     
		$ressource_exist=1;
	}

	/**----**********************----**/
	/**----**** Ligne de TVA ****----**/
	/**----**********************----**/
	
	if($ressource_exist){
		$sql="SELECT CAST(SUM(e.coutEntrepriseTTC) as DECIMAL(16,2)) as coutEntrepriseTTC, 
					CAST(SUM(e.coutEntrepriseHT) as DECIMAL(16,2)) as coutEntrepriseHT , e.date_facture, e.idImport,e.numFacture
		FROM ".MAIN_DB_PREFIX."rh_evenement as e
		LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (r.rowid=e.fk_rh_ressource)
		LEFT JOIN ".MAIN_DB_PREFIX."rh_type_evenement as t ON (e.type=t.code)
		WHERE (e.date_debut<='".$date_fin."' AND e.date_debut>='".$date_debut."')
		AND e.entity = ".$entity."
		AND e.fk_fournisseur =".$fk_fournisseur;
		if ($idImport){ $sql .= " AND e.idImport = '".$idImport."' ";}
		
		$sql.=" GROUP BY e.numFacture ";
		
		if(isset($_REQUEST['DEBUG'])) {
			print $sql;
		}
		
		$ATMdb->Execute($sql);
		while($row = $ATMdb->Get_line()) {
			$total_tva	= number_format(floatval($ATMdb->Get_field('coutEntrepriseTTC')) - floatval($ATMdb->Get_field('coutEntrepriseHT')),2,'.','');
			
			$TLignes[] =array(
				'numFacture'=>$row->numFacture
				,'codeJournal'=>'RES'
				,'datePiece'=>date('dmy', date2ToInt($row->date_facture))
				,'typePiece'=> 'FF'
				,'compteGeneral'=> '445660'
				,'typeCompte'=> 'G'
				,'codeAnalytique'=> ''
				,'nom'=>''
				,'prenom'=>''
				
				,'referenceEcriture' => ''
				,'libelleEcriture'=> 'RESSOURCE '.date('m/Y')
				,'modePaiement'=> 'V'
				,'dateEcheance'=> date('dmy')
				,'sens'=> 'D'
				,'montant'=> $total_tva
				,'typeEcriture'=> 'N'
				,'numeroPiece'=> ''
				,'devise'=>'EUR'
				,'idImport'=>$row->idImport
				
			); 
			
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
	
	$sql="SELECT SUM(e.coutEntrepriseTTC) as coutEntrepriseTTC, 
				e.coutEntrepriseHT as coutEntrepriseHT, type, e.date_facture, 
				DATE_FORMAT(e.date_debut, '%d%m%y') as date_debut, 
				DATE_FORMAT(e.date_debut, '%m') as mois_date_debut, 
				DATE_FORMAT(e.date_debut, '%Y') as annee_date_debut, 
				r.typeVehicule, t.codecomptable, r.fk_loueur, e.fk_fournisseur, 
				r.fk_entity_utilisatrice,e.idImport,e.numFacture
	FROM ".MAIN_DB_PREFIX."rh_evenement as e
	LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (r.rowid=e.fk_rh_ressource)
	LEFT JOIN ".MAIN_DB_PREFIX."rh_type_evenement as t ON (e.type=t.code)
	WHERE (e.date_debut<='".$date_fin."' AND e.date_debut>='".$date_debut."')
	AND e.fk_fournisseur =".$fk_fournisseur."
	AND e.entity = ".$entity;
	if ($idImport){ $sql .= " AND e.idImport = '".$idImport."'";}
	
	$sql.=" GROUP BY e.numFacture ";
	
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
		/*if ($idTypeRessource==$idVoiture){
			$montant = (strtoupper($row->typeVehicule) == 'VP') ? $row->coutEntrepriseTTC : $row->coutEntrepriseHT;}
		else {
			$montant = $row->coutEntrepriseTTC;
		}*/
		
		$montant = $row->coutEntrepriseTTC;
		
		$sens = 'C';
		$code_compta = '401100'; //TODO paramètre
		$type_compte = 'X';
		
		//if($row->fk_entity_utilisatrice==$entity || $row->$fk_fournisseur==$idTotal){
			$compte_tiers=$TLoueurs[$fk_fournisseur];
		/*}else{
			$compte_tiers=$TEntity[$entity];
		}*/
	
		$TLignes[] =array(
				'numFacture'=>$row->numFacture
				,'codeJournal'=>'RES'
				,'datePiece'=>date('dmy', date2ToInt($row->date_facture))
				,'typePiece'=> 'FF'
				,'compteGeneral'=> $code_compta
				,'typeCompte'=> $type_compte
				,'codeAnalytique'=> $compte_tiers
				,'nom'=>''
				,'prenom'=>''
				,'referenceEcriture' => ''
				,'libelleEcriture'=> 'RESSOURCE '.date('m/Y')
				,'modePaiement'=> 'V'
				,'dateEcheance'=> date('dmy')
				,'sens'=> $sens
				,'montant'=>  number_format($montant,2,'.','')
				,'typeEcriture'=> 'N'
				,'numeroPiece'=> ''
				,'devise'=>'EUR'
				,'idImport'=>$row->idImport
				
		);
	
		
		
	}

	return $TLignes;
	
}

// TODO delete, deprecated
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


function _exportOrange2($ATMdb, $date_debut, $date_fin, $entity, $idImport){
	
	global $db;
	
	dol_include_once("/core/lib/admin.lib.php");
	dol_include_once("/ressource/class/numeros_speciaux.class.php");
	dol_include_once('/valideur/class/analytique_user.class.php');
	dol_include_once('/ressource/class/ressource.class.php');	

	$TabLigne = array();
	
	$date_deb = Tools::get_time($date_debut);
	$date_deb = date("Y-m-d", $date_deb);
	
	$date_end = Tools::get_time($date_fin);
	$date_end = date("Y-m-d", $date_end);
	
	$TabLigne = array();
	
	/*
	 * Requete pour récupérer les lignes de la dernière facture
	 * Auxquelles on associe la ressource correspondante (numero de telephone de la ligne facture correspondant au numero de tel d'une ressource)
	 * A laquelle on associe la ressource utilisatrice (Le téléphone qui utilise la carte SIM et donc le numéro de téléphone)
	 * A laquelle on associe l'évènement detype "emprunt"
	 * Auquel on associe l'utilisateur ayant fait cet emprunt (le user a qui est attribué ce téléphone)
	 */
	/*$sql = "SELECT u.rowid, u.email, u.firstname, u.name, ue.COMPTE_TIERS as compte_tiers, au.code, au.pourcentage, r1.fk_rh_ressource, ea.num_gsm, ea.montant_euros_ht";
	$sql.= " FROM ".MAIN_DB_PREFIX."rh_evenement_appel ea";
	$sql.= " INNER JOIN ".MAIN_DB_PREFIX."rh_ressource r1 on (ea.num_gsm = r1.numerotel)";
	$sql.= " INNER JOIN ".MAIN_DB_PREFIX."rh_ressource r2 on (r1.fk_rh_ressource = r2.rowid)";
	$sql.= " INNER JOIN ".MAIN_DB_PREFIX."rh_evenement e on (r2.rowid = e.fk_rh_ressource)";
	$sql.= " INNER JOIN ".MAIN_DB_PREFIX."user u on (e.fk_user = u.rowid)";
	$sql.= " INNER JOIN ".MAIN_DB_PREFIX."user_extrafields ue on (u.rowid = ue.fk_object)";
	$sql.= " INNER JOIN ".MAIN_DB_PREFIX."rh_analytique_user au on (u.rowid = au.fk_user)";
	//$sql.= " WHERE ea.num_import = (SELECT MAX(ea.num_import) FROM ".MAIN_DB_PREFIX."rh_evenement_appel ea)";
	$sql.= ' WHERE 1
			AND ea.date_appel BETWEEN "'.$date_deb.'" AND "'.$date_end.'" 
			 AND ea.idImport = "'.$idImport.'"';
	$sql.= ' AND e.type="emprunt"';*/
	
	$TNumerosSpeciaux = TRH_Numero_special::getAllNumbers($db);
	
	$sql="SELECT ea.num_gsm, SUM(ea.montant_euros_ht) as 'montant_euros_ht',ea.date_appel FROM ".MAIN_DB_PREFIX."rh_evenement_appel ea
	WHERE ea.date_appel BETWEEN '$date_deb 00:00:00' AND '$date_end 23:59:59' ";
	
	if(!empty($TNumerosSpeciaux)) {
		$sql.=" AND ea.num_appele NOT IN ('".implode("','", $TNumerosSpeciaux)."')";	
	}
	
	if($idImport)$sql.=" AND ea.idImport = '$idImport' ";
	
	$sql.=" GROUP BY ea.num_gsm"; //,ea.date_appel"; Je sais c'est moche
	
//	return $sql;exit;

	$resql = $db->query($sql);
//exit($sql);
	
	$total = array();
	
	// On récupère le tableau des numéros spéciaux (ceux à ne pas facturer)
	//$TNumerosSpeciaux = unserialize(dolibarr_get_const($db, "RESSOURCE_ARRAY_NUMEROS_SPECIAUX"));
	
	$r1=new TRH_Ressource;
	$r2=new TRH_Ressource;
	$user_ressource=new User($db);
	$TAnal=array();
	while($res = $db->fetch_object($resql)) {
//		echo $res->num_gsm.'<br/>';flush();
		$gsm = trim($res->num_gsm);
		
		$non_facture = false;

		// Si le numéro de la ligne de facture fait partie du tableau TNumerosSpeciaux, on passe à la ligne suivante (on facture pas)
		/*if(is_array($TNumerosSpeciaux) && count($TNumerosSpeciaux) > 0) {
			foreach ($TNumerosSpeciaux as $num) {
				if($num == $res->num_gsm) {
					$non_facture = true; 
					break;
				}
			}
		}
		*/
		if($non_facture || $res->montant_euros_ht == 0) continue; // On sort pas les lignes à 0 dans le CSV
				
					
		if(!$r1->load_by_numId($ATMdb, $gsm)) continue; // pas de ressource associée		
	
		$r2->load($ATMdb, $r1->fk_rh_ressource);		
	
		$id_user = $r2->isEmpruntee($ATMdb, $res->date_appel);
		if($id_user>0) {
			
			
			// Règle tk 2446 **************
			$financement = isset($r2->financement) ? $r2->financement : 0;
			
			if($res->montant_euros_ht >= 5.5) $res->montant_euros_ht -= 5;
			else $res->montant_euros_ht = 0;
			$res->montant_euros_ht = $res->montant_euros_ht - $financement;
			if($res->montant_euros_ht < 0) $res->montant_euros_ht = 0;
			
			if($res->montant_euros_ht == 0) continue;
		
			// Fin règle ******************
		
			if($user_ressource->id!=$id_user) {
					$user_ressource->fetch($id_user);
					$user_ressource->fetch_optionals($user_ressource->id, array('COMPTE_TIERS' => ""));
					$TAnal = TRH_analytique_user::getUserAnalytique($ATMdb, $id_user);	
			} 
			
			foreach($TAnal as $anal) {
				$total[$id_user][$gsm][$anal->code]['total'] += $res->montant_euros_ht * ($anal->pourcentage/100);
				$total[$id_user][$gsm][$anal->code]['total_nm'] += $res->montant_euros_ht ;
	
	
				/*
				 * On crée un tableau qui associe à chaque user la liste de ses codes analytiques
				 * A chaque code analytique est associé la ligne qui sera exportée
				 */
				$TabLigne[$id_user][$gsm][$anal->code] = array(
						'nom'=>$user_ressource->nom." ".$user_ressource->firstname
						,'fk_user'=>$id_user
						,'numero'=>$res->num_gsm
						,'email'=>$user_ressource->email
						,'compte_tier'=>$user_ressource->array_options['options_COMPTE_TIERS']
						,'code_agence'=>mb_strimwidth($user_ressource->array_options['options_COMPTE_TIERS'], 0, 3)
						,'code_analytique'=>$anal->code
						,'pourcentage'=>$anal->pourcentage
						,'total'=>$total[$id_user][$gsm][$anal->code]['total'] // Total qui va être calculé en fonction du pourcentage
						,'total_non_pondere'=>$total[$id_user][$gsm][$anal->code]['total_nm'] // Vrai total
				);
			
	
			}	
		}
		else{
			null;
		}

/*		if(!empty($TabLigne)){	
		var_dump($TabLigne);exit;}*/
		
	}
	
	/*
	 * Pour chaque ligne du tableau $TabLigne, si certains user ont plusieurs codes analytiques,
	 * on dispatch le montant à facturer en fonction du pourcentage correspondant au code analytique
	 */
	 
	//$TabLigne = _dispatchTarifsParCodeAnalytique($TabLigne);
	//_getFormattedArray($TabLigne); // TODO pas de fucking CSV ici, convertir à l'affichage //DODO beh vlà !
	
	return $TabLigne;
}

function _dispatchTarifsParCodeAnalytique(&$TabLigne) {
	
	$tab = array();
	
	foreach($TabLigne as $user_name => $TCodesAnalytiques) {
		if(count($TCodesAnalytiques) > 1) {
			foreach($TCodesAnalytiques as $code => $TArrayLines) {
				$tab[$user_name][$code] = $TArrayLines;
				$tab[$user_name][$code][count($TArrayLines)-2] = ($tab[$user_name][$code][count($TArrayLines)-2] * ($tab[$user_name][$code][count($TArrayLines)-3] / 100));
			}
		} else {
			$tab[$user_name] = $TCodesAnalytiques;
		}
	}
	
	return $tab;
	
}

function _getFormattedArray(&$TabLine) {
	
	foreach($TabLine as $user_name => $TCodesAnalytiques) {
		foreach($TCodesAnalytiques as $code => $line)
			$TabLine[$user_name][$code] = implode(";", $line);
	}
	
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

//Autocomplete sur les différents champs d'une ressource
function _autocomplete(&$ATMdb,$fieldcode,$value){
	$sql = "SELECT DISTINCT(".$fieldcode.")
			FROM ".MAIN_DB_PREFIX."rh_ressource
			WHERE ".$fieldcode." LIKE '".$value."%'
			ORDER BY ".$fieldcode." ASC"; //TODO Rajouté un filtre entité ?
	$ATMdb->Execute($sql);
	
	while ($ATMdb->Get_line()) {
		$TResult[] = $ATMdb->Get_field($fieldcode);
	}
	
	$ATMdb->close();
	return $TResult;
}
