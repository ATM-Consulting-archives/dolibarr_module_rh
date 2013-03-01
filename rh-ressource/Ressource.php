<?php
	require('config.php');
	require('./class/ressource.class.php');
	
	$langs->load('ressource@ressource');
	
	//if (!$user->rights->financement->affaire->read)	{ accessforbidden(); }
	$ATMdb=new Tdb;
	$ressource=new TRH_ressource;
	
	$mesg = '';
	$error=false;
	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				
				$ressource->set_values($_REQUEST);
				//$ressource->load($ATMdb, 20);
				$mesg = '<div class="ok">Nouvelle ressource créée</div>';
				_fiche($ATMdb, $ressource,'edit');
				
				break;	
			case 'edit'	:
				
				$ressource->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $ressource,'edit');
				break;
				
			case 'save':
				//$ATMdb->db->debug=true;
				$ressource->load($ATMdb, $_REQUEST['id']);
				$ressource->set_values($_REQUEST);
				$ressource->save($ATMdb);
				$ressource->load($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">Modifications effectuées</div>';
				_fiche($ATMdb, $ressource,'view');
				break;
			
			case 'view':
				$ressource->load($ATMdb, $_REQUEST['id']);
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
		_fiche($ATMdb, $ressource, 'view');
	}
	else {
		/*
		 * Liste
		 */
		 //$ATMdb->db->debug=true;
		 _liste($ATMdb, $ressource);
	}
	
	
	$ATMdb->close();
	
	llxFooter();
	
function _liste(&$ATMdb, &$ressource) {
	global $langs,$conf, $db;	
	llxHeader('','Liste des ressources');
	getStandartJS();
	
	$r = new TSSRenderControler($ressource);
	$sql="SELECT r.rowid as 'ID', r.date_cre as 'DateCre',r.libelle as 'Libellé', t.libelle as 'Type',  r.bail as 'Bail', r.statut as 'Statut'
		FROM llx_rh_ressource as r, llx_rh_ressource_type as t 
		WHERE r.entity=".$conf->entity."
		AND r.fk_rh_ressource_type=t.rowid
		";
	
	$TOrder = array('DateCre'=>'ASC');
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
			'Libellé'=>'<a href="?id=@ID@&action=view">@val@</a>'
		)
		,'translate'=>array()
		,'hide'=>array('DateCre')
		,'type'=>array()
		,'liste'=>array(
			'titre'=>'Liste des ressources'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Il n'y a aucune ressource à afficher"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			
		)
		,'orderBy'=>$TOrder
		
	));
	
	
	llxFooter();
}	
	
function _fiche(&$ATMdb, &$ressource, $mode) {
	global $db,$user;
	llxHeader('', 'Ressource', '', '', 0, 0, array('/hierarchie/js/jquery.jOrgChart.js'));

	//llxHeader('','Ressource');
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $ressource->getId());
	echo $form->hidden('action', 'save');
	
	//Ressources
	$TFields=array();
	
	foreach($ressource->ressourceType->TField as $k=>$field) {
		//echo $field->getId().' - '.$field->obligatoire.'<br>';
		//print_r($field);
		
		//echo $field->code;
		$TFields[$k]=array(
				//'id'=>$field->getId()
				'libelle'=>$field->libelle//$form->texte('', 'TFields['.$k.'][libelle]', $field->libelle, 50,255,'','','-')
				,'valeur'=>$form->texte('', $field->code, $ressource->{$field->code}, 50,255,'','','-')
				/*,'type'=>$form->combo('','TRessource['.$k.'][type]',$ressource->type->TType,$field->type)
				,'bail'=>$form->combo('','TFields['.$k.'][bail]',$ressource->TBail,$ressource->TBail[0])
				,'statut'=>$form->combo('','TFields['.$k.'][statut]',$ressource->TStatut,$ressource->TStatut[0])*/
			);
	}

		//requete pour avoir toutes les ressources associées à la ressource concernées
		$k=0;
		$sqlReq="SELECT libelle FROM `llx_rh_ressource` where fk_rh_ressource=".$ressource->rowid;
		$ATMdb->Execute($sqlReq);
		$Tab=array();
		$Tab_sous_ressource=array();
		while($ATMdb->Get_line()) {
			//récupère les id des différents nom des  groupes de l'utilisateur
			$Tab_sous_ressource[$k]=array('libelle'=>$ATMdb->Get_field('libelle'));
			$k++;
		}
		

	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/ressource.tpl.php'
		,array(
			'ressourceField'=>$TFields
			,'sous_ressource'=>$Tab_sous_ressource
		)
		,array(
			'ressource'=>array(
				'id'=>$ressource->getId()
				,'libelle'=>$form->texte('', 'libelle', $ressource->libelle, 50,255,'','','-')
				,'type'=>$form->combo('','fk_rh_ressource_type',$ressource->TType,$ressource->fk_rh_ressource_type)
				,'bail'=>$form->combo('','bail',$ressource->TBail,$ressource->TBail[0])
				,'statut'=>$form->combo('','statut',$ressource->TStatut,$ressource->TStatut[0])
			
			)
			,'fk_ressource'=>array(
				'liste_fk_rh_ressource'=>$form->combo('','fk_rh_ressource',$ressource->TRessource,$ressource->fk_rh_ressource)
				,'fk_rh_ressource'=>$ressource->fk_rh_ressource ? $ressource->TRessource[$ressource->fk_rh_ressource] : "aucune ressource"
			)
			
			,'view'=>array(
				'mode'=>$mode
			/*	,'userRight'=>((int)$user->rights->financement->affaire->write)*/
			)
			
			
		)	
		
	);
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

	
	
