<?php
/**
 * Ce script vérifie la consommation des cartes TOTAL : à savoir si l'utilisation de la carte est abusive 
 */ 

require('./config.php');
require('./lib/ressource.lib.php');


llxHeader('','Vérification des consommations d\'essence');

print dol_get_fiche_head(array()  , '', 'Vérification');

$TBS=new TTemplateTBS();
print $TBS->render('./tpl/verificationEssence.tpl.php'
	,array()
	,array(
		'infos'=>array(
			'texte'=>'$texte'
			,'limite'=>'<input class="text" type="text" id="limite" name="limite" value="" size="5" maxlength="5" >'//$form->texte('', 'limite', '', 5)
			)
	)	
	
);


llxFooter();
