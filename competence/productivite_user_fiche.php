<?php
	require('config.php');
	require('./class/productivite.class.php');
	
	require_once DOL_DOCUMENT_ROOT.'/core/lib/usergroups.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
	dol_include_once('/competence/lib/competence.lib.php');
	
	$langs->load('formulaire@formulaire');
	
	$ATMdb=new TPDOdb;
	$productivite_user = new TRH_productiviteUser;
	
	if(isset($_REQUEST['action'])) {
		
		switch($_REQUEST['action']) {
			
			case 'save':
				
				$productivite_user->load($ATMdb, $_REQUEST['id']);
				$productivite_user->set_values($_REQUEST);
				
				$productivite_user->save($ATMdb);
				$productivite_user->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $productivite_user, 'view');
				break;
			
			case 'delete':
				$productivite_user->load($ATMdb, $_REQUEST['id']);
				$productivite_user->delete($ATMdb, $_REQUEST['id']);
				
				$productivite_user->save($ATMdb);
				?>
					<script>
						document.location.href='productivite_user.php?action=view&fk_user=<?php echo $_REQUEST['fk_user'] ?>';
					</script>
				<?php
				break;
			
			case 'view':
				$productivite_user->load($ATMdb, $_REQUEST['id']);
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
		
		$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
		$form->Set_typeaff($mode);
		
		echo $form->hidden('id', $productivite_user->getId());
		echo $form->hidden('action', 'save');
		echo $form->hidden('fk_user', $fuser->id);

		$TBS=new TTemplateTBS();

		print $TBS->render('./tpl/productivite_user_fiche.tpl.php'
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
					,'indice'=>$form->texteRO('', 'indice', $productivite_user->indice, 20,"")
					,'objectif'=>$form->texte('', 'objectif', $productivite_user->objectif, 20,255,'','','à saisir')
					//,'supprimable'=>$form->hidden('supprimable', 1)
				)
				,'view'=>array(
					'mode'=>$mode
					,'action'=>$_REQUEST['action']
					,'head'=>dol_get_fiche_head(competencePrepareHead($productivite_user, 'productivite_user'),'fiche','Productivité utilisateur')
					,'onglet'=>dol_get_fiche_head(array(),'','Edition indice de productivité utilisateur')
				)
				
			)	
			
		);
		if($_REQUEST['action'] === 'view')
			_listeChiffresUser($ATMdb, $productivite_user);
		
	}

	function _listeChiffresUser(&$ATMdb, $productivite_user) {
		global $langs, $conf, $db, $user;	
		
		$fuser = new User($db);
		$fuser->fetch($_REQUEST['fk_user']);
		$fuser->getrights();
		
		////////////AFFICHAGE DES LIGNES DE REMUNERATION
		$r = new TSSRenderControler($productivite_user);
		$sql = "SELECT rowid as 'ID', chiffre_realise as 'Chiffre réalisé', DATE_FORMAT(date_indice, \"%d-%m-%Y\") as 'Date'";
		$sql.= 'FROM '.MAIN_DB_PREFIX.'rh_productivite_indice ';
		$sql.= 'WHERE fk_user = '.$_REQUEST['fk_user'];
		$sql.= ' AND fk_productivite = '.$productivite_user->fk_productivite;

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
				'ID'=>'<a href="'.dol_buildpath("/competence/productivite_user_indice.php?id=@ID@&action=view&fk_productivite=".$_REQUEST['id']."&fk_user=".$_REQUEST['fk_user'], 2).'">@val@</a>'
				//,'Supprimer'=>$user->rights->curriculumvitae->myactions->ajoutRemuneration?'<a href="?id=@ID@&action=delete&fk_user='.$fuser->id.'"><img src="./img/delete.png"></a>':''
				//,'Supprimer'=>$user->rights->curriculumvitae->myactions->ajoutRemuneration?"<a onclick=\"if (window.confirm('Voulez vous supprimer l\'élément ?')){document.location.href='?fk_user=@fk_user@&id=@ID@&action=delete'}\"><img src=\"./img/delete.png\"></a>":''
			)
			,'translate'=>array(
				
			)
			,'hide'=>array('DateCre', 'fk_user')
			,'type'=>array()
			,'liste'=>array(
				'titre'=>'Chiffres réalisés par l\'utilisateur'
				,'image'=>img_picto('','title.png', '', 0)
				,'picto_precedent'=>img_picto('','back.png', '', 0)
				,'picto_suivant'=>img_picto('','next.png', '', 0)
				,'noheader'=> (int)isset($_REQUEST['socid'])
				,'messageNothing'=>"Aucun chiffre"
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
	
		$form->end();
		
		?>
			<div class="tabsAction">
				<a class="butAction" href="productivite_user_indice.php?action=new&fk_user=<?php echo $fuser->id; ?>&fk_productivite=<?php echo $_REQUEST['id']; ?>">Ajouter un chiffre</a><div style="clear:both"></div>
			</div>
		<?
		
		llxFooter();
	}
