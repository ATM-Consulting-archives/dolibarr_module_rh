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
			
			case 'delete':
				$fiche_poste->load($ATMdb, $_REQUEST['id']);
				$fiche_poste->delete($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">Type de poste enregistré avec succès</div>';
				
				$fiche_poste->save($ATMdb);
				?>
					<script>
						document.location.href='liste_types_postes.php';
					</script>
				<?php
				break;
			
			case 'view':
				$fiche_poste->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $fiche_poste, 'view');
				break;
			
			case 'edit':
				$fiche_poste->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $fiche_poste);
				break;
			
			default:
				_fiche($ATMdb, $fiche_poste);
				break;
			
		}
		
	}
	
	function _fiche(&$ATMdb, $fiche_poste, $mode="edit") {
		
		global $db,$user,$langs,$conf,$grille_salaire;
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
		
		$grille_salaire = new TRH_grilleSalaire;
		$grille_salaire->fk_type_poste = $fiche_poste->getId();
		if($mode === 'view') _listeGrillesSalaire($ATMdb, $grille_salaire);
		
	}

	function _listeGrillesSalaire(&$ATMdb, $grille_salaire) {
		global $langs, $conf, $db, $user;	
		
		$fuser = new User($db);
		$fuser->fetch($_REQUEST['fk_user']);
		$fuser->getrights();
		
		////////////AFFICHAGE DES LIGNES DE REMUNERATION
		$r = new TSSRenderControler($grille_salaire);
		$sql = "SELECT rowid as 'ID', nb_annees_anciennete as 'Années d\'ancienneté', montant as 'Montant'";
		$sql.= " FROM ".MAIN_DB_PREFIX."rh_grille_salaire";
		$sql.= " WHERE fk_type_poste = ".$grille_salaire->fk_type_poste;
		
		$TOrder = array('rowid'=>'ASC');
		if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
		if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
					
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;			
		$form=new TFormCore($_SERVER['PHP_SELF'],'formtranslateList','GET');
		
		$r->liste($ATMdb, $sql, array(
			'limit'=>array(
				'page'=>$page
				,'nbLine'=>'30'
			)
			,'link'=>array(
				//'Rémunération brute annuelle'=>'<a href="?id=@ID@&action=view&fk_user='.$fuser->id.'">@val@</a>'
				'ID'=>'<a href="'.dol_buildpath("/formulaire/grille_salaire.php?id=@ID@&action=view", 2).'">@val@</a>'
				//,'Supprimer'=>$user->rights->curriculumvitae->myactions->ajoutRemuneration?'<a href="?id=@ID@&action=delete&fk_user='.$fuser->id.'"><img src="./img/delete.png"></a>':''
				//,'Supprimer'=>$user->rights->curriculumvitae->myactions->ajoutRemuneration?"<a onclick=\"if (window.confirm('Voulez vous supprimer l\'élément ?')){document.location.href='?fk_user=@fk_user@&id=@ID@&action=delete'}\"><img src=\"./img/delete.png\"></a>":''
			)
			,'translate'=>array(
				
			)
			,'hide'=>array('DateCre', 'fk_user')
			,'type'=>array()
			,'liste'=>array(
				'titre'=>'Visualisation des grilles de salaire'
				,'image'=>img_picto('','title.png', '', 0)
				,'picto_precedent'=>img_picto('','back.png', '', 0)
				,'picto_suivant'=>img_picto('','next.png', '', 0)
				,'noheader'=> (int)isset($_REQUEST['socid'])
				,'messageNothing'=>"Aucune grille de salaire"
				,'order_down'=>img_picto('','1downarrow.png', '', 0)
				,'order_up'=>img_picto('','1uparrow.png', '', 0)
				,'picto_search'=>'<img src="../../theme/rh/img/search.png">'
			)
			,'title'=>array(
				'nb_annees_anciennete'=>"Années d'ancienneté"
				,'montant'=>'Montant'
			)
			,'search'=>array(
			)
			,'orderBy'=>$TOrder
			
		));
			if($user->rights->curriculumvitae->myactions->ajoutRemuneration==1){
			?>
			<a class="butAction" href="grille_salaire.php?action=new&fk_type_poste=<?php echo $_REQUEST['id'] ?>">Ajouter une grille de salaire</a><div style="clear:both"></div>
			
			<?
			}
	
	
		$form->end();
		
		llxFooter();
	}