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

$plagedeb = isset($_REQUEST['plagedebut']) ? $_REQUEST['plagedebut'] : date("d/m/Y",time()-3600*24*31*12);
$date_debut = mktime(0,0,0,substr($plagedeb, 3,2),substr($plagedeb, 0,2), substr($plagedeb, 6,4));
$plagefin = isset($_REQUEST['plagefin']) ? $_REQUEST['plagefin'] : date("d/m/Y", time()+3600*24*31*12);
$date_fin = mktime(0,0,0,substr($plagefin, 3,2),substr($plagefin, 0,2), substr($plagefin, 6,4));

$incoherance = isset($_REQUEST['incoherance']) ? $_REQUEST['incoherance'] : 1 ;

$TRessource = getTab($ATMdb, $plagedeb, $plagefin,$incoherance);

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


function getTab(&$ATMdb, $plagedeb, $plagefin,$incoherance){
	
	$deb = dateToInt($plagedeb);
	$fin = dateToInt($plagefin);
	
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
	$sql="SELECT rowid, loyer_TTC, assurance, entretien, date_debut, date_fin, fk_tier_fournisseur
		FROM ".MAIN_DB_PREFIX."rh_contrat` 
		WHERE entity=".$conf->entity."
		";
	$ATMdb->Execute($sql);
	while($row = $ATMdb->Get_line()) {
		$date_debut = mktime(0,0,0,substr($row->date_debut,5,2),substr($row->date_debut,8,2),substr($row->date_debut,0,4));
		$date_fin = mktime(0,0,0,substr($row->date_fin,5,2),substr($row->date_fin,8,2),substr($row->date_fin,0,4));
		$TContrats[$row->rowid] = array(
			'loyer'=>$row->loyer_TTC,2
			,'date_debut'=>date("d/m/Y", $date_debut)
			,'date_fin'=>date("d/m/Y", $date_fin)
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
		AND date_debut>= '".date("Y-m-d",$deb)."' AND date_fin<='".date("Y-m-d",$fin)." ' ";
	$ATMdb->Execute($sql);
	while($row = $ATMdb->Get_line()) {
		$TFactures[$row->fk_rh_ressource] = array(
		'cout'=>$row->coutEntrepriseTTC
		,'date'=>date("d/m/Y",date2ToInt($row->date_debut))
		);
	}
	//print_r($TFactures);exit();
	
	
	
	
	
	$TRetour = array();
	$texte = '';
	$cpt = 0;
	foreach ($TFactures as $fk_rh_ressource => $facture) {
		
		$voiture = $TVoitures[$fk_rh_ressource];
		$contrat = $TContrats[$TAssociations[$fk_rh_ressource]];
		
		if (empty($voiture)){
			echo 'pas de voiture n°'.$value['voiture'].'<br>';		
		}
		else if (empty($contrat)){
			echo 'pas de contrat n°'.$value['contrat'].'<br>';		
		}
		else{
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
	}
	
	//print_r($TRetour);
	return $TRetour;
}


/*
 * prend un format d/m/Y et renvoie un timestamp
 */
function dateToInt($chaine){
	return mktime(0,0,0,substr($chaine,3,2),substr($chaine,0,2),substr($chaine,6,4));
}

/*
 * prend un format Y-m-d et renvoie un timestamp
 */
function date2ToInt($chaine){
	return mktime(0,0,0,substr($chaine,6,2),substr($chaine,8,2),substr($chaine,0,4));
}
