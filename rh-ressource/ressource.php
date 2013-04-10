<?php
	require('config.php');
	require('./class/ressource.class.php');
	require('./class/contrat.class.php');
	require('./class/evenement.class.php');
	require('./lib/ressource.lib.php');
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
				_choixType($ATMdb, $ressource,'new');
				break;	
			case 'edit'	:
				//$ATMdb->db->debug=true;
				$ressource->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $ressource,'edit');
				break;
			
			case 'type':
				$ressource->set_values($_REQUEST);
				$ressource->save($ATMdb);
				$ressource->load($ATMdb, $_REQUEST['id']);
				$ressource->fk_rh_ressource_type=$_REQUEST['fk_rh_ressource_type'];
				_fiche($ATMdb, $ressource,'edit');
				break;
			
				
			case 'save':
				//$ATMdb->db->debug=true;
				$ressource->load($ATMdb, $_REQUEST['id']);
				//on vérifie que les champs obligatoires sont renseignés
				foreach($ressource->ressourceType->TField as $k=>$field) {
					if (! $field->obligatoire){
						if  ( empty($_REQUEST[$field->code]) ){
							$mesg .= '<div class="error">Le champs '.$field->libelle.' doit être renseigné.</div>';
						}
					}
				}
				
				//ensuite on vérifie ici que les champs sont bien du type attendu
				if ($mesg == ''){
					foreach($ressource->ressourceType->TField as $k=>$field) {
						switch ($field->type){
							case 'float':
							case 'entier':
								//la conversion en entier se fera lors de la sauvegarde dans l'objet.
								if (! is_numeric($_REQUEST[$field->code]) ){
									$mesg .= '<div class="error">Le champ '.$field->libelle.' doit être un nombre.</div>';
									}
								break;
							default :
								break;
						}
					}
				}
				
				$ressource->set_values($_REQUEST);
				$ressource->save($ATMdb);
				

				if ($mesg==''){
					$mesg = '<div class="ok">Modifications effectuées</div>';
					$mode = 'view';
					if(isset($_REQUEST['validerType']) ) {
						$mode = 'edit';
					}
				}
				else {$mode = 'edit';}
				
				$ressource->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $ressource,$mode);
				break;
			
			case 'view':
				//$ATMdb->db->debug=true;
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
	global $langs,$conf,$db,$user;	
	llxHeader('','Liste des ressources');
	getStandartJS();
	
	$r = new TSSRenderControler($ressource);
	$sql="SELECT r.rowid as 'ID', r.date_cre as 'DateCre', r.libelle, r.fk_rh_ressource_type,
		r.numId , '' as 'Statut'";
	if($user->rights->ressource->ressource->createRessource){
		$sql.=", '' as 'Supprimer'";
	}
	$sql.=" FROM ".MAIN_DB_PREFIX."rh_ressource as r";
	$sql.=" LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource_type as t ON r.fk_rh_ressource_type=t.rowid";
	if(!$user->rights->ressource->ressource->viewRessource){
		$sql.=" LEFT JOIN ".MAIN_DB_PREFIX."rh_evenement as e ON (e.fk_rh_ressource=r.rowid OR e.fk_rh_ressource=r.fk_rh_ressource)";
	}
	$sql.=" WHERE r.entity=".$conf->entity;
	if(!$user->rights->ressource->ressource->viewRessource){
		$sql.=" AND e.type ='emprunt'";
		$sql.=" AND e.fk_user=".$user->id;
	}
		
	$TOrder = array('DateCre'=>'ASC');
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
			'libelle'=>'<a href="?id=@ID@&action=view">@val@</a>'
			,'Supprimer'=>'<a href="?id=@ID@&action=delete"><img src="./img/delete.png"></a>'
		)
		,'eval'=>array(
			'Statut'=>'getStatut(@ID@, date("Y-m-d"))'
		)
		,'translate'=>array(
			'fk_rh_ressource_type'=>$ressource->TType
			)
		,'hide'=>array('DateCre')
		,'type'=>array('libelle'=>'string')
		,'liste'=>array(
			'titre'=>'Liste des ressources'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Il n'y a aucune ressource à afficher"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			,'picto_search'=>'<img src="../../theme/rh/img/search.png">'
			
		)
		,'title'=>array(
			'libelle'=>'Libellé'
			,'numId'=>'Numéro Id'
			,'fk_rh_ressource_type'=> 'Type'
			
			
		)
		,'search'=>array(
			'fk_rh_ressource_type'=>array('recherche'=>$ressource->TType)
			,'numId'=>true
			,'libelle'=>true
			,'Statut'=>array('recherche'=>array('Libre','Attribuée', 'Réservée'))
			
		)
		,'orderBy'=>$TOrder
		
	));
	
	$form->end();
	llxFooter();
}	

/**
 * Retourne un statut selon le jour donnée. Prend en compte la ressource associé éventuelle (si celle ci est attribué, elle le devient aussi)
 */
