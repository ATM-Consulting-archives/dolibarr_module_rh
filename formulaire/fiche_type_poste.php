<?php
	require('config.php');
	require('./class/type_poste.class.php');
	
	require_once DOL_DOCUMENT_ROOT.'/core/lib/usergroups.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
	
	$langs->load('formulaire@formulaire');
	
	$ATMdb=new TPDOdb;
	$fiche_poste=new TRH_fichePoste;
	
	if(isset($_REQUEST['action'])) {
		
		switch($_REQUEST['action']) {
			
			case 'save':
				$fiche_poste->load($ATMdb, $_REQUEST['id']);
				$fiche_poste->set_values($_REQUEST);
				
				$mesg = '<div class="ok">Type de poste enregistré avec succès</div>';
				
				$fiche_poste->save($ATMdb);
				$fiche_poste->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $fiche_poste, 'view');
				break;
			
			case 'view':
				$fiche_poste->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $fiche_poste, 'view');
				break;
			
			default:
				_fiche($ATMdb, $fiche_poste);
				break;
			
		}
		
	}
	
	function _fiche(&$ATMdb, $fiche_poste, $mode="edit") {
		
		global $db,$user,$langs,$conf;
		llxHeader('','Types de postes');
		
		$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
		$form->Set_typeaff($mode);
		
		echo $form->hidden('id', $fiche_poste->getId());
		echo $form->hidden('action', 'save');

		$TBS=new TTemplateTBS();
		
		print $TBS->render('./tpl/fiche_poste.tpl.php'
			,array()
			,array(
				'fiche_poste'=>array(
					'id'=>$fiche_poste->getId()
					,'type_poste'=>$form->texte('', 'type_poste', $fiche_poste->type_poste, 20,255,'','','à saisir')
					,'numero_convention'=>$form->texte('', 'numero_convention', $fiche_poste->numero_convention, 20,255,'','','à saisir')
					,'descriptif'=>$form->texte('', 'descriptif', $fiche_poste->descriptif, 20,255,'','','à saisir') 
					//,'supprimable'=>$form->hidden('supprimable', 1)
				)
				,'view'=>array(
					'mode'=>$mode
					,'head'=>dol_get_fiche_head(array()  , '', 'Création d\'un type de poste')
				)
				
			)	
			
		);
		
	}
