<?php
/**
 * Ce script vérifie la consommation des cartes TOTAL : à savoir si l'utilisation de la carte est abusive 
 */ 

require('./config.php');
require('./lib/ressource.lib.php');


llxHeader('','Vérification des Véhicules');

print dol_get_fiche_head(array()  , '', 'Vérification');
$plagedeb = !empty($_REQUEST['plagedebut']) ? $_REQUEST['plagedebut'] : date("d/m/Y",time()-31532400);
$plagefin = !empty($_REQUEST['plagefin']) ? $_REQUEST['plagefin'] : date("d/m/Y", time()+31532400);

$url ='http://'.$_SERVER['SERVER_NAME']. DOL_URL_ROOT_ALT."/ressource/script/loadContratLimite.php?plagedebut=".$plagedeb."&plagefin=".$plagefin;
//echo $url.'<br>';
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
			'plagedebut'=>$form->calendrier('', 'plagedebut', $plagedeb, 8)
			,'plagefin'=>$form->calendrier('', 'plagefin', $plagefin, 8)
			,'valider'=>$form->btsubmit('Valider', 'valider')
		)
	)	
	
);

$form->end();
llxFooter();