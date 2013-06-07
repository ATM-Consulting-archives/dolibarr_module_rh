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
				_fiche($ATMdb,  'new');
				break;
			case 'view':
				_fiche($ATMdb, 'view');
				break;
			case 'save':
				//$date_debut=$_REQUEST['date_debut'];
				//$date_fin=$_REQUEST['date_fin'];
				$date_debut = $_REQUEST['date_debut'];
				$date_debut = mktime(0,0,0,substr($date_debut, 3,2),substr($date_debut, 0,2), substr($date_debut, 6,4));
				$date_fin = $_REQUEST['date_fin'];
				$date_fin = mktime(0,0,0,substr($date_fin, 3,2),substr($date_fin, 0,2), substr($date_fin, 6,4));
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
	echo $form->hidden('action', 'save');
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/verificationTelephone.tpl.php'
		,array(
		)
		,array(
			'infos'=>array(
				'titre'=>load_fiche_titre("Vérification des consommations téléphoniques",'', 'title.png', 0, '')
				,'date_debut'=>$form->calendrier('Date de début', 'date_debut', time()-3600*24*31*12, 12)
				,'date_fin'=>$form->calendrier('Date de fin', 'date_fin', time(), 12)
				//,'action'=>$form->hidden('action','save')
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
	echo $form->hidden('action', 'save');
	
	$TUser = array();
	$TRowidUser = array();
	$sql="SELECT rowid, name, firstname, login FROM ".MAIN_DB_PREFIX."user WHERE entity IN (0,".$conf->entity.")";
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TUser[strtolower($ATMdb->Get_field('firstname').' '.$ATMdb->Get_field('name'))] = $ATMdb->Get_field('rowid');
		$TRowidUser[] = $ATMdb->Get_field('rowid');		
	}
	
	$TGroups= array();
	$sql="SELECT fk_user, fk_usergroup FROM ".MAIN_DB_PREFIX."usergroup_user WHERE entity IN (0,".$conf->entity.")";
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TGroups[$ATMdb->Get_field('fk_usergroup')][] = $ATMdb->Get_field('fk_user');
	}
	
	$TLimites = load_limites_telephone($ATMdb, $TGroups, $TRowidUser);
	
	//echo '<br><br><br>';
	/*foreach ($TLimites as $key => $value) {
		echo $key.' ';	
		print_r($value);
		echo '<br>';
	}*/
	
	
	
	$sql="SELECT dureeI, dureeE, duree, u.rowid as 'idUser', name, firstname
	FROM ".MAIN_DB_PREFIX."rh_evenement as e
	LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (u.rowid=e.fk_user)
	LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as c ON (c.fk_object = e.fk_user)
	WHERE e.type='factTel' 
	AND (e.date_debut<='".date("Y-m-d", $date_fin)."' AND e.date_debut>='".date("Y-m-d", $date_debut)."')";
	
	echo $sql;
	
	$TTelephone = array();
	$ATMdb->Execute($sql);
	$k=0;

	
	while($row = $ATMdb->Get_line()) {
		$lim = $TLimites[$row->idUser]['lim']/60;
		$dep = intToString($row->duree/60);
		$limI = $TLimites[$row->idUser]['limInterne']/60;
		$depI = intToString($row->dureeI/60);
		$limE = $TLimites[$row->idUser]['limExterne']/60;
		$depE = intToString($row->dureeE/60);
	
		$TTelephone[$k][0] = 'Orange';
		$TTelephone[$k][1] = htmlentities($row->firstname.' '.$row->name, ENT_COMPAT , 'ISO8859-1');
		$TTelephone[$k][2] = 'tel';//$ATMdb->Get_field('Type');
		$TTelephone[$k][3] = ($lim != 0) ? 'extint' : 'gen';//$ATMdb->Get_field('ChoixForfait');
		$TTelephone[$k][4] = intToString($lim);
		$TTelephone[$k][5] = intToString($limI);
		$TTelephone[$k][6] = intToString($limE);
		$TTelephone[$k][7] = ($dep<0) ? '00:00' : $dep;
		$TTelephone[$k][8] = ($depI<0) ? '00:00' : $depI;
		$TTelephone[$k][9] = ($depE<0) ? '00:00' : $depE;
		$TTelephone[$k][10] = 'lol';//$ATMdb->Get_field('Option');
		$k++;
	}
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/verificationTelephone.tpl.php'
		,array(
			'tabTel'=>$TTelephone
		)
		,array(
			'infos'=>array(
				'titre'=>load_fiche_titre("Vérification des consommations téléphoniques",'', 'title.png', 0, '')
				,'date_debut'=>$form->calendrier('Date de début', 'date_debut', $date_debut, 12)
				,'date_fin'=>$form->calendrier('Date de fin', 'date_fin', $date_fin, 12)
				//,'action'=>$form->hidden('action','save')
			)
			,'view'=>array(
				'mode'=>$mode
			)
		)	
		
	);
	$form->end();
	
}
