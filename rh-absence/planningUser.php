<?php
	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	$langs->load('absence@absence');
	
	$ATMdb=new Tdb;
	$absence=new TRH_Absence;

	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'recherche':
				_planningResult($ATMdb,$absence,'edit');
				break;
			case 'view':
				_fiche($ATMdb,$absence, 'edit');
				break;
			case 'edit':
				
				break;
			
		}
	}
	else if(isset($_REQUEST['valider'])){
		_planningResult($ATMdb,$absence, 'edit');
	}
	else{
		_planningResult($ATMdb,$absence, 'edit');
	}
	
	$ATMdb->close();
	
	llxFooter();
	
	

function _fiche(&$ATMdb, $absence,  $mode) {
	global $db,$user, $langs, $conf;
	llxHeader('','Planning des collaborateurs');

	print dol_get_fiche_head(adminRecherchePrepareHead($absence, '')  , '', 'Planning');
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('fk_user', $user->id);
	echo $form->hidden('entity', $conf->entity);
	
	$idGroupeRecherche=isset($_REQUEST['groupe']) ? $_REQUEST['groupe'] : 0;
	
	//tableau pour la combobox des groupes
	$TGroupe  = array();
	$TGroupe[0]  = 'Tous';
	$sqlReq="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup WHERE entity IN (0,".$conf->entity.")";
	$ATMdb->Execute($sqlReq);
	while($ATMdb->Get_line()) {
		$TGroupe[$ATMdb->Get_field('rowid')] = htmlentities($ATMdb->Get_field('nom'), ENT_COMPAT , 'ISO8859-1');
	}
	
		
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/planningUser.tpl.php'
		,array(
			
		)
		,array(
			'recherche'=>array(
				'TGroupe'=>$form->combo('','groupe',$TGroupe,$idGroupeRecherche)
				,'btValider'=>$form->btsubmit('Valider', 'valider')
				,'date_debut'=> $form->calendrier('', 'date_debut', time(), 12)
				,'date_fin'=> $form->calendrier('', 'date_fin', strtotime('+7day',time()), 12)
				,'titreRecherche'=>load_fiche_titre("Planning des collaborateurs",'', 'title.png', 0, '')
				,'titrePlanning'=>load_fiche_titre("Planning des collaborateurs",'', 'title.png', 0, '')
				
			)
			,'userCourant'=>array(
				'id'=>$fuser->id
				,'nom'=>$fuser->lastname
				,'prenom'=>$fuser->firstname
				,'droitRecherche'=>$user->rights->absence->myactions->rechercherAbsence?1:0
			)
			,'view'=>array(
				'mode'=>'view'
				,'head'=>dol_get_fiche_head(adminRecherchePrepareHead($absence, '')  , '', 'Planning')
			)
		)	
	);
	
	echo $form->end_form();
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

	
function _planningResult(&$ATMdb, &$absence, $mode) {
	global $langs, $conf, $db, $user;	
	llxHeader('','Récapitulatif');
	print dol_get_fiche_head(adminRecherchePrepareHead($absence, '')  , '', 'Planning');

	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','GET');
	$form->Set_typeaff($mode);
	echo $form->hidden('fk_user', $user->id);
	echo $form->hidden('entity', $conf->entity);
	
	if(isset($_REQUEST['groupe'])) $idGroupeRecherche=$_REQUEST['idGroupeRecherche'];
	if(isset($_REQUEST['date_debut'])) $date_debut=$_REQUEST['date_debut'];
	if(isset($_REQUEST['date_fin'])) $date_fin=$_REQUEST['date_fin'];

	$idGroupeRecherche=$_REQUEST['groupe'];
	
	
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

	$TGroupe  = array();
	$TGroupe[0]  = 'Tous';
	$sqlReq="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup WHERE entity IN (0,".$conf->entity.")";
	$ATMdb->Execute($sqlReq);
	while($ATMdb->Get_line()) {
		$TGroupe[$ATMdb->Get_field('rowid')] = htmlentities($ATMdb->Get_field('nom'), ENT_COMPAT , 'ISO8859-1');
	}
	
	
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/planningUser.tpl.php'
		,array(
		)
		,array(
			'recherche'=>array(
				'TGroupe'=>$form->combo('','groupe',$TGroupe,$idGroupeRecherche)
				,'btValider'=>$form->btsubmit('Valider', 'valider')
				,'date_debut'=> $form->calendrier('', 'date_debut', $date_debut, 12)
				,'date_fin'=> $form->calendrier('', 'date_fin', $date_fin, 12)
				,'titreRecherche'=>load_fiche_titre("Récapitulatif de la recherche",'', 'title.png', 0, '')
				,'titrePlanning'=>load_fiche_titre("Planning des collaborateurs",'', 'title.png', 0, '')
			)
			,'userCourant'=>array(
				'id'=>$fuser->id
				,'nom'=>$fuser->lastname
				,'prenom'=>$fuser->firstname
				,'droitRecherche'=>$user->rights->absence->myactions->rechercherAbsence?1:0
			)
			,'view'=>array(
				'mode'=>$mode
				,'head'=>dol_get_fiche_head(adminRecherchePrepareHead($absence, '')  , '', 'Planning')
			)
		)	
	);
	
	
	
	?><style type="text/css">
	table.planning {
		border-collapse:collapse; border:1px solid #ccc; font-size:9px;
	}		
	table.planning td {
		border:1px solid #ccc;
	}	
	
	table.planning tr:nth-child(even) {
		background: #ddd;
	}
	table.planning tr:nth-child(odd) {
		background: #fff;
	}
	
	table.planning tr td.rouge{
			background-color:#C03000;
	}
	table.planning tr td.rougeRTT {
			background-color:#d87a00;
	}
			
	</style>
	<?
	
	
	$absence->set_date('debut_debut_planning', $_REQUEST['date_debut']);
	$absence->set_date('debut_fin_planning', $_REQUEST['date_fin']);
	
	$t_current = $absence->debut_debut_planning;
	
	while($t_current<=$absence->debut_fin_planning) {
		
		if($t_current==$absence->debut_debut_planning) {
			$date_debut =date('d/m/Y', $absence->debut_debut_planning);	
		}
		else {
			$date_debut =date('01/m/Y', $t_current);	
		}
		
		if($t_current==$absence->debut_fin_planning) {
			$date_fin =date('d/m/Y', $absence->debut_fin_planning);	
		}
		else {
			$date_fin =date('t/m/Y', $t_current);	
		}
		
		_planning($ATMdb, $absence, $date_debut, $date_fin );
	
		
		$t_current=strtotime('+1 month', $t_current);
	}
	
	
	echo $form->end_form();
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
	
}	

