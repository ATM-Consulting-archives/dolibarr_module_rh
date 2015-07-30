<?php
	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	$langs->load('absence@absence');
	
	$ATMdb=new TPDOdb;
	$absence=new TRH_Absence;

	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'recherche':
				_listeResult($ATMdb,$absence);
				break;
			case 'view':
				_fiche($ATMdb,$absence, 'edit');
				break;
			case 'edit':
				
				break;
			
		}
	}
	else if(isset($_REQUEST['valider'])){
		_listeResult($ATMdb,$absence);
	}
	else{
		_listeResult($ATMdb,$absence);
	}
	
	$ATMdb->close();
	
	llxFooter();
	
	

function _fiche(&$ATMdb, $absence,  $mode) {
	global $db,$user, $langs, $conf;
	llxHeader('','Recherche Absences');

	print dol_get_fiche_head(adminRecherchePrepareHead($absence, '')  , '', $langs->trans('Search'));
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('fk_user', $user->id);
	echo $form->hidden('entity', $conf->entity);

	
	$idTagRecherche=isset($_REQUEST['libelle']) ? $_REQUEST['libelle'] : 0;
	$idGroupeRecherche=isset($_REQUEST['groupe']) ? $_REQUEST['groupe'] : 0;
	$idUserRecherche=isset($_REQUEST['user']) ? $_REQUEST['user'] : 0;
	$typeRecherche=isset($_REQUEST['typeAbsence']) ? $_REQUEST['typeAbsence'] :'Tous';

	
	//tableau pour la combobox des groupes
	$TGroupe  = array();
	$TGroupe[0]  = 'Tous';
	$sqlReq="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup WHERE entity IN (0,".$conf->entity.")";
	$ATMdb->Execute($sqlReq);
	while($ATMdb->Get_line()) {
		$TGroupe[$ATMdb->Get_field('rowid')] = htmlentities($ATMdb->Get_field('nom'), ENT_COMPAT , 'UTF-8');
	}
	
	//tableau pour la combobox des utilisateurs
	$TUser=array();
	$TUser[0]='Tous';
	$sqlReqUser="SELECT u.rowid, u.lastname,  u.firstname FROM `".MAIN_DB_PREFIX."user` as u";

	$ATMdb->Execute($sqlReqUser);
	while($ATMdb->Get_line()) {
		$TUser[$ATMdb->Get_field('rowid')]=htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'UTF-8')." ".htmlentities($ATMdb->Get_field('lastname'), ENT_COMPAT , 'ISO8859-1');
	}
	
	//on récupère tous les types d'absences existants
	$TTypeAbsence=array();
	$TTypeAbsence['Tous']='Tous';
	$sql="SELECT typeAbsence, libelleAbsence  FROM `".MAIN_DB_PREFIX."rh_type_absence` ";
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TTypeAbsence[$ATMdb->Get_field('typeAbsence')]=$ATMdb->Get_field('libelleAbsence');
	}
	
		
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/rechercheAbsence.tpl.php'
		,array(
			
		)
		,array(
			'recherche'=>array(
				'TGroupe'=>$form->combo('','groupe',$TGroupe,$idGroupeRecherche)
				,'TUser'=>$form->combo('','user',$TUser,$idUserRecherche)
				,'TTypeAbsence'=>$form->combo('','typeAbsence',$TTypeAbsence,$typeRecherche)
				,'btValider'=>$form->btsubmit($langs->trans('Submit'), 'valider')
				,'date_debut'=> $form->calendrier('', 'date_debut', $absence->date_debut, 12)
				,'date_fin'=> $form->calendrier('', 'date_fin', $absence->date_fin, 12)
				,'horsConges'=>$form->checkbox1('','horsConges','1','')
				,'titreRecherche'=>load_fiche_titre($langs->trans('SearchCollabsAbsences'),'', 'title.png', 0, '')
				
			)
			,'userCourant'=>array(
				'id'=>$fuser->id
				,'nom'=>$fuser->lastname
				,'prenom'=>$fuser->firstname
				,'droitRecherche'=>$user->rights->absence->myactions->rechercherAbsence?1:0
			)
			,'view'=>array(
				'mode'=>$mode
				,'head'=>dol_get_fiche_head(adminRecherchePrepareHead($absence, '')  , '', $langs->trans('Search'))
			)
			,'translate' => array(
				'InformSearchAbsencesParameters' => $langs->trans('InformSearchAbsencesParameters'),
				'StartDate' => $langs->trans('StartDate'),
				'EndDate' => $langs->trans('EndDate'),
				'Group' => $langs->trans('Group'),
				'User' => $langs->trans('User'),
				'Type' => $langs->trans('Type'),
				'NoHolidays' => $langs->trans('NoHolidays'),
				'NoRightsForSearchCollabAbsences' => $langs->trans('NoRightsForSearchCollabAbsences')
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
	llxHeader('', $langs->trans('Summary'));
	print dol_get_fiche_head(adminRecherchePrepareHead($absence, '')  , '', $langs->trans('Search'));
	
	$r = new TSSRenderControler($absence);
	
	
	if(isset($_REQUEST['groupe'])) $idGroupeRecherche=$_REQUEST['idGroupeRecherche'];
	if(isset($_REQUEST['user'])) $idUserRecherche=$_REQUEST['idUserRecherche'];
	if(isset($_REQUEST['horsConges'])) $horsConges=$_REQUEST['horsConges'];
	if(isset($_REQUEST['date_debut'])) $date_debut=$_REQUEST['date_debut'];
	if(isset($_REQUEST['date_fin'])) $date_fin=$_REQUEST['date_fin'];

	$idGroupeRecherche=$_REQUEST['groupe'];
	$idUserRecherche=$_REQUEST['user'];
	$typeAbsence=$_REQUEST['typeAbsence'];
	
	
	if($idGroupeRecherche!=0){	//	on recherche le nom du groupe
		$sql="SELECT nom FROM ".MAIN_DB_PREFIX."usergroup
		WHERE rowid =".$idGroupeRecherche;
		$ATMdb->Execute($sql);
		while($ATMdb->Get_line()) {
			$nomGroupeRecherche=$ATMdb->Get_field('nom');
		}
	}else{
		$nomGroupeRecherche='Tous';
	}

	
	if($idUserRecherche!=0){	//	on recherche le nom de l'utilisateur
		$sql="SELECT name,  firstname FROM ".MAIN_DB_PREFIX."user
		WHERE rowid =".$idUserRecherche;
		$ATMdb->Execute($sql);
		while($ATMdb->Get_line()) {
			$nomUserRecherche=htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'UTF-8')." ".htmlentities($ATMdb->Get_field('lastname'), ENT_COMPAT , 'UTF-8');
		}
	}else{
		$nomUserRecherche='Tous';
	}
	
	if($typeAbsence!='Tous'){	//	on recherche le type d'absence
		$sql="SELECT libelleAbsence FROM ".MAIN_DB_PREFIX."rh_type_absence
		WHERE typeAbsence LIKE '".$typeAbsence."'";
		$ATMdb->Execute($sql);
		while($ATMdb->Get_line()) {
			$typeAbsenceVisu=$ATMdb->Get_field('libelleAbsence');
		}
	}else{
		$typeAbsenceVisu='Tous';
	}
	
	
	$horsConges=$_REQUEST['horsConges']==1?'1':'0';
	if($horsConges==1){
		$typeRecherche= $langs->trans('ThoseNotTakeHolidaysDuringPeriod');
	}else $typeRecherche= $langs->trans('AbsencesDuringThisPeriod');
		
	
	print load_fiche_titre($langs->trans('KeywordsUsed'),'', 'title.png', 0, '');
	?>
	<div>			
		<br/>
		<table class="border" style="width:100%">	
			<tr>
				<td colspan="2"><b><?php echo $langs->trans('KeywordsUsed'); ?></b></td>	
			</tr>
			<tr>
				<td style="width:30%"><?php echo $langs->trans('StartDate'); ?></td>
				<td ><?php echo $_REQUEST['date_debut'];?></td>
			</tr>
			<tr>
				<td style="width:30%"><?php echo $langs->trans('EndDate'); ?></td>
				<td><?php echo $_REQUEST['date_fin'];  ?></td>
			</tr>
			<tr>
				<td style="width:30%"><?php echo $langs->trans('Group'); ?></td>
				<td><?php echo $nomGroupeRecherche;?></td>
			</tr> 
			<tr>
				<td style="width:30%"><?php echo $langs->trans('User'); ?></td>
				<td><?php echo $nomUserRecherche;?></td>
			</tr> 
			<tr>
				<td style="width:30%"><?php echo $langs->trans('AbsenceType'); ?></td>
				<td><?php echo $typeAbsenceVisu;?></td>
			</tr> 
			<tr>
				<td style="width:30%"><?php echo $langs->trans('SearchType'); ?></td>
				<td><?php echo $typeRecherche;?></td>
			</tr> 
			
		</table>	
	</div><br/><br/>
	<?php

	
	//on va obtenir la requête correspondant à la recherche désirée
	$sql=$absence->requeteRechercheAbsence($ATMdb, $idGroupeRecherche, $idUserRecherche, $horsConges, $_REQUEST['date_debut'], $_REQUEST['date_fin'], $typeAbsence);
	
	
	$TOrder = array('lastname'=>'ASC');
	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
				
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;	
	$form=new TFormCore($_SERVER['PHP_SELF'],'formtranslateList','GET');		
	echo $form->hidden('action','recherche');
	echo $form->hidden('groupe',$idGroupeRecherche);
	echo $form->hidden('user',$idUserRecherche);
	echo $form->hidden('horsConges',$horsConges);
	echo $form->hidden('date_debut',$_REQUEST['date_debut']);
	echo $form->hidden('date_fin',$_REQUEST['date_fin']);
	

	$r->liste($ATMdb, $sql, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>'10000'
		)
		,'link'=>array(
			'libelle'=>'<a href="absence.php?id=@ID@&action=view">@val@</a>'
		)
		,'translate'=>array('libelleEtat'=>array(
			'Refusée'=>'<b style="color:#A72947">' . $langs->trans('Refused') . '</b>',
			'En attente de validation'=>'<b style="color:#5691F9">' . $langs->trans('WaitingValidation') . '</b>' , 
			'Acceptée'=>'<b style="color:#30B300">' . $langs->trans('Accepted') . '</b>')
			,'avertissement'=>array('1'=>'<img src="./img/warning.png" title="' . $langs->trans('DoNotRespectRules') . '"></img>')
		)
		,'hide'=>array('fk_user', 'ID')
		,'type'=>array()
		,'liste'=>array(
			'titre'=> $langs->trans('SearchResult')
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','previous.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=> $langs->trans('MessageNothingAbsence')
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			,'picto_search'=>'<img src="../../theme/rh/img/search.png">'
			
		)
		,'title'=>array(
			'date_debut'=> $langs->trans('StartDate')
			,'date_fin'=> $langs->trans('EndDate')
			,'libelle'=>$langs->trans('AbsenceType')
			,'firstname'=> $langs->trans('FirstName')
			,'lastname'=> $langs->trans('LastName')
			,'login'=> $langs->trans('Login')
			,'libelleEtat'=> $langs->trans('RequestStatus')
		)
		,'search'=>array(
			'login'=>true
			,'lastname'=>true
			,'firstname'=>true
		)
		,'eval'=>array(
				'lastname'=>'htmlentities("@val@", ENT_COMPAT , "UTF-8")'
				,'firstname'=>'htmlentities("@val@", ENT_COMPAT , "UTF-8")'
		)
		,'orderBy'=>$TOrder
		
	));
	
	$form->end();
	?><a class="butAction" href="?action=view"><?php echo $langs->trans('Back'); ?></a><div style="clear:both"></div><?php
	
	llxFooter();
}	


