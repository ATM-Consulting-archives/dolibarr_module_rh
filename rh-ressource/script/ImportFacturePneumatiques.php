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
$idTiers = getIdSociete($ATMdb, 'Côté-Route');
if (!$idTiers){echo 'Pas de fournisseur (tiers) du nom de "Côté-Route" ! Pensez à le créer';exit();}

if ($idTiers == 0){echo 'Aucun fournisseur du nom de "Côté-Route" ! Pensez à le créer';exit;}

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
			
			$timestamp = mktime(0,0,0,substr($infos[3], 3,2),substr($infos[3], 0,2), substr($infos[3], 6,4));
			$date = date("Y-m-d", $timestamp);
			
			?>
			<tr>
				<td>Ajout facture <?=$numFacture ?></td>
				<td><?=$plaque ?></td>
			<?
				 
				 
			$loyerTTC = floatval(strtr($infos[22], ',','.'));
			$loyerHT = floatval(strtr($infos[12], ',','.'));
		
			$taux = '20';
			
			$fact = new TRH_Evenement;
			$fact->type = 'facturepneumatique';
			$fact->numFacture = $infos[$mapping['num_facture']];
			$fact->fk_rh_ressource = $r->rowid;
			$fact->fk_user = $idUser;
			$fact->fk_rh_ressource_type = $idVoiture;
			$fact->motif = 'Facture pneumatique';
			$fact->commentaire = 'Facture lié au contrat '.$infos[0];
			$fact->set_date('date_debut', $infos[$mapping['date_facture']]);
			$fact->set_date('date_fin', $infos[$mapping['echeance']]);
			$fact->coutTTC = $loyerTTC;
			$fact->coutEntrepriseTTC =  $loyerTTC;
			$fact->TVA= $TTVA[$taux];
			$fact->coutEntrepriseHT = $loyerHT;
			$fact->entity =$entity;
			$fact->fk_fournisseur = $idTiers;
			$fact->idImport = $idImport;
			$fact->date_facture = dateToInt($infos[3]);
			$fact->save($ATMdb);
			$cptFactureLoyer++;
			
			print '<td>'.$infos[$mapping['id_agence']].'</td>'
			.'<td>'.$infos[$mapping['sigle']].'</td>'
			.'<td>'.$infos[$mapping['num_facture']].'</td>'
			.'<td>'.$infos[$mapping['date_facture']].'</td>'
			.'<td>'.$infos[$mapping['echeance']].'</td>'
			.'<td>'.$infos[$mapping['num_bl']].'</td>'
			.'<td>'.$infos[$mapping['designation']].'/td>'
			.'<td>'.$infos[$mapping['qty']].'</td>'
			.'<td>'.$infos[$mapping['vehicule']].'</td>'
			.'<td>'.$infos[$mapping['ca_facture_ht']].'</td>'
			.'<td>'.$infos[$mapping['pv_base_unitaire_ht']].'</td>'
			.'<td>'.$infos[$mapping['pv_reel_unitaire_ht']].'</td>'
			.'<td>'.$infos[$mapping['total_ttc']].'</td>'
			.'<td>'.$infos[$mapping['remise']].'</td>';
						
		}
		$numLigne++;
	
	}
	?></table>
	<?
	//Fin du code PHP : Afficher le temps d'éxecution et le bilan.
	//$message .= $cptContrat.' contrats importés.<br>';
	$message .= $cptNoVoiture.' plaques sans correspondance.<br>';
	$message .= $cptNoAttribution.' voitures non attribués<br>';
	$message .= $cptFactureLoyer.' factures loyer importés.<br>';
	$message .= $cptFactureGestEntre.' factures gestion+entretien importés.<br>';
	$timeend=microtime(true);
	$page_load_time = number_format($timeend-$timestart, 3);
	$message .= '<br>Fin du traitement. Durée : '.$page_load_time . " sec.<br><br>";
	echo $message;

}

/*
 * prend un format d/m/Y et renvoie un timestamp
 */
function dateToInt($chaine){
	return mktime(0,0,0,substr($chaine,3,2),substr($chaine,0,2),substr($chaine,6,4));
}



	
