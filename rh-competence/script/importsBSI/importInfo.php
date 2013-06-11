<?php
set_time_limit(0);
ini_set('memory_limit','512M');


require('../../config.php');
require('../../class/competence.class.php');

global $conf;
$ATMdb=new TPDOdb;

// relever le point de départ
$timestart=microtime(true);

echo 'Import initial des Informations Utilisateurs.<br><br>';

$TTrigramme = array();
$sql="SELECT rowid, login FROM ".MAIN_DB_PREFIX."user WHERE entity IN (0,".$conf->entity.")";
$ATMdb->Execute($sql);
while($row = $ATMdb->Get_line()) {
	$TTrigramme[strtolower($row->login)] = $row->rowid;
}
//print_r($TTrigramme);exit();



$nomFichier = "Sal_info_130606";
echo 'Traitement du fichier '.$nomFichier.' : <br>';
$cpt = 0;
//début du parsing
$numLigne = 0;
if (($handle = fopen("./".$nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle, 0,'\r')) != false){
		$infos = explode(';', str_replace('"', '', $data[0])) ;

		//print_r($infos);echo '<br>';
		
		$trigramme = strtolower($infos[0]);
		
		if (!empty($TTrigramme[$trigramme])){
			//récupération des données
			$fk_user = $TTrigramme[$trigramme];
			$r = 1;
			$ddn = $infos[$r];$r++;
			$sitFam = ucwords(htmlentities($infos[$r]));$r++;
			$nbEnfCharge = $infos[$r];$r++;
			$dda = $infos[$r];$r++;
			$horaire = $infos[$r];$r++;
			$statut = ucwords(htmlentities($infos[$r]));$r++;
			$niveau = $infos[$r];$r++;
			$contrat = $infos[$r];$r++;
			$r++;						//la colonne affectation à un groupe, c'est pas ici
			$fonction = $infos[$r];$r++;	
			
			
			//on regarde si une ligne de l'user existe déjà.
			$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."user_extrafields
			WHERE fk_object=".$TTrigramme[$trigramme];
			$ATMdb->Execute($sql);
			if ($ATMdb->Get_line()) {
				//la ligne existe déjà : on fait un UPDATE
				$req = "UPDATE ".MAIN_DB_PREFIX."user_extrafields SET 
					DDN='".$ddn."'
					,SIT_FAM='".$sitFam."'
					,NB_ENF_CHARGE=".$nbEnfCharge."
					,DDA='".$dda."'
					,HORAIRE='".$horaire."'
					,STATUT='".$statut."'
					,NIVEAU='".$niveau."'
					,CONTRAT='".$contrat."'
					,FONCTION='".$fonction." '
					WHERE fk_object = ".$fk_user;
			}
			else{
				//la ligne n'existe pas, on fait un INSERT
				"INSERT INTO llx_user_extrafields ([value-1],[value-2],[value-3],[value-4],[value-5],[value-6],[value-7],[value-8],[value-9],[value-10],[value-11],[value-12],[value-13],[value-14],[value-15])";
				$req = "INSERT INTO ".MAIN_DB_PREFIX."user_extrafields 
					(fk_object, DDN, SIT_FAM, NB_ENF_CHARGE, DDA, HORAIRE, STATUT, NIVEAU, CONTRAT, FONCTION) VALUES
					(".$fk_user.",'".$ddn."','".$sitFam."',".$nbEnfCharge.",'".$dda."','".$horaire."','".$statut."','".$niveau."','".$contrat."','".$fonction."')";	
			}
			$ATMdb->Execute($req);
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
echo $cpt." infos utilisateurs importées.<br>";
echo 'Fin du traitement. Durée : '.$page_load_time . " sec<br><br>";
$ATMdb->close();

	
