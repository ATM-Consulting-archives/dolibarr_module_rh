<?php
/**
 * Ce script vérifie la consommation des cartes TOTAL : à savoir si l'utilisation de la carte est abusive 
 */ 

require('./config.php');
require('./lib/ressource.lib.php');


llxHeader('','Vérification des Véhicules');

print dol_get_fiche_head(array()  , '', 'Vérification');

$plagedeb = isset($_REQUEST['plagedebut']) ? $_REQUEST['plagedebut'] : date("d/m/Y",time());
$date_debut = mktime(0,0,0,substr($plagedeb, 3,2),substr($plagedeb, 0,2), substr($plagedeb, 6,4));
$plagefin = isset($_REQUEST['plagefin']) ? $_REQUEST['plagefin'] : date("d/m/Y", time()+3600*24*31);
$date_fin = mktime(0,0,0,substr($plagefin, 3,2),substr($plagefin, 0,2), substr($plagefin, 6,4));

$url ='http://'.$_SERVER['SERVER_NAME']. DOL_URL_ROOT_ALT."/ressource/script/loadContratLimite.php?plagedebut=".$plagedeb."&plagefin=".$plagefin;
$result = file_get_contents($url);
$TRessource = unserialize($result);

$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
$form->Set_typeaff('edit');
	
$TBS=new TTemplateTBS();
print $TBS->render('./tpl/verificationVehicule.tpl.php'
	,array(
		'ressource'=>$TRessource
	)
	,array(
		'infos'=>array(
			'titre'=>load_fiche_titre("Vérification des contrats des véhicules",'', 'title.png', 0, '')
			,'plagedebut'=>$form->calendrier('', 'plagedebut', $date_debut, 12)
			,'plagefin'=>$form->calendrier('', 'plagefin', $date_fin, 12)
			,'valider'=>$form->btsubmit('Génerer', 'valider')
		)
	)	
	
);

$form->end();
llxFooter();