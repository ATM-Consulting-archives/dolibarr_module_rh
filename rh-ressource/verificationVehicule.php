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


$idVoiture = getIdType('voiture');

//chargement des voitures
$TVoitures = getRessource($idVoiture);
$sql = "SELECT rowid, fk_soc,  immatriculation , marquevoit, modlevoit
	FROM ".MAIN_DB_PREFIX."rh_ressource` 
	WHERE entity=".$conf->entity."
	AND fk_rh_ressource_type =".$idVoiture;
$ATMdb->Execute($sql);
while($row = $ATMdb->Get_line()) {
	$TVoitures[$row->rowid] = array(
		'societe'=>$row->fk_soc
		,'fk_user'=>27786//$row->fk_user
		,'immatriculation'=>$row->immatriculation
		,'marque'=>$row->marquevoit
		,'version'=>$row->modlevoit
		);
}

print_r($TVoitures);


$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
$form->Set_typeaff('edit');
	
$TBS=new TTemplateTBS();
print $TBS->render('./tpl/verificationVehicule.tpl.php'
	,array()
	,array(
		'infos'=>array(
			'date_debut'=>$form->calendrier('', 'date_debut', '', 12)
			,'date_fin'=>$form->calendrier('', 'date_fin', '', 12)
			)
	)	
	
);


$form->end();
llxFooter();