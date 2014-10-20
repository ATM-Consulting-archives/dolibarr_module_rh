<?php

	ini_set('memory_limit', '-1');
	set_time_limit(0);

	require('config.php');
	
	dol_include_once("societe/class/societe.class.php");
	dol_include_once("contact/class/contact.class.php");
	dol_include_once("cliacticontrole/admin/cliacticontrole.class.php");
	dol_include_once('/core/lib/company.lib.php');
	dol_include_once('/core/class/html.formfile.class.php');
	
	global $db, $langs;
	
	$langs->load('competence@competence');
	
	$action = $_REQUEST['actionATM'];
	//print_r($_FILES);exit;
	switch ($action) {
		case 'import':
			
			_afficheHead($db, $langs);
			
			print _returnFormImportDonneesProductivite();
			
			break;
		
		default:
			
			_afficheHead($db, $langs);
			print _returnFormImportDonneesProductivite();
			
			break;
	}
	
	function _afficheHead(&$db, $langs) {
	
		llxHeader('',$langs->trans('UploadLots'),'','');

		dol_fiche_head($head, 'pointage', $langs->trans('Données de Pointage'), 0, 'pointage');
		
		$form = new Form($db);
		
		print '<br />';
		
		$title = "Sélectionnez un fichier";
		print_titre($title);
		
	}
	
	function _returnFormImportDonneesProductivite() {
		
		$formFile.= '<form enctype="multipart/form-data" action="" method="post">';
		$formFile.= '<input type="hidden" name="actionATM" value="import" />';
		$formFile.= '<input type="file" name="monfichier" />';
		$formFile.= '<input class="button" type="submit" value="Importer données" />';
		$formFile.= '</form>';

		return $formFile;
		
	}
	