function _planning(&$ATMdb, &$absence, $date_debut, $date_fin) {
	
//on va obtenir la requête correspondant à la recherche désirée
	$TPlanningUser=$absence->requetePlanningAbsence($ATMdb, $idGroupeRecherche, $date_debut, $date_fin);
	
	print '<table class="planning" border="0">';
	print "<tr>";
	print "<td ></td>";
	foreach($TPlanningUser as $planning=>$val){
		print '<td colspan="2">'.substr($planning,0,5).'</td>';
		foreach($val as $id=>$present){
			$tabUserMisEnForme[$id][$planning]=$present;	
		}
	}
	print "</tr>";
	
	foreach($tabUserMisEnForme as $id=>$planning){
		$sql="SELECT name, firstname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$id;
		$ATMdb->Execute($sql);
		if($ATMdb->Get_line()) {
			$name = htmlentities($ATMdb->Get_field('name'), ENT_COMPAT , 'ISO8859-1')." ".htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1');
		}
		print '<tr >';		
		print '<td style="text-align:right; font-weight:bold;height:20px;" nowrap="nowrap">'.$name.'</td>';
		foreach($planning as $ouinon){
			if($ouinon=='non'){
				print '<td style="text-align:center;" colspan="2">&nbsp;</td>';
			}else{
				$boucleOk=0;
				
				$labelAbs = substr($ouinon,0,-5);
				
				$class = (strpos($ouinon, 'RTT')!==false) ? 'rougeRTT' : 'rouge';
				
				if(strpos($ouinon,'DAM')!==false){
						print '<td class="'.$class.'" title="'.$labelAbs.'" colspan="2">&nbsp;</td>';
				}	
				else if(strpos($ouinon,'DPM')!==false){
						print '<td class="vert">&nbsp;</td>
						<td class="'.$class.'" title="'.$labelAbs.'">&nbsp;</td>';
				}	
				else if(strpos($ouinon,'FAM')!==false){
						print '<td class="'.$class.'"  title="'.$labelAbs.'">&nbsp;</td>
						<td class="vert" >&nbsp;</td>';
				}
				else if(strpos($ouinon,'FPM')!==false){
						print '<td class="'.$class.'" title="'.$labelAbs.'" colspan="2">&nbsp;</td>';
				}
				else if(strpos($ouinon,'AM')!==false){
						print '<td class="'.$class.'"  title="'.$labelAbs.'">&nbsp;</td>
						<td class="vert" >&nbsp;</td>';
				}
				else if(strpos($ouinon,'PM')!==false){
						print '<td class="vert" >&nbsp;</td>
						<td class="'.$class.'"  title="'.$labelAbs.'">&nbsp;</td>';
				}
				else {
						print '<td class="'.$class.'" title="'.$ouinon.'" colspan="2">&nbsp;</td>';
				}
			}
		}
		
		print "</tr>";
	}
	
	print '</table><p>&nbsp;</p>';
	
}


