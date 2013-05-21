<?php
	require('config.php');
	require('./class/ressource.class.php');
	require('./class/contrat.class.php');
	require('./class/evenement.class.php');
	require('./lib/ressource.lib.php');
	$langs->load('ressource@ressource');
	
	//if (!$user->rights->financement->affaire->read)	{ accessforbidden(); }
	$ATMdb=new Tdb;
	$emprunt=new TRH_Evenement;
	$ressource=new TRH_ressource;
	$contrat=new TRH_Contrat;
	$contrat_ressource=new TRH_Contrat_Ressource;
	
	$mesg = '';
	$error=false;
	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				$ressource->set_values($_REQUEST);
				_fiche($ATMdb, $emprunt, $ressource, $contrat,'new');
				break;	
			case 'edit'	:
				//$ATMdb->db->debug=true;
				//print_r($_REQUEST);
				
				//$ressource->set_values($_REQUEST['fk_rh_ressource_type']);
				$ressource->fk_rh_ressource_type = $_REQUEST['fk_rh_ressource_type'];
				$ressource->load_ressource_type($ATMdb);
				$ressource->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $emprunt, $ressource, $contrat,'edit');
				break;
				
			case 'save':
				//$ATMdb->db->debug=true;
				$ressource->fk_rh_ressource_type = $_REQUEST['fk_rh_ressource_type'];
				$ressource->load($ATMdb, $_REQUEST['id']);
				//on vérifie que le libellé est renseigné
				if  ( empty($_REQUEST['numId']) ){
					$mesg .= '<div class="error">Le numéro Id doit être renseigné.</div>';
				}
				
				if  ( empty($_REQUEST['libelle']) ){
					$mesg .= '<div class="error">Le libellé doit être renseigné.</div>';
				}
				
				
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
				
				////////
				if($_REQUEST["fieldChoice"]=="O"){
					$emprunt->load($ATMdb, $_REQUEST['idEven']);
					$emprunt->set_values($_REQUEST['evenement']);
					$emprunt->fk_rh_ressource = $ressource->getId();
					$emprunt->save($ATMdb);
				}
				////////
				
				////////
				if($_REQUEST["fieldChoiceContrat"]=="O"){
					$contrat->set_values($_REQUEST['contrat']);
					$contrat->fk_tier_fournisseur=$_REQUEST['fk_tier_fournisseur'];
					$contrat->fk_rh_ressource_type=$_REQUEST['fk_rh_ressource_type'];
					$contrat->save($ATMdb);
					$contrat_ressource->fk_rh_ressource = $ressource->getId();
					$contrat_ressource->fk_rh_contrat = $contrat->getId();
					$contrat_ressource->save($ATMdb);
				}
				////////

				if ($mesg==''){
					$mesg = '<div class="ok">Modifications effectuées</div>';
					$mode = 'view';
					if(isset($_REQUEST['validerType']) ) {
						$mode = 'edit';
					}
				}
				else {$mode = 'edit';}
				
				$ressource->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $emprunt, $ressource, $contrat, $mode);
				break;
			
			case 'view':
				//$ATMdb->db->debug=true;
				$ressource->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $emprunt, $ressource, $contrat, 'view');
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
		_fiche($ATMdb, $emprunt, $ressource, $contrat, 'view');
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
	print dol_get_fiche_head(array()  , '', 'Liste ressources');
	
	//getStandartJS();
	
	$r = new TSSRenderControler($ressource);
	$sql="SELECT r.rowid as 'ID', r.date_cre as 'DateCre', r.libelle, r.fk_rh_ressource_type,
		r.numId , name as 'Statut', firstname, name ";
	if($user->rights->ressource->ressource->createRessource){
		$sql.=", '' as 'Supprimer'";
	}
	$sql.=" FROM ".MAIN_DB_PREFIX."rh_ressource as r
		LEFT JOIN ".MAIN_DB_PREFIX."rh_evenement as e ON  (e.fk_rh_ressource=r.rowid OR e.fk_rh_ressource=r.fk_rh_ressource)
		AND e.entity = ".$conf->entity."
		AND e.date_debut<='".date("Y-m-d")."' AND e.date_fin >= '". date("Y-m-d")."' 
	 LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (e.fk_user = u.rowid )";	
	$sql.=" WHERE  r.entity=".$conf->entity;
	
	
	if(!$user->rights->ressource->ressource->viewRessource){
		$sql.=" AND e.fk_user=".$user->id;
	}
	
	$TOrder = array('ID'=>'ASC');
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
			'Statut'=>'getStatut("@val@")'
			,'name'=>'htmlentities("@val@", ENT_COMPAT , "ISO8859-1")'
			,'firstname'=>'htmlentities("@val@", ENT_COMPAT , "ISO8859-1")'
		)
		,'translate'=>array(
			'fk_rh_ressource_type'=>$ressource->TType
			)
		,'hide'=>array('DateCre')
		,'type'=>array('libelle'=>'string')
		,'liste'=>array(
			'titre'=>'Liste des ressources'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','previous.png', '', 0)
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
			,'name'=>'Nom'
			,'firstname'=>'Prénom'
			
			
		)
		,'search'=>($user->rights->ressource->ressource->searchRessource) ? 		
			array(
				'fk_rh_ressource_type'=>array('recherche'=>$ressource->TType)
				,'numId'=>true
				,'libelle'=>true
				,'name'=>true
				,'firstname'=>true
				//,'Statut'=>array('recherche'=>array('Libre'=>'Libre','Attribué'=>'Attribuée', 'Réservé'=>'Réservée'))	
			)
			: array()
		,'orderBy'=>$TOrder
		
	));
	
	$form->end();
	llxFooter();
}	


