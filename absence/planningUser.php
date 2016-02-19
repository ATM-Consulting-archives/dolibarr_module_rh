<?php
	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	$langs->load('absence@absence');
	
	$ATMdb=new TPDOdb;
	$absence=new TRH_Absence;

	_planningResult($ATMdb,$absence, 'edit');
	
	$ATMdb->close();
	
	
function _planningResult(&$ATMdb, &$absence, $mode) {
	global $langs, $conf, $db, $user;
	/*echo $form->hidden('fk_user', $user->id);
	echo $form->hidden('entity', $conf->entity);
	*/
	$date_debut=strtotime( date('Y-m-01') );
	$date_fin=strtotime( date('Y-m-t') );
	$idGroupeRecherche=$idGroupeRecherche2=$idGroupeRecherche3=0;
	$idUserRecherche = (GETPOST('mode')=='auto') ? $user->id : 0;
	
	if(!isset($_GET['actionSearch'])) {
		
		if(!empty($_COOKIE['TRHPlanning']) ){
				
			$idGroupeRecherche=$_COOKIE['TRHPlanning']['groupe'];
			$idGroupeRecherche2=$_COOKIE['TRHPlanning']['groupe2'];
			$idGroupeRecherche3=$_COOKIE['TRHPlanning']['groupe3'];
			$idUserRecherche = $_COOKIE['TRHPlanning']['fk_user'];
			
			if(!empty($_COOKIE['TRHPlanning']['date_debut_search'])) {
				$date_debut=$_COOKIE['TRHPlanning']['date_debut_search'];
				$date_debut_recherche = $date_debut;
			}

			if(!empty($_COOKIE['TRHPlanning']['date_fin_search'])) {
				$date_fin=$_COOKIE['TRHPlanning']['date_fin_search'];
				$date_fin_recherche = $date_fin;
			}
		} 
		
	}
	else{
		
	
		if(isset($_REQUEST['groupe'])) {
			$idGroupeRecherche=$_REQUEST['groupe'];
			setcookie('TRHPlanning[groupe]', $idGroupeRecherche,strtotime( '+30 days' ),'/');
			
		}
		
		if(isset($_REQUEST['groupe2'])) {
			$idGroupeRecherche2=$_REQUEST['groupe2'];
			setcookie('TRHPlanning[groupe2]', $idGroupeRecherche2,strtotime( '+30 days' ),'/');
		}
		if(isset($_REQUEST['groupe3'])) {
			$idGroupeRecherche3=$_REQUEST['groupe3'];
			setcookie('TRHPlanning[groupe3]', $idGroupeRecherche3,strtotime( '+30 days' ),'/');
		}
		
		if(isset($_REQUEST['date_debut_search'])) {
			 $date_debut=$_REQUEST['date_debut_search'];
			 $date_debut_recherche = $date_debut;
			 setcookie('TRHPlanning[date_debut_search]', $date_debut,strtotime( '+30 days' ),'/');
		}
		if(isset($_REQUEST['date_fin_search'])) {
			$date_fin=$_REQUEST['date_fin_search'];
			$date_fin_recherche = $date_fin;
			setcookie('TRHPlanning[date_fin_search]', $date_fin,strtotime( '+30 days' ),'/');
		}
		if(isset($_REQUEST['fk_user'])){
			 $idUserRecherche=$_REQUEST['fk_user'];
			 setcookie('TRHPlanning[fk_user]', $idUserRecherche,strtotime( '+30 days' ),'/');
		}
	
	}
	
	
	
	//TODO object USerGroup !
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

	$TGroupe = $TUser = array();
	
	if($user->rights->absence->myactions->voirTousEdt) {

		$TGroupe[0]  = $langs->trans('AllThis');
		$sqlReq="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup WHERE entity IN (0,".$conf->entity.")";
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$TGroupe[$ATMdb->Get_field('rowid')] = htmlentities($ATMdb->Get_field('nom'), ENT_COMPAT , 'ISO8859-1');
		}
		
		$TUser=array($langs->trans('AllThis'));
		$sql=" SELECT DISTINCT u.rowid, u.lastname, u.firstname 
				FROM ".MAIN_DB_PREFIX."user as u LEFT JOIN ".MAIN_DB_PREFIX."usergroup_user as ug ON (u.rowid=ug.fk_user)
				";
	
		if($idGroupeRecherche>0) {
			$sql.=" WHERE ug.fk_usergroup=".$idGroupeRecherche;
		}
		
	}
	elseif($user->rights->absence->myactions->voirGroupesAbsences)  {
		
		$TGroupe[99999]  = $langs->trans('None');
		
		$sqlReq="SELECT g.rowid, g.nom FROM ".MAIN_DB_PREFIX."usergroup g
			LEFT JOIN ".MAIN_DB_PREFIX."usergroup_user as ug ON (g.rowid=ug.fk_usergroup)
		WHERE g.entity IN (0,".$conf->entity.")
		AND ug.fk_user=".$user->id;
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$TGroupe[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('nom');
		}
		
		$TUser[0] = $langs->trans('AllThis');
		$TUser[$user->id] = $user->firstname.' '.$user->lastname;
	}
	else{
		$TUser[$user->id] = $user->firstname.' '.$user->lastname;
	}
	

	$sql.=" ORDER BY u.lastname, u.firstname";
	//print $sql;
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TUser[$ATMdb->Get_field('rowid')]=$ATMdb->Get_field('lastname')." ".$ATMdb->Get_field('firstname');
	}
	
	llxHeader('', $langs->trans('Summary'));
	print dol_get_fiche_head(adminRecherchePrepareHead($absence, '')  , '', $langs->trans('Planning'));

	
	$form=new TFormCore($_SERVER['PHP_SELF'],'formPlanning','GET');
	echo $form->hidden('jsonp', 1);
	echo $form->hidden('actionSearch', 1);
	$form->Set_typeaff($mode);
	
	$TStatPlanning=array();
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/planningUser.tpl.php'
		,array(
		)
		,array(
			'recherche'=>array(
				'TGroupe'=> (empty($TGroupe) ? "Vous n'avez pas les droits pour faire une sélection de groupe" : $form->combo('','groupe',$TGroupe,$idGroupeRecherche).$form->combo('','groupe2',$TGroupe,$idGroupeRecherche2).$form->combo('','groupe3',$TGroupe,$idGroupeRecherche3))
				,'btValider'=>$form->btsubmit($langs->trans('Submit'), 'valider')
				,'TUser'=>$form->combo('','fk_user',$TUser,$idUserRecherche)
				
				,'date_debut'=> $form->calendrier('', 'date_debut_search', $date_debut, 12)
				,'date_fin'=> $form->calendrier('', 'date_fin_search', $date_fin, 12)
				,'titreRecherche'=>load_fiche_titre($langs->trans('SearchSummary'),'', 'title.png', 0, '')
				,'titrePlanning'=>load_fiche_titre($langs->trans('CollabsSchedule'),'', 'title.png', 0, '')
			)
			,'userCourant'=>array(
				'id'=>$fuser->id
				,'nom'=>$fuser->lastname
				,'prenom'=>$fuser->firstname
				,'droitRecherche'=>$user->rights->absence->myactions->rechercherAbsence?1:0
			)
			,'view'=>array(
				'mode'=>$mode
				,'head'=>dol_get_fiche_head(adminRecherchePrepareHead($absence, '')  , '', $langs->trans('Schedule'))
			)
			,'translate' => array(
				'InformSearchAbsencesParameters' => $langs->trans('InformSearchAbsencesParameters'),
				'StartDate' => $langs->trans('StartDate'),
				'EndDate' => $langs->trans('EndDate'),
				'Group' => $langs->trans('Group'),
				'Or' => $langs->trans('Or'),
				'User' => $langs->trans('User')
			)
		)	
	);
	
	
	
	?>
	<div id="plannings" style="background-color:#fff">
		
	<style type="text/css">

	table.planning tr td.jourTravailleNON,table.planning tr td[rel=pm].jourTravailleAM,table.planning tr td[rel=am].jourTravaillePM  {
			background:url("./img/fond_hachure_01.png");
			background-color:#858585; 
	}

	table.planning {
		border-collapse:collapse; border:1px solid #ccc; font-size:9px;
	}		
	table.planning td {
		border:1px solid #ccc;
		text-align: center;
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
	table.planning tr td.vert{
		/*	background:url("./img/fond_hachure_01.png");*/
			background-color:#86ce86;
	}
	table.planning tr td.rougeRTT {
			background-color:#d87a00;
	}
	table.planning tr td.jourFerie {
			background:none;
			background-color:#666;
	}
	
	table.planning tr.footer {
			font-weight:bold;
			background-color:#eee;
	}
	.just-print {
  			display:none;
  	}
  	
	div.bodyline {
		z-index:1050;
	}

    <?php
    for($i=1;$i<=15;$i++) {
    	print ' .persocolor'.$i.' { background-color:'.TRH_TypeAbsence::getColor($i).' !important;  }';
    }
    
    ?>
	@media print {
  	
  		.no-print, #id-left,#tmenu_tooltip,.login_block  {
  			display:none;
  		}
  		.just-print {
  			display:block;
  		}
	}		
	</style>
	
		
	<script type="text/javascript">
	
	function popAddAbsence(date, fk_user) {
		$('#popAbsence').remove();
		$('body').append('<div id="popAbsence"></div>');
		
		var url = "<?php echo dol_buildpath('/absence/absence.php?action=new',1) ?>&dfMoment=apresmidi&ddMoment=matin&fk_user="+fk_user+"&date_debut="+date+"&date_fin="+date+"&popin=1 #fiche-abs";
		
		$('#popAbsence').load(url, function() {
			$('#popAbsence form').submit(function() {
				$.post($(this).attr('action'), $(this).serialize())
					.done(function(data) {
						$.jnotify('<?php echo $langs->trans('AbsenceAdded') ?>', "ok");
					});
			
				$("#popAbsence").dialog('close');
				
				refreshPlanning();

				return false;
		
			});

		});
		
		
		$('#popAbsence').dialog({
			title:"Créer une nouvelle absence ou présence" /* TODO langs */
			,width:500
			,modal:true
		});
	}	

	</script>
	<?php
	
	if(!empty( $_GET['actionSearch'] ) || GETPOST('mode')=='auto' || $idUserRecherche>0) {
		
		if($idUserRecherche>0 && empty( $date_debut_recherche )) {
			
			if(GETPOST('mode')=='auto') {
				$absence->date_debut_planning = $date_debut;
				$absence->date_fin_planning = $date_fin;
						
			}
			else{
				$absence->date_debut_planning = strtotime( date('Y-m-01', strtotime('-1 month') ) );
				$absence->date_fin_planning = strtotime( date('Y-m-t', strtotime('+3 month') ) );
				
			}
	
		}
		else {
			$absence->set_date('date_debut_planning', $date_debut_recherche); 
			$absence->set_date('date_fin_planning',$date_fin_recherche); 
		}
		
		if(GETPOST('jsonp') == 1) {
			
			?><script type="text/javascript">
				
				function refreshPlanning() {
				
					$.ajax({
						url: "script/interface.php"
						,dataType: "jsonp"
						,async: true
			    		,crossDomain: true
						,data: {
							get:'planning'
							,date_debut_search: "<?php echo date('d/m/Y', $absence->date_debut_planning) ?>"
							,date_fin_search: "<?php echo date('d/m/Y', $absence->date_fin_planning) ?>"
							,groupe : <?php echo (int)$idGroupeRecherche ?>
							,groupe2 : <?php echo (int)$idGroupeRecherche2 ?>
							,groupe3 : <?php echo (int)$idGroupeRecherche3 ?>
							,fk_user : <?php echo (int)$idUserRecherche ?>
							,jsonp : 1
							,inc:'main'
						}
						
					})
					.done(function (response) {
						$('#planning_html').html( response ); // server response
						
						$("table.planning td.rouge, table.planning td.vert").each(function() {
				
							$(this).append("<span class=\"just-print\">"+ $(this).attr("title")+"</span>" );
							
						});
						
						$(".classfortooltip").tipTip({maxWidth: "600px", edgeOffset: 10, delay: 50, fadeIn: 50, fadeOut: 50});
					});
				}
					
				refreshPlanning();	
				
			</script>
			<div id="planning_html">
					<img src="img/Loading.gif" width="100%" />
			</div>
			<?php
			
		}
		else{
		
			getPlanningAbsence($ATMdb, $absence, array((int)$idGroupeRecherche,(int)$idGroupeRecherche2,(int)$idGroupeRecherche3), $idUserRecherche);
			
		}
		

	}
	
	
	echo $form->end_form();
	
	?></div>
	
	
	<?php
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
	
	
}	

