<?php
	require('config.php');
	require('./class/ressource.class.php');
	
	$langs->load('ressource@ressource');
	
	//if (!$user->rights->financement->affaire->read)	{ accessforbidden(); }
	$ATMdb=new Tdb;
	$ressource=new TRH_ressource_type;
	
	$mesg = '';
	$error=false;
	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				
				$ressource->set_values($_REQUEST);
	
				//$ressource->save($ATMdb);
				_fiche($ATMdb, $ressource,'edit');
				
				break;	
			case 'edit'	:
				$ressource->load($ATMdb, $_REQUEST['id']);
				
				_fiche($ATMdb, $ressource,'edit');
				break;
				
			case 'save':
				$ressource->load($ATMdb, $_REQUEST['id']);
				$ressource->set_values($_REQUEST);
				
				$ATMdb->db->debug=true;
				//print_r($_REQUEST);
				
				$ressource->save($ATMdb);
				
				_fiche($ATMdb, $ressource,'view');
				
				break;
			
				
			case 'delete':
				$ressource->load($ATMdb, $_REQUEST['id']);
				//$ATMdb->db->debug=true;
				$ressource->delete($ATMdb);
				
				?>
				<script language="javascript">
					document.location.href="?delete_ok=1";					
				</script>
				<?
				
				
				break;
		}
	}
	elseif(isset($_REQUEST['id'])) {
		$ressource->load($ATMdb, $_REQUEST['id']);
		
		
		//_liste_fields($ATMdb, $ressource);
		_fiche($ATMdb, $ressource, 'view');
		
	}
	else {
		/*
		 * Liste
		 */
		 _liste($ATMdb, $ressource);
	}
	
	
	$ATMdb->close();
	
	llxFooter();
	
function _liste(&$ATMdb, &$ressource) {
	global $langs,$conf, $db;	
	
	llxHeader('','Type Ressource');
	getStandartJS();
	
	$r = new TSSRenderControler($ressource);
	$sql="SELECT rowid as 'ID', code as 'Code', libelle as 'Libellé'
		FROM @table@
		WHERE entity=".$conf->entity;
	
	$TOrder = array('Code'=>'ASC');
	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
				
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;			
	//print $page;
	$r->liste($ATMdb, $sql, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>'30'
		)
		,'link'=>array(
			'Code'=>'<a href="?id=@ID@">@val@</a>'
		)
		,'translate'=>array()
		,'hide'=>array()
		,'type'=>array()
		,'liste'=>array(
			'titre'=>'Liste des types de ressources'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Il n'y a aucun type de ressource à afficher"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			
		)
		,'orderBy'=>$TOrder
		
	));
	
	
	llxFooter();
}	
	
function _fiche(&$ATMdb, &$ressource, $mode) {
	global $db,$user;

	llxHeader('','Type de ressource');
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	
	
	
	$TFields=array();
	
	echo $form->hidden('id', $ressource->getId());
	echo $form->hidden('action', 'save');
	
	$TBS=new TTemplateTBS();
	
	print $TBS->render('./tpl/ressource.type.tpl.php'
		,array(
			'ressourceField'=>$TFields
		)
		,array(
			'ressourceType'=>array(
				'id'=>$ressource->getId()
				,'code'=>$form->texte('', 'code', $ressource->code, 30,255,'','','à saisir')
				,'libelle'=>$form->texte('', 'libelle', $ressource->libelle, 100,255,'','','à saisir') 
				,'date_maj'=>$ressource->get_date('date_maj','d/m/Y à H:i:s')
				,'date_cre'=>$ressource->get_date('date_cre','d/m/Y')
			)
			,'view'=>array(
				'mode'=>$mode
			/*	,'userRight'=>((int)$user->rights->financement->affaire->write)*/
			)
			
		)
	);
	
	/*
	$sql="SELECT rowid as 'IDField', code as 'Code', libelle as 'Libellé', 
				obligatoire as 'Obligatoire', fk_rh_ressource_type
		FROM llx_rh_ressource_field ";
	$TOrder = array('Code'=>'ASC');
	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
	
	print $TBS->render('./tpl/ressource.type.tpl.php'
		,array(
			'ressourceField'=>$TFields
		)
		,array(
			'ressourceType'=>array(
				'id'=>$ressource->getId()
				,'code'=>$form->texte('', 'code', $ressource->code, 30,255,'','','à saisir')
				,'libelle'=>$form->texte('', 'libelle', $ressource->libelle, 100,255,'','','à saisir') 
				,'date_maj'=>$ressource->get_date('date_maj','d/m/Y à H:i:s')
				,'date_cre'=>$ressource->get_date('date_cre','d/m/Y')
			)
			,'view'=>array(
				'mode'=>$mode
			/*	,'userRight'=>((int)$user->rights->financement->affaire->write)
			)
			
		)
	);
		*/
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

	/*
function _liste_fields(&$ATMdb, &$ressource) {
	global $langs,$conf, $db;	
	
	llxHeader('','Champs de la ressource');
	getStandartJS();
	
	$r = new TSSRenderControler($ressource);
	$sql="SELECT rowid as 'IDField', code as 'Code', libelle as 'Libellé', 
				obligatoire as 'Obligatoire', fk_rh_ressource_type
		FROM llx_rh_ressource_field ";
		//WHERE fk_rh_ressource_type=".$ressource->getId();
	
	$TOrder = array('Code'=>'ASC');
	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
				
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;			
	//print $page;
	$r->liste($ATMdb, $sql, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>'30'
		)
		,'link'=>array()
		,'translate'=>array()
		,'hide'=>array()
		,'type'=>array()
		,'liste'=>array(
			'titre'=>'Liste des champs'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Il n'y a aucun champs à afficher"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			
		)
		,'orderBy'=>$TOrder
		
	));
	
	
	llxFooter();
}	

//*/
	
