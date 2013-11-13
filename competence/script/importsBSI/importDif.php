<?php
set_time_limit(0);
ini_set('memory_limit','512M');


require('../../config.php');
require('../../class/competence.class.php');

global $conf;
$ATMdb=new TPDOdb;

// relever le point de départ
$timestart=microtime(true);

echo 'Import initial des DIF.<br><br>';

$TTrigramme = array();
$sql="SELECT rowid, login FROM ".MAIN_DB_PREFIX."user WHERE entity IN (0,".$conf->entity.")";
$ATMdb->Execute($sql);
while($row = $ATMdb->Get_line()) {
	$TTrigramme[strtolower($row->login)] = $row->rowid;
}
//print_r($TTrigramme);exit();



$nomFichier = "Sal_dif_130606";
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
		$annee = $infos[1];
		
		
		if (!empty($TTrigramme[$trigramme])){
			$dif = new TRH_dif;
			$dif->load_by_user_and_annee($ATMdb, $TTrigramme[$trigramme], $annee);
			
			$dif->fk_user = $TTrigramme[$trigramme];
			
			$dif->annee = intval($annee);
			$dif->nb_heures_acquises = intval($infos[2]);
			$dif->nb_heures_prises = intval($infos[3]);
			$dif->nb_heures_restantes = intval($infos[4]);
					
			$dif->entity = $conf->entity;
			$dif->save($ATMdb);
			$cpt++;
		}
		else{
			echo "Attention : ".$trigramme." non trouvé. La dif de la ligne ".$numLigne." n'a pas été importé.<br>";
		}
		$numLigne++;
	}
}


//Fin du code PHP : Afficher le temps d'éxecution
$timeend=microtime(true);
$page_load_time = number_format($timeend-$timestart, 3);
echo $cpt." dif importées.<br>";
echo 'Fin du traitement. Durée : '.$page_load_time . " sec<br><br>";
$ATMdb->close();

	
