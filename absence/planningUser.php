<?php
	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	$langs->load('absence@absence');
	
	$ATMdb=new TPDOdb;
	$absence=new TRH_Absence;


	_planningResult($ATMdb,$absence, 'edit');
	
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
	
	$TStatPlanning=array();
	
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
	
	
	
	?>
	<div id="plannings" style="background-color:#fff">
		
	<style type="text/css">

	table.planning tr td.jourTravailleNON {
			background:url("./img/fond_hachure_01.gif");
	}
	table.planning tr td[rel=pm].jourTravailleAM {
			background:url("./img/fond_hachure_01.gif");
	}
	table.planning tr td[rel=am].jourTravaillePM {
			background:url("./img/fond_hachure_01.gif");
	}

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
			background:none;
			background-color:#666;
	}
	
			
	</style>
	
	<?
	
	if(!empty( $_REQUEST['date_debut'] ) || $idUserRecherche>0) {
		
		if($idUserRecherche>0 && empty( $_REQUEST['date_debut'] )) {
			
			$absence->debut_debut_planning = strtotime( date('Y-m-01', strtotime('-1 month') ) );
			$absence->debut_fin_planning = strtotime( date('Y-m-t', strtotime('+3 month') ) );
	
		}
		else {
			$absence->set_date('debut_debut_planning', $_REQUEST['date_debut']);
			$absence->set_date('debut_fin_planning', $_REQUEST['date_fin']);
			
		}
		
		
		$t_current = $absence->debut_debut_planning;
		
		$annee_old = '';
		
		while($t_current<=$absence->debut_fin_planning) {
			
			$annee = date('Y', $t_current);
			
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
			
			if($annee!=$annee_old) print '<p style="text-align:left;font-weight:bold">'.$annee.'</strong><br />';
			
			_planning($ATMdb, $absence, $idGroupeRecherche, $idUserRecherche, $date_debut, $date_fin, $TStatPlanning );
		
			$annee_old = $annee;
		
			
			$t_current=strtotime('+1 month', $t_current);
		}
	}
	
	_recap_abs($TStatPlanning);
	
	echo $form->end_form();
	
	?></div>
	
	
	<?
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
	
}	

function _recap_abs($TStatPlanning) {
global $db;	
	
	print '<table class="planning" border="0">';
	print "<tr class=\"entete\">";
	

	$first=true;

	foreach($TStatPlanning as $idUser=>$stat) {
		$u=new User($db);
		$u->fetch($idUser);
		
		
		if($first) {
			
			print '<tr>
				<td>Nom</td>
				<td>Présence (jour)</td>
				<td>Absence</td>
				<td>Présence+Férié</td>
				<td>Absence+Férié</td>
				<td>Férié</td>
				
				
			</tr>';
			
			$first = false;
		}
		
		print '<tr><td>'.$u->getNomUrl().'</td>';
		
		print '<td>'.$stat['presence'].'</td>';
		print '<td>'.$stat['absence'].'</td>';
		print '<td>'.$stat['presence+ferie'].'</td>';
		print '<td>'.$stat['absence+ferie'].'</td>';
		print '<td>'.$stat['ferie'].'</td></tr>';
		
		
	}
	

	print '</table><p>&nbsp;</p>';

}

