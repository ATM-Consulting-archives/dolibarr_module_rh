<?php
define('INC_FROM_CRON_SCRIPT', true);
require('../config.php');
require('../lib/ressource.lib.php');
global $conf;
$ATMdb=new TPDOdb;

$idTotal = getIdType('cartetotal');
$TCartes = getRessource($idTotal);

$TPleins = array();
$sql="SELECT rowid, fk_rh_ressource, motif, commentaire, litreEssence, kilometrage 
	FROM ".MAIN_DB_PREFIX."rh_evenement 
	WHERE entity=".$conf->entity." AND type='pleindessence' ORDER BY date_debut";
$ATMdb->Execute($sql);
while($row = $ATMdb->Get_line()) {
	$TPleins[$row->fk_rh_ressource][$row->kilometrage] = $row->litreEssence;
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
					$texte = ($km-$memKm).'km fait avec '.number_format($memLitre,2).' litres. Conso : '. $conso.'L/100km';
					//$texte .= "<span style=\"margin-left: 3em;\">".($km-$memKm).'km fait avec '.number_format($memLitre,2).' litres. Conso : '. $conso.'L/100km<br></span>';
				}
			}
			else{
				//$texte .= "<span style=\"margin-left: 3em;\">".($km-$memKm).'km fait avec '.number_format($memLitre,2).' litres. Conso : '. $conso.'L/100km<br></span>';
				$texte = ($km-$memKm).'km fait avec '.number_format($memLitre,2).' litres. Conso : '. $conso.'L/100km';
			}
		}
		$memKm = $km;
		$memLitre = $litre;
		if (!empty($texte)){
			$TRessource[] = array(
				'nom'=>$TCartes[$idRessource]
				,'info'=> $texte
			);
		}
	}
	
	
}


//echo json_encode($TRessource);
__out($TRessource);


exit();

	