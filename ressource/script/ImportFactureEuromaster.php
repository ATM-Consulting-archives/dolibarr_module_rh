<?php

/**
 * Importation de la facture Total
 * On créé un évenement par ligne de ce fichier
 * et une évenement de type facture
 */

/*  
require('../config.php');
require('../class/evenement.class.php');
require('../class/ressource.class.php');
require('../lib/ressource.lib.php');//*/

global $conf;

$ATMdb=new TPDOdb;
$ATMdbEvent=new TPDOdb;

// relever le point de départ
$timestart=microtime(true);

$TUser = array();
$sql="SELECT rowid, lastname, firstname FROM ".MAIN_DB_PREFIX."user WHERE entity=".$conf->entity;
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TUser[strtolower($ATMdb->Get_field('firstname').' '.$ATMdb->Get_field('lastname'))] = $ATMdb->Get_field('rowid');
}
$idVoiture = getIdType('voiture');
$idEuromaster = getIdSociete($ATMdb, 'euromaster');
if (!$idEuromaster){echo 'Pas de fournisseur (tiers) du nom de Euromaster !';exit();}
$TRessource = chargeVoiture($ATMdb);
$TNonAttribuee = array();
$TNoPlaque = array();
if (empty($nomFichier)){$nomFichier = "./fichierImports/B60465281_Masterplan-CPRO_M_20130430.csv";}
$entity = (isset($_REQUEST['entity'])) ? $_REQUEST['entity'] : $conf->entity;
$message = 'Traitement du fichier '.$nomFichier.' : <br><br>';

//pour avoir un joli nom, on prend la chaine après le dernier caractère /  et on remplace les espaces par des underscores
$idImport = _url_format(basename($nomFichier), false, true);

$ATMdb->Execute("DELETE FROM ".MAIN_DB_PREFIX."rh_evenement WHERE idImport='$idImport'");


$idRessFactice = createRessourceFactice($ATMdb, $idVoiture, $idImport, $entity, $idEuromaster);
$idSuperAdmin = getIdSuperAdmin($ATMdb);

$ressource_source = new TRH_Evenement;
$ressource_source->load_liste($ATMdb);

//début du parsing
$numLigne = 0;
if (($handle = fopen($nomFichier, "r")) !== FALSE) {
	
	?>
<table class="border">
	<tr>
		<th>Message</th>
		<th>Ressource</th>
		<th>Montant</th>
		<th>TVA</th>
		<th>Info</th>
	</tr>

<?
	
	
	
	while(($data = fgetcsv($handle, 0,'\r')) != false) {
		//echo 'Traitement de la ligne '.$numLigne.'...';
		if ($numLigne >=1) {
			$infos = explode(';', $data[0]);
			
			$plaque = str_replace('-','',$infos[0]);
			$plaque = str_replace(' ','',$plaque);
			
			if(empty($plaque)) continue;
			
			$timestamp = mktime(0,0,0,intval(substr($infos[4], 3,2)),intval(substr($infos[4], 0,2)), intval(substr($infos[4], 6,4)));
			$date = date("Y-m-d", $timestamp);
		
			$numero =  $infos[22];
		
			
			$style = '';
			if (!empty($TRessource[$plaque])){
				$idUser = ressourceIsEmpruntee($ATMdb, $TRessource[$plaque], $date);
				if ($idUser==0){ //si il trouve, on l'affecte à l'utilisateur 
					$idUser = $idSuperAdmin;
					$cptNoAttribution++;
					$info = 'Voiture non attribué le '.$date.' : '.$plaque.'<br>';
				}
				else {
					$info = 'Ok';	
				}
				$id_ressource = $TRessource[$plaque];
				
				$ressource = new TRH_Ressource();
                $ressource->load($ATMdb, $TRessource[$plaque]);
                $typeVehicule = $ressource->typevehicule;
				
			}	
			else {
				$idUser = $idSuperAdmin;
				$TNoPlaque[$plaque] = 1 ;
				$cptNoVoiture ++;
				
				$id_ressource = $idRessFactice;
				
				$info = 'Véhicule non trouvé';
				$style = 'background-color:red;';
			}
			
			?>
			<tr style="<?=$style ?>">
				<td>Ajout facture <?=$numero ?></td>
				<td><?=$plaque ?></td>
			<?
		
			
		
			$temp = new TRH_Evenement;
			
			$loyerHT = (double)strtr($infos[10], ',','.');
			$loyerTTC = strtr($infos[25], ',','.');
			 
			$taux = '19.6';
            if($typeVehicule == "VU") { null; }
            else {
                   $taux="0";
                   $loyerHT = $loyerTTC;
            } 
			
			$temp->fk_rh_ressource = $id_ressource;
			$temp->type = 'facture';
			$temp->fk_user = $idUser;
			$temp->set_date('date_debut', $date);
			$temp->set_date('date_fin', $date);
			$temp->coutEntrepriseHT = $loyerHT;
			$temp->coutTTC = $loyerTTC;
			$temp->coutEntrepriseTTC = $loyerTTC;
			$temp->numFacture = $numero;
			$temp->motif = $infos[8];
			$temp->commentaire = $infos[7];
			$temp->fk_fournisseur = $idEuromaster;
			$temp->entity = $entity;
			
			//$ttva = array_keys($temp->TTVA , floatval());
			
			$temp->TVA = getTVAId($ressource_source->TTVA,$taux);
			//$temp->compteFacture = $infos[13];
			$temp->idImport = $idImport;
			$temp->numFacture = $infos[22];
			$temp->date_facture = dateToInt($infos[23]);
			
			
			$temp->save($ATMdbEvent);
		
			?><td><?=$infos[25] ?></td><td><?=$ressource_source->TTVA[$temp->TVA] ?></td><td><?=$info ?></td></tr><?
		
		}
		$numLigne++;
		
	}
	?></table><?
	//Fin du code PHP : Afficher le temps d'éxecution et les résultats.
	if (!empty($TNoPlaque)){
		$message .= 'Voitures non trouvées :<br>';}
	foreach($TNoPlaque as $plaque=>$rien){
		$message .= $plaque.'<br>';}
	
	if (!empty($TNonAttribuee)){
		$message .= 'Voitures non attribué :<br>';}
	foreach($TNonAttribuee as $date=>$plaque){
		$message.= $plaque.' non attribuée le '.$date.'<br>';}
	$message .= '<br>';
	
	$timeend=microtime(true);
	$page_load_time = number_format($timeend-$timestart, 3);
	$message .= 'Fin du traitement. Durée : '.$page_load_time . " sec.<br><br>";
	send_mail_resources('Import - Factures Euromaster',$message);
	echo $message;
	
	
	
}