function getStatut($val){
	if (empty($val)){return "Libre";}
	return "Attribué";
}
/**
 * Retourne un statut selon le jour donnée. Prend en compte la ressource associé éventuelle (si celle ci est attribué, elle le devient aussi)
 */
function getAttribution($id, $jour){
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
	$return = $return = array('name'=>''
				,'firstname'=>''
				,'statut'=>'Libre') ;
	while($ATMdb->Get_line()) {
		if ( date("Y-m-d",strtotime($ATMdb->Get_field('date_debut'))) <= $jour  
			&& date("Y-m-d",strtotime($ATMdb->Get_field('date_fin'))) >= $jour ){
				return array('name'=>htmlentities($ATMdb->Get_field('name'), ENT_COMPAT , 'ISO8859-1')
				,'firstname'=>htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1')
				,'statut'=>'Attribuée') ;
				//return 'Attribuée à '.htmlentities($ATMdb->Get_field('firstname').' '.$ATMdb->Get_field('name'), ENT_COMPAT , 'ISO8859-1');
				break;
		}
		if (date("Y-m-d",strtotime($ATMdb->Get_field('date_debut'))) >= $jour ){
			$return =  array('name'=>htmlentities($ATMdb->Get_field('name'), ENT_COMPAT , 'ISO8859-1')
				,'firstname'=>htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1')
				,'statut'=>'Réservée') ;
				$return='Réservée à '.htmlentities($ATMdb->Get_field('firstname').' '.$ATMdb->Get_field('name'), ENT_COMPAT , 'ISO8859-1');
				break;
			}
	}
	
	
	//le statut est égal est celui de la ressource attribué.
	$sqlReq="SELECT fk_rh_ressource FROM ".MAIN_DB_PREFIX."rh_ressource WHERE rowid=".$id." AND entity=".$conf->entity;
	$ATMdb->Execute($sqlReq);
	while($row=$ATMdb->Get_line()){
		if ($ATMdb->Get_field('fk_rh_ressource') !=  0){
			$return = array('name'=>''
				,'firstname'=>''
				,'statut'=>'Libre') ;
		}
			//$return = getStatut($ATMdb->Get_field('fk_rh_ressource'), $jour);}
	}
	
	$ATMdb->close();
	return $return;			
	}



