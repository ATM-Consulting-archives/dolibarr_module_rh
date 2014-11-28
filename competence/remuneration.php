<?php
	require('config.php');
	require('./class/competence.class.php');
	require('./class/type_poste.class.php');
	
	require_once DOL_DOCUMENT_ROOT.'/core/lib/usergroups.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
	dol_include_once('/user/class/usergroup.class.php');
	
	$langs->load('competence@competence');
	$langs->load("users");
	
	$ATMdb=new TPDOdb;
	$remuneration=new TRH_remuneration;
	$remunerationPrime=new TRH_remunerationPrime;

	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				//$ATMdb->db->debug=true;
				$type = $_REQUEST['type'];
				
				if($type === "remuneration") {
					$remuneration->set_values($_REQUEST);
					_fiche($ATMdb, $remuneration, 'edit');
				}
				elseif($type === "prime") {
					$remunerationPrime->set_values($_REQUEST);
					_fichePrime($ATMdb, $remunerationPrime, 'edit');
				}
				break;
				
			case 'edit'	:
				if($_REQUEST['type'] !== 'prime'){
					
					$remuneration->load($ATMdb, $_REQUEST['id']);
					_fiche($ATMdb, $remuneration,'edit');
					
				} else {
					
					$remunerationPrime->load($ATMdb, $_REQUEST['id']);
					_fichePrime($ATMdb, $remunerationPrime,'edit');					
					
				}
				break;
				
			case 'save':
				if($_REQUEST['type'] !== 'prime'){
					
					$remuneration->load($ATMdb, $_REQUEST['id']);
					$remuneration->set_values($_REQUEST);
					$mesg = '<div class="ok">La ligne de rémunération a bien été enregistrée</div>';
					$remuneration->save($ATMdb);
					$remuneration->load($ATMdb, $_REQUEST['id']);
					_fiche($ATMdb, $remuneration, 'view');
					
				} else {
					$remunerationPrime->load($ATMdb, $_REQUEST['id']);
					$remunerationPrime->set_values($_REQUEST);
					$mesg = '<div class="ok">La ligne de prime a bien été enregistrée</div>';
					$remunerationPrime->save($ATMdb);
					$remunerationPrime->load($ATMdb, $_REQUEST['id']);
					_fichePrime($ATMdb, $remunerationPrime, 'view');
				}
				break;
				
			case 'view':
				if($_REQUEST['type'] !== 'prime'){
					$remuneration->load($ATMdb, $_REQUEST['id']);
					_fiche($ATMdb, $remuneration, 'view');
				} else {
					$remunerationPrime->load($ATMdb, $_REQUEST['id']);
					_fichePrime($ATMdb, $remunerationPrime, 'view');					
				}
				break;
				
			case 'delete':
				//$ATMdb->db->debug=true;
				if($_REQUEST['type'] !== 'prime'){
					$remuneration->load($ATMdb, $_REQUEST['id']);
					$remuneration->delete($ATMdb, $_REQUEST['id']);
					$mesg = '<div class="ok">La ligne de rémunération a bien été supprimée</div>';
					_liste($ATMdb, $remuneration);
				} else {
					$remunerationPrime->load($ATMdb, $_REQUEST['id']);
					$remunerationPrime->delete($ATMdb, $_REQUEST['id']);
					$mesg = '<div class="ok">La ligne de prime a bien été supprimée</div>';
					_liste($ATMdb, $remuneration);					
				}
				break;
				
		}
	}
	elseif(isset($_REQUEST['id'])) {
		$remuneration->load($ATMdb, $_REQUEST['id']);
		_liste($ATMdb, $remuneration);	
	}
	else {
		
		//$ATMdb->db->debug=true;
		$remuneration->load($ATMdb, $_REQUEST['id']);
		_liste($ATMdb,$remuneration);
	}
	
	$ATMdb->close();
	
	llxFooter();
	
	