function _planning(&$ATMdb, &$absence, $idGroupeRecherche, $idUserRecherche, $date_debut, $date_fin, &$TStatPlanning) {
	
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
	
	foreach($tabUserMisEnForme as $idUser=>$planning){
			
		$sql="SELECT lastname, firstname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$idUser;
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
			if(TRH_JoursFeries::estFerie($ATMdb, $std->get_date('date_jour','Y-m-d') )) { $isFerie = 1; $class .= ' jourFerie';  } else { $isFerie = 0; }	
			
			$estUnJourTravaille = TRH_EmploiTemps::estTravaille($ATMdb, $idUser, $std->get_date('date_jour','Y-m-d')); // OUI/NON/AM/PM
			$classTravail= ' jourTravaille'.$estUnJourTravaille;
			
			
			if(!isset($TStatPlanning[$idUser]))$TStatPlanning[$idUser]=array(
				'presence'=>0
				,'absence'=>0
				,'absence+ferie'=>0
				,'presence+ferie'=>0
				,'ferie'=>0
			);
			
			if($isFerie && $estUnJourTravaille!='NON') { $TStatPlanning[$idUser]['ferie']++; }
			
			if($ouinon=='non'){
				print '<td class="'.$class.$classTravail.'" rel="am">&nbsp;</td>
					<td class="'.$class.$classTravail.'" rel="pm">&nbsp;</td>';
					
				if($estUnJourTravaille=='AM' || $estUnJourTravaille=='PM')$TStatPlanning[$idUser]['presence']+=0.5;
				else if($estUnJourTravaille=='OUI')$TStatPlanning[$idUser]['presence']+=1;
						
			}else{
				$boucleOk=0;
				
				$labelAbs = substr($ouinon,0,-5);
				
				$class .= (strpos($ouinon, 'RTT')!==false) ? ' rougeRTT' : ' rouge';
				if(!empty($class))$class.= ' classfortooltip';
				
				if(strpos($ouinon,'DAM')!==false){
						print '<td class="'.$class.$classTravail.'" title="'.$labelAbs.'" rel="am">&nbsp;</td>
						<td class="'.$class.$classTravail.'" title="'.$labelAbs.'" rel="pm">&nbsp;</td>';

					if($estUnJourTravaille=='AM' || $estUnJourTravaille=='PM')$TStatPlanning[$idUser]['absence']+=0.5;
					else if($estUnJourTravaille=='OUI')$TStatPlanning[$idUser]['absence']+=1;

				}	
				else if(strpos($ouinon,'DPM')!==false){
						print '<td class="vert'.$classTravail.'" rel="am">&nbsp;</td>
						<td class="'.$class.$classTravail.'" title="'.$labelAbs.'" rel="pm">&nbsp;</td>';

					if($estUnJourTravaille=='PM' || $estUnJourTravaille=='OUI')$TStatPlanning[$idUser]['absence']+=0.5;
					if($estUnJourTravaille=='AM' || $estUnJourTravaille=='OUI')$TStatPlanning[$idUser]['presence']+=0.5;


				}	
				else if(strpos($ouinon,'FAM')!==false){
						print '<td class="'.$class.$classTravail.'"  title="'.$labelAbs.'" rel="am">&nbsp;</td>
						<td class="vert'.$classTravail.'"  rel="pm">&nbsp;</td>';

					if($estUnJourTravaille=='AM' || $estUnJourTravaille=='OUI')$TStatPlanning[$idUser]['absence']+=0.5;
					if($estUnJourTravaille=='PM' || $estUnJourTravaille=='OUI')$TStatPlanning[$idUser]['presence']+=0.5;


				}
				else if(strpos($ouinon,'FPM')!==false){
						print '<td class="'.$class.$classTravail.'" title="'.$labelAbs.'" rel="am">&nbsp;</td>
						<td class="'.$class.$classTravail.'" title="'.$labelAbs.'" rel="pm">&nbsp;</td>';


					if($estUnJourTravaille=='AM' || $estUnJourTravaille=='PM')$TStatPlanning[$idUser]['absence']+=0.5;
					else if($estUnJourTravaille=='OUI')$TStatPlanning[$idUser]['absence']+=1;

				}
				else if(strpos($ouinon,'AM')!==false){
						print '<td class="'.$class.$classTravail.'"  title="'.$labelAbs.'" rel="am">&nbsp;</td>
						<td class="vert'.$classTravail.'"  rel="pm">&nbsp;</td>';
						
					if($estUnJourTravaille=='AM' || $estUnJourTravaille=='OUI') $TStatPlanning[$idUser]['absence']+=0.5;
					if($estUnJourTravaille=='PM' || $estUnJourTravaille=='OUI')$TStatPlanning[$idUser]['presence']+=0.5;
				}
				else if(strpos($ouinon,'PM')!==false){
						print '<td class="vert'.$classTravail.'" rel="am">&nbsp;</td>
						<td class="'.$class.$classTravail.'" title="'.$labelAbs.'" rel="pm">&nbsp;</td>';

					if($estUnJourTravaille=='PM' || $estUnJourTravaille=='OUI') $TStatPlanning[$idUser]['absence']+=0.5;
					if($estUnJourTravaille=='AM' || $estUnJourTravaille=='OUI')$TStatPlanning[$idUser]['presence']+=0.5;

				}
				else {
					print '<td class="'.$class.$classTravail.'" title="'.$ouinon.'" rel="am">&nbsp;</td>
					<td class="'.$class.$classTravail.'"  rel="pm">&nbsp;</td>';
						
					if($estUnJourTravaille=='AM' || $estUnJourTravaille=='PM')$TStatPlanning[$idUser]['absence']+=0.5;
					else if($estUnJourTravaille=='OUI')$TStatPlanning[$idUser]['absence']+=1;	
				}
			}

			$TStatPlanning[$idUser]['absence+ferie'] = $TStatPlanning[$idUser]['absence'] + $TStatPlanning[$idUser]['ferie'];  
			$TStatPlanning[$idUser]['presence+ferie'] = $TStatPlanning[$idUser]['presence'] + $TStatPlanning[$idUser]['ferie'];

		}
		
		print "</tr>";
	}
	
	print '</table><p>&nbsp;</p>';
	
}


