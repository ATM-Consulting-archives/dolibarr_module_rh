<?php

/**
 * Importation de la facture Total
 * On créé deux évenements par ligne de ce fichier : un loyer et un gestion+entretien
 * 
 */

/*
require('../config.php');
require('../class/evenement.class.php');
require('../class/ressource.class.php');
require('../lib/ressource.lib.php');
require('../class/contrat.class.php');
//*/

global $conf;
$entity = (isset($_REQUEST['entity'])) ? $_REQUEST['entity'] : $conf->entity;

$ATMdb=new TPDOdb;

$mapping = array(
				'id_agence'=>0
				,'sigle'=>1
				,'num_facture'=>2
				,'date_facture'=>3
				,'echeance'=>4
				,'num_bl'=>5
				,'designation'=>6
				,'qty'=>7
				,'vehicule'=>8
				,'ca_facture_ht'=>9
				,'pv_base_unitaire_ht'=>10
				,'pv_reel_unitaire_ht'=>11
				,'total_ttc'=>12
				,'remise'=>13
			);

// relever le point de départ
$timestart=microtime(true);

$idVoiture = getIdType('voiture');
$idTiers = getIdSociete($ATMdb, strtolower('Cote-Route'));
if (!$idTiers){echo 'Pas de fournisseur (tiers) du nom de "Cote-Route" ! Pensez à le créer';exit();}

if ($idTiers == 0){echo 'Aucun fournisseur du nom de "Cote-Route" ! Pensez à le créer';exit;}

if (empty($nomFichier)){$nomFichier = "./fichierImports/CPRO - PRELVT DU 05 04 13.csv";}
$message = 'Traitement du fichier '.$nomFichier.' : <br><br>';

$idImport = _url_format(basename($nomFichier), false, true);

//début du parsing
$numLigne = 0;
if (($handle = fopen($nomFichier, "r")) !== FALSE) {
	
	?>
<table class="border">
	<tr>
		<th>Id agence</th>
		<th>Sigle</th>
		<th>Numéro facture</th>
		<th>Date facture</th>
		<th>Date échéance</th>
		<th>Num BL</th>
		<th>Désignation</th>
		<th>Qté vendue</th>
		<th>Véhicule</th>
		<th>CA Facturé</th>
		<th>PV base unitaire HT</th>
		<th>PV réel unitaire</th>
		<th>Montant TTC</th>
		<th>Remise</th>
	</tr>

<?
	

	$TVehiculesNonTrouve= array();
	while(($infos = fgetcsv($handle, 0,';')) != false){
		//echo 'Traitement de la ligne '.$numLigne.'...';
		/*echo '<pre>';
	print_r($infos);
	echo '</pre>';*/
	
		$r = new TRH_Ressource;
		$r->load_by_numId($ATMdb, $infos[$mapping['vehicule']]);
		
		if($r->rowid <= 0) $TVehiculesNonTrouve[] = array('line'=>$numLigne, 'num_id'=>$infos[$mapping['vehicule']]);
		
		$numFacture = $infos[1];
		
		if ($numLigne >=1 && $r->rowid > 0){

			$timestamp = mktime(0,0,0,substr($infos[$mapping['date_facture']], 3,2),substr($infos[$mapping['date_facture']], 0,2), substr($infos[$mapping['date_facture']], 6,4));
			$date = date("Y-m-d", $timestamp);
			
			//echo $infos[$mapping['vehicule']].' : '.$r->typevehicule.'<br>';
			
			$idUser = ressourceIsEmpruntee($ATMdb, $r->rowid, $date);
			if(empty($idUser)) $idUser = 3;
			
			$fact = new TRH_Evenement;
			$fact->type = 'Pneumatique';
			$fact->numFacture = $infos[$mapping['num_facture']];
			$fact->fk_rh_ressource = $r->rowid;
			$fact->fk_user = $idUser;
			$fact->fk_rh_ressource_type = $idVoiture;
			$fact->motif = 'Facture pneumatique';
			$fact->commentaire = $infos[$mapping['designation']];
			$fact->commentaire.= "<br />\nQuantité : ".(int)$infos[$mapping['qty']];
			$fact->commentaire.= "<br />\nPV base unitaire HT : ".$infos[$mapping['pv_base_unitaire_ht']];
			$fact->commentaire.= "<br />\nRemise : ".$infos[$mapping['remise']];
			$fact->commentaire.= "<br />\nNum. BL : ".$infos[$mapping['num_bl']];
			$fact->set_date('date_debut', $infos[$mapping['date_facture']]);
			$fact->set_date('date_fin', $infos[$mapping['echeance']]);
			$fact->coutTTC = $infos[$mapping['total_ttc']];
			$fact->coutEntrepriseTTC =  $infos[$mapping['total_ttc']];
			//$fact->TVA= $TTVA[$taux]; Non renseigné dans le fichier d'exemple
			//$fact->coutEntrepriseHT = $infos[$mapping['pv_reel_unitaire_ht']];
			
			// En fait le countEntrepriseHT se calcule dans le save grâce à $fact->TTVA :
			
			$tva = 2463; // TVA 20 %
			if($r->typevehicule == "VU") { null; }
			else {
				$tva=0; // Pas de TVA
			} 
			
			$fact->TVA = $tva;
			$fact->entity =$entity;
			$fact->fk_fournisseur = $idTiers;
			$fact->idImport = $idImport;
			$fact->date_facture = dateToInt($infos[$mapping['date_facture']]);
			$fact->save($ATMdb);
			$cptFactureLoyer++;
			
			print '<tr>';
			print '<td>'.$infos[$mapping['id_agence']].'</td>'
			.'<td>'.$infos[$mapping['sigle']].'</td>'
			.'<td>'.$infos[$mapping['num_facture']].'</td>'
			.'<td>'.$infos[$mapping['date_facture']].'</td>'
			.'<td>'.$infos[$mapping['echeance']].'</td>'
			.'<td>'.$infos[$mapping['num_bl']].'</td>'
			.'<td>'.$infos[$mapping['designation']].'</td>'
			.'<td>'.$infos[$mapping['qty']].'</td>'
			.'<td>'.$infos[$mapping['vehicule']].'</td>'
			.'<td>'.$infos[$mapping['ca_facture_ht']].'</td>'
			.'<td>'.$infos[$mapping['pv_base_unitaire_ht']].'</td>'
			.'<td>'.$infos[$mapping['pv_reel_unitaire_ht']].'</td>'
			.'<td>'.$infos[$mapping['total_ttc']].'</td>'
			.'<td>'.$infos[$mapping['remise']].'</td>';
			print '</tr>';
			
		}
		$numLigne++;
	
	}
	?></table>
	<?

	print count($TVehiculesNonTrouve).' voiture(s) non trouvée(s)<br><br>';
	
	if(count($TVehiculesNonTrouve)> 0) {
		print 'Détail : <br />';
		foreach($TVehiculesNonTrouve as $TData) {
			
			print 'ligne : '.$TData['line'];
			print '<br />';
			print 'voiture : '.$TData['num_id'];
			print '<br />';
			print '<br />';
			
		}
	}
	
	$timeend=microtime(true);
	$page_load_time = number_format($timeend-$timestart, 3);
	$message = '<br>Fin du traitement. Durée : '.$page_load_time . " sec.<br><br>";
	echo $message;

}

/*
 * prend un format d/m/Y et renvoie un timestamp
 */
function dateToInt($chaine){
	return mktime(0,0,0,substr($chaine,3,2),substr($chaine,0,2),substr($chaine,6,4));
}



	