function _liste(&$ATMdb, $remuneration) {
	global $langs, $conf, $db, $user;	
	llxHeader('','Liste de vos rémunérations');
	
	$fuser = new User($db);
	$fuser->fetch($_REQUEST['fk_user']);
	$fuser->getrights();

	$head = user_prepare_head($fuser);
	dol_fiche_head($head, 'remuneration', $langs->trans('Utilisateur'),0, 'user');
	
	?><table width="100%" class="border"><tbody>
		<tr><td width="25%" valign="top">Réf.</td><td><?=$fuser->id ?></td></tr>
		<tr><td width="25%" valign="top">Nom</td><td><?=$fuser->lastname ?></td></tr>
		<tr><td width="25%" valign="top">Prénom</td><td><?=$fuser->firstname ?></td></tr>
	</tbody></table>
	<br/><?
	
	_bloqueAffichageSelonDroitUtilisateurGroupe();
	
	////////////AFFICHAGE DES LIGNES DE REMUNERATION
	$r = new TSSRenderControler($remuneration);
	$sql="SELECT r.rowid as 'ID', r.date_cre as 'DateCre', DATE_FORMAT(r.date_debutRemuneration, '%d/%m/%Y') as 'Date début', DATE_FORMAT(r.date_finRemuneration, '%d/%m/%Y') as 'Date fin', 
			CONCAT(u.firstname,' ',u.lastname) as 'Utilisateur' ,
			  CONCAT( ROUND(r.bruteAnnuelle,2),' €') as 'Rémunération brute annuelle',  
			  CONCAT( ROUND(r.salaireMensuel,2),' €') as 'Salaire mensuel', r.fk_user, '' as 'Supprimer'
		FROM   ".MAIN_DB_PREFIX."rh_remuneration as r, ".MAIN_DB_PREFIX."user as u
		WHERE r.fk_user=".$_REQUEST['fk_user']." AND r.entity=".$conf->entity." AND u.rowid=r.fk_user";
	
	$TOrder = array('date_debutRemuneration'=>'ASC');
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
			'Rémunération brute annuelle'=>'<a href="?id=@ID@&action=view&fk_user='.$fuser->id.'">@val@</a>'
			,'Date début'=>'<a href="?id=@ID@&action=view&fk_user='.$fuser->id.'">@val@</a>'
			//,'Supprimer'=>$user->rights->curriculumvitae->myactions->ajoutRemuneration?'<a href="?id=@ID@&action=delete&fk_user='.$fuser->id.'"><img src="./img/delete.png"></a>':''
			,'Supprimer'=>$user->rights->curriculumvitae->myactions->ajoutRemuneration?"<a onclick=\"if (window.confirm('Voulez vous supprimer l\'élément ?')){document.location.href='?fk_user=@fk_user@&id=@ID@&action=delete'}\"><img src=\"./img/delete.png\"></a>":''
		)
		,'translate'=>array(
			
		)
		,'hide'=>array('DateCre', 'fk_user')
		,'type'=>array()
		,'liste'=>array(
			'titre'=>'Visualisation de vos rémunérations'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Aucune rémunération enregistrée"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			,'picto_search'=>'<img src="../../theme/rh/img/search.png">'
		)
		,'title'=>array(
			'date_debutRemuneration'=>'Date début'
		)
		,'search'=>array(
		)
		,'orderBy'=>$TOrder
		
	));
	
	$form->end();
		if($user->rights->curriculumvitae->myactions->ajoutRemuneration==1){
		?>
		<div class="tabsAction">
			<a class="butAction" href="?&action=new&type=remuneration&fk_user=<?=$fuser->id?>">Ajouter une rémunération</a><div style="clear:both"></div>
		</div>
		<?
		}
		
	$r = new TSSRenderControler($remuneration);
	$sql="SELECT r.rowid as 'ID', r.fk_user as 'fk_user', DATE_FORMAT(r.date_prime, '%d/%m/%Y') as 'Date prime', 
			CONCAT(u.firstname,' ',u.lastname) as 'Utilisateur' , CONCAT(r.montant, ' €') as Montant, '' as 'Supprimer'
		FROM   ".MAIN_DB_PREFIX."rh_remuneration_prime as r, ".MAIN_DB_PREFIX."user as u
		WHERE r.fk_user=".$_REQUEST['fk_user']." AND r.entity=".$conf->entity." AND u.rowid=r.fk_user";
	
	$TOrder = array('date_prime'=>'ASC');
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
			'Date prime'=>'<a href="?id=@ID@&action=view&type=prime&fk_user='.$fuser->id.'">@val@</a>'
			,'Utilisateur'=>'<a href="'.dol_buildpath('/user/fiche.php?id=@fk_user@', 2).'">@val@</a>'
			//,'Supprimer'=>$user->rights->curriculumvitae->myactions->ajoutRemuneration?'<a href="?id=@ID@&action=delete&type=prime&fk_user='.$fuser->id.'"><img src="./img/delete.png"></a>':''
			//,'Supprimer'=>$user->rights->curriculumvitae->myactions->ajoutRemuneration?"<a href=\"?id=@ID@&action=delete&type=prime&fk_user=$fuser->id\"><img src=\"./img/delete.png\"></a>":''
			,'Supprimer'=>$user->rights->curriculumvitae->myactions->ajoutRemuneration?"<a onclick=\"if (window.confirm('Voulez vous supprimer l\'élément ?')){document.location.href='?fk_user=@fk_user@&id=@ID@&type=prime&action=delete'}\"><img src=\"./img/delete.png\"></a>":''
		)
		,'translate'=>array(
			
		)
		,'hide'=>array('DateCre', 'fk_user')
		,'type'=>array()
		,'liste'=>array(
			'titre'=>'Visualisation de vos primes'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Aucune prime enregistrée"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			,'picto_search'=>'<img src="../../theme/rh/img/search.png">'
		)
		,'title'=>array(
		)
		,'search'=>array(
		)
		,'orderBy'=>$TOrder
		
	));
		if($user->rights->curriculumvitae->myactions->ajoutRemuneration==1){
		?>
		<div class="tabsAction">
			<a class="butAction" href="?&action=new&type=prime&fk_user=<?=$fuser->id?>">Ajouter une prime</a><div style="clear:both"></div>
		</div>
		<?
		}


	$form->end();
	
	_displayChartRemunerations($ATMdb);
	
	_displayFormRemunerationChart();
	print "<br />";
	
	_displayChartPrimes($ATMdb);
	
	print "<br />";
	
	_displayChartRemunerationsPrimes($ATMdb);
	
	llxFooter();
}	

	
function _fiche(&$ATMdb, $remuneration,  $mode) {
	global $db,$user,$langs,$conf;
	llxHeader('','Vos Rémunérations');
	
	$fuser = new User($db);
	$fuser->fetch(isset($_REQUEST['fk_user']) ? $_REQUEST['fk_user'] : $remuneration->fk_user);
	$fuser->getrights();
	
	$head = user_prepare_head($fuser);
	$current_head = 'remuneration';
	dol_fiche_head($head, $current_head, $langs->trans('Utilisateur'),0, 'user');
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $remuneration->getId());
	echo $form->hidden('fk_user', $_REQUEST['fk_user'] ? $_REQUEST['fk_user'] : $user->id);
	echo $form->hidden('entity', $conf->entity);
	echo $form->hidden('action', 'save');

	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/remuneration.tpl.php'
		,array(
		)
		,array(
			'remuneration'=>array(
				'id'=>$remuneration->getId()
				,'date_entreeEntreprise'=>$form->calendrier('', 'date_entreeEntreprise', $remuneration->date_entreeEntreprise, 12)
				,'date_debutRemuneration'=>$form->calendrier('', 'date_debutRemuneration', $remuneration->date_debutRemuneration, 12)
				,'date_finRemuneration'=>$form->calendrier('', 'date_finRemuneration', $remuneration->date_finRemuneration, 12)
				,'bruteAnnuelle'=>$form->texte('','bruteAnnuelle',$remuneration->bruteAnnuelle, 30,100,'','','-')
				,'salaireMensuel'=>$form->texte('','salaireMensuel',$remuneration->salaireMensuel, 30,100,'','','-')
				,'primeAnciennete'=>$form->texte('','primeAnciennete',$remuneration->primeAnciennete, 30,100,'','','-')
				,'primeNoel'=>$form->texte('','primeNoel',$remuneration->primeNoel, 30,100,'','','-')
				,'commission'=>$form->texte('','commission',$remuneration->commission, 30,100,'','','-')
				,'participation'=>$form->texte('','participation',$remuneration->participation, 30,100,'','','-')
				,'autre'=>$form->texte('','autre',$remuneration->autre, 30,100,'','','-')
				,'prevoyancePartSalariale'=>$form->texte('','prevoyancePartSalariale',$remuneration->prevoyancePartSalariale, 30,100,'','','-')
				,'prevoyancePartPatronale'=>$form->texte('','prevoyancePartPatronale',$remuneration->prevoyancePartPatronale, 30,100,'','','-')
				,'urssafPartSalariale'=>$form->texte('','urssafPartSalariale',$remuneration->urssafPartSalariale, 30,100,'','','-')
				,'urssafPartPatronale'=>$form->texte('','urssafPartPatronale',$remuneration->urssafPartPatronale, 30,100,'','','-')
				,'retraitePartSalariale'=>$form->texte('','retraitePartSalariale',$remuneration->retraitePartSalariale, 30,100,'','','-')
				,'retraitePartPatronale'=>$form->texte('','retraitePartPatronale',$remuneration->retraitePartPatronale, 30,100,'','','-')
				,'mutuellePartSalariale'=>$form->texte('','mutuellePartSalariale',$remuneration->mutuellePartSalariale, 30,100,'','','-')
				,'mutuellePartPatronale'=>$form->texte('','mutuellePartPatronale',$remuneration->mutuellePartPatronale, 30,100,'','','-')
				,'diversPartSalariale'=>$form->texte('','diversPartSalariale',$remuneration->diversPartSalariale, 30,100,'','','-')
				,'diversPartPatronale'=>$form->texte('','diversPartPatronale',$remuneration->diversPartPatronale, 30,100,'','','-')
				,'totalRemPatronale'=>$remuneration->diversPartPatronale+$remuneration->mutuellePartPatronale+$remuneration->retraitePartPatronale+$remuneration->urssafPartPatronale+$remuneration->prevoyancePartPatronale
				,'totalRemSalariale'=>$remuneration->diversPartSalariale+$remuneration->mutuellePartSalariale+$remuneration->retraitePartSalariale+$remuneration->urssafPartSalariale+$remuneration->prevoyancePartSalariale
				,'commentaire'=>$form->texte('','commentaire',$remuneration->commentaire, 30,100,'','','')
				,'fk_user'=>$remuneration->fk_user
				,'lieuExperience'=>$form->texte('','lieuExperience',$remuneration->lieuExperience, 30,100,'','','')
				
				,'charges'=>$form->texte('','charges',$remuneration->charges, 30,100,'','','')
				
				
				// Nouveaux champs
				,'nbHeuresMois'=>$form->texte('','nbHeuresMois',$remuneration->nbHeuresMois, 30,100,'','','-')
				,'an'=>$form->texte('','an',$remuneration->an, 30,100,'','','-')
				,'coutGlobal'=>$form->texte('','coutGlobal',$remuneration->coutGlobal, 30,100,'','','-')
				,'cumHeureTrav'=>$form->texte('','cumHeureTrav',$remuneration->cumHeureTrav, 30,100,'','','-')
				,'cumHSup'=>$form->texte('','cumHSup',$remuneration->cumHSup, 30,100,'','','-')
				,'HS'=>$form->texte('','HS',$remuneration->HS, 30,100,'','','-')
				
			)
			,'userCourant'=>array(
				'id'=>$_REQUEST['fk_user'] ? $_REQUEST['fk_user'] : $user->id
				,'ajoutRem'=>$user->rights->curriculumvitae->myactions->ajoutRemuneration
			)
			,'user'=>array(
				'id'=>$fuser->id
				,'lastname'=>$fuser->lastname
				,'firstname'=>$fuser->firstname
			)
			,'view'=>array(
				'mode'=>$mode
			)
		)	
	);
	
	echo $form->end_form();
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}	

	
function _fichePrime(&$ATMdb, $remunerationPrime,  $mode) {
	global $db,$user,$langs,$conf;
	llxHeader('','Vos Rémunérations');
	
	$fuser = new User($db);
	$fuser->fetch(isset($_REQUEST['fk_user']) ? $_REQUEST['fk_user'] : $remunerationPrime->fk_user);
	$fuser->getrights();
	
	$head = user_prepare_head($fuser);
	$current_head = 'remuneration';
	dol_fiche_head($head, $current_head, $langs->trans('Utilisateur'),0, 'user');
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $remunerationPrime->getId());
	echo $form->hidden('fk_user', $_REQUEST['fk_user'] ? $_REQUEST['fk_user'] : $user->id);
	echo $form->hidden('entity', $conf->entity);
	echo $form->hidden('type', $_REQUEST['type']);
	echo $form->hidden('action', 'save');
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/remuneration_prime.tpl.php'
		,array(
		)
		,array(
			'remunerationPrime'=>array(
				'id'=>$remunerationPrime->getId()
				,'date_prime'=>$form->calendrier('', 'date_prime', $remunerationPrime->date_prime, 12)
				,'fk_user_list'=>$form->combo('', 'fk_user', _getUsers(), -1)
				,'montant_prime'=>$form->texte('','montant',$remunerationPrime->montant, 30,100,'','','-')
				,'motif'=>$form->texte('','motif',$remunerationPrime->motif, 30,100,'','','-')
			)
			,'userCourant'=>array(
				'id'=>$_REQUEST['fk_user'] ? $_REQUEST['fk_user'] : $user->id
				,'ajoutRem'=>$user->rights->curriculumvitae->myactions->ajoutRemuneration
			)
			,'user'=>array(
				'id'=>$fuser->id
				,'lastname'=>$fuser->lastname
				,'firstname'=>$fuser->firstname
			)
			,'view'=>array(
				//'type'=>$_REQUEST['type']
				'mode'=>$mode
			)
		)	
	);
	
	echo $form->end_form();
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

