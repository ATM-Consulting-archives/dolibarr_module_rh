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
$limite = (isset($_REQUEST['limite'])) ? floatval($_REQUEST['limite']) : 0;
//$plagedeb = !empty($_REQUEST['plagedebut']) ? date("Y-m-d 00:00:00", dateToInt($_REQUEST['plagedebut'])) : date("Y-m-d 00:00:00",time()-31532400);
//$plagefin = !empty($_REQUEST['plagefin']) ? date("Y-m-d 00:00:00", dateToInt($_REQUEST['plagefin'])) : date("Y-m-d  00:00:00", time()+31532400);

$TPleins = array();
$sql="SELECT e.rowid, DATE_FORMAT(date_debut,'%d/%m/%Y') as point,  date_debut , 
	r.fk_rh_ressource as 'voiture', e.fk_rh_ressource as 'carte', e.motif, e.commentaire, 
	e.litreEssence, e.kilometrage, e.fk_user 
	FROM ".MAIN_DB_PREFIX."rh_evenement as e
	LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (e.fk_rh_ressource = r.rowid)
	WHERE (e.type='gazolepremier' OR e.type='gazoleexcellium') ";
if ($fk_user!= 0){ $sql .= "AND e.fk_user=".$fk_user;}	
$sql .=	" ORDER BY kilometrage";
//echo $sql;

$TUser = getUsers(false, false);

$ATMdb->Execute($sql);
while($row = $ATMdb->Get_line()) {	
	$TPleins[$row->carte][$row->kilometrage] = array(
		//'idcarte'=>$row->fk_rh_ressource
		'km'=>$row->kilometrage
		,'litre'=>$row->litreEssence
		,'fk_user'=>$row->fk_user //firstname.' '.$row->name
		,'fk_rh_ressource'=>$row->voiture
		,'date'=>$row->point
		,'date_debut'=>date2ToInt($row->date_debut)
	);
}	


$TRessource = array();
$cpt = 0;

//on lit une carte
foreach ($TPleins as $idcarte => $value) {
	
	$memKm = 0;
	$memLitre = 0;
	$texte = '';
	$depassement = false;  //indique si il y a au moins un plein dépassement sur l'ensemble de la carte.
	$TTempLigne = array();
	
	$sommeEssence = 0;
	
	//on lit une ligne de plein de la carte.
	foreach ($value as $km => $tab) {
		$sommeEssence += $tab['litre'];
		$memLitre = $tab['litre'];
		
		//calcul de la consommation instantanée
		if ($memKm!=0){
			$conso = number_format((100*$memLitre)/($km-$memKm),2);
			$consotexte = $conso.'L/100km';
			$diffkmtexte = ($km-$memKm).'km';
			$essencetexte = number_format($tab['litre'],2).' L';
			
			if ($conso>=$limite){$depassement = true;}
			
			//on met en gras si il y a dépassement.
			if ($depassement && $limite>0){
				$consotexte = '<b>'.$consotexte.'</b>'; 
				$diffkmtexte = '<b>'.$diffkmtexte.'</b>'; 
				$essencetexte = '<b>'.$essencetexte.'</b>';
			}
			
		}
		//ajout de la conso instantanée
		if (($tab['date_debut']<= $plagefin) && ($tab['date_debut']>= $plagedeb)){
			$TTempLigne[] = array(
				'nom'=>$TCartes[$idcarte]
				,'vehicule'=>$TVoiture[$tab['fk_rh_ressource']]
				,'km'=>$tab['km'].' km'
				,'diffkm' =>  ($memKm!=0) ? $diffkmtexte : ''
				,'essence'=>($memKm!=0) ? $essencetexte : ''
				,'conso'=> ($memKm!=0) ? $consotexte : ''
				,'user'=>$TUser[$tab['fk_user']]
				,'date'=> $tab['date']
				,'ok'=>$tab['date_debut']
				,'parite'=>($cpt%2==0) ? 'pair' : 'impair'
			);
		$memKm = $km;
		
		}
	}
	
	//calcul et ajout de la consommation générale sur la carte Total
	$kmdebut = min(array_keys($value));
	$kmfin = max(array_keys($value));
	$diffkm = $kmfin-$kmdebut;
	if ($diffkm>0){
		//$Moyconso = number_format((100*$sommeEssence)/($diffkm),2);
		
		$TTempLigne[] = array(
				'nom'=>''
				,'vehicule'=>''
				,'km'=>''
				,'diffkm' =>'Total: '.$diffkm.'km'
				,'conso'=> ''//($limite>0) ? '<b style="color:red;">Moyenne : '.$Moyconso.'L/100km</b>' : 'Moyenne : '.$Moyconso.'L/100km'
				,'essence'=>'Total: '.$sommeEssence.'L'
				,'date'=> ''
				,'user'=>''
				,'ok'=>''
				,'parite'=>($cpt%2==0) ? 'pair' : 'impair'
			);
		//echo $kmdebut.' km ->'.$kmfin.'km : '.$diffkm.'km.   '.$sommeEssence.' L Moyenne : '.$Moyconso.'L/100km <br>';
	}
	if ($depassement){//il y a eu dépasement, on ajoute les lignes de la carte au tableau final.
		$cpt++;
		foreach ($TTempLigne as $key => $value) {
			$TRessource[] = $value;}
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
