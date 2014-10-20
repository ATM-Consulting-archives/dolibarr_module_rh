<?php
	require('config.php');
	require('./class/productivite.class.php');
	
	require_once DOL_DOCUMENT_ROOT.'/core/lib/usergroups.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
	dol_include_once('/competence/lib/competence.lib.php');
	
	$langs->load('formulaire@formulaire');
	
	$ATMdb=new TPDOdb;
	$productivite_indice = new TRH_productiviteIndice;
	
	if(isset($_REQUEST['action'])) {
		
		switch($_REQUEST['action']) {
			
			case 'save':
				
				$productivite_indice->load($ATMdb, $_REQUEST['id']);
				
				$productivite_indice->set_values($_REQUEST);
				
				$mesg = '<div class="ok">Indice de productivité enregistré avec succès</div>';
				
				$productivite_indice->save($ATMdb);
				$productivite_indice->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $productivite_indice, 'view');
				break;
			
			case 'delete':
				$productivite_indice->load($ATMdb, $_REQUEST['id']);
				$productivite_indice->delete($ATMdb, $_REQUEST['id']);
				
				?>
					<script>
					
						document.location.href="<?php echo dol_buildpath("/competence/productivite_user_fiche.php?action=view&id=".$_REQUEST['fk_productivite']."&fk_user=".$_REQUEST['fk_user'], 2) ?>"
					
					</script>
				<?php

				break;
			
			case 'view':
				$productivite_indice->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $productivite_indice, 'view');
				break;
			
			case 'edit':
				$productivite_indice->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $productivite_indice);
				break;
			
			default:
				_fiche($ATMdb, $productivite_indice);
				break;
			
		}
		
	}
	
	function _fiche(&$ATMdb, $productivite_indice, $mode="edit") {
		
		global $db,$user,$langs,$conf;
		llxHeader('','Données de productivité');
		
		$fuser = new User($db);
		$fuser->fetch($_REQUEST['fk_user']);
		$fuser->getrights();
		
		$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
		$form->Set_typeaff($mode);
		
		echo $form->hidden('id', $productivite_indice->getId());
		echo $form->hidden('action', 'save');
		echo $form->hidden('fk_user', $fuser->id);
		echo $form->hidden('fk_productivite', $_REQUEST['fk_productivite']);

		$TBS=new TTemplateTBS();

		print $TBS->render('./tpl/productivite_user_indice.tpl.php'
			,array()
			,array(
				'user'=>array(
					'id'=>$fuser->id
					,'lastname'=>$fuser->lastname
					,'firstname'=>$fuser->firstname
				)
				,'productivite_indice'=>array(
					'id'=>$productivite_indice->getId()
					,'date_indice'=>$form->calendrier('', 'date_indice', $productivite_indice->date_indice, 12)
					,'fk_productivite'=>$_REQUEST['fk_productivite']
					//,'date_objectif'=>$form->calendrier('', 'date_objectif', $productivite_indice->date_objectif, 12)
					,'indice'=>$form->texteRO('', 'indice', _getLibIndice($_REQUEST['fk_productivite']), 20,255)
					,'chiffre_realise'=>$form->texte('', 'chiffre_realise', $productivite_indice->chiffre_realise, 20,255,'','','à saisir')
					//,'label'=>$form->texte('', 'label', $productivite_indice->label, 20,255,'','','à saisir')
					//,'supprimable'=>$form->hidden('supprimable', 1)
				)
				,'view'=>array(
					'mode'=>$mode
					,'action'=>$_REQUEST['action']
					,'head'=>dol_get_fiche_head(competencePrepareHead($productivite_indice, 'chiffre_user'),'fiche','Chiffre utilisateur')
					,'onglet'=>dol_get_fiche_head(array(),'','Edition chiffre réalisé')
				)
				
			)	
			
		);
		
	}

	function _getLibIndice($id_productivite) {
		
		global $db;
		
		$sql = "SELECT indice FROM ".MAIN_DB_PREFIX."rh_productivite_user WHERE rowid = ".$id_productivite;
		$resql = $db->query($sql);
		
		if($resql) {
			while($res = $db->fetch_object($resql)) {
				return $res->indice;
			}
		}
		
	}
	