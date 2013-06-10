<?php
set_time_limit(0);
ini_set('memory_limit','512M');


require('../../config.php');
require('../../class/competence.class.php');

global $conf;
$ATMdb=new TPDOdb;

// relever le point de départ
$timestart=microtime(true);

echo 'Import initial des formations.<br><br>';

$TTrigramme = array();
$sql="SELECT rowid, login FROM ".MAIN_DB_PREFIX."user WHERE entity IN (0,".$conf->entity.")";
$ATMdb->Execute($sql);
while($row = $ATMdb->Get_line()) {
	$TTrigramme[strtolower($row->login)] = $row->rowid;
}
//print_r($TTrigramme);exit();


//----------------------Import des cartes total---------------


$nomFichier = "Sal_form_130606";
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
		$libelleFormation = $infos[1];
		
		
		if (!empty($TTrigramme[$trigramme])){
			$forma = new TRH_formation_cv;
			$forma->load_by_user_and_libelle($ATMdb, $TTrigramme[$trigramme], $libelleFormation);
			
			$forma->fk_user = $TTrigramme[$trigramme];
			$forma->libelleFormation = $libelleFormation;
			$forma->date_debut = $infos[2];
			$forma->date_debut = $infos[3];
			$forma->date_fin;
			$forma->coutFormation = floatval(str_replace(',','.',$infos[4]));
			$forma->montantOrganisme = floatval(str_replace(',','.',$infos[5]));
			$forma->montantEntreprise = floatval(str_replace(',','.',$infos[6]));
			$forma->lieuFormation = $infos[7];
			
			/*
			$forma->competenceFormation;  non renseigné dans le fichier
			$forma->commentaireFormation;
			*/
			
			$forma->entity = $conf->entity;
			$forma->save($ATMdb);
			$cpt++;
		}
		else{
			echo "Attention : ".$trigramme." non trouvé. La formation de la ligne ".$numLigne." n'a pas été importé.<br>";
		}
		$numLigne++;
	}
}


//Fin du code PHP : Afficher le temps d'éxecution
$timeend=microtime(true);
$page_load_time = number_format($timeend-$timestart, 3);
echo $cpt." formations importées.<br>";
echo 'Fin du traitement. Durée : '.$page_load_time . " sec<br><br>";
$ATMdb->close();

	
