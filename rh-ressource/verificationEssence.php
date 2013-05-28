<?php
/**
 * Ce script vérifie la consommation des cartes TOTAL : à savoir si l'utilisation de la carte est abusive 
 */ 

require('./config.php');
require('./lib/ressource.lib.php');
global $user;

llxHeader('','Vérification des consommations d\'essence');

print dol_get_fiche_head(array()  , '', 'Vérification');
$limite = isset($_REQUEST['limite']) ? $_REQUEST['limite'] : 0;
$plagedeb = !empty($_REQUEST['plagedebut']) ? $_REQUEST['plagedebut'] : date("d/m/Y",time()-31532400);
$plagefin = !empty($_REQUEST['plagefin']) ? $_REQUEST['plagefin'] : date("d/m/Y", time());
$fk_user = !empty($_REQUEST['fk_user']) ? $_REQUEST['fk_user'] : 0 ;

$url ='http://'.$_SERVER['SERVER_NAME']. DOL_URL_ROOT_ALT."/ressource/script/loadConsommationEssence.php?limite=".$limite."&plagedebut=".$plagedeb."&plagefin=".$plagefin."&fk_user=".$fk_user;
$result = file_get_contents($url);
$TRessource = unserialize($result);
	
$TUser = getUsers();

$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
$form->Set_typeaff('edit');
$TBS=new TTemplateTBS();
print $TBS->render('./tpl/verificationEssence.tpl.php'
	,array(
		'ressource'=>$TRessource
	)
	,array(
		'infos'=>array(
			'texte'=>'$texte'
			,'titre'=>load_fiche_titre("Vérification des consommations d'essence",'', 'title.png', 0, '')
			,'limite'=>$form->texte('', 'limite', $limite, 10)
			,'plagedebut'=>$form->calendrier('', 'plagedebut', $plagedeb, 8)
			,'plagefin'=>$form->calendrier('', 'plagefin', $plagefin, 8)
			,'fk_user'=>$form->combo('', 'fk_user', $TUser, $fk_user)
			,'valider'=>$form->btsubmit('Valider', 'valider')
			)
	)	
);
$form->end();

llxFooter();