/**
 * Retourne un tableau associatif avec en clef id et en valeur nom prenom de tous les user actifs dans dolibarr
 */
function _getUsers() {
	
	global $db;
	
	$TUsers = array();
	
	$sql = "SELECT rowid, lastname, firstname";
	$sql.= " FROM ".MAIN_DB_PREFIX."user";
	$sql.= " WHERE statut=1";
	$resql = $db->query($sql);
	
	while($res = $db->fetch_object($resql)) {
		$TUsers[$res->rowid] = $res->lastname." ".$res->firstname;
	}
	
	return $TUsers;
	
}

function _displayChartRemunerations(&$ATMdb) {
	
	global $conf,$langs,$db;
	
	$langs->load('report@report');
	dol_include_once("/report/class/dashboard.class.php");
	//llxHeader('', '', '', '', 0, 0, array('http://www.google.com/jsapi'));
	
	$title = $langs->trans('Graphiques des rémunérations');
	print_fiche_titre($title, '', 'report.png@report');
	
	$dash=new TReport_dashboard;
	//$dash->initByCode($ATMdb, 'SALAIREMOIS');
	
	$sql = "SELECT DATE_FORMAT(date_debutRemuneration, \"%Y-%m\" ) AS 'mois', SUM( salaireMensuel ) AS 'Salaire', commentaire as 'Commentaire' FROM ".MAIN_DB_PREFIX."rh_remuneration WHERE fk_user=".$_REQUEST['fk_user']." GROUP BY `mois`";
	$sql_moy = "SELECT DATE_FORMAT(date_debutRemuneration, \"%Y-%m\" ) AS 'mois', AVG( salaireMensuel ) AS 'Salaire moyen' FROM ".MAIN_DB_PREFIX."rh_remuneration GROUP BY `mois`";
	$sql_min = "SELECT DATE_FORMAT(date_debutRemuneration, \"%Y-%m\" ) AS 'mois', MIN( salaireMensuel ) AS 'Salaire minimum' FROM ".MAIN_DB_PREFIX."rh_remuneration GROUP BY `mois`";
	$sql_max = "SELECT DATE_FORMAT(date_debutRemuneration, \"%Y-%m\" ) AS 'mois', MAX( salaireMensuel ) AS 'Salaire maximum' FROM ".MAIN_DB_PREFIX."rh_remuneration GROUP BY `mois`";
	
	$TData = array(0=>array(
					'code'=>'SALAIREMOIS'
					,'yDataKey' => 'Salaire'
					,'sql'=>$sql
					,'complement' => 'Commentaire'
					,'hauteur'=>dolibarr_get_const($db, 'COMPETENCE_HAUTEURGRAPHIQUES')
					)/*,
				   1=>array(
				     'code'=>'SALAIREMOIS'
				     ,'yDataKey' => 'Salaire moyen'
				     ,'sql'=>$sql_moy
				     ,'hauteur'=>dolibarr_get_const($db, 'COMPETENCE_HAUTEURGRAPHIQUES')
				   ),
				   2=>array(
				     'code'=>'SALAIREMOIS'
				     ,'yDataKey' => 'Salaire minimum'
				     ,'sql'=>$sql_min
				     ,'hauteur'=>dolibarr_get_const($db, 'COMPETENCE_HAUTEURGRAPHIQUES')
				   ),
				   3=>array(
				     'code'=>'SALAIREMOIS'
				     ,'yDataKey' => 'Salaire maximum'
				     ,'sql'=>$sql_max
				     ,'hauteur'=>dolibarr_get_const($db, 'COMPETENCE_HAUTEURGRAPHIQUES')
				   )*/
				);
	
	// On rajoute au graphique la courbe correspondant au salaire associé à la grille de salaire du poste du user courant
	_addLineGrilleRemunerationAssociee($ATMdb, $TData);
	
	if(isset($_REQUEST['fk_usergroup'])) _addLinesGroup($TData, $_REQUEST['fk_usergroup']); 
	
	$dash->concat_title = false;
	
	$dash->initByData($ATMdb, $TData);
	//$dash->dataSource[0] = strtr($dash->dataSource[0], array("__iduser__"=>$_REQUEST['fk_user']));
		
	?><div id="chart_remunerations" style="height:<?=$dash->hauteur?>px; margin-bottom:20px;"></div><?
			
	$dash->get('chart_remunerations');
	
}

