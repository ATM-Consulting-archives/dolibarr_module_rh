<?php
	require('config.php');
	require('./class/ressource.class.php');
	require('./class/regle.class.php');
	require('./lib/ressource.lib.php');
	
	$langs->load('ressource@ressource');
	
	//if (!$user->rights->financement->affaire->read)	{ accessforbidden(); }
	$ATMdb=new Tdb;
	$ressourceType=new TRH_ressource_type;
	$regle = new TRH_Ressource_Regle;
	$mesg = '';
	$error=false;
	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				//$ATMdb->db->debug=true;
				$ressourceType->load($ATMdb, $_REQUEST['id']);
				//$ressourceType->save($ATMdb);
				_fiche($ATMdb, $regle, $ressourceType,'new');
				break;
				
			case 'edit'	:
				//$ATMdb->db->debug=true;
				$ressourceType->load($ATMdb, $_REQUEST['id']);
				$regle->load($ATMdb, $_REQUEST['idRegle']);
				_fiche($ATMdb,  $regle, $ressourceType,'edit');
				break;
				
			case 'save':
				//$ATMdb->db->debug=true;
				$ressourceType->load($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">Modifications effectuées</div>';
				$mode = 'view';
				$regle->load($ATMdb, $_REQUEST['idRegle']);
				$regle->set_values($_REQUEST);
				$regle->duree = timeToInt($_REQUEST['dureeH'],$_REQUEST['dureeM'] );
				$regle->dureeInt = timeToInt($_REQUEST['dureeHInt'],$_REQUEST['dureeMInt'] );
				$regle->dureeExt = timeToInt($_REQUEST['dureeHExt'],$_REQUEST['dureeMExt'] );
								
				$regle->save($ATMdb);
				$ressourceType->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb,  $regle, $ressourceType,$mode);
				break;
			
			case 'view':
				$ressourceType->load($ATMdb, $_REQUEST['id']);
				$regle->load($ATMdb, $_REQUEST['idRegle']);
				_fiche($ATMdb,  $regle, $ressourceType,'view');
				break;
		
			case 'delete':
				$regle->load($ATMdb, $_REQUEST['idRegle']);
				$regle->delete($ATMdb);
				//$ATMdb->db->debug=true;
				
				/*
				?>
				<script language="javascript">
					document.location.href="?delete_ok=1";					
				</script>
				<?*/
				$ressourceType->load($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">Le type de ressource est utilisé par une ressource. Il ne peut pas être supprimé.</div>';
				_liste($ATMdb, $ressourceType, $regle);
			
				break;
		}
	}
	elseif(isset($_REQUEST['id']) && isset($_REQUEST['idRegle'])) {
		$ressourceType->load($ATMdb, $_REQUEST['id']);
		$regle->load($ATMdb, $_REQUEST['idRegle']);
		_fiche($ATMdb,  $regle, $ressourceType,'view');
	}
	
	elseif(isset($_REQUEST['id'])) {
		$ressourceType->load($ATMdb, $_REQUEST['id']);
		_liste($ATMdb, $ressourceType, $regle);
		
	}
	else {
		/*
		 * Liste
		 */
		 _liste($ATMdb, $ressourceType, $regle);
	}
	
	
	$ATMdb->close();
	
	