function _fiche(&$ATMdb, &$emprunt, &$ressource, &$contrat, $mode) {
	global $db,$user;
	llxHeader('', 'Ressource', '', '', 0, 0, array('/hierarchie/js/jquery.jOrgChart.js'));

	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $ressource->getId());
	if ($mode=='new'){
		echo $form->hidden('action', 'edit');
	}
	else {echo $form->hidden('action', 'save');}
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
		$Tab_sous_ressource[$k]=array('libelle'=>'<a>'.$ATMdb->Get_field('libelle').'</a>');
		$k++;
		$reqVide=1;
	}

	$contrat->load_liste($ATMdb);
	$emprunt->load_liste($ATMdb);
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

				,'titreChamps'=>load_fiche_titre("Champs",'', 'title.png', 0, '')
				,'titreOrganigramme'=>load_fiche_titre("Organigramme des ressources associées",'', 'title.png', 0, '')
				,'titreRessourceAssocie'=>load_fiche_titre("Organigramme des ressources associées",'', 'title.png', 0, '')
				,'titreAttribution'=>load_fiche_titre("Attribution de la ressource",'', 'title.png', 0, '')
				,'titreContrat'=>load_fiche_titre("Création d'un contrat directement lié",'', 'title.png', 0, '')
				
				,'typehidden'=>$form->hidden('fk_rh_ressource_type', $ressource->fk_rh_ressource_type) 
				,'type'=>$ressource->TType[$ressource->fk_rh_ressource_type]
				,'bail'=>$form->combo('','bail',$ressource->TBail,$ressource->TBail[0])
				,'date_achat'=>$form->calendrier('', 'date_achat', $ressource->get_date('date_achat'), 10)
				,'date_vente'=>(empty($ressource->date_vente) || ($ressource->date_vente<=0) || ($mode=='new')) ? $form->calendrier('', 'date_vente', '' , 10) : $form->calendrier('', 'date_vente', $ressource->get_date('date_vente') , 10)
				//,'date_garantie'=>(empty($ressource->date_garantie) || ($ressource->date_garantie<=0) || ($mode=='new')) ? $form->calendrier('', 'date_garantie', '' , 10) : $form->calendrier('', 'date_garantie', $ressource->get_date('date_garantie'), 10)
				,'fk_proprietaire'=>$form->combo('','fk_proprietaire',$ressource->TAgence,$ressource->fk_proprietaire)
			)
			,'ressourceNew' =>array(
				'typeCombo'=> count($ressource->TType) ? $form->combo('','fk_rh_ressource_type',$ressource->TType,$ressource->fk_rh_ressource_type): "Aucun type"
				,'validerType'=>$form->btsubmit('Valider', 'validerType')
				
			)
			,'fk_ressource'=>array(
				'liste_fk_rh_ressource'=>$form->combo('','fk_rh_ressource',$ressource->TRessource,$ressource->fk_rh_ressource)
				,'fk_rh_ressource'=>$ressource->fk_rh_ressource ? $ressource->TRessource[$ressource->fk_rh_ressource] : "aucune ressource"
				,'id'=>$ressource->fk_rh_ressource
				,'reqExiste'=>$reqVide
			)
			,'NEmprunt'=>array(
				'id'=>$emprunt->getId()
				,'type'=>$form->hidden('evenement[type]', 'emprunt')
				,'fk_user'=>$form->combo('','evenement[fk_user]',$emprunt->TUser,$emprunt->fk_user)
				,'fk_rh_ressource'=> $form->hidden('evenement[fk_rh_ressource]', $ressource->getId())
				,'commentaire'=>$form->texte('','evenement[commentaire]',$emprunt->commentaire, 30,100,'','','-')
				,'date_debut'=> $form->calendrier('', 'evenement[date_debut]', $emprunt->get_date('date_debut'), 10)
				,'date_fin'=> $form->calendrier('', 'evenement[date_fin]', $emprunt->get_date('date_fin'), 10)
			)
			,'contrat'=>array(
				'id'=>$contrat->getId()
				,'libelle'=>$form->texte('', 'contrat[libelle]', $contrat->libelle, 50,255,'','','-')
				,'fk_rh_ressource'=> $form->hidden('contrat[fk_rh_ressource]', $ressource->getId())
				,'tiersFournisseur'=> $form->combo('','fk_tier_fournisseur',$contrat->TFournisseur,$contrat->fk_tier_fournisseur)
				,'tiersAgence'=> $form->combo('','contrat[fk_tier_utilisateur]',$contrat->TAgence,$contrat->fk_tier_utilisateur)
				,'date_debut'=> $form->calendrier('', 'contrat[date_debut]', $contrat->get_date('date_debut'), 10)
				,'date_fin'=> $form->calendrier('', 'contrat[date_fin]', $contrat->get_date('date_fin'), 10)
				,'entretien'=>$form->texte('', 'contrat[entretien]', $contrat->entretien, 10,20,'','','')
				,'assurance'=>$form->texte('', 'contrat[assurance]', $contrat->assurance, 10,20,'','','')
				,'kilometre'=>$form->texte('', 'contrat[kilometre]', $contrat->kilometre, 8,8,'','','')
				,'loyer_TTC'=>$form->texte('', 'contrat[loyer_TTC]', $contrat->loyer_TTC, 10,20,'','','')
				,'TVA'=>$form->combo('','contrat[TVA]',$contrat->TTVA,$contrat->TVA)
				,'loyer_HT'=>($contrat->loyer_TTC)*(1-(0.01*$contrat->TTVA[$contrat->TVA]))
				
			)
			,'view'=>array(
				'mode'=>$mode
				,'userRight'=>((int)$user->rights->ressource->ressource->createRessource)
				,'head'=>dol_get_fiche_head(ressourcePrepareHead($ressource, 'ressource')  , 'fiche', 'Ressource')
				,'onglet'=>dol_get_fiche_head(array()  , '', 'Création ressource')
			)
			
			
		)	
		
	);
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

	
	