function _displayChartPrimes(&$ATMdb) {
	
	global $conf,$langs,$db;
	
	$langs->load('report@report');
	dol_include_once("/report/class/dashboard.class.php");
	//llxHeader('', '', '', '', 0, 0, array('http://www.google.com/jsapi'));
	
	$title = $langs->trans('Graphiques des primes');
	print_fiche_titre($title, '', 'report.png@report');
	
	$dash=new TReport_dashboard;
	//$dash->initByCode($ATMdb, 'PRIMESMOIS');
	$sql = "SELECT DATE_FORMAT(date_prime, \"%Y-%m\" ) AS 'mois', SUM( montant ) AS 'Montant prime' , motif as 'Motif' FROM ".MAIN_DB_PREFIX."rh_remuneration_prime WHERE fk_user=".$_REQUEST['fk_user']." GROUP BY `mois`";
	$sql_moy = "SELECT DATE_FORMAT(date_prime, \"%Y-%m\" ) AS 'mois', AVG( montant ) AS 'Montant prime moyen' FROM ".MAIN_DB_PREFIX."rh_remuneration_prime GROUP BY `mois`";
	$sql_min = "SELECT DATE_FORMAT(date_prime, \"%Y-%m\" ) AS 'mois', MIN( montant ) AS 'Montant prime minimum' FROM ".MAIN_DB_PREFIX."rh_remuneration_prime GROUP BY `mois`";
	$sql_max = "SELECT DATE_FORMAT(date_prime, \"%Y-%m\" ) AS 'mois', MAX( montant ) AS 'Montant prime maximum' FROM ".MAIN_DB_PREFIX."rh_remuneration_prime GROUP BY `mois`";
	
	$TData = array(0=>array(
					'code'=>'PRIMESMOIS'
					,'yDataKey' => 'Montant prime'
					,'sql'=>$sql
					,'complement' => 'Motif'
					,'hauteur'=>dolibarr_get_const($db, 'COMPETENCE_HAUTEURGRAPHIQUES')
					)
					 // Pour l'instant on s'enf out d'avoir la moyenne des primes
					 /*,
				   1=>array(
				     'code'=>'PRIMESMOIS'
				     ,'yDataKey' => 'Montant prime moyen'
				     ,'sql'=>$sql_moy
				     ,'hauteur'=>dolibarr_get_const($db, 'COMPETENCE_HAUTEURGRAPHIQUES')
				   ),
				   2=>array(
				     'code'=>'PRIMESMOIS'
				     ,'yDataKey' => 'Montant prime minimum'
				     ,'sql'=>$sql_min
				     ,'hauteur'=>dolibarr_get_const($db, 'COMPETENCE_HAUTEURGRAPHIQUES')
				   ),
				   3=>array(
				     'code'=>'PRIMESMOIS'
				     ,'yDataKey' => 'Montant prime maximum'
				     ,'sql'=>$sql_max
				     ,'hauteur'=>dolibarr_get_const($db, 'COMPETENCE_HAUTEURGRAPHIQUES')
				   )*/
				);
	
	//if(isset($_REQUEST['fk_usergroup'])) _addLinesGroup($TData, $_REQUEST['fk_usergroup'], "prime"); 
	
	$dash->concat_title = false;
	
	$dash->initByData($ATMdb, $TData);
	//$dash->dataSource[0] = strtr($dash->dataSource[0], array("__iduser__"=>$_REQUEST['fk_user']));
			//echo $dash->dataSource[0];exit;
	?><div id="chart_primes" style="height:<?=$dash->hauteur?>px; margin-bottom:20px;"></div><?
			
	$dash->get('chart_primes');
	
}

