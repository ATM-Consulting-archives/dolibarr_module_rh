<?php

set_time_limit(0);
ini_set("memory_limit", "512M");

require('../../config.php');
require('../../class/absence.class.php');


global $conf;

$ATMdb=new Tdb;


		
//on charge quelques listes pour avoir les clés externes.
$TUser = array();

$sql="SELECT rowid, login FROM ".MAIN_DB_PREFIX."user";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TUser[$ATMdb->Get_field('login')] = $ATMdb->Get_field('rowid');
}



//chargement des groupes et des users dans la liste $TGroups;
$TGroups= array();
$sql="SELECT fk_user, fk_usergroup
	FROM ".MAIN_DB_PREFIX."usergroup_user
	
	";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TGroups[$ATMdb->Get_field('fk_usergroup')][] = $ATMdb->Get_field('fk_user');

}



//----------------DEBUT DU TRAITEMENT DES LIGNES D'APPELS----------------------------------------------------------
$nomFichier = "./Horaire.csv";
echo 'Traitement du fichier '.$nomFichier.' : <br><br>';



//début du parsing
$numLigne = 0;
$ligneok=0;
if (($handle = fopen($nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle)) != false){
		
		
		if($numLigne>1){
			$infos = explode(';', $data[0]);
			echo 'Traitement de la ligne '.$numLigne.'...';
			
			if (empty( $TUser[strtolower($infos[3])])){	//si le login n'existe pas, on ne traite pas la ligne
				echo 'Erreur : Utilisateur '.strtolower($infos[3]).' inexistant ';
			}
			else{
					
					$edt=new TRH_EmploiTemps;
					
					//traitement des lignes et insertion en base
					//on récupère le compteur de l'utilisateur si celui-ci existe sinon il sera créé
					$edt->load_by_fkuser($ATMdb, $TUser[strtolower($infos[3])]);
					
					//echo $edt->rowid;exit;
					$cpt=4;
					foreach ($edt->TJour as $jour) {
						$cpt++;
						if($jour!='samedi' && $jour!='dimanche') {
								//	traitement du matin
								 if(!empty($infos[$cpt])){
								 	$edt->{$jour.'am'}=1;
								 }
								 
								 //début matin
								 if(!empty($edt->{'date_'.$jour."_heuredam"})){
								 	$edt->{'date_'.$jour."_heuredam"}=strtotime(str_replace('h',':',$infos[$cpt]));
								 }else $edt->{'date_'.$jour."_heuredam"}=strtotime('0:00');
								 
								 
								 //fin matin
								 $cpt++;
								  if(!empty($edt->{'date_'.$jour."_heurefam"})){
								 	$edt->{'date_'.$jour."_heurefam"}=strtotime(str_replace('h',':',$infos[$cpt]));
								 }else $edt->{'date_'.$jour."_heurefam"}=strtotime('0:00');
								
								 
								 
								//	traitement de l'après-midi 
								$cpt++;
								 if(!empty($infos[$cpt])){
								 	$edt->{$jour.'pm'}=1;
								 }
								  if(!empty($edt->{'date_'.$jour."_heuredpm"})){
								 	$edt->{'date_'.$jour."_heuredpm"}=strtotime(str_replace('h',':',$infos[$cpt]));
								 }else $edt->{'date_'.$jour."_heuredpm"}=strtotime('0:00');
								 
								 
								 //fin matin
								 $cpt++;
								  if(!empty($edt->{'date_'.$jour."_heurefpm"})){
								 	$edt->{'date_'.$jour."_heurefpm"}=strtotime(str_replace('h',':',$infos[$cpt]));
								 }else $edt->{'date_'.$jour."_heurefpm"}=strtotime('0:00');
							 
							}
				
							else{
								$edt->{'date_'.$jour."_heuredam"}=$edt->{'date_'.$jour."_heurefam"}=$edt->{'date_'.$jour."_heuredpm"}=$edt->{'date_'.$jour."_heurefpm"}= strtotime('0:00');
							}
							
						}
						$edt->tempsHebdo=$infos[4];	
						
						$sql='SELECT label, rowid FROM '.MAIN_DB_PREFIX.'entity WHERE label LIKE "'.$infos[0].'"';
						$ATMdb->Execute($sql);
						
						if($ATMdb->Get_line()) {
							$edt->societeRtt=$ATMdb->Get_field('rowid');
						}					
						
						$edt->save($ATMdb);
						$ligneok++;
						
				
			}
			
			}	
			$numLigne++;	
			echo '<br>';
		}
}

echo 'Fin du traitement. '.($ligneok).' lignes rajoutées à la table.<br><br>';	

$ATMdb->close();

//------------FIN DU TRAITEMENT DES LIGNES----------------------------------------------------------





