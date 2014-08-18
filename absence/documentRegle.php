<?php
	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	require_once(DOL_DOCUMENT_ROOT."/core/class/html.form.class.php");
	require_once(DOL_DOCUMENT_ROOT."/core/class/html.formfile.class.php");
	require_once(DOL_DOCUMENT_ROOT."/core/class/fileupload.class.php");
	require_once(DOL_DOCUMENT_ROOT."/core/lib/functions2.lib.php");
	
	$langs->load('ressource@ressource');
	$langs->load('main');
	$langs->load('other');
	
	$ATMdb=new TPDOdb;
	$absence=new TRH_Absence;
	
	_fiche($ATMdb, $absence);
	
	$ATMdb->close();
	llxFooter();
	
	function _fiche(&$ATMdb, &$absence) {
		global $db,$user,$conf,$langs;
		llxHeader('','Fichiers joints');
		
		$confirm = $_REQUEST['confirm'];
		$action = $_REQUEST['action'];
		
		$error = false;
		$message = false;
		$formconfirm = false;
		
		$html = new Form($db);
		$formfile = new FormFile($db);
		
		$upload_dir = DOL_DATA_ROOT.'/regle';

		$absence->id = 0;
		$absence->element = "regle";
		
		include_once DOL_DOCUMENT_ROOT . '/core/tpl/document_actions_post_headers.tpl.php';
		
		dol_fiche_end();
		llxFooter();
		
		$db->close();
	}