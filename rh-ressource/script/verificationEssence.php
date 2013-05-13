<?php
/**
 * Ce script vérifie la consommation des cartes TOTAL : à savoir si l'utilisation de la carte est abusive 
 */ 

require('../config.php');
require('../class/evenement.class.php');
require('../class/ressource.class.php');
global $conf;
$ATMdb=new TPDOdb;
llxHeader('','Liste des ressources');

$TPleins = array();

$sql="SELECT rowid, fk_rh_ressource, motif, commentaire, litreEssence, kilometrage 
	FROM ".MAIN_DB_PREFIX."rh_evenement 
	WHERE entity=".$conf->entity." AND type='pleindessence' ORDER BY date_debut";
$ATMdb->Execute($sql);
while($row = $ATMdb->Get_line()) {
	$TPleins[$row->fk_rh_ressource][$row->kilometrage] = $row->litreEssence;
}
/*
foreach ($TPleins as $idRessource => $value) {
	echo 'ressource : '.$idRessource.' : ';
	print_r($value);
	echo '<br>';
}
//*/

foreach ($TPleins as $idRessource => $value) {
	$texte .= 'ressource : '.$idRessource.' : ';
	$memKm = 0;
	$memLitre = 0;
	foreach ($value as $km => $litre) {
		if ($memKm!=0){
			$conso = number_format((100*$memLitre)/($km-$memKm),2);
			$texte .= $km-$memKm.'km fait avec '.number_format($memLitre,2).' litres. Conso : '. $conso.'L/100km';
		} 
		$memKm = $km;
		$memLitre = $litre;
		$texte .= '<br>'	;
	}
	$texte .= '<br>';
}

echo $texte;

llxFooter();
