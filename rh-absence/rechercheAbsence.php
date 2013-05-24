<?php
	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	$langs->load('absence@absence');
	
	$ATMdb=new Tdb;
	$absence=new TRH_Absence;

	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			
			case 'view':
				
				break;
			case 'edit':
				
				break;
		}
	}
	else if(isset($_REQUEST['valider'])){
		_listeResult($ATMdb,$absence);
	}
	else{
		_fiche($ATMdb,$absence, 'edit');
	}
	
	$ATMdb->close();
	
	llxFooter();
	
	

function _fiche(&$ATMdb, $absence,  $mode) {
	global $db,$user, $langs, $conf;
	llxHeader('','Formations');

	print dol_get_fiche_head(adminRecherchePrepareHead($absence, '')  , '', 'Recherche');
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('fk_user', $user->id);
	echo $form->hidden('entity', $conf->entity);

	
	$idTagRecherche=isset($_REQUEST['libelle']) ? $_REQUEST['libelle'] : 0;
	$idGroupeRecherche=isset($_REQUEST['groupe']) ? $_REQUEST['groupe'] : 0;
	$idUserRecherche=isset($_REQUEST['user']) ? $_REQUEST['user'] : 0;

	
	//tableau pour la combobox des groupes
	$TGroupe  = array();
	$sqlReq="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup WHERE entity IN (0,".$conf->entity.")";
	$ATMdb->Execute($sqlReq);
	while($ATMdb->Get_line()) {
		$TGroupe[$ATMdb->Get_field('rowid')] = htmlentities($ATMdb->Get_field('nom'), ENT_COMPAT , 'ISO8859-1');
	}
	
	//tableau pour la combobox des utilisateurs
	$TUser=array();
	$TUser[0]='Tous';
	$sqlReqUser="SELECT u.rowid, u.name,  u.firstname FROM `".MAIN_DB_PREFIX."user` as u, ".MAIN_DB_PREFIX."usergroup_user as g
	 WHERE u.entity IN (0,".$conf->entity.")";
	if($idGroupeRecherche!=0){
		$sqlReqUser.=" AND g.fk_user=u.rowid AND g.fk_usergroup=".$idGroupeRecherche;
	}
	$ATMdb->Execute($sqlReqUser);
	while($ATMdb->Get_line()) {
		$TUser[$ATMdb->Get_field('rowid')]=htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1')." ".htmlentities($ATMdb->Get_field('name'), ENT_COMPAT , 'ISO8859-1');
	}
		
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/rechercheAbsence.tpl.php'
		,array(
			
		)
		,array(
			'recherche'=>array(
				'TGroupe'=>$form->combo('','groupe',$TGroupe,$idGroupeRecherche)
				,'TUser'=>$form->combo('','user',$TUser,$idUserRecherche)
				,'btValider'=>$form->btsubmit('Valider', 'valider')
				,'date_debut'=> $form->calendrier('', 'date_debut', $absence->get_date('date_debut'), 10)
				,'date_fin'=> $form->calendrier('', 'date_fin', $absence->get_date('date_fin'), 10)
				,'horsConges'=>$form->checkbox1('','horsConges','1','')
				,'titreRecherche'=>load_fiche_titre("Recherche des absences des collaborateurs",'', 'title.png', 0, '')
			)
			,'userCourant'=>array(
				'id'=>$fuser->id
				,'nom'=>$fuser->lastname
				,'prenom'=>$fuser->firstname
				,'droitRecherche'=>$user->rights->absence->myactions->rechercherAbsence?1:0
			)
			,'view'=>array(
				'mode'=>$mode
				,'head'=>dol_get_fiche_head(adminRecherchePrepareHead($absence, '')  , '', 'Recherche')
			)
		)	
	);
	
	echo $form->end_form();
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

	
function _listeResult(&$ATMdb, &$absence) {
	global $langs, $conf, $db, $user;	
	llxHeader('','Récapitulatif');
	print dol_get_fiche_head(adminRecherchePrepareHead($absence, '')  , '', 'Recherche');
	
	$r = new TSSRenderControler($absence);
	
	$idGroupeRecherche=$_REQUEST['groupe'];
	$idUserRecherche=$_REQUEST['user'];
	
	if($idGroupeRecherche!=0){	//on recherche le nom du groupe
		$sql="SELECT nom FROM ".MAIN_DB_PREFIX."usergroup
		WHERE rowid =".$idGroupeRecherche." AND entity IN (0,".$conf->entity.")";
		$ATMdb->Execute($sql);
		while($ATMdb->Get_line()) {
			$nomGroupeRecherche=$ATMdb->Get_field('nom');
		}
	}else{
		$nomGroupeRecherche='Tous';
	}

	
	if($idUserRecherche!=0){	//on recherche le nom de l'utilisateur
		$sql="SELECT name,  firstname FROM ".MAIN_DB_PREFIX."user
		WHERE rowid =".$idUserRecherche." AND entity IN (0,".$conf->entity.")";
		$ATMdb->Execute($sql);
		while($ATMdb->Get_line()) {
			$nomUserRecherche=htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1')." ".htmlentities($ATMdb->Get_field('name'), ENT_COMPAT , 'ISO8859-1');
		}
	}else{
		$nomUserRecherche='Tous';
	}
	$horsConges=$_REQUEST['horsConges']==1?'1':'0';
	if($horsConges==1){
		$typeRecherche='Ceux qui n\'ont pas pris de congés pendant cette période';
	}else $typeRecherche='Absences durant cette période';
		
	
	print load_fiche_titre("Mots clés utilisés",'', 'title.png', 0, '');
	?>
	<div>			
		<br/>
		<table class="border" style="width:100%">	
			<tr>
				<td colspan="2"><b>Mots clés utilisés</b></td>	
			</tr>
			<tr>
				<td style="width:30%"> Date début </td>
				<td ><? echo $_REQUEST['date_debut'];?></td>
			</tr>
			<tr>
				<td style="width:30%"> Date Fin </td>
				<td><? echo $_REQUEST['date_fin'];  ?></td>
			</tr>
			<tr>
				<td style="width:30%"> Groupe </td>
				<td><?echo $nomGroupeRecherche;?></td>
			</tr> 
			<tr>
				<td style="width:30%"> Utilisateur </td>
				<td><?echo $nomUserRecherche;?></td>
			</tr> 
			<tr>
				<td style="width:30%"> Type de recherche</td>
				<td><?echo $typeRecherche;?></td>
			</tr> 
		</table>	
	</div><br/><br/>
	<?

	//on va obtenir la requête correspondant à la recherche désirée
	$sql=$absence->requeteRechercheAbsence($ATMdb, $idGroupeRecherche, $idUserRecherche, $horsConges, $_REQUEST['date_debut'], $_REQUEST['date_fin']);
	
	
	$TOrder = array('name'=>'ASC');
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
		)
		,'translate'=>array('libelleEtat'=>array(
			'Refusée'=>'<b style="color:#A72947">Refusée</b>',
			'En attente de validation'=>'<b style="color:#5691F9">	En attente de validation</b>' , 
			'Acceptée'=>'<b style="color:#30B300">Acceptée</b>')
			,'avertissement'=>array('1'=>'<img src="./img/warning.png" title="Ne respecte pas les règles en vigueur"></img>')
		)
		,'hide'=>array('fk_user')
		,'type'=>array('date_debut'=>'date', 'date_fin'=>'date')
		,'liste'=>array(
			'titre'=>'Résultat de votre recherche'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','previous.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Il n'y a aucune absence à afficher"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			,'picto_search'=>'<img src="../../theme/rh/img/search.png">'
			
		)
		,'title'=>array(
			'date_debut'=>'Date début'
			,'date_fin'=>'Date fin'
			,'libelle'=>'Type d\'absence'
			,'firstname'=>'Prénom'
			,'name'=>'Nom'
			,'libelleEtat'=>'Statut demande'
		)
		,'search'=>array(
		)
		,'eval'=>array(
				'name'=>'htmlentities("@val@", ENT_COMPAT , "ISO8859-1")'
				,'firstname'=>'htmlentities("@val@", ENT_COMPAT , "ISO8859-1")'
		)
		,'orderBy'=>$TOrder
		
	));
	
	$form->end();
	?><a class="butAction" href="?">Retour</a><div style="clear:both"></div><?
	
	llxFooter();
}	

