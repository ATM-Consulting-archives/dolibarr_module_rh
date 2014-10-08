<?php

	require('config.php');
	require('./class/type_poste.class.php');
	
	$langs->load('formulaire@formulaire');
	
	$ATMdb=new TPDOdb;
	$fiche_poste=new TRH_fichePoste;
	
	_liste($ATMdb, $fiche_poste);

	function _liste(&$ATMdb, $fiche_poste) {
		global $langs, $conf, $db, $user;	
		llxHeader('','Liste des types de postes');
		
		$fuser = new User($db);
		$fuser->fetch($_REQUEST['fk_user']);
		$fuser->getrights();
		
		////////////AFFICHAGE DES LIGNES DE REMUNERATION
		$r = new TSSRenderControler($fiche_poste);
		$sql = "SELECT rowid as 'ID', type_poste as 'Type poste', numero_convention as 'Numero convention', descriptif as 'Descriptif'";
		$sql.=" FROM ".MAIN_DB_PREFIX."rh_fiche_poste";
		
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
				'ID'=>'<a href="'.dol_buildpath("/formulaire/fiche_type_poste.php?id=@ID@&action=view", 2).'">@val@</a>'
				,'Type poste'=>'<a href="'.dol_buildpath("/formulaire/fiche_type_poste.php?id=@ID@&action=view", 2).'">@val@</a>'
				//,'Supprimer'=>$user->rights->curriculumvitae->myactions->ajoutRemuneration?'<a href="?id=@ID@&action=delete&fk_user='.$fuser->id.'"><img src="./img/delete.png"></a>':''
				//,'Supprimer'=>$user->rights->curriculumvitae->myactions->ajoutRemuneration?"<a onclick=\"if (window.confirm('Voulez vous supprimer l\'élément ?')){document.location.href='?fk_user=@fk_user@&id=@ID@&action=delete'}\"><img src=\"./img/delete.png\"></a>":''
			)
			,'translate'=>array(
				
			)
			,'hide'=>array('DateCre', 'fk_user')
			,'type'=>array()
			,'liste'=>array(
				'titre'=>'Visualisation des types de postes'
				,'image'=>img_picto('','title.png', '', 0)
				,'picto_precedent'=>img_picto('','back.png', '', 0)
				,'picto_suivant'=>img_picto('','next.png', '', 0)
				,'noheader'=> (int)isset($_REQUEST['socid'])
				,'messageNothing'=>"Aucun type de poste"
				,'order_down'=>img_picto('','1downarrow.png', '', 0)
				,'order_up'=>img_picto('','1uparrow.png', '', 0)
				,'picto_search'=>'<img src="../../theme/rh/img/search.png">'
			)
			,'title'=>array(
				'type_poste'=>'Type poste'
				,'numero_convention'=>'Numero convention'
				,'descriptif'=>'Descriptif'
			)
			,'search'=>array(
			)
			,'orderBy'=>$TOrder
			
		));
			if($user->rights->curriculumvitae->myactions->ajoutRemuneration==1){
			?>
			<a class="butAction" href="fiche_type_poste.php?action=new">Ajouter un type de poste</a><div style="clear:both"></div>
			
			<?
			}
	
	
		$form->end();
		
		llxFooter();
	}