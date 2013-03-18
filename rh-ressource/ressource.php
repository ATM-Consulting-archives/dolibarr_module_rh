<?php
	require('config.php');
	require('./class/ressource.class.php');
	require('./class/contrat.class.php');
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
				print_r($_REQUEST);
				//on vérifie que les champs obligatoires sont renseignés
				foreach($ressource->ressourceType->TField as $k=>$field) {
					if (! $field->obligatoire){
						//echo $field->libelle.':'.$_REQUEST[$field->code].'<br>';
						if  ( empty($_REQUEST[$field->code]) ){
							$mesg .= '<div class="error">Le champs '.$field->libelle.' doit être renseigné.</div>';
						}
					}
				}
				
				
				//ensuite on vérifie ici que les champs sont bien du type attendu
				if ($mesg == ''){
					foreach($ressource->ressourceType->TField as $k=>$field) {
						switch ($field->type){
							case 'entier':
								$ressource->{$field->code} = (intval( $_REQUEST[$field->code]));
								echo 'entier '.$field->code." : ".$ressource->{$field->code}."<br>";
								if (is_int($ressource->{$field->code})){
									$mesg .= '<div class="error">Le champs '.$field->libelle.' doit être un entier.</div>';
									}
								break;
							case 'float':
								$ressource->{$field->code} = (float)$_REQUEST[$field->code];
								echo 'float '.$field->code." : ".$ressource->{$field->code}."<br>";
								if (gettype($ressource->{$field->code}) != 'double' && gettype($ressource->{$field->code}) != 'integer' ){
									$mesg .= '<div class="error">Le champs '.$field->libelle.' doit être un nombre.</div>';
									}
								break;
							default :
								echo $field->code." : ".$_REQUEST[$field->code]."<br>";
								break;
						}
					}
				}
				
				$ressource->set_values($_REQUEST);
				$ressource->save($ATMdb);
				$ressource->load($ATMdb, $_REQUEST['id']);
				
				if ($mesg==''){
					$mesg .= '<div class="ok">Modifications effectuées</div>';
					$mode = 'view';
					if(isset($_REQUEST['validerType']) ) {
						$mode = 'edit';	
					}
				}
				else{
					$mode = 'edit';
				}
				echo $mesg;
				$mesg='';
				_fiche($ATMdb, $ressource,$mode);
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
	$sql="SELECT r.rowid as 'ID', r.date_cre as 'DateCre',r.libelle as 'Libellé', t.libelle as 'Type',  r.statut as 'Statut'		FROM llx_rh_ressource as r, llx_rh_ressource_type as t 
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
		switch ($field->type){
			case liste:
				$temp = $form->combo('',$field->code, $field->TListe, $ressource->{$field->code});
				break;
			case checkbox:
				$temp = $form->combo('',$field->code,array(true=>'Oui', false=>'Non'),$ressource->{$field->code});
				break;
			default:
				$temp = $form->texte('', $field->code, $ressource->{$field->code}, 50,255,'','','-');
				break;
		}
		$TFields[$k]=array(
				'libelle'=>$field->libelle
				,'valeur'=>$temp//$form->texte('', $field->code, $ressource->{$field->code}, 50,255,'','','-')
				
			);
	}


	//requete pour avoir toutes les ressources associées à la ressource concernées
	$k=0;
	$sqlReq="SELECT libelle FROM `llx_rh_ressource` where fk_rh_ressource=".$ressource->rowid;
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
				,'libelle'=>$form->texte('', 'libelle', $ressource->libelle, 50,255,'','','-')
				,'type'=> count($ressource->TType) ? $form->combo('','fk_rh_ressource_type',$ressource->TType,$ressource->fk_rh_ressource_type): "Aucun type" 
				,'bail'=>$form->combo('','bail',$ressource->TBail,$ressource->TBail[0])
				,'statut'=>$form->combo('','statut',$ressource->TStatut,$ressource->TStatut[0])
			
			)
			,'fk_ressource'=>array(
				'liste_fk_rh_ressource'=>$form->combo('','fk_rh_ressource',$ressource->TRessource,$ressource->fk_rh_ressource)
				,'fk_rh_ressource'=>$ressource->fk_rh_ressource ? $ressource->TRessource[$ressource->fk_rh_ressource] : "aucune ressource"
				,'reqExiste'=>$reqVide
			)
			
			,'view'=>array(
				'mode'=>$mode
				/*,'userRight'=>((int)$user->rights->financement->affaire->write)*/
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

	
	
