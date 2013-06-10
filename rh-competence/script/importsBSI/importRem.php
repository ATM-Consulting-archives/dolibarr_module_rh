<?php
set_time_limit(0);
ini_set('memory_limit','512M');


require('../../config.php');
require('../../class/competence.class.php');

global $conf;
$ATMdb=new TPDOdb;

// relever le point de départ
$timestart=microtime(true);

echo 'Import initial des Remuneration.<br><br>';

$TTrigramme = array();
$sql="SELECT rowid, login FROM ".MAIN_DB_PREFIX."user WHERE entity IN (0,".$conf->entity.")";
$ATMdb->Execute($sql);
while($row = $ATMdb->Get_line()) {
	$TTrigramme[strtolower($row->login)] = $row->rowid;
}
//print_r($TTrigramme);exit();



$nomFichier = "Sal_rem_130606";
echo 'Traitement du fichier '.$nomFichier.' : <br>';
$cpt = 0;
//début du parsing
$numLigne = 0;
if (($handle = fopen("./".$nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle, 0,'\r')) != false){
		//echo 'Traitement de la ligne '.$numLigne.'...';
		
		$infos = explode(';', str_replace('"', '', $data[0])) ;
		print_r($infos);
		echo '<br>';
		$trigramme = strtolower($infos[0]);
		$deb = mktime(0,0,0,substr($infos[1],5,2), substr($infos[1],8,2), substr($infos[1], 0,4));
		$fin = mktime(0,0,0,substr($infos[2],5,2), substr($infos[2],8,2), substr($infos[2], 0,4));
		
		if (!empty($TTrigramme[$trigramme])){
			$rem = new TRH_remuneration;
			$rem->load_by_user_and_dates($ATMdb, $TTrigramme[$trigramme], $deb, $fin);
			
			$rem->fk_user = $TTrigramme[$trigramme];
		
			$rem->date_debutRemuneration = $deb;
			$rem->date_finRemuneration = $fin;
			$r = 3;
			$rem->bruteAnnuelle = $infos[$r];$r++;
			$rem->salaireMensuel = $infos[$r];$r++;
			$rem->primeAnciennete = $infos[$r];$r++;
			$rem->primeNoel = $infos[$r];$r++;
			$rem->commission = $infos[$r];$r++;
			$rem->autre = $infos[$r];$r++;
			
			$rem->participation = $infos[$r];$r++;
			
			
			$rem->prevoyancePartSalariale = $infos[$r];$r++;
			$rem->retraitePartSalariale = $infos[$r];$r++;
			$rem->mutuellePartSalariale = $infos[$r];$r++;
			$rem->urssafPartSalariale = $infos[$r];$r++;
			$rem->diversPartSalariale = $infos[$r];$r++;
			
			$rem->prevoyancePartPatronale = $infos[$r];$r++;
			$rem->retraitePartPatronale = $infos[$r];$r++;
			$rem->mutuellePartPatronale = $infos[$r];$r++;
			$rem->urssafPartPatronale = $infos[$r];$r++;
			$rem->diversPartPatronale  = $infos[$r];$r++;
			
		
			$rem->entity = $conf->entity;
			$rem->save($ATMdb);
			$cpt++;
		}
		else{
			echo "Attention : ".$trigramme." non trouvé. La rem de la ligne ".$numLigne." n'a pas été importée.<br>";
		}
		$numLigne++;
	}
}


//Fin du code PHP : Afficher le temps d'éxecution
$timeend=microtime(true);
$page_load_time = number_format($timeend-$timestart, 3);
echo $cpt." rem importées.<br>";
echo 'Fin du traitement. Durée : '.$page_load_time . " sec<br><br>";
$ATMdb->close();

	