function _displayChartRemunerationsPrimes(&$ATMdb){
	
	global $conf,$langs,$db;
	
	$langs->load('report@report');
	dol_include_once("/report/class/dashboard.class.php");
	//llxHeader('', '', '', '', 0, 0, array('http://www.google.com/jsapi'));
	
	$title = $langs->trans('Graphiques des rémunérations totale annuelles');
	print_fiche_titre($title, '', 'report.png@report');
	
	$dash=new TReport_dashboard;
	//$dash->initByCode($ATMdb, 'SALAIREMOIS');
	
	$sql = "SELECT DATE_FORMAT(date_debutRemuneration, \"%Y\" ) AS 'annee', SUM( salaireMensuel ) + (
				SELECT SUM( montant ) FROM ".MAIN_DB_PREFIX."rh_remuneration_prime WHERE fk_user=".$_REQUEST['fk_user']." AND DATE_FORMAT(date_prime, \"%Y\" ) = `annee`
			) AS 'Salaire', commentaire as 'Commentaire' 
			FROM ".MAIN_DB_PREFIX."rh_remuneration 
			WHERE fk_user=".$_REQUEST['fk_user']." 
			GROUP BY `annee`";
	
	$sql_moy = "SELECT DATE_FORMAT(date_debutRemuneration, \"%Y\" ) AS 'mois', AVG( salaireMensuel ) AS 'Salaire moyen' FROM ".MAIN_DB_PREFIX."rh_remuneration GROUP BY `annee`";
	$sql_min = "SELECT DATE_FORMAT(date_debutRemuneration, \"%Y\" ) AS 'mois', MIN( salaireMensuel ) AS 'Salaire minimum' FROM ".MAIN_DB_PREFIX."rh_remuneration GROUP BY `annee`";
	$sql_max = "SELECT DATE_FORMAT(date_debutRemuneration, \"%Y\" ) AS 'mois', MAX( salaireMensuel ) AS 'Salaire maximum' FROM ".MAIN_DB_PREFIX."rh_remuneration GROUP BY `annee`";
	
	$TData = array(0=>array(
					'code'=>'SALAIREYEAR'
					,'yDataKey' => 'Salaire'
					,'sql'=>$sql
					,'complement' => 'Commentaire'
					,'hauteur'=>dolibarr_get_const($db, 'COMPETENCE_HAUTEURGRAPHIQUES')
					)/*,
				   1=>array(
				     'code'=>'SALAIREMOIS'
				     ,'yDataKey' => 'Salaire moyen'
				     ,'sql'=>$sql_moy
				     ,'hauteur'=>dolibarr_get_const($db, 'COMPETENCE_HAUTEURGRAPHIQUES')
				   ),
				   2=>array(
				     'code'=>'SALAIREMOIS'
				     ,'yDataKey' => 'Salaire minimum'
				     ,'sql'=>$sql_min
				     ,'hauteur'=>dolibarr_get_const($db, 'COMPETENCE_HAUTEURGRAPHIQUES')
				   ),
				   3=>array(
				     'code'=>'SALAIREMOIS'
				     ,'yDataKey' => 'Salaire maximum'
				     ,'sql'=>$sql_max
				     ,'hauteur'=>dolibarr_get_const($db, 'COMPETENCE_HAUTEURGRAPHIQUES')
				   )*/
				);
	
	// On rajoute au graphique la courbe correspondant au salaire associé à la grille de salaire du poste du user courant
	//_addLineGrilleRemunerationPrimeAssociee($ATMdb, $TData);
	
	if(isset($_REQUEST['fk_usergroup'])) _addLinesGroup($TData, $_REQUEST['fk_usergroup']); 
	
	$dash->concat_title = false;
	
	$dash->initByData($ATMdb, $TData);
	//$dash->dataSource[0] = strtr($dash->dataSource[0], array("__iduser__"=>$_REQUEST['fk_user']));
		
	?><div id="chart_remunerations_primes" style="height:<?php echo $dash->hauteur; ?>px; margin-bottom:20px;"></div><?php
			
	$dash->get('chart_remunerations_primes');
	
	
	
}

