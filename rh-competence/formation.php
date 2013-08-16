<?php
	require('config.php');
	require('./class/formation.class.php');
	$langs->load('competence@competence');
	
	$ATMdb=new Tdb;
	$formation = new TRH_formation;
	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
				_liste($ATMdb);
				break;
				
			case 'new':
				$formation->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb,$formation);
				break;	
				
			case 'edit':
				$formation->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb,$formation);
				break;
				
			case 'save':
				$formation->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb,$formation);
				break;
			
			case 'view':
				$formation->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb,$formation);
				break;
				
			case 'delete':
				_liste($ATMdb);
				break;
		}
	}
	elseif(isset($_REQUEST['id'])) {
		$formation->load($ATMdb, $_REQUEST['id']);
		_fiche($ATMdb,$formation);
	}
	else {
		_liste($ATMdb);
	}
	
	$ATMdb->close();
	llxFooter();
	

function _liste(&$ATMdb) {
	global $langs,$conf,$db,$user;
	
	llxHeader('','Liste des Formations');
	print dol_get_fiche_head(array()  , '', 'Liste des Formations');
	
	llxFooter();
}

function _fiche(&$ATMdb,&$formation) {
	global $langs,$conf,$db,$user;
	
	llxHeader('', 'Formation');
	print dol_get_fiche_head(array()  , '', 'Formation');
	
	llxFooter();
}