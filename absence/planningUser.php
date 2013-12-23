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
				_planningResult($ATMdb,$absence,'edit');
				break;
			case 'view':
				_planningResult($ATMdb,$absence, 'edit');
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
	
	
	
function _planningResult(&$ATMdb, &$absence, $mode) {
	global $langs, $conf, $db, $user;	
	llxHeader('','Récapitulatif');
	print dol_get_fiche_head(adminRecherchePrepareHead($absence, '')  , '', 'Planning');

	
	$form=new TFormCore($_SERVER['PHP_SELF'],'formPlanning','GET');
	$form->Set_typeaff($mode);
	/*echo $form->hidden('fk_user', $user->id);
	echo $form->hidden('entity', $conf->entity);
	*/
	$date_debut=time();
	$date_fin=strtotime('+7day');
	$idGroupeRecherche=0;
	$idUserRecherche=0;
	
	if(isset($_REQUEST['groupe'])) $idGroupeRecherche=$_REQUEST['idGroupeRecherche'];
	if(isset($_REQUEST['date_debut'])) $date_debut=$_REQUEST['date_debut'];
	if(isset($_REQUEST['date_fin'])) $date_fin=$_REQUEST['date_fin'];
	if(isset($_REQUEST['fk_user'])) $idUserRecherche=$_REQUEST['fk_user'];

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
	
	$TUser=array('Tous');
	$sql=" SELECT DISTINCT u.rowid, u.lastname, u.firstname 
			FROM ".MAIN_DB_PREFIX."user as u LEFT JOIN ".MAIN_DB_PREFIX."usergroup_user as ug ON (u.rowid=ug.fk_user)
			";

	if($idGroupeRecherche>0) {
		$sql.=" WHERE ug.fk_usergroup=".$idGroupeRecherche;
	}

	$sql.=" ORDER BY u.lastname, u.firstname";
	//print $sql;
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TUser[$ATMdb->Get_field('rowid')]=ucwords(strtolower(htmlentities($ATMdb->Get_field('lastname'), ENT_COMPAT , 'ISO8859-1')))." ".htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1');
	}
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/planningUser.tpl.php'
		,array(
		)
		,array(
			'recherche'=>array(
				'TGroupe'=>$form->combo('','groupe',$TGroupe,$idGroupeRecherche)
				,'btValider'=>$form->btsubmit('Valider', 'valider')
				,'TUser'=>$form->combo('','fk_user',$TUser,$idUserRecherche)
				
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
	table.planning tr td.jourFerie {
			background-color:#666;
	}
			
	</style>
	<?
	
	if(!empty( $_REQUEST['date_debut'] )) {
		
		
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
			
			_planning($ATMdb, $absence, $idGroupeRecherche, $idUserRecherche, $date_debut, $date_fin );
		
			
			$t_current=strtotime('+1 month', $t_current);
		}
	}
	
	
	echo $form->end_form();
	
	?><script type="text/javascript">
		
	/*	$(document).ready(function() {
			
			$('table.planning tr.entete').each(function() {
								
			});
			
		});
		
		function isScrolledIntoView(elem)
		{
		    var docViewTop = $(window).scrollTop();
		    var docViewBottom = docViewTop + $(window).height();
		
		    var elemTop = $(elem).offset().top;
		    var elemBottom = elemTop + $(elem).height();
		
		    return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
		}
		*/
	</script>
	
	
	<?
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
	
}	

function _planning(&$ATMdb, &$absence, $idGroupeRecherche, $idUserRecherche, $date_debut, $date_fin) {
	
//on va obtenir la requête correspondant à la recherche désirée

	$TPlanningUser=$absence->requetePlanningAbsence($ATMdb, $idGroupeRecherche, $idUserRecherche, $date_debut, $date_fin);
	
	print '<table class="planning" border="0">';
	print "<tr class=\"entete\">";
	print "<td ></td>";
	foreach($TPlanningUser as $planning=>$val){
		print '<td colspan="2">'.substr($planning,0,5).'</td>';
		foreach($val as $id=>$present){
			$tabUserMisEnForme[$id][$planning]=$present;	
		}
	}
	print "</tr>";
	/*pre($tabUserMisEnForme);
	exit;*/
	foreach($tabUserMisEnForme as $id=>$planning){
		$sql="SELECT lastname, firstname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$id;
		$ATMdb->Execute($sql);
		if($ATMdb->Get_line()) {
			$name = htmlentities($ATMdb->Get_field('lastname'), ENT_COMPAT , 'ISO8859-1')." ".htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1');
		}
		print '<tr >';		
		print '<td style="text-align:right; font-weight:bold;height:20px;" nowrap="nowrap">'.$name.'</td>';
		foreach($planning as $dateJour=>$ouinon){
			
			$class='';
			
			$std = new TObjetStd;
			$std->set_date('date_jour', $dateJour);
			if(TRH_JoursFeries::estFerie($ATMdb, $std->get_date('date_jour','Y-m-d') )) { $class = 'jourFerie';  }	
			
			if($ouinon=='non'){
				print '<td style="text-align:center;" colspan="2" class="'.$class.'">&nbsp;</td>';
			}else{
				$boucleOk=0;
				
				$labelAbs = substr($ouinon,0,-5);
				
				$class .= (strpos($ouinon, 'RTT')!==false) ? ' rougeRTT' : ' rouge';
				
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