function _liste(&$ATMdb, &$ressourceType, &$regle) {
	global $langs,$conf, $db, $user;	
	
	llxHeader('','Règles sur les Ressources');
	dol_fiche_head(ressourcePrepareHead($ressourceType, 'type-ressource')  , 'regle', 'Type de ressource');
	
		echo '<table width="100%" class="border">
			<tr><td width="20%">Libellé</td><td>'.$ressourceType->libelle.'</td></tr>
			<tr><td width="20%">Code</td><td>'.$ressourceType->code.'</td></tr>
		</table><br>';
		
	$r = new TSSRenderControler($ressourceType);
	$sql="SELECT DISTINCT r.rowid as 'ID', r.choixApplication as 'CA',  r.choixLimite as 'CL', u.firstname ,u.name, g.nom as 'Groupe',
		duree, dureeInt,dureeExt,dataIllimite, dataIphone, mailforfait, smsIllimite, data15Mo, '' as 'Supprimer'
		FROM ".MAIN_DB_PREFIX."rh_ressource_regle as r
		LEFT OUTER JOIN ".MAIN_DB_PREFIX."user as u ON (r.fk_user = u.rowid)
		LEFT OUTER JOIN ".MAIN_DB_PREFIX."usergroup as g ON (r.fk_usergroup = g.rowid)
		WHERE 1 
		 AND r.fk_rh_ressource_type=".$ressourceType->getId();
	
	$TOrder = array('ID'=>'ASC');
	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
	
	$TOuiRien = array('vrai'=>'Oui', 'faux'=>'');
	$TOuiNon = array('vrai'=>'Oui', 'faux'=>'Non');
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
	$form=new TFormCore($_SERVER['PHP_SELF'].'?id='.$ressourceType->getId(),'formtranslateList','GET');
	echo $form->hidden('id',$ressourceType->getId());
	
	$r->liste($ATMdb, $sql, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>'30'
		)
		,'link'=>array(
			'ID'=>'<a href="?id='.$ressourceType->getId().'&idRegle=@ID@&action=view">@val@</a>'
			,'Supprimer'=>"<a style=\"cursor:pointer;\" onclick=\"if (window.confirm('Voulez vous supprimer l\'élément ?')){document.location.href='?id=".$ressourceType->getId()."&idRegle=@ID@&action=delete'};\"><img src=\"./img/delete.png\"></a>"
			//'<a href="?id='.$ressourceType->getId().'&idRegle=@ID@&action=delete"><img src="./img/delete.png"></a>'
		) 
		,'eval'=>array(
			'dureeInt'=>'afficheOuPas(@val@, @CL@, "extint")'
			,'dureeExt'=>'afficheOuPas(@val@, @CL@, "extint")'
			,'duree'=>'afficheOuPas(@val@, @CL@, "gen")'
			,'Groupe'=>'TousOuPas(@CA@,"@val@")'
			,'firstname'=>'TousOuPas(@CA@,"@val@")'
			,'name'=>'TousOuPas(@CA@,"@val@")'
			//htmlentities("@val@", ENT_COMPAT , "ISO8859-1"))'
		)
		,'title'=>array(
			'name'=>'Nom'
			,'firstname'=>'Prénom'
			,'duree'=>'Lim. générale'
			,'dureeInt'=>'Lim. interne'
			,'dureeExt'=>'Lim. externe'
			,'dataIllimite'=>'3G illimité'
			,'smsIllimite'=> 'SMS illimité'
			,'dataIphone' => 'Forfait Data Iphone'
			,'mailforfait' => 'Forfait Mail'
			,'data15Mo' => 'Forfait Data 15 Mo'
			//,'carteJumelle' => 'Forfait carte jumellé'
		)
		,'translate'=>array(
			'Sur'=>$regle->TObjet
			,'Période'=>$regle->TPeriode
			,'dataIllimite' => $TOuiRien
			,'smsIllimite' => $TOuiRien
			,'dataIphone' => $TOuiRien
			,'mailforfait' => $TOuiRien
			,'data15Mo' => $TOuiRien
			//,'carteJumelle' => $TOuiRien
		)
		,'hide'=>array('CA', 'CL')
		,'type'=>array()
		,'liste'=>array(
			'titre'=>'Liste des règles'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','previous.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['ID'])
			,'messageNothing'=>"Il n'y a aucune règle à afficher"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			,'picto_search'=>'<img src="../../theme/rh/img/search.png">'
		)
		,'search'=>array(
			'Utilisateur'=>true
			,'dataIllimite' => $TOuiNon
			,'smsIllimite' => $TOuiNon
			,'dataIphone' => $TOuiNon
			,'mailforfait' =>$TOuiNon
			,'data15Mo' => $TOuiNon
			//,'carteJumelle' => array('recherche'=>$TOuiNon)
			,'name'=>TRUE
			,'firstname'=>TRUE
			//,'Statut'=>array('recherche'=>array('Libre'=>'Libre','Attribué'=>'Attribuée', 'Réservée'=>'Réservée'))	
		)
		,'orderBy'=>$TOrder
		
	));
	
	?></div><a class="butAction" href="?id=<?=$ressourceType->getId()?>&action=new">Nouveau</a>
	<div style="clear:both"></div><?
	$form->end();
	llxFooter();
}	

