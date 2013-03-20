<?php
require('../config.php');
require('../class/evenement.class.php');
require('../class/ressource.class.php');
/*
 * parent::add_champs('date_debut, date_fin','type=date;');
		
		parent::add_champs('fk_rh_ressource','type=entier;index;');	
		parent::add_champs('fk_user,entity','type=entier;index;');
		parent::add_champs('fk_tier,entity','type=entier;index;');
		
		//type : accident, répération, ou emprunt
		parent::add_champs('type','type=chaine;');

		//pour le wdCalendar
		parent::add_champs('color','type=chaine;');
		parent::add_champs('isAllDayEvent','type=entier;');
		parent::add_champs('location','type=chaine;');
		parent::add_champs('subject','type=chaine;');
		parent::add_champs('description','type=chaine;');
		parent::add_champs('recurringrule','type=chaine;');
		
		//pour un accident, une réparation
		parent::add_champs('motif','type=chaine;');
		parent::add_champs('coutHT','type=float;');
		parent::add_champs('coutEntrepriseHT','type=float;');
		parent::add_champs('TVA','type=entier;');
		
		//pour un appel
		
		parent::add_champs('appelDate','type=date;');  //ou utiliser date_debut ?
		parent::add_champs('appelHeure','type=chaine;');
		parent::add_champs('appelNumero','type=chaine;');
		parent::add_champs('appelDureeReel','type=chaine;');
		parent::add_champs('appelDureeFacturee','type=chaine;');
		parent::add_champs('fk_facture','type=entier;index');
		
 * 
 * 
 */
 
$ATMdb=new Tdb;

$numLigne = 0;
if (($handle = fopen("ListeAppel.csv", "r")) !== FALSE) {
	while(($data = fgetcsv($handle)) != false){
		echo $numLigne.' : ';
		//print_r($data);
		if ($numLigne >=3){
			$infos = explode(';', $data[0]);
			//print_r($infos);
			//$date = explode('/', $infos[6]);
			//echo $infos[6].'    '.mktime(0,0,0,$date[1],$date[0],$date[2]);
			$temp = new TRH_Evenement;
			$temp->set_date('date_debut', $infos[6]);
			//$temp->date_debut = $temp->date_fin = mktime(0,0,0,$date[1],$date[0],$date[2]);
			$temp->type = 'appel';
			$temp->fk_rh_ressource = 8;
			$temp->appelHeure= $infos[7];
			$temp->appelNumero = $infos[1];
			$temp->appelDureeReel = $infos[9];
			$temp->appelDureeFacturee = $infos[10];
			$temp->motif = $infos[11];
			
			//le cout pour l'entreprise est celui donnée dans l'import
			$temp->coutEntrepriseHT = (float)$infos[12];
		
			//TODO : le coût va dépendre des règles sur le type et sur l'utilisateur
			$temp->coutHT = (float)$infos[12];
			
			//TODO rajouter les autres données
			//print_r($temp);
			$temp->save($ATMdb);		
}
		
		echo '<br>';
		$numLigne++;
		
		//print_r(explode('\n', $data));
	}
}
	