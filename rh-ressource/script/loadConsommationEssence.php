<?php
define('INC_FROM_CRON_SCRIPT', true);
require('../config.php');
require('../lib/ressource.lib.php');
global $conf;
$ATMdb=new TPDOdb;

$idTotal = getIdType('cartetotal');
$TCartes = getRessource($idTotal);

$plagedeb = !empty($_REQUEST['plagedebut']) ? dateToInt($_REQUEST['plagedebut']) : (time()-31532400);
$plagefin = !empty($_REQUEST['plagefin']) ? dateToInt($_REQUEST['plagefin']) : (time()+31532400);
//$plagedeb = !empty($_REQUEST['plagedebut']) ? date("Y-m-d 00:00:00", dateToInt($_REQUEST['plagedebut'])) : date("Y-m-d 00:00:00",time()-31532400);
//$plagefin = !empty($_REQUEST['plagefin']) ? date("Y-m-d 00:00:00", dateToInt($_REQUEST['plagefin'])) : date("Y-m-d  00:00:00", time()+31532400);

$TPleins = array();
$sql="SELECT e.rowid, DATE_FORMAT(date_debut,'%d/%m/%Y') as point,  date_debut , e.fk_rh_ressource, e.motif, e.commentaire, e.litreEssence, e.kilometrage, u.name, u.firstname
	FROM ".MAIN_DB_PREFIX."rh_evenement as e
	LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (e.fk_user=u.rowid)
	WHERE e.entity=".$conf->entity." 
	AND type='pleindessence' 
	ORDER BY date_debut";


$ATMdb->Execute($sql);
while($row = $ATMdb->Get_line()) {	
	$TPleins[$row->fk_rh_ressource][$row->kilometrage] = array(
		//'idcarte'=>$row->fk_rh_ressource
		//,'km'=>$row->kilometrage
		'litre'=>$row->litreEssence
		,'nom'=>$row->firstname.' '.$row->name
		,'date'=>$row->point
		,'date_debut'=>date2ToInt($row->date_debut)
	);
	
}	

$TRessource = array();

foreach ($TPleins as $idcarte => $value) {
	$memKm = 0;
	$memLitre = 0;
	$texte = '';
	foreach ($value as $km => $tab) {
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
		$memLitre = $tab['litre'];
		//echo date("d/m/Y",$plagedeb).' '.date("d/m/Y",$tab['date_debut']).' '.date("d/m/Y",$plagefin).'<br>';
		if ((!empty($texte)) && ($tab['date_debut']<= $plagefin) && ($tab['date_debut']>= $plagedeb)){
			$TRessource[] = array(
				'nom'=>$TCartes[$idcarte]
				,'info'=> $texte
				,'user'=> htmlentities($tab['nom'], ENT_COMPAT , 'ISO8859-1')
				,'date'=> $tab['date']
				,'ok'=>$tab['date_debut']
			);
		}
	
	}
}

/*foreach ($TRessource as $key => $value) {
	echo $key.' : ';
	print_r($value);
	echo '<br>';
}*/
//print_r($TRessource);
//echo json_encode($TRessource);
__out($TRessource);


exit();

/**
 * prend un format 2013-03-19 00:00:00 et renvoie un timestamp
 */
function date2ToInt($chaine){
	//echo $chaine.' '.substr($chaine,5,2).' '.substr($chaine,8,2).' '.substr($chaine,0,4).'<br>';
	return mktime(0,0,0,substr($chaine,5,2),substr($chaine,8,2),substr($chaine,0,4));
}
/*
 * prend un format d/m/Y et renvoie un timestamp
 */
function dateToInt($chaine){
	return mktime(0,0,0,substr($chaine,3,2),substr($chaine,0,2),substr($chaine,6,4));
}
	