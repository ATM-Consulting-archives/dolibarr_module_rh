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
		
		if ($_REQUEST["sendit"])
		{		
			if (dol_mkdir($upload_dir) >= 0)
			{
				
				$resupload = dol_move_uploaded_file($_FILES['userfile']['tmp_name'], $upload_dir . "/" . $_FILES['userfile']['name'],0,0,$_FILES['userfile']['error']);
				
		        if (is_numeric($resupload) && $resupload > 0)
				{
					$message = $langs->trans("FileTransferComplete");
		            $error = false;
				}
				else
				{
					$langs->load("errors");
		
					if ($resupload < 0)	// Unknown error
					{
						$message = $langs->trans("ErrorFileNotUploaded");
					}
					else if (preg_match('/ErrorFileIsInfectedWithAVirus/',$resupload))	// Files infected by a virus
					{
						$message = $langs->trans("ErrorFileIsInfectedWithAVirus");
					}
					else	// Known error
					{
						$message = $langs->trans($resupload);
					}
				}
			}
		
		}
		elseif ($_REQUEST["linkit"]){
	        $link = GETPOST('link', 'alpha');
	        if ($link)
	        {
	            if (substr($link, 0, 7) != 'http://' && substr($link, 0, 8) != 'https://') {
	                $link = 'http://' . $link;
	            }
	            dol_add_file_process($upload_dir, 0, 1, 'regle', null, $link);
	        }
		}
		
		/*if ($action == 'delete')
		{
			$langs->load("companies");	// Need for string DeleteFile+ConfirmDeleteFiles
			$ret = $html->form_confirm(
					$_SERVER["PHP_SELF"] . '?id=0&urlfile=' . urlencode(GETPOST("urlfile")) . '&linkid=' . GETPOST('linkid', 'int') . (empty($param)?'':$param),
					$langs->trans('DeleteFile'),
					$langs->trans('ConfirmDeleteFile'),
					'confirm_deletefile',
					'',
					0,
					1
			);
			if ($ret == 'html') print '<br>';
		}*/
		
		// Delete
		/*if ($action == 'confirm_deletefile' && $confirm == 'yes')
		
			$upload_dir = DOL_DATA_ROOT.'/regle';
		
			$file = $upload_dir . '/' . $_REQUEST['urlfile'];
			dol_delete_file( $file, 0, 0, 0, 'FILE_DELETE', $absence);
		
			$message = $langs->trans("FileHasBeenRemoved");
		}*/
		
		// Get all files
		$sortfield  = GETPOST("sortfield", 'alpha');
		$sortorder  = GETPOST("sortorder", 'alpha');
		$page       = GETPOST("page", 'int');
		
		if ($page == -1)
		{
		    $page = 0;
		}
		
		$offset = $conf->liste_limit * $page;
		$pageprev = $page - 1;
		$pagenext = $page + 1;
		
		if (!$sortorder) $sortorder = "ASC";
		if (!$sortfield) $sortfield = "name";
		
		
		$upload_dir = DOL_DATA_ROOT.'/regle';
		
		$filearray = dol_dir_list($upload_dir, "all", 0, '', '\.meta$', $sortfield, (strtolower($sortorder) == 'desc' ? SORT_DESC : SORT_ASC), 1);
		$totalsize = 0;
		foreach($filearray as $key => $file)
		{
			$totalsize += $file['size'];
		}
		
		/*if ($action == 'delete')
		{
			$formconfirm = $html->formconfirm($_SERVER["PHP_SELF"].'?urlfile='.urldecode($_REQUEST['urlfile']), $langs->trans('DeleteFile'), $langs->trans('ConfirmDeleteFile'), 'confirm_deletefile', '', 0, 0);
		}*/
		
		$can_upload = 1;
		
		echo dol_get_fiche_head(reglePrepareHead($absence, 'import', $absence), 'fiche', 'Fichiers joints');
		
		echo ($message ? dol_htmloutput_mesg($message, '', ($error ? 'error' : 'ok'), 0) : '');

		echo ($formconfirm ? $formconfirm : '');
		
		$absence->id = 0;
		$absence->element = "regle";
		
		if($user->rights->absence->myactions->uploadFilesRegle){
			$formfile->form_attach_new_file($_SERVER["PHP_SELF"], '', '', 0, $can_upload,50,$absence);
			$formfile->list_of_documents($filearray, $absence, 'absence', '',0,'regle/',1);
			//List of links
			$formfile->listOfLinks($absence, 1, $action, GETPOST('linkid', 'int'), $param);
		}else{
			$formfile->list_of_documents($filearray, $absence, 'absence', '',0,'regle/',0);
			//List of links
			$formfile->listOfLinks($absence, 1, $action, GETPOST('linkid', 'int'), $param);
		}
		
		dol_fiche_end();
		llxFooter();
		
		$db->close();
	}