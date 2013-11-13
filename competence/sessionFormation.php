<?php
	require('config.php');
	require('./class/formation.class.php');
	$langs->load('competence@competence');
	
	$ATMdb=new Tdb;
	$sessionFormation = new TRH_formation_session;
	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
				_liste($ATMdb);
				break;
				
			case 'new':
				$sessionFormation->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb,$sessionFormation);
				break;	
				
			case 'edit':
				$sessionFormation->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb,$sessionFormation);
				break;
				
			case 'save':
				$sessionFormation->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb,$sessionFormation);
				break;
			
			case 'view':
				$sessionFormation->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb,$sessionFormation);
				break;
				
			case 'delete':
				_liste($ATMdb);
				break;
		}
	}
	elseif(isset($_REQUEST['id'])) {
		$sessionFormation->load($ATMdb, $_REQUEST['id']);
		_fiche($ATMdb,$sessionFormation);
	}
	else {
		_liste($ATMdb);
	}
	
	$ATMdb->close();
	llxFooter();
	

function _liste(&$ATMdb) {
	global $langs,$conf,$db,$user;
	
	llxHeader('','Liste Sessions de Formations');
	print dol_get_fiche_head(array()  , '', 'Liste Sessions de Formations');
	
	llxFooter();
}

function _fiche(&$ATMdb,&$sessionFormation) {
	global $langs,$conf,$db,$user;
	
	llxHeader('', 'Session de Formation');
	print dol_get_fiche_head(array()  , '', 'Session de Formation');
	
	llxFooter();
}