<?php
require('../config.php');
require('../lib/ressource.lib.php');
global $conf;
$ATMdb=new TPDOdb;

$plagedeb = !empty($_REQUEST['plagedebut']) ? $_REQUEST['plagedebut'] : date("d/m/Y",time());
$plagefin = !empty($_REQUEST['plagefin']) ? $_REQUEST['plagefin'] : date("d/m/Y", time()+31532400);

$deb = dateToInt($plagedeb);
$fin = dateToInt($plagefin);

$idVoiture = getIdType('voiture');


//chargement des voitures
$TVoitures = getRessource($idVoiture);
$sql = "SELECT r.rowid, fk_proprietaire,  immatriculation , marquevoit, modlevoit, name, firstname, date_debut, date_fin
	FROM ".MAIN_DB_PREFIX."rh_ressource as r
	LEFT JOIN ".MAIN_DB_PREFIX."rh_evenement as e ON (
										e.type='emprunt' 
										AND r.rowid=e.fk_rh_ressource)
	LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (u.rowid=e.fk_user)
	WHERE r.entity=".$conf->entity."
	AND fk_rh_ressource_type =".$idVoiture;

	//echo $sql;
$ATMdb->Execute($sql);
while($row = $ATMdb->Get_line()) {
	
	//echo $plagedeb.'   '.$row->date_debut.'<br>';
	$TVoitures[$row->rowid] = array(
		'societe'=>$row->fk_proprietaire
		,'fk_user'=>htmlentities($row->firstname.' '.$row->name, ENT_COMPAT , 'ISO8859-1')
		,'immatriculation'=>$row->immatriculation
		,'marque'=>$row->marquevoit
		,'version'=>$row->modlevoit
		);
}


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
		'loyer'=>number_format($row->loyer_TTC,2).' €'
		,'assurance'=>number_format($row->assurance,2).' €'
		,'entretien'=>number_format($row->entretien,2).' €'
		,'date_debut'=>date("d/m/Y", $date_debut)
		,'date_fin'=>date("d/m/Y", $date_fin)
		,'fk_soc'=>$row->fk_tier_fournisseur
		);
}

//chargement des associations
$TAssociations = array();
$sql="SELECT rowid, fk_rh_ressource, fk_rh_contrat 
	FROM ".MAIN_DB_PREFIX."rh_contrat_ressource` 
	WHERE entity=".$conf->entity;
$ATMdb->Execute($sql);
while($row = $ATMdb->Get_line()) {
	$TAssociations[$row->rowid] = array(
		'voiture'=>$row->fk_rh_ressource
		,'contrat'=>$row->fk_rh_contrat
		);
}

//chargement des groupes
$TGroups = getGroups();

//chargement des fournisseurs
$TFournisseurs = array();
$sqlReq="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."societe WHERE entity=".$conf->entity;
$ATMdb->Execute($sqlReq);
while($row = $ATMdb->Get_line()) {
	$TFournisseurs[$row->rowid] = htmlentities($row->nom, ENT_COMPAT , 'ISO8859-1'); 
	}


$TRetour = array();

$texte = '';
foreach ($TAssociations as $value) {
	$voiture = $TVoitures[$value['voiture']];
	$contrat = $TContrats[$value['contrat']]; 
	if (empty($voiture)){
		echo 'pas de voiture n°'.$value['voiture'].'<br>';		
	}
	else if (empty($voiture)){
		echo 'pas de contrat n°'.$value['contrat'].'<br>';		
	}
	else{
		if ( (dateToInt($contrat['date_fin'])<=$fin)
			&&
			(dateToInt($contrat['date_fin'])>=$deb) ){
			$TRetour[] = array(
				'societe'=>$TGroups[$voiture['societe']]
				,'collaborateur'=>$voiture['fk_user']
				,'immatriculation'=>$voiture['immatriculation']
				,'marque'=>$voiture['marque']
				,'version'=>$voiture['version']
				,'loyer'=>$contrat['loyer']
				,'assurance'=>$contrat['assurance']
				,'entretien'=>$contrat['entretien']
				,'date_debut'=>$contrat['date_debut']
				,'date_fin'=>$contrat['date_fin']
				,'fournisseur'=>$TFournisseurs[$contrat['fk_soc']]
			);
		
		}
		
		
	}		
}






echo json_encode($TRetour);

exit();

/*
 * prend un format d/m/Y et renvoie un timestamp
 */
function dateToInt($chaine){
	return mktime(0,0,0,substr($chaine,3,2),substr($chaine,0,2),substr($chaine,6,4));
}
