<?php
require('../config.php');
require('../lib/ressource.lib.php');
global $conf;
$ATMdb=new TPDOdb;

$plagedeb = isset($_REQUEST['plagedebut']);
$plagefin = isset($_REQUEST['plagefin']);

$idVoiture = getIdType('voiture');

//chargement des voitures
$TVoitures = getRessource($idVoiture);
$sql = "SELECT rowid, fk_soc, fk_user , immatriculation , marquevoit, modlevoit
	FROM ".MAIN_DB_PREFIX."rh_ressource` 
	WHERE entity=".$conf->entity."
	AND fk_rh_ressource_type =".$idVoiture;
$ATMdb->Execute($sql);
while($row = $ATMdb->Get_line()) {
	$TVoitures[$row->rowid] = array(
		'societe'=>$row->fk_soc
		,'fk_user'=>$row->fk_user
		,'immatriculation'=>$row->immatriculation
		,'marque'=>$row->marquevoit
		,'version'=>$row->modlevoit
		);
}

print_r($TVoitures);


//chargement des contrats
/*$TContrats = array();
$sql="SELECT rowid, fk_rh_ressource, fk_rh_contrat 
	FROM ".MAIN_DB_PREFIX."rh_contrat` 
	WHERE entity=".$conf->entity;
$ATMdb->Execute($sql);
while($row = $ATMdb->Get_line()) {
	$TAssociations[$row->rowid] = array(
		''=>$row->fk_rh_ressource
		,''=>$row->fk_rh_contrat
		);
}
*/

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


$TRessource = array();

foreach ($TPleins as $idRessource => $value) {
	$memKm = 0;
	$memLitre = 0;
	$texte = '';
	foreach ($value as $km => $litre) {
		if ($memKm!=0){
			$conso = number_format((100*$memLitre)/($km-$memKm),2);
			if(isset($_REQUEST['limite'])) {
				if ($conso>=$_REQUEST['limite']){
					$texte .= 'lol';
				}
			}
			else{
				$texte .= "<span style=\"margin-left: 3em;\">".($km-$memKm).'km fait avec '.number_format($memLitre,2).' litres. Conso : '. $conso.'L/100km<br></span>';
			}
		}
		$memKm = $km;
		$memLitre = $litre;
	}
	$TRessource[] = array(
		'nom'=>$TCartes[$idRessource]
		,'info'=>empty($texte) ? "<span style=\"margin-left: 3em;\">Aucun dépassement<br></span>" : $texte
	);
	
}

echo json_encode($TRessource);

exit();

	