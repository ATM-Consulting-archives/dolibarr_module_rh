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
				$ATMdb->db->debug=true;
				$ressource->load($ATMdb, $_REQUEST['id']);
				/*print_r($ressource);	
				print '<hr>';
				 * Alexis, pense au bug classe objet standart dans set_values sur Tableau
				 * */
				$ressource->set_values($_REQUEST);
				
				foreach($_REQUEST['TField'] as $k=>$field) {
					/*print_r($ressource);*/	
					$ressource->TField[$k]->set_values($field);					
				}
				
				if(isset($_REQUEST['newField']) && !empty($_REQUEST['TNField']['code'])) {
					
					$ressource->addField($_REQUEST['TNField']);
					
				}
				
				
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
	$form->Set_typeaff('edit');//$mode);
	echo $form->hidden('id', $ressource->getId());
	echo $form->hidden('action', 'save');
	
	
	//Champs
	$TFields=array();
	foreach($ressource->TField as $k=>$field) {
		//echo $field->getId().' - '.$field->obligatoire.'<br>';
		//print_r($field);
		
		$TFields[$k]=array(
				'id'=>$field->getId()
				,'code'=>$form->texte('', 'TField['.$k.'][code]', $field->code, 30,255,'','','-')
				,'libelle'=>$form->texte('', 'TField['.$k.'][libelle]', $field->libelle, 50,255,'','','-')
				,'type'=>$form->texte('', 'TField['.$k.'][type]', $field->type, 50,255,'','','-')
									 //checkbox1($pLib,$pName,$pVal,$checked=false,$plus='',$class='',$id='',$order='case_after'){
				,'obligatoire'=>$form->checkbox1('', 'TField['.$k.'][obligatoire]', 1)
			);
		
	}
	
	$TBS=new TTemplateTBS();
	//$TBS->TBS->protect=false;
	
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
			,'newField'=>array(
				'hidden'=>$form->hidden('action', 'save')
				,'code'=>$form->texte('', 'TNField[code]', '', 30,255,'','','-')
				,'libelle'=>$form->texte('', 'TNField[libelle]', '', 50,255,'','','-')
				,'type'=>$form->texte('', 'TNField[type]', '', 50,255,'','','-')
				,'obligatoire'=>$form->checkbox1('','TNField[obligatoire]',1,true)
				//$form->texte('', 'obligatoire', $field->obligatoire, 100,255,'','','à saisir')
				//		texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default=''){
			
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

	
	
