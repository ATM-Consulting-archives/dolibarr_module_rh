<?php
define('INC_FROM_CRON_SCRIPT', true);
require('../config.php');
require('../lib/ressource.lib.php');
global $conf;
$ATMdb=new TPDOdb;

$idTotal = getIdType('cartetotal');
$idVoiture = getIdType('voiture');
$TCartes = getRessource($idTotal);
$TVoiture = getRessource($idVoiture);
//print_r($TVoiture);exit();

$plagedeb = !empty($_REQUEST['plagedebut']) ? dateToInt($_REQUEST['plagedebut']) : (time()-31532400);
$plagefin = !empty($_REQUEST['plagefin']) ? dateToInt($_REQUEST['plagefin']) : (time()+31532400);
$fk_user = !empty($_REQUEST['fk_user']) ? $_REQUEST['fk_user'] : 0 ;
//$plagedeb = !empty($_REQUEST['plagedebut']) ? date("Y-m-d 00:00:00", dateToInt($_REQUEST['plagedebut'])) : date("Y-m-d 00:00:00",time()-31532400);
//$plagefin = !empty($_REQUEST['plagefin']) ? date("Y-m-d 00:00:00", dateToInt($_REQUEST['plagefin'])) : date("Y-m-d  00:00:00", time()+31532400);

$TPleins = array();
$sql="SELECT e.rowid, DATE_FORMAT(date_debut,'%d/%m/%Y') as point,  date_debut , 
	r.fk_rh_ressource as 'voiture', e.fk_rh_ressource as 'carte', e.motif, e.commentaire, e.litreEssence, e.kilometrage, e.fk_user 
	FROM ".MAIN_DB_PREFIX."rh_evenement as e
	LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (e.fk_rh_ressource = r.rowid)
	WHERE (e.type='gazolepremier' OR e.type='gazoleexcellium') ";
if ($fk_user!= 0){ $sql .= "AND e.fk_user=".$fk_user;}	
$sql .=	" ORDER BY date_debut, kilometrage";
//echo $sql;

$TUser = getUsers();

$ATMdb->Execute($sql);
while($row = $ATMdb->Get_line()) {	
	$TPleins[$row->carte][$row->kilometrage] = array(
		//'idcarte'=>$row->fk_rh_ressource
		//,'km'=>$row->kilometrage
		'litre'=>$row->litreEssence
		,'fk_user'=>$row->fk_user //firstname.' '.$row->name
		,'fk_rh_ressource'=>$row->voiture
		,'date'=>$row->point
		,'date_debut'=>date2ToInt($row->date_debut)
	);
}	


$TRessource = array();
$cpt = 0;

foreach ($TPleins as $idcarte => $value) {
	
	$memKm = 0;
	$memLitre = 0;
	$texte = '';
	foreach ($value as $km => $tab) {
		
		
		if ($memKm!=0){
			$conso = number_format((100*$memLitre)/($km-$memKm),2);
			if(isset($_REQUEST['limite'])) {
				if ($conso>=$_REQUEST['limite']){
					$texte = ($km-$memKm).'km fait avec '.number_format($memLitre,2).' litres.';
					$consotexte = $conso.'L/100km';
					//$texte .= "<span style=\"margin-left: 3em;\">".($km-$memKm).'km fait avec '.number_format($memLitre,2).' litres. Conso : '. $conso.'L/100km<br></span>';
				}
			}
			else{
				//$texte .= "<span style=\"margin-left: 3em;\">".($km-$memKm).'km fait avec '.number_format($memLitre,2).' litres. Conso : '. $conso.'L/100km<br></span>';
				$texte = ($km-$memKm).'km fait avec '.number_format($memLitre,2).' litres';
				$consotexte = $conso.'L/100km';
			}
		}
		$memKm = $km;
		$memLitre = $tab['litre'];
		//echo $tab['fk_rh_ressource'].'<br>';
		//echo date("d/m/Y",$plagedeb).' '.date("d/m/Y",$tab['date_debut']).' '.date("d/m/Y",$plagefin).'<br>';
		if ((!empty($texte)) && ($tab['date_debut']<= $plagefin) && ($tab['date_debut']>= $plagedeb)){
			$TRessource[] = array(
				'nom'=>$TCartes[$idcarte]
				,'vehicule'=>$TVoiture[$tab['fk_rh_ressource']]
				,'info'=> $texte
				,'conso'=>$consotexte
				,'user'=>$TUser[$tab['fk_user']]
				,'date'=> $tab['date']
				,'ok'=>$tab['date_debut']
				,'parite'=>($cpt%2==0) ? 'pair' : 'impair'
			);
		}
	
	}
	$cpt++;
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
	