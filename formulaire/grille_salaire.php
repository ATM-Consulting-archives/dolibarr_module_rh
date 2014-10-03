<?php
	require('config.php');
	require('./class/type_poste.class.php');
	
	require_once DOL_DOCUMENT_ROOT.'/core/lib/usergroups.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
	
	$langs->load('formulaire@formulaire');
	
	$ATMdb=new TPDOdb;
	$grille_salaire = new TRH_grilleSalaire;
	
	if(isset($_REQUEST['action'])) {
		
		switch($_REQUEST['action']) {
			
			case 'save':
				$grille_salaire->load($ATMdb, $_REQUEST['id']);
				$grille_salaire->set_values($_REQUEST);
				
				$mesg = '<div class="ok">Grille de salaire enregistrée avec succès</div>';
				
				$grille_salaire->save($ATMdb);
				$grille_salaire->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $grille_salaire, 'view');
				break;
			
			case 'delete':
				$grille_salaire->load($ATMdb, $_REQUEST['id']);
				$grille_salaire->delete($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">Grille de salaire enregistrée avec succès</div>';
				
				$grille_salaire->save($ATMdb);
				?>
					<script>
						document.location.href='fiche_type_poste.php?id=<?php echo $_REQUEST['fk_type_poste'] ?>&action=view';
					</script>
				<?php
				break;
			
			case 'view':
				$grille_salaire->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $grille_salaire, 'view');
				break;
			
			case 'edit':
				$grille_salaire->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $grille_salaire);
				break;
			
			default:
				_fiche($ATMdb, $grille_salaire);
				break;
			
		}
		
	}
	
	function _fiche(&$ATMdb, $grille_salaire, $mode="edit") {
		
		global $db,$user,$langs,$conf;
		llxHeader('','Types de postes');
		
		$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
		$form->Set_typeaff($mode);
		
		echo $form->hidden('id', $grille_salaire->getId());
		echo $form->hidden('action', 'save');
		echo $form->hidden('fk_type_poste', $_REQUEST['fk_type_poste']);

		$TBS=new TTemplateTBS();
		
		print $TBS->render('./tpl/grille_salaire.tpl.php'
			,array()
			,array(
				'grille_salaire'=>array(
					'id'=>$grille_salaire->getId()
					,'nb_annees_anciennete'=>$form->texte('', 'nb_annees_anciennete', $grille_salaire->nb_annees_anciennete, 20,255,'','','à saisir')
					,'montant'=>$form->texte('', 'montant', $grille_salaire->montant, 20,255,'','','à saisir')
					,'id_fiche_poste'=>$grille_salaire->fk_type_poste
					//,'supprimable'=>$form->hidden('supprimable', 1)
				)
				,'view'=>array(
					'mode'=>$mode
					,'head'=>dol_get_fiche_head(array()  , '', 'Création d\'un type de poste')
				)
				
			)	
			
		);
		
	}