function _getUserGroups() {
			
	global $db;
	
	$TGroups = array(0 => "(Sélectionnez un groupe)");
	
    $sql = "SELECT ug.rowid, ug.nom";
    $sql.= " FROM ".MAIN_DB_PREFIX."usergroup as ug";
	$resql = $db->query($sql);
	
	if($resql) {
		while($res = $db->fetch_object($resql)) {
			$TGroups[$res->rowid] = 'Groupe "'.$res->nom.'"';
		}
	}
	
	return $TGroups;
	
}

function _displayFormRemunerationChart() {

	$form = new TFormCore("", "formRemunerationChart");
	print $form->btsubmit("Comparer chiffres","subFormRemunerationChart");
	print $form->combo($pLib, "fk_usergroup", _getUserGroups(), $_REQUEST['fk_usergroup']);
	
	print '</form>';
	
}

function _addLinesGroup(&$TData, $fk_usergroup, $type="remuneration") {
		
	global $db;
	
	if($type == "remuneration") {
		
		$sql = "SELECT DATE_FORMAT(r.date_debutRemuneration, \"%Y-%m\" ) AS 'mois'
				, AVG( r.salaireMensuel ) AS 'Salaire moyen du groupe' 
				FROM ".MAIN_DB_PREFIX."rh_remuneration r 
				LEFT JOIN ".MAIN_DB_PREFIX."usergroup_user ug ON (r.fk_user = ug.fk_user)
				WHERE ug.fk_usergroup = ".$fk_usergroup." 
				GROUP BY `mois`";
		
		$sql_min = "SELECT DATE_FORMAT(r.date_debutRemuneration, \"%Y-%m\" ) AS 'mois'
					, MIN( r.salaireMensuel ) AS 'Salaire minimum du groupe' 
					FROM ".MAIN_DB_PREFIX."rh_remuneration r
					LEFT JOIN ".MAIN_DB_PREFIX."usergroup_user ug ON (r.fk_user = ug.fk_user)
					WHERE ug.fk_usergroup = ".$fk_usergroup." 
					GROUP BY `mois`";
					
		$sql_max = "SELECT DATE_FORMAT(r.date_debutRemuneration, \"%Y-%m\" ) AS 'mois'
					, MAX( r.salaireMensuel ) AS 'Salaire maximum du groupe' 
					FROM ".MAIN_DB_PREFIX."rh_remuneration r 
					LEFT JOIN ".MAIN_DB_PREFIX."usergroup_user ug ON (r.fk_user = ug.fk_user)
					WHERE ug.fk_usergroup = ".$fk_usergroup."
					GROUP BY `mois`";
		
		$TData[] = array(
							'code'=>'SALAIREMOIS'
							,'yDataKey' => 'Salaire moyen du groupe'
							,'sql'=>$sql
							,'hauteur'=>dolibarr_get_const($db, 'COMPETENCE_HAUTEURGRAPHIQUES')
						);
		$TData[] = array(
							'code'=>'SALAIREMOIS'
							,'yDataKey' => 'Salaire minimum du groupe'
							,'sql'=>$sql_min
							,'hauteur'=>dolibarr_get_const($db, 'COMPETENCE_HAUTEURGRAPHIQUES')
						);
		$TData[] = array(
							'code'=>'SALAIREMOIS'
							,'yDataKey' => 'Salaire maximum du groupe'
							,'sql'=>$sql_max
							,'hauteur'=>dolibarr_get_const($db, 'COMPETENCE_HAUTEURGRAPHIQUES')
						);
					
	} elseif($type == "prime") {
		
		$sql = "SELECT DATE_FORMAT(r.date_prime, \"%Y-%m\" ) AS 'mois'
				, AVG( r.montant ) AS 'Montant moyen du groupe' 
				FROM ".MAIN_DB_PREFIX."rh_remuneration_prime r
				LEFT JOIN ".MAIN_DB_PREFIX."usergroup_user ug ON (r.fk_user = ug.fk_user)
				WHERE ug.fk_usergroup = ".$fk_usergroup." 
				GROUP BY `mois`";
		
		$TData[] = array(
						'code'=>'PRIMESMOIS'
						,'yDataKey' => 'Montant moyen du groupe'
						,'sql'=>$sql
						,'hauteur'=>dolibarr_get_const($db, 'COMPETENCE_HAUTEURGRAPHIQUES')
					);
					
	}
		
}

