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

	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
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
	
	
	//on va obtenir la requête correspondant à la recherche désirée
	$TPlanningUser=$absence->requetePlanningAbsence($ATMdb, $idGroupeRecherche, $_REQUEST['date_debut'], $_REQUEST['date_fin']);

	print '<table border="1" >';
	print "<tr>";
	print "<td ></td>";
	foreach($TPlanningUser as $planning=>$val){
		print '<td colspan="2">'.$planning.'</td>';
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
		print '<td style="width:15%;text-align:right;">'.$name.'</td>';
		foreach($planning as $ouinon){
			if($ouinon=='non'){
				print '<td class="vert" style="text-align:center;width:100px;height:20px;"></td><td class="vert" style="text-align:center;width:100px;"></td>';
			}else{
				$boucleOk=0;
				if(strpos($ouinon,'DAM')===false){
				}else{
					if($boucleOk!=1){
						print '<td class="rouge" style="text-align:center;color:#FEFEFE;width:100px;height:20px;">'.substr($ouinon,0,-5).'</td><td class="rouge" style="text-align:center;color:#FEFEFE;width:100px;">'.substr($ouinon,0,-5).'</td>';
						$boucleOk=1;
					}
				}
				
				if(strpos($ouinon,'DPM')===false){
				}else{
					if($boucleOk!=1){
						print '<td class="vert" style="text-align:center;color:#FEFEFE;width:100px;height:20px;"></td><td class="rouge" style="text-align:center;color:#FEFEFE;width:100px;">'.substr($ouinon,0,-5).'</td>';
						$boucleOk=1;
					}
				}	
				
				if(strpos($ouinon,'FAM')===false){
				}else{
					if($boucleOk!=1){
						print '<td class="rouge" style="text-align:center;color:#FEFEFE;width:100px;height:20px;">'.substr($ouinon,0,-5).'</td><td class="vert" style="text-align:center;color:#FEFEFE;width:100px;"></td>';
						$boucleOk=1;
					}
				}
				
				if(strpos($ouinon,'FPM')===false){
				}else{
					if($boucleOk!=1){
						print '<td class="rouge" style="text-align:center;color:#FEFEFE;width:100px;height:20px;">'.substr($ouinon,0,-5).'</td><td class="rouge" style="text-align:center;color:#FEFEFE;width:100px;">'.substr($ouinon,0,-5).'</td>';
						$boucleOk=1;
					}
				}
				if(strpos($ouinon,'AM')===false){
				}else{
					if($boucleOk!=1){
						print '<td class="rouge" style="text-align:center;color:#FEFEFE;width:100px;height:20px;">'.substr($ouinon,0,-5).'</td><td class="vert" style="text-align:center;color:#FEFEFE;width:100px;"></td>';
						$boucleOk=1;
					}
				}
				if(strpos($ouinon,'PM')===false){
				}else {
					if($boucleOk!=1){
						print '<td class="vert" style="text-align:center;color:#FEFEFE;width:100px;height:20px;"></td><td class="rouge" style="text-align:center;color:#FEFEFE;width:100px;">'.substr($ouinon,0,-5).'</td>';
						$boucleOk=1;
					}
				}
				
				if($boucleOk!=1){
						print '<td class="rouge" style="text-align:center;color:#FEFEFE;width:100px;height:20px;">'.$ouinon.'</td><td class="rouge" style="text-align:center;color:#FEFEFE;width:100px;">'.$ouinon.'</td>';
						$boucleOk=1;
				}
			}
		}
		
		print "</tr>";
	}
	
	print '</table>';
				
	?><style>
		.rouge{
			background-color:#C03000;
		}
		.vert{
			background-color:#D8DFDE;
		}
		
	</style><?
	echo $form->end_form();
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
	
}	


