<?php
	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	require_once(DOL_DOCUMENT_ROOT."/core/class/html.form.class.php");
	require_once(DOL_DOCUMENT_ROOT."/core/class/html.formfile.class.php");
	require_once(DOL_DOCUMENT_ROOT."/core/class/fileupload.class.php");
	require_once(DOL_DOCUMENT_ROOT."/core/lib/functions2.lib.php");
	
	$langs->load('absence@absence');
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
		llxHeader('', $langs->trans('Documents'));
		
		$id = GETPOST('id', 'int');
		$ref = GETPOST('ref', 'alpha');
		$action=GETPOST('action','alpha');
		$confirm=GETPOST('confirm','alpha');
		$sortfield = GETPOST("sortfield",'alpha');
		$sortorder = GETPOST("sortorder",'alpha');
		
		$error = false;
		$message = false;
		$formconfirm = false;
		
		$form = new Form($db);
		$formfile = new FormFile($db);
		
		$upload_dir = DOL_DATA_ROOT.'/regle';
		
		
		$absence->id = 0;
		$absence->element = "regle";
		$object = $absence;
		
		$modulepart = 'absence';

		$permission  = $user->rights->absence->myactions->uploadFilesRegle;
		$param = '&id=' . $object->id;
		
		// Construit liste des fichiers
		$filearray=dol_dir_list($upload_dir,"files",0,'','\.meta$',$sortfield,(strtolower($sortorder)=='desc'?SORT_DESC:SORT_ASC),1);
		$totalsize=0;
		foreach($filearray as $key => $file)
		{
			$totalsize+=$file['size'];
		}
		
		include_once DOL_DOCUMENT_ROOT . '/core/tpl/document_actions_post_headers.tpl.php';
		
		dol_fiche_end();
		llxFooter();
		
		$db->close();
	}