function _addLineGrilleRemunerationAssociee(&$ATMdb, &$TData) {
	
	global $db;
	
	// On récupère le type de poste de l'utilisateur
	$u = new User($db);
	$u->fetch($_REQUEST['fk_user']);
	$u->fetch_optionals($u->id);
	
	$id_type_poste = $u->array_options['options_type_poste'];
	
	$nb_annees_anciennete = _getAnneesAncienneteuser($u);
	
	if($nb_annees_anciennete === false) return false;
	
	if(empty($id_type_poste)) return false;
	else {
		
		$id_grille = _getIDGrilleSalaireCorrespondante($id_type_poste, $nb_annees_anciennete);
		
	}
	
	if($id_grille === false) return false;
	else {
		
		$grille = new TRH_grilleSalaire;
		$grille->load($ATMdb, $id_grille);	
		
		$sql = "SELECT DATE_FORMAT(r.date_debutRemuneration, \"%Y-%m\") AS 'mois', ";
		$sql.= $grille->montant." as 'Niveau de la grille de salaires'"; 
		$sql.= "FROM ".MAIN_DB_PREFIX."rh_remuneration r";

		$TData[] = array(
							'code'=>'SALAIREMOIS'
							,'yDataKey' => 'Niveau de la grille de salaires'
							,'sql'=>$sql
							,'hauteur'=>dolibarr_get_const($db, 'COMPETENCE_HAUTEURGRAPHIQUES')
						);
		
	}
	
}