function TousOuPas($choix, $val){
	if ($choix=='all'){
		return 'Tous';
	}
	return htmlentities($val, ENT_COMPAT , "ISO8859-1");
}

function _fiche(&$ATMdb, &$regle, &$ressourceType, $mode) {
	global $user;
	llxHeader('','Règle sur les Ressources', '', '', 0, 0);
	


	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $ressourceType->getId());
	echo $form->hidden('idRegle', $regle->getId());
	echo $form->hidden('action', 'save');
	echo $form->hidden('fk_rh_ressource_type', $ressourceType->getId());
	
	$TBool = array('faux'=>'Non', 'vrai'=>'Oui');
	$TBS=new TTemplateTBS();
	$regle->load_liste($ATMdb);
	
	if ($mode == 'new'){
		$regle->choixApplication = 'all';
		$regle->choixLimite = 'gen';
		$mode = 'edit';
	}
	
	print $TBS->render('./tpl/ressource.type.regle.tpl.php'
		,array()
		,array(
			'ressourceType'=>array(
				'id'=>$ressourceType->getId()
				,'code'=> $ressourceType->code
				,'libelle'=> $ressourceType->libelle
				,'titreRegle'=>load_fiche_titre('Règle','', 'title.png', 0, '')
			)
			,'newRule'=>array(
				'id'=>$regle->getId()
				,'choixApplication'=>$form->radiodiv('','choixApplication',$regle->TChoixApplication, $regle->choixApplication)
				,'choixApplicationViewMode'=>$regle->TChoixApplication[$regle->choixApplication]
				,'fk_user'=>$form->combo('', 'fk_user',$regle->TUser, $regle->fk_user)
				,'fk_group'=>$form->combo('', 'fk_usergroup',$regle->TGroup, $regle->fk_usergroup)
				,'choixLimite'=>$form->radiodiv('','choixLimite',$regle->TChoixLimite, $regle->choixLimite)
				,'choixLimiteViewMode'=>$regle->TChoixLimite[$regle->choixLimite]
				,'dureeH'=>$form->texte('', 'dureeH', intToHour($regle->duree), 2,2,'','','')
				,'dureeM'=>$form->texte('', 'dureeM', intToMinute($regle->duree), 2,2,'','','')
				,'dureeHInt'=>$form->texte('', 'dureeHInt', intToHour($regle->dureeInt), 2,2,'','','')
				,'dureeMInt'=>$form->texte('', 'dureeMInt', intToMinute($regle->dureeInt), 2,2,'','','')
				,'dureeHExt'=>$form->texte('', 'dureeHExt', intToHour($regle->dureeExt), 2,2,'','','')
				,'dureeMExt'=>$form->texte('', 'dureeMExt', intToMinute($regle->dureeExt), 2,2,'','','')
				,'natureRefac'=>$form->texte('', 'natureRefac', $regle->natureRefac, 100,255,'','','')
				,'montantRefac'=>$form->texte('', 'montantRefac', $regle->montantRefac, 5,20,'','','')
				,'dataIllimite'=>$form->combo('', 'dataIllimite',$TBool, $regle->dataIllimite)
				,'dataIphone'=>$form->combo('', 'dataIphone',$TBool, $regle->dataIphone)
				,'smsIllimite'=>$form->combo('', 'smsIllimite',$TBool, $regle->smsIllimite)
				,'mailforfait'=>$form->combo('', 'mailforfait',$TBool, $regle->mailforfait)
				,'data15Mo'=>$form->combo('', 'data15Mo',$TBool, $regle->data15Mo)
				,'numeroExclus'=>$form->texte('', 'numeroExclus', $regle->numeroExclus,30 ,255,'','','')
		
			)
			,'view'=>array(
				'mode'=>$mode
				,'userRight'=>((int)$user->rights->ressource->ressource->manageRegle)
				,'head'=>dol_get_fiche_head(ressourcePrepareHead($ressourceType)  , 'regle', 'Type de ressource')
				,'onglet'=>dol_get_fiche_head(array()  , '', 'Règle')
			)
			
		)	
		
	);
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}
