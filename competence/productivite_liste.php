<?php

	require('config.php');
	require('./class/productivite.class.php');
	
	$langs->load('formulaire@formulaire');
	
	$ATMdb=new TPDOdb;
	$productivite=new TRH_productivite;
	
	_liste($ATMdb, $productivite);

	function _liste(&$ATMdb, $productivite) {
		global $langs, $conf, $db, $user;	
		llxHeader('','Liste des indices de productivité');
		
		////////////AFFICHAGE DES LIGNES DE REMUNERATION
		$r = new TSSRenderControler($productivite);
		$sql = 'SELECT rowid as "ID", indice as "Libellé", DATE_FORMAT(date_objectif, "%d-%m-%Y") as "Date objectif"';
		$sql.=" FROM ".MAIN_DB_PREFIX."rh_productivite";
		
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
				'ID'=>'<a href="'.dol_buildpath("/competence/productivite.php?id=@ID@&action=view", 2).'">@val@</a>'
				//,'Supprimer'=>$user->rights->curriculumvitae->myactions->ajoutRemuneration?'<a href="?id=@ID@&action=delete&fk_user='.$fuser->id.'"><img src="./img/delete.png"></a>':''
				//,'Supprimer'=>$user->rights->curriculumvitae->myactions->ajoutRemuneration?"<a onclick=\"if (window.confirm('Voulez vous supprimer l\'élément ?')){document.location.href='?fk_user=@fk_user@&id=@ID@&action=delete'}\"><img src=\"./img/delete.png\"></a>":''
			)
			,'translate'=>array(
				
			)
			,'hide'=>array('DateCre', 'fk_user')
			,'type'=>array()
			,'liste'=>array(
				'titre'=>'Visualisation des indices de productivité'
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
				'label'=>'Type poste'
				,'date'=>'Numero convention'
				,'indice'=>'Descriptif'
			)
			,'search'=>array(
			)
			,'orderBy'=>$TOrder
			
		));
			if($user->rights->curriculumvitae->myactions->ajoutRemuneration==1){
			?>
			<a class="butAction" href="productivite.php?action=new">Ajouter un nouvel indice</a><div style="clear:both"></div>
			
			<?
			}
	
	
		$form->end();
		
		llxFooter();
	}