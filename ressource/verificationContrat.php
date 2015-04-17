<?php
/**
 * Ce script vérifie la consommation des cartes TOTAL : à savoir si l'utilisation de la carte est abusive 
 */ 

require('./config.php');
require('./lib/ressource.lib.php');
global $conf;
$ATMdb=new TPDOdb;

llxHeader('','Vérification des concordances entre contrats et factures des véhicules');

print dol_get_fiche_head(array()  , '', 'Vérification');


$plagedeb = isset($_REQUEST['plagedebut']) ? $_REQUEST['plagedebut'] : date("d/m/Y",time()-3600*24*30*12);
$date_debut = dateToInt($plagedeb);

$plagefin = isset($_REQUEST['plagefin']) ? $_REQUEST['plagefin'] : date("d/m/Y", time()+3600*24*30*12);
$date_fin = dateToInt($plagefin);

$incoherance = isset($_REQUEST['incoherance']) ? $_REQUEST['incoherance'] : 1 ;

$TRessource = getTab($ATMdb, $date_debut, $date_fin,$incoherance);

$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
$form->Set_typeaff('edit');
$TBS=new TTemplateTBS();
print $TBS->render('./tpl/verificationContrat.tpl.php'
	,array(
		'tab'=>$TRessource
	)
	,array(
		'infos'=>array(
			'titre'=>load_fiche_titre("Vérification des concordances entre contrats et factures des véhicules",'', 'title.png', 0, '')
			,'plagedebut'=>$form->calendrier('', 'plagedebut', $date_debut, 12)
			,'plagefin'=>$form->calendrier('', 'plagefin', $date_fin, 12)
			,'incoherance'=>$form->checkbox('', 'incoherance', array('Afficher seulement les incohérences') , $incoherance)
			,'valider'=>$form->btsubmit('Génerer', 'valider')
		)
	)	
	
);

$form->end();
llxFooter();


function getTab(&$ATMdb, $deb, $fin,$incoherance){
	

	
	$idVoiture = getIdType('voiture');
	
	
	//chargement des voitures
	$TVoitures = array();
	$sql = "SELECT rowid, fk_utilisatrice,  immatriculation , marquevoit, modlevoit
		FROM ".MAIN_DB_PREFIX."rh_ressource 
		WHERE fk_rh_ressource_type =".$idVoiture;
	$ATMdb->Execute($sql);
	while($row = $ATMdb->Get_line()) {
		$TVoitures[$row->rowid] = array(
			'societe'=>$row->fk_utilisatrice
			,'immatriculation'=>$row->immatriculation
			,'marque'=>$row->marquevoit
			,'version'=>$row->modlevoit
			);
	}
	//print_r($TVoitures);exit();
	
	//chargement des contrats
	$TContrats = array();
	$sql="SELECT rowid, loyer_TTC, fk_tier_fournisseur
		FROM ".MAIN_DB_PREFIX."rh_contrat` ";
	$ATMdb->Execute($sql);
	while($row = $ATMdb->Get_line()) {
		$TContrats[$row->rowid] = array(
			'loyer'=>$row->loyer_TTC
			,'fk_soc'=>$row->fk_tier_fournisseur
			);
	}
	//print_r($TContrats);exit();
	
	//chargement des associations
	$TAssociations = array();
	$sql="SELECT rowid, fk_rh_ressource, fk_rh_contrat 
		FROM ".MAIN_DB_PREFIX."rh_contrat_ressource ";
	$ATMdb->Execute($sql);
	while($row = $ATMdb->Get_line()) {
		$TAssociations[$row->fk_rh_ressource] = $row->fk_rh_contrat;
	}
	//print_r($TAssociations);exit();
	
	//chargement des groupes
	$TGroups = getGroups();
	//print_r($TGroups);exit();
	
	//chargement des fournisseurs
	$TFournisseurs = array();
	$sqlReq="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."societe";
	$ATMdb->Execute($sqlReq);
	while($row = $ATMdb->Get_line()) {
		$TFournisseurs[$row->rowid] = htmlentities($row->nom, ENT_COMPAT , 'ISO8859-1'); 
		}
	//print_r($TFournisseurs);exit();
	
	//chargement des factures
	$TFactures = array();
	$sql="SELECT rowid, fk_rh_ressource, date_debut , coutEntrepriseTTC 
		FROM ".MAIN_DB_PREFIX."rh_evenement 
		WHERE type='factureloyer'
		AND date_debut>= '".date("Y-m-d",$deb)." 00:00:00' AND date_debut<='".date("Y-m-d",$fin)." 00:00:00' ";
	//ECHO $sql.'<br>';
	$ATMdb->Execute($sql);
	while($row = $ATMdb->Get_line()) {
		$TFactures[] = array(
		'cout'=>$row->coutEntrepriseTTC
		,'date'=>date("d/m/Y",date2ToInt($row->date_debut))
		,'fk_rh_ressource'=>$row->fk_rh_ressource
		,'id'=>$row->rowid
		);
	}
	//print_r($TFactures);exit();
	
	$TRetour = array();
	$cpt = 0;
	foreach ($TFactures as $facture) {
		$voiture = $TVoitures[$facture['fk_rh_ressource']];
		$contrat = $TContrats[$TAssociations[$facture['fk_rh_ressource']]];
			if ($incoherance || ($facture['cout']!=$contrat['loyer']) ){
				$cpt ++;
				$TRetour[] = array(
					'societe'=>$cpt.' '.$TGroups[$voiture['societe']]
					,'immatriculation'=>$voiture['immatriculation']
					,'marque'=>$voiture['marque']
					,'version'=>$voiture['version']
					
					,'loyer'=>($facture['cout']==$contrat['loyer']) ? number_format($contrat['loyer'],2).' €' : '<b>'.number_format($contrat['loyer'],2).' €</b>'
					,'fournisseur'=>$TFournisseurs[$contrat['fk_soc']]
					
					,'date'=>$facture['date']
					,'montantfacture'=>($facture['cout']==$contrat['loyer']) ? number_format($facture['cout'],2).' €' : '<b>'.number_format($facture['cout'],2).' €</b>'
				);
		}
	}
	
	//print_r($TRetour);
	return $TRetour;
}



