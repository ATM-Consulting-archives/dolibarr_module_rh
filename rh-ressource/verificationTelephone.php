<?php
	/**
	 * Ce script vérifie la consommation des cartes TOTAL : à savoir si l'utilisation de la carte est abusive 
	 */ 
	require('./config.php');
	require('./class/evenement.class.php');
	require('./class/ressource.class.php');
	require('./lib/ressource.lib.php');
	
	global $conf;
	$ATMdb=new TPDOdb;
	
	$mesg = '';
	$error=false;
	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				_fiche($ATMdb, 'new');
				break;
			case 'view':
				_fiche($ATMdb, 'view');
				break;
			case 'save':
				$date_debut=$_REQUEST['date_debut'];
				$date_fin=$_REQUEST['date_fin'];
				_genererRapport($ATMdb, $date_debut, $date_fin, 'view');
				break;
		}
	}else{
		 _fiche($ATMdb, 'view');
	}
	
	$ATMdb->close();
	llxFooter();
	
	
function _fiche(&$ATMdb, $mode) {
	global $db, $user, $langs, $conf;
	
	llxHeader('','Vérification des consommations téléphonique');
	print dol_get_fiche_head(array()  , '', 'Vérification');
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/verificationTelephone.tpl.php'
		,array(
		)
		,array(
			'infos'=>array(
				'titre'=>load_fiche_titre("Vérification des consommations téléphoniques",'', 'title.png', 0, '')
				,'date_debut'=>$form->calendrier('Date de début', 'date_debut', $date_debut, 10)
				,'date_fin'=>$form->calendrier('Date de fin', 'date_fin', $date_fin, 10)
				,'action'=>$form->hidden('action','save')
			)
			,'view'=>array(
				'mode'=>$mode
			)
		)	
		
	);
	
	
	llxFooter();
}

function _genererRapport(&$ATMdb, $date_debut, $date_fin, $mode) {
	global $db, $user, $langs, $conf;
	
	llxHeader('','Vérification des consommations téléphonique');
	print dol_get_fiche_head(array()  , '', 'Vérification');
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff('new');
	
	$TTelephone = array();
	
	$ATMdb->Execute($sql);
	$k=0;
	while($ATMdb->Get_line()) {
		$TTelephone[$k][0] = $ATMdb->Get_field('Societe');
		$TTelephone[$k][1] = $ATMdb->Get_field('Utilisateur');
		$TTelephone[$k][2] = $ATMdb->Get_field('Type');
		$TTelephone[$k][3] = $ATMdb->Get_field('ChoixForfait');
		$TTelephone[$k][4] = intToString($ATMdb->Get_field('Forfait'));
		$TTelephone[$k][5] = intToString($ATMdb->Get_field('ForfaitExterne'));
		$TTelephone[$k][6] = intToString($ATMdb->Get_field('ForfaitExterne'));
		$TTelephone[$k][7] = ($ATMdb->Get_field('DepassementForfait')<0) ? '00:00' : $ATMdb->Get_field('DepassementForfait');
		$TTelephone[$k][8] = ($ATMdb->Get_field('DepassementForfaitInterne')<0) ? '00:00' : $ATMdb->Get_field('DepassementForfaitInterne');
		$TTelephone[$k][9] = ($ATMdb->Get_field('DepassementForfaitExterne')<0) ? '00:00' : $ATMdb->Get_field('DepassementForfaitExterne');
		$TTelephone[$k][10] = $ATMdb->Get_field('Option');
		$k++;
	}
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/verificationTelephone.tpl.php'
		,array(
			'tabTel'=>$TTelephone
		)
		,array(
			'infos'=>array(
				'date_debut'=>$form->calendrier('Date de début', 'date_debut', $date_debut, 10)
				,'date_fin'=>$form->calendrier('Date de fin', 'date_fin', $date_fin, 10)
				,'action'=>$form->hidden('action','save')
			)
			,'view'=>array(
				'mode'=>$mode
			)
		)	
		
	);
	$form->end();
	
}
