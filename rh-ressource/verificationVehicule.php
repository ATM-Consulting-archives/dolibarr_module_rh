<?php
/**
 * Ce script vérifie la consommation des cartes TOTAL : à savoir si l'utilisation de la carte est abusive 
 */ 

require('./config.php');
require('./lib/ressource.lib.php');

global $conf;
$ATMdb=new TPDOdb;

llxHeader('','Vérification des Véhicules');
print dol_get_fiche_head(array()  , '', 'Vérification');

$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
$form->Set_typeaff('edit');
	
$TBS=new TTemplateTBS();
print $TBS->render('./tpl/verificationVehicule.tpl.php'
	,array()
	,array(
		'infos'=>array(
			'date_debut'=>$form->calendrier('', 'date_debut', '', 12)
			,'date_fin'=>$form->calendrier('', 'date_fin', '', 12)
			,'texte'=>$texte
			)
	)	
	
);


$form->end();
$ATMdb->close();
llxFooter();