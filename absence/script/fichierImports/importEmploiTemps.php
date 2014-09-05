<?php

set_time_limit(0);
ini_set("memory_limit", "512M");

require('../../config.php');
require('../../class/absence.class.php');


global $conf, $langs;

$ATMdb=new TPDOdb;


		
//on charge quelques listes pour avoir les clés externes.
$TUser = array();

$sql="SELECT rowid, login FROM ".MAIN_DB_PREFIX."user";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TUser[strtolower($ATMdb->Get_field('login'))] = $ATMdb->Get_field('rowid');
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
echo $langs->trans('FileProcessing', $nomFichier) . ' : <br><br>';


//début du parsing
$numLigne = 0;
$ligneok=0;
if (($handle = fopen($nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle)) != false){
		
		
		if($numLigne>1){
			$infos = explode(';', $data[0]);
			echo $langs->trans('LineProcessing', $numLigne) . '...';
			
			if (empty( $TUser[strtolower($infos[3])])){	//si le login n'existe pas, on ne traite pas la ligne
				echo $langs->trans('ErrNonExistentUser', strtolower($infos[3]));
			}
			else{
					$edt=new TRH_EmploiTemps;
					$entreprise='';
					
					//traitement des lignes et insertion en base
					//on récupère le compteur de l'utilisateur si celui-ci existe sinon il sera créé
					//echo $TUser[strtolower($infos[3])];exit;
					$edt->load_by_fkuser($ATMdb, $TUser[strtolower($infos[3])]);
					$edt->fk_user=$TUser[strtolower($infos[3])];
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
						
						if(stristr($infos[0],'agt')!=false){	//on est chez AGT
							 $entreprise='%agt%';
							$sql='SELECT label, rowid FROM '.MAIN_DB_PREFIX.'entity WHERE label LIKE "'.$entreprise.'"';

						}
						elseif(stristr($infos[0],'impression')!=false){	//on est chez global impression
							$entreprise='%impression%';
							$sql='SELECT label, rowid FROM '.MAIN_DB_PREFIX.'entity WHERE label LIKE "'.$entreprise.'"';
						}
						elseif(stristr($infos[0],'informatique')!=false){	//on est chez global impression
							$entreprise='%info%';
							$sql='SELECT label, rowid FROM '.MAIN_DB_PREFIX.'entity WHERE label LIKE "'.$entreprise.'"';
						}
						elseif(stristr($infos[0],'groupe')!=false){//on est chez  cpro groupe
							$entreprise='%groupe%';
							$sql='SELECT label, rowid FROM '.MAIN_DB_PREFIX.'entity WHERE label LIKE "'.$entreprise.'"';
						}else{	//on est chez cpro
							$entreprise='cpro';
							$entreprise1="c'pro";
							$sql='SELECT label, rowid FROM '.MAIN_DB_PREFIX.'entity WHERE label LIKE "'.$entreprise.'" OR label LIKE "'.$entreprise1.'"';
						}
						
						$ATMdb->Execute($sql);
						
						while($ATMdb->Get_line()) {
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

echo $langs->trans('EndOfProcessing') . ' ' . $langs->trans('AddedLines', $ligneok) . '<br><br>';	

$ATMdb->close();

//------------FIN DU TRAITEMENT DES LIGNES----------------------------------------------------------





