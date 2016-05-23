<?php
	require('config.php');
	set_time_limit(0);
	ini_set("memory_limit", "128M");

	dol_include_once('/ressource/class/ressource.class.php');
	dol_include_once('/ressource//class/evenement.class.php');
	dol_include_once('/ressource//class/contrat.class.php');
	dol_include_once('/ressource//lib/ressource.lib.php');
	
	dol_include_once("/core/class/html.form.class.php");
	dol_include_once("/core/class/html.formfile.class.php");
	dol_include_once("/core/class/fileupload.class.php");
	dol_include_once("/core/lib/functions2.lib.php");
	
	$langs->load('ressource@ressource');
	$langs->load('main');
	$langs->load('other');
	
	$ATMdb=new TPDOdb;
	$ressource = new TRH_Ressource;
	
	_fiche($ATMdb, $ressource);
	
	$ATMdb->close();
	llxFooter();
	
	function _fiche(&$ATMdb, &$ressource) {
		global $db,$user,$conf,$langs,$mysoc;
		llxHeader('','Fichiers joints');
		$upload_dir = DOL_DATA_ROOT.'ressource/import_fournisseurs/';
		
		$confirm = $_REQUEST['confirm'];
		$action = $_REQUEST['action'];
		
		$error = false;
		$message = false;
		$formconfirm = false;
		
		$html = new Form($db);
		$formfile = new FormFile($db);
		
		if ($_REQUEST["sendit"])
		{
					
			dol_mkdir($upload_dir);
			
				
				$resupload = dol_move_uploaded_file($_FILES['userfile']['tmp_name'], $upload_dir . "/" . $_FILES['userfile']['name'],1,0,$_FILES['userfile']['error']);
				
		        if (is_numeric($resupload) && $resupload > 0)
				{
					
					$nomFichier= $upload_dir . "/" . $_FILES['userfile']['name'];
					include("./script/".$_REQUEST["typeImport"]);
					
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
		
		// Delete
		if ($action == 'confirm_deletefile' && $confirm == 'yes')
		{
		
			$file = $upload_dir . '/' . $_REQUEST['urlfile'];
			dol_delete_file( $file, 0, 0, 0, 'FILE_DELETE', $object);
		
			$message = $langs->trans("FileHasBeenRemoved");
		}
		
		//Suppression d'un import
		if($action == 'delimport'){
			if(!empty($_REQUEST['idImport'])){
				$ATMdb->Execute('DELETE FROM '.MAIN_DB_PREFIX.'rh_evenement WHERE idImport = "'.$_REQUEST['idImport'].'" AND idImport != "" AND idImport IS NOT NULL');
			}
		}
		
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
		
				
		$filearray = dol_dir_list($upload_dir, "files", 0, '', '\.meta$', $sortfield, (strtolower($sortorder) == 'desc' ? SORT_DESC : SORT_ASC), 1);
		$totalsize = 0;
		foreach($filearray as $key => $file)
		{
			$totalsize += $file['size'];
		}
		
		if ($action == 'delete')
		{
			$formconfirm = $html->formconfirm($_SERVER["PHP_SELF"].'?urlfile='.urldecode($_REQUEST['urlfile']), $langs->trans('DeleteFile'), $langs->trans('ConfirmDeleteFile'), 'confirm_deletefile', '', 0, 0);
		}
		
		$can_upload = 1;
		
		echo dol_get_fiche_head(ressourcePrepareHead($ressource, 'import', $ressource), 'fiche', 'Import fournisseurs');
		
		echo ($message ? dol_htmloutput_mesg($message, '', ($error ? 'error' : 'ok'), 0) : '');

		echo ($formconfirm ? $formconfirm : '');
		
		$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
		
		//listes des entités
		$liste_entities = array();
		$sql="SELECT rowid,label FROM ".MAIN_DB_PREFIX."entity WHERE 1";
		$ATMdb->Execute($sql);
		while($ATMdb->Get_line()) {
			$liste_entities[$ATMdb->Get_field('rowid')] = htmlentities($ATMdb->Get_field('label'), ENT_COMPAT , 'ISO8859-1');

		}
		
		if(empty($liste_entities)) {
			$liste_entities[$conf->entity] = $mysoc->name;
		}
		
		$liste_types_imports=array('ImportFactureTotal.php' => 'Total'
									,'ImportFactureArea.php' => 'Area'
									,'ImportFactureEuromaster.php' => 'Euromaster'
									,'ImportFactureParcours.php' => 'Parcours'
									,'ImportFactureOrange.php' => 'Orange');
		
		$TBS=new TTemplateTBS();
		$select_types_imports = $TBS->render('./tpl/documentSupplier.tpl.php'
			,array()
			,array(
				'import'=>array(
					'typeImport'=>$form->combo('','typeImport',$liste_types_imports,'')
					,'entity'=>$form->combo('','entity',$liste_entities,$conf->entity)
				)
			)	
			
		);
		$select_types_imports = preg_replace("/(\r\n|\n|\r)/", " ", $select_types_imports);
		$select_types_imports = preg_replace("/'/", "\"", $select_types_imports);
		
		echo $form->end_form();
		
		$formfile->form_attach_new_file($_SERVER["PHP_SELF"], '', 0, 0, $can_upload,80,'','',0,'',0,'formDocSupplier');
		$formfile->list_of_documents($filearray, $ressource, 'ressource', '',0,'import_fournisseurs/',1);

		?>
		<script>
			$(document).ready(function(){
				$("form#formDocSupplier").children().children().children().children().prepend('<? print $select_types_imports; ?>');
				$("form#formDocSupplier").submit(function() {
					
					$(this).hide();
					$(this).after('Chargement de votre document en cours, merci de patienter...');
					
					return true;
				});
			});
		</script>
		<br>
		<div class="titre">Liste des imports déjà effectués</div>
		<?php
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
		
		$sql = "SELECT e.idImport as idImport, count(*) as 'Nombre de lignes', e.rowid as ID, e.date_cre, ent.label as 'entity', '' as Action 
		
			FROM ".MAIN_DB_PREFIX."rh_evenement e LEFT JOIN ".MAIN_DB_PREFIX."entity ent ON (e.entity=ent.rowid)
				WHERE e.idImport IS NOT NULL AND e.idImport != ''
				GROUP BY e.idImport
				ORDER BY e.date_cre DESC";
		
		$l=new TListviewTBS('listImports');
		
		print $l->render($ATMdb, $sql, array(
			'limit'=>array(
				'page'=>$page
				,'nbLine'=>'15'
			)
			,'link'=>array(
				'Action' => '<a href="?idImport=@idImport@&action=delimport" onclick="return confirm(\'Voulez-vous vraiment supprimer tous les événements importés depuis le fichier @idImport@ ?\');"><img border="0" title="Supprimer" alt="Supprimer" src="'.DOL_URL_ROOT.'/theme/rh/img/delete.png"></a>'
			)
			,'title'=>array(
				'idImport' => 'Intitulé du fichier importé'
				,'date_cre'=> 'Importé le'
				,'entity'=>'Entité'
			)
			,'type'=>array(
				'date_cre'=>'date'
			)
			,'hide'=>array('ID')
			,'liste'=>array(
				'titre' => 'Liste des Imports'
				,'image'=>img_picto('','title.png', '', 0)
				,'picto_precedent'=>img_picto('','previous.png', '', 0)
				,'picto_suivant'=>img_picto('','next.png', '', 0)
				,'messageNothing'=>"Il n'y a aucun imports à afficher"
				,'order_down'=>img_picto('','1downarrow.png', '', 0)
				,'order_up'=>img_picto('','1uparrow.png', '', 0)
			)
		));
		
		
		?>
		
		<div style="clear:both"></div></div><?
		
		dol_fiche_end();
		llxFooter();
		
		$db->close();
	}