function getStatut($id, $jour){
	global $conf;
	$ATMdb=new Tdb;
	$sqlReq="SELECT e.date_debut, e.date_fin , firstname, name 
	FROM ".MAIN_DB_PREFIX."rh_evenement as e  
	LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (e.fk_user=u.rowid) 
	WHERE fk_rh_ressource=".$id."
	AND type='emprunt'
	AND e.entity=".$conf->entity."
	ORDER BY date_debut";
	
	$ATMdb->Execute($sqlReq);
	$return = 'Libre';
	while($ATMdb->Get_line()) {
		//echo $ATMdb->Get_field('date_debut').'  '.$ATMdb->Get_field('date_fin').'   <br>';
		if ( date("Y-m-d",strtotime($ATMdb->Get_field('date_debut'))) <= $jour  
			&& date("Y-m-d",strtotime($ATMdb->Get_field('date_fin'))) >= $jour ){
				return 'Attribuée à '.$ATMdb->Get_field('firstname').' '.$ATMdb->Get_field('name');
				break;
		}
		if (date("Y-m-d",strtotime($ATMdb->Get_field('date_debut'))) >= $jour ){
				$return='Réservée à '.$ATMdb->Get_field('firstname').' '.$ATMdb->Get_field('name');
				break;
			}
	}
	
	
	//le statut est égal est celui de la ressource attribué.
	$sqlReq="SELECT fk_rh_ressource FROM ".MAIN_DB_PREFIX."rh_ressource WHERE rowid=".$id." AND entity=".$conf->entity;
	$ATMdb->Execute($sqlReq);
	while($row=$ATMdb->Get_line()){
		if ($ATMdb->Get_field('fk_rh_ressource') !=  0){
			$return = getStatut($ATMdb->Get_field('fk_rh_ressource'), $jour);}
	}
	
	$ATMdb->close();
	return $return;			
	}


/*
 * à la création de la ressource, on choisi premierement le type
 */
function _choixType(&$ATMdb, &$ressource, $mode) {
	global $db,$user;
	llxHeader('', 'Ressource', '', '', 0, 0, array('/hierarchie/js/jquery.jOrgChart.js'));

	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','GET');
	$form->Set_typeaff($mode);
	//echo $form->hidden('id', $ressource->getId());
	echo $form->hidden('action', 'type');
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/ressource.new.tpl.php'
		,array()
		,array(
			'ressource'=>array(
				'id'=>$ressource->getId()
				,'type'=> count($ressource->TType) ? $form->combo('','fk_rh_ressource_type',$ressource->TType,$ressource->fk_rh_ressource_type): "Aucun type" 
				
			)
			,'view'=>array(
				'mode'=>$mode
				,'userRight'=>((int)$user->rights->ressource->ressource->createRessource)
				,'head'=>dol_get_fiche_head(ressourcePrepareHead($ressource, 'ressource')  , 'fiche', 'Ressource')
			)
			
			
		)	
		
	);
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
	
}



function _fiche(&$ATMdb, &$ressource, $mode) {
	global $db,$user;
	llxHeader('', 'Ressource', '', '', 0, 0, array('/hierarchie/js/jquery.jOrgChart.js'));

	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $ressource->getId());
	echo $form->hidden('action', 'save');
	
	//Ressources
	$TFields=array();
	
	foreach($ressource->ressourceType->TField as $k=>$field) {
		switch($field->type){
			case liste:
				$temp = $form->combo('',$field->code,$field->TListe,$ressource->{$field->code});
				break;
			case checkbox:
				$temp = $form->combo('',$field->code,array('oui'=>'Oui', 'non'=>'Non'),$ressource->{$field->code});
				break;
			default:
				$temp = $form->texte('', $field->code, $ressource->{$field->code}, 50,255,'','','-');
				break;
		}
		
		$TFields[$k]=array(
				'libelle'=>$field->libelle
				,'valeur'=>$temp
				//champs obligatoire : 0 = obligatoire ; 1 = non obligatoire
				,'obligatoire'=>$field->obligatoire ? 'class="field"': 'class="fieldrequired"' 
			);
	}


	//requete pour avoir toutes les ressources associées à la ressource concernées
	$k=0;
	$sqlReq="SELECT libelle FROM ".MAIN_DB_PREFIX."rh_ressource where fk_rh_ressource=".$ressource->rowid;
	$ATMdb->Execute($sqlReq);
	$Tab=array();
	$Tab_sous_ressource=array();
	$reqVide=0;	//variable permettant de savoir si la requete existe, et donc au final si on affichera l'organigramme
	while($ATMdb->Get_line()) {
		//récupère les id des différents nom des  groupes de l'utilisateur
		$Tab_sous_ressource[$k]=array('libelle'=>$ATMdb->Get_field('libelle'));
		$k++;
		$reqVide=1;
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
				,'numId'=>$form->texte('', 'numId', $ressource->numId, 00,20,'','','-')
				,'libelle'=>$form->texte('', 'libelle', $ressource->libelle, 50,255,'','','-')
				//,'type'=> count($ressource->TType) ? $form->combo('','fk_rh_ressource_type',$ressource->TType,$ressource->fk_rh_ressource_type): "Aucun type" 
				,'type'=>$ressource->TType[$ressource->fk_rh_ressource_type]
				,'bail'=>$form->combo('','bail',$ressource->TBail,$ressource->TBail[0])
				,'date_achat'=>$form->calendrier('', 'date_achat', $ressource->get_date('date_achat'), 10)
				,'date_vente'=>$form->calendrier('', 'date_vente', $ressource->get_date('date_vente') , 10)
				,'date_garantie'=>$form->calendrier('', 'date_garantie', $ressource->get_date('date_garantie'), 10)
				,'fk_proprietaire'=>$form->combo('','fk_proprietaire',$ressource->TAgence,$ressource->fk_proprietaire)

			)
			,'fk_ressource'=>array(
				'liste_fk_rh_ressource'=>$form->combo('','fk_rh_ressource',$ressource->TRessource,$ressource->fk_rh_ressource)
				,'fk_rh_ressource'=>$ressource->fk_rh_ressource ? $ressource->TRessource[$ressource->fk_rh_ressource] : "aucune ressource"
				,'reqExiste'=>$reqVide
			)
			
			,'view'=>array(
				'mode'=>$mode
				,'userRight'=>((int)$user->rights->ressource->ressource->createRessource)
				,'head'=>dol_get_fiche_head(ressourcePrepareHead($ressource, 'ressource')  , 'fiche', 'Ressource')
			)
			
			
		)	
		
	);
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

	
	
