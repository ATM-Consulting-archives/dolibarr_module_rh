<?php
/**
 * Ce script vérifie la consommation des cartes TOTAL : à savoir si l'utilisation de la carte est abusive 
 */ 

require('./config.php');
require('./lib/ressource.lib.php');


llxHeader('','Vérification des consommations d\'essence');

print dol_get_fiche_head(array()  , '', 'Vérification');
$limite = isset($_REQUEST['limite']) ? $_REQUEST['limite'] : 0;

$url ='http://'.$_SERVER['SERVER_NAME']. DOL_URL_ROOT_ALT."/ressource/script/loadConsommationEssence.php?limite=".$limite;
$result = file_get_contents($url);
$TRessource = unserialize($result);
	


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
			,'limite'=>$form->texte('', 'limite', $limite, 10) //'<input class="text" type="text" id="limite" name="limite" value="" size="5" maxlength="5" >'//$form->texte('', 'limite', '', 5)
			,'valider'=>$form->btsubmit('Valider', 'valider')
			)
	)	
);
$form->end();

llxFooter();