/**
 * retourne le nombre d'années d'acienneté de l'utilisateur passé en param
 * @param object : user dont on veut récup le nombre d'années d'ancienneté
 * @return int : nombre d'années d'ancienneté
 */
function _getAnneesAncienneteuser(&$u) {
	
	$date_anciennete_user = $u->array_options['options_DDA'];
	$date_du_jour = date('Y-m-d');
	
	if(empty($date_anciennete_user)) return false;
	
	$dt_jour = new DateTime($date_du_jour);
	$dt_anciennete = new DateTime($date_anciennete_user);
	
	$intervale = $dt_anciennete->diff($dt_jour);
	
	$nb_annees_anciennete = $intervale->y;
	
	return $nb_annees_anciennete;
	
}

/**
 * retourne l'id de la grille de salaires par laquelle est concerné l'utilisateur courant en fonction de son type de poste e de son ancienneté
 * @param $id_type_poste int : id du poste
 * @param $nb_annees_anciennete_user int : nb années ancienneté user courant
 * @return $id_grille int : id de la grille de salaire
 * 
 */
function _getIDGrilleSalaireCorrespondante($id_type_poste, $nb_annees_anciennete_user) {
	
	global $db;
	
	$TResult = array();
	
	$sql = "SELECT rowid, nb_annees_anciennete ";
	$sql.= "FROM ".MAIN_DB_PREFIX."rh_grille_salaire ";
	$sql.= "WHERE fk_type_poste = ".$id_type_poste." ";
	$sql.= "ORDER BY nb_annees_anciennete DESC";
	
	$resql = $db->query($sql);
	
	while($res = $db->fetch_object($resql)) {
				
		$TResult[$res->rowid] = $res->nb_annees_anciennete;
		
	}
	
	if(count($TResult) == 0) return false;
	else {
		
		foreach ($TResult as $id_grille => $nb_annees_anciennete) {
			
			if($nb_annees_anciennete_user > $nb_annees_anciennete) return $id_grille;
			
		}
		
	}
	
	return false;
	
}

/**
 * Empêche ou autorise l'accès à la liste des rémunération des utilisateurs des groupes dont fait partie le user courant
 * En fonction du droit défini
 */
function _bloqueAffichageSelonDroitUtilisateurGroupe() {
	
	global $user,$db;
	
	// 3 solutions :
	
	//**************************************************************************************************************
	// 1 - Le user courant est admin, dans ce cas, l'accès n'est pas restreint, l'admin peut voir toutes les remunérations
	if($user->admin) return true;
	//**************************************************************************************************************
	
	
	//**************************************************************************************************************
	// 2 - Le user courant n'est pas admin et n'a pas le droit de voir les rémunérations des user de son/ses groupes, dans ce cas on bloque l'affichage si $_REQUEST['fk_user'] !== $user->id
	if(!$user->rights->curriculumvitae->myactions->voirRemunerationGroupe && $_REQUEST['fk_user'] != $user->id)
		accessforbidden('', 0);
	//**************************************************************************************************************
	
	
	//**************************************************************************************************************
	// 3 - Le user courant n'est pas admin mais a le droit de voir les rémunérations des user de son/ses groupes, dans ce cas on on cherche les groupes du user courant et on regarde si le user de la fiche sur laquelle on se trouve fait partie d'au moins l'un d'entre eux, si oui on affiche, sinon on bloque
	if($user->rights->curriculumvitae->myactions->voirRemunerationGroupe) {
		
		$ug = new UserGroup($db);
		
		// On récupère un tableau des groupes dont fait partie l'utilisateur courant
		$TIDGroups_userCourant = array_keys($ug->listGroupsForUser($user->id));
		
		// On récupère un tableau des groupes dont fait partie l'utilisateur en $_REQUEST['fk_user']
		$TIDGroups_userRequest = array_keys($ug->listGroupsForUser($_REQUEST['fk_user']));
		
		// Si l'un des 2 tableaux est vide, ça veut dire que le user en request ne fait forcément pas partie d'un même groupe que le user courant, donc on bloque
		if(count($TIDGroups_userRequest) == 0 || $TIDGroups_userCourant == 0) {
			accessforbidden('', 0);
		} else { // Sinon, on les parcoure jusqu'à ce qu'on trouve un chiffre qui apparait dans les 2 tableaux
		
			foreach($TIDGroups_userCourant as $idGroup_userCourant) {
				foreach($TIDGroups_userRequest as $idGroup_userRequest) {
					// Si un chiffre apparait dans les 2 tableaux, ça veut dire que les 2 user font partie d'au moins un même groupe
					if($idGroup_userCourant == $idGroup_userRequest)
						return true;
				}
			}
			
			accessforbidden('', 0);
			
		}
		
	}
	//**************************************************************************************************************
	
}