function getTVAId(&$TTVA, $tva) {
		
	foreach($TTVA as $id=>$taux) {
		$ecart = abs((double)$tva-(double)$taux);
		if($ecart <= 1) return $id;
		/*else print "($taux $tva)".$ecart.'<br />';*/
	}
	//print_r ($tva);
	return -1;
	
}

function chargeAssocies(&$ATMdb){
	global $conf;
	$sqlReq="SELECT rowid, fk_rh_ressource 
	FROM ".MAIN_DB_PREFIX."rh_ressource 
	WHERE entity=".$conf->entity;
	$TAssoc = array();
	$ATMdb->Execute($sqlReq);
	while($ATMdb->Get_line()) {
		$TAssoc[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('fk_rh_ressource');
	}
	return $TAssoc;
	
	
}

function getUser(&$listeEmprunts , $id, $jour){
	if (empty($listeEmprunts[$id])){return 0;}
	foreach ($listeEmprunts[$id] as $k => $value) {
		if ( ($value['debut'] <= date("Y-m-d",$jour))  
			&& ($value['fin'] >= date("Y-m-d",$jour)) ){
				return $value['fk_user'];
		}
	}
	return 0;
}

function chargeEmprunts(&$ATMdb){
	global $conf;
	$sqlReq="SELECT DISTINCT e.date_debut, e.date_fin , e.fk_user, e.fk_rh_ressource, u.firstname, u.lastname 
	FROM ".MAIN_DB_PREFIX."rh_evenement as e  
	LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (e.fk_user=u.rowid) 
	WHERE e.type='emprunt'
	AND e.entity=".$conf->entity."
	ORDER BY date_debut";
	$TUsers = array();
	$ATMdb->Execute($sqlReq);
	while($ATMdb->Get_line()) {
		$TUsers[$ATMdb->Get_field('fk_rh_ressource')][] = array(
			'debut'=>$ATMdb->Get_field('date_debut')
			,'fin'=>$ATMdb->Get_field('date_fin')
			,'fk_user'=>$ATMdb->Get_field('fk_user')
			,'user'=>$ATMdb->Get_field('firstname').' '.$ATMdb->Get_field('lastname')
		);
	}
	return $TUsers;
}

function chargeVoiture(&$ATMdb){
	global $conf;
	$TRessource = array();
	$sql="SELECT r.rowid as 'ID', t.rowid as 'IdType', r.numId FROM ".MAIN_DB_PREFIX."rh_ressource as r 
	LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource_type as t on (r.fk_rh_ressource_type = t.rowid)
	WHERE r.entity=".$conf->entity."
	 AND (t.code='voiture' OR t.code='cartearea') ";
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TRessource[$ATMdb->Get_field('numId')] = $ATMdb->Get_field('ID');
		}
	return $TRessource;
}



/*
 * prend un format d/m/Y et renvoie un timestamp
 */
function dateToInt($chaine){
	return mktime(0,0,0,substr($chaine,3,2),substr($chaine,0,2),substr($chaine,6,4));
}
