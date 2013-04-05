<?php
	require('config.php');
	require('./class/ressource.class.php');
	require('./lib/ressource.lib.php');
	
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
				//$ATMdb->db->debug=true;
				$ressource->set_values($_REQUEST);
				//$ressource->save($ATMdb);
				_fiche($ATMdb, $ressource,'edit');
				
				break;	
			case 'edit'	:
				//$ATMdb->db->debug=true;
				$ressource->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $ressource,'edit');
				break;
				
			case 'save':
				//$ATMdb->db->debug=true;
				$ressource->load($ATMdb, $_REQUEST['id']);
				$ressource->set_values($_REQUEST);
				$mesg = '<div class="ok">Modifications effectuées</div>';
				$mode = 'view';
				if(isset($_REQUEST['TField'])){
					if (!empty($ressource->TField)){
						foreach($_REQUEST['TField'] as $k=>$field) {
							$ressource->TField[$k]->set_values($field);					
						}
					}
				}
				
				if ($_REQUEST['TNField']['libelle']!=''){
					$ressource->addField($ATMdb, $_REQUEST['TNField']);
					$mesg = '<div class="ok">Le champs a bien été créé</div>';
				}
				if(isset($_REQUEST['newField'])) {
					$mode = 'edit';
				}

				
				$ressource->save($ATMdb);
				$ressource->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $ressource,$mode);
				break;
			
			case 'view':
				$ressource->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $ressource,'view');
				break;
		
			case 'deleteField':
				//$ATMdb->db->debug=true;
				if ($ressource->delField($ATMdb, $_REQUEST['idField'])){
					$mesg = '<div class="ok">Le champ a bien été supprimé</div>';
				}
				else {
					$mesg = '<div class="error">Ce champ ne peut pas être supprimé</div>';
				}
				$ressource->load($ATMdb, $_REQUEST['id']);
				
				
				$mode = 'edit';
				_fiche($ATMdb, $ressource,$mode);
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
		 _liste($ATMdb, $ressource);
	}
	
	
	$ATMdb->close();
	
	
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
			'Code'=>'<a href="?id=@ID@&action=view">@val@</a>'
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


	llxHeader('','Type de ressource', '', '', 0, 0);
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $ressource->getId());
	echo $form->hidden('action', 'save');
	
	
	
	//Champs
	$TFields=array();
	foreach($ressource->TField as $k=>$field) {
		
		//print_r($field);
		
		$TFields[$k]=array(
				'id'=>$field->getId()
				,'code'=>$form->texte('', 'TField['.$k.'][code]', $field->code, 20,255,'','','-')
				,'libelle'=>$form->texte('', 'TField['.$k.'][libelle]', $field->libelle, 20,255,'','','-')
				,'indice'=>$k
				,'ordre'=>$form->hidden('TField['.$k.'][ordre]', $k, 'class="ordre'.$k.'"')
				,'type'=>$form->combo('','TField['.$k.'][type]',$ressource->TType,$field->type)
				,'options'=>$form->texte('', 'TField['.$k.'][options]', $field->options, 20,255,'','','-')
				,'obligatoire'=>$form->combo('','TField['.$k.'][obligatoire]',array('Oui','Non'),$field->obligatoire)
				,'numero'=>$k
			);
	}
	
	$TBS=new TTemplateTBS();
	
	print $TBS->render('./tpl/ressource.type.field.tpl.php'
		,array(
			'ressourceField'=>$TFields
		)
		,array(
			'ressourceType'=>array(
				'id'=>$ressource->getId()
				,'code'=> $ressource->code
				,'libelle'=> $ressource->libelle
			)
			,'newField'=>array(
				//'hidden'=>$form->hidden('action', 'save')
				'code'=>$form->texte('', 'TNField[code]', '', 20,255)
				,'ordre'=>$form->hidden('TNField[ordre]', $k+1, 'class="ordre'.($k+1).'"')
				,'indice'=>$k+1
				,'libelle'=>$form->texte('', 'TNField[libelle]', '', 20,255,'','','-')
				,'type'=>$form->combo('', 'TNField[type]',$ressource->TType, 'texte')
				,'options'=>$form->texte('', 'TNField[options]', '', 20,255,'','','-')
				,'obligatoire'=>$form->combo('','TNField[obligatoire]',array('Oui','Non'),'0')
			
			)
			,'view'=>array(
				'mode'=>$mode
				,'nbChamps'=>count($ressource->TField)
			/*	,'userRight'=>((int)$user->rights->financement->affaire->write)*/
				,'head'=>dol_get_fiche_head(ressourcePrepareHead($ressource)  , 'field', 'Type de ressource')
			)
			
		)	
		
	);
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

	
	
