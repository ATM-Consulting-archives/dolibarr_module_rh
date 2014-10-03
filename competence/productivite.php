<?php
	require('config.php');
	require('./class/productivite.class.php');
	
	require_once DOL_DOCUMENT_ROOT.'/core/lib/usergroups.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
	
	$langs->load('formulaire@formulaire');
	
	$ATMdb=new TPDOdb;
	$productivite_user = new TRH_productiviteUser;
	
	if(isset($_REQUEST['action'])) {
		
		switch($_REQUEST['action']) {
			
			case 'save':
				
				$productivite_user->load($ATMdb, $_REQUEST['id']);
				$productivite_user->set_values($_REQUEST);
				
				$mesg = '<div class="ok">Grille de salaire enregistrée avec succès</div>';
				
				$productivite_user->save($ATMdb);
				$productivite_user->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $productivite_user, 'view');
				break;
			
			case 'delete':
				$productivite_user->load($ATMdb, $_REQUEST['id']);
				$productivite_user->delete($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">Grille de salaire enregistrée avec succès</div>';
				
				$productivite_user->save($ATMdb);
				?>
					<script>
						document.location.href='fiche_type_poste.php?id=<?php echo $_REQUEST['fk_type_poste'] ?>&action=view';
					</script>
				<?php
				break;
			
			case 'view':
				$productivite_user->loadBy($ATMdb, $_REQUEST['fk_user'], 'fk_user');
				_fiche($ATMdb, $productivite_user, 'view');
				break;
			
			case 'edit':
				$productivite_user->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $productivite_user);
				break;
			
			default:
				_fiche($ATMdb, $productivite_user);
				break;
			
		}
		
	}
	
	function _fiche(&$ATMdb, $productivite_user, $mode="edit") {
		
		global $db,$user,$langs,$conf;
		llxHeader('','Données de productivité');
		
		$fuser = new User($db);
		$fuser->fetch($_REQUEST['fk_user']);
		$fuser->getrights();
		
		$head = user_prepare_head($fuser);
		$current_head = 'productivite';
		dol_fiche_head($head, $current_head, $langs->trans('Utilisateur'),0, 'user');
		
		$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
		$form->Set_typeaff($mode);
		
		echo $form->hidden('id', $productivite_user->getId());
		echo $form->hidden('action', 'save');
		echo $form->hidden('fk_user', $fuser->id);

		$TBS=new TTemplateTBS();

		print $TBS->render('./tpl/productivite.tpl.php'
			,array()
			,array(
				'user'=>array(
					'id'=>$fuser->id
					,'lastname'=>$fuser->lastname
					,'firstname'=>$fuser->firstname
				)
				,'productivite_user'=>array(
					'id'=>$productivite_user->getId()
					,'date_objectif'=>$form->calendrier('', 'date_objectif', $productivite_user->date_objectif, 12)
					,'indice'=>$form->texte('', 'indice', $productivite_user->indice, 20,255,'','','à saisir')
					,'objectif'=>$form->texte('', 'objectif', $productivite_user->objectif, 20,255,'','','à saisir')
					//,'supprimable'=>$form->hidden('supprimable', 1)
				)
				,'view'=>array(
					'mode'=>$mode
					,'action'=>$_REQUEST['action']
				)
				
			)	
			
		);
		
	}