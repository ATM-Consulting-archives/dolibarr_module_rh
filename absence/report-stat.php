<?php
	require('config.php');
	require('./class/absence.class.php');
	
	dol_include_once('/core/class/extrafields.class.php');
	
	$langs->load('report@report');
	$langs->load('absence@absence');
	
	$ATMdb=new TPDOdb;
	$absence=new TRH_Absence;
	
	$mesg = '';
	$error=false;
	
	if(!empty($_REQUEST['export'])){
		//On récupère  les données sous forme d'un tableau bien comme il faut
		$TRecap = _get_stat_recap($ATMdb, $_REQUEST['TType'], $_REQUEST['date_debut'], $_REQUEST['date_fin'], $_REQUEST['fk_usergroup'], $_REQUEST['fk_user'],true);
		
		$filename="Export_stats_absence_".date('d-m-Y').".csv";
		
		header("Content-disposition: attachment; filename=$filename");
		header("Content-Type: application/force-download");
		header("Content-Transfer-Encoding: application/octet-stream");
		header("Pragma: no-cache");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public");
		header("Expires: 0");
		
		$arraysize=count($TRecap);
		
		print "Collaborateur;Type Absence;Intitule Absence;Duree en Jours;Duree en Heures;Date de debut; Date de fin;\r\n";
		
		for($k=0;$k<$arraysize;$k++){
			
			//print $TRecap[$k]['trigramme'].";";
			print utf8_decode($TRecap[$k]['nom']).";";
			print $TRecap[$k]['type_absence'].";";
			print html_entity_decode($TRecap[$k]['libelle_absence']).";";
			print $TRecap[$k]['dureeJour'].";";
			print $TRecap[$k]['dureeHeure'].";";
			print $TRecap[$k]['date_debut'].";";
			print $TRecap[$k]['date_fin'].";\r\n";
		}
		
		exit;
	}
	
	_fiche($ATMdb);
	
	$ATMdb->close();
	llxFooter();


function _fiche(&$ATMdb) {
	global $db, $user, $langs, $conf;
	llxHeader('', $langs->trans('AbsenceExports'));
	
	print dol_get_fiche_head(array()  , '', $langs->trans('AbsenceStats'));
	
	$title = $langs->trans('GenerateStatsAbsenceExports');
	print_fiche_titre($title, '', 'report.png@report');

	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','GET');
	
	echo $form->hidden('showStat', 1);
	
	$fk_usergroup= isset($_REQUEST['fk_usergroup']) ? $_REQUEST['fk_usergroup'] : 0;
	$fk_user=$_REQUEST['fk_user']? $_REQUEST['fk_user']:0;
	
	//LISTE DE USERS
	$TUser=array();
	$sql="SELECT u.rowid,u.lastname, u.firstname FROM ".MAIN_DB_PREFIX."user as u ORDER BY u.lastname, u.firstname ";

	$ATMdb->Execute($sql);	
	$TUser[0] = 'Tous';		
	while($ATMdb->Get_line()) {
		$TUser[$ATMdb->Get_field('rowid')] = htmlentities($ATMdb->Get_field('lastname'), ENT_COMPAT , 'ISO8859-1').' '.htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1');
	}
	
	//LISTE DE GROUPES	
	$TGroup=array();
	$TGroup[0] = 'Tous';
	$sql="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup";
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TGroup[$ATMdb->Get_field('rowid')] = htmlentities($ATMdb->Get_field('nom'), ENT_COMPAT , 'ISO8859-1');
	}
	
	//LISTE DES TYPES ABSENCES	
	$TType=array();
	$sql="SELECT typeAbsence, libelleAbsence  FROM `".MAIN_DB_PREFIX."rh_type_absence` ";
	$ATMdb->Execute($sql);
	$k=0;
	while($ATMdb->Get_line()) {
		$type = $ATMdb->Get_field('typeAbsence');
		
		$TType[$k]['libelle'] =$ATMdb->Get_field('libelleAbsence');
		$TType[$k]['type']= $type;
		$TType[$k]['case']=$form->checkbox1('','TType['. $type .']','1',(isset($_REQUEST['TType'][$type]))? '1':'0');
		$k++;
	}
	
	$TRecap=array();
	if(isset($_REQUEST['showStat'])) {
		$TRecap = _get_stat_recap($ATMdb, $_REQUEST['TType'], $_REQUEST['date_debut'], $_REQUEST['date_fin'], $_REQUEST['fk_usergroup'], $_REQUEST['fk_user']);
		$TRecap = _get_stat_recap_format($TRecap);
	}
	

	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/statsAbsence.tpl.php'
		,array(
			'TType'=>$TType
			,'TRecap'=>$TRecap
		)
		,array(
			'exports'=>array(
				'date_debut'=>$form->calendrier('', 'date_debut', __get('date_debut', date('01/m/Y')), 15,10)
				,'date_fin'=>$form->calendrier('', 'date_fin', __get('date_fin', date('t/m/Y')), 15,10)
				,'action'=>$form->hidden('action','save')
				,'fk_user'=>$form->combo('', 'fk_user',$TUser, $fk_user)
				,'fk_group'=>$form->combo('', 'fk_usergroup',$TGroup, $fk_usergroup)
			)
			,'view'=>array(
				'showStat'=>(int)isset($_REQUEST['showStat'])
			)
			,'translate' => array(
				'Groups' => $langs->trans('Groups'),
				'Users' => $langs->trans('Users'),
				'StartDate' => $langs->trans('StartDate'),
				'EndDate' => $langs->trans('EndDate'),
				'UnCheckAllTypes' => $langs->trans('UncheckAllTypes'),
				'CheckAllTypes' => $langs->trans('CheckAllTypes'),
				'Generate' => $langs->trans('Generate'),
				'IncidentsAndEvents' => $langs->trans('IncidentsAndEvents'),
				'StartDate' => $langs->trans('StartDate'),
				'EndDate' => $langs->trans('EndDate'),
				'RealDurationInDays' => $langs->trans('RealDurationInDays'),
				'RealDurationInHours' => $langs->trans('RealDurationInHours'),
				'StartDateOnSlot' => $langs->trans('StartDateOnSlot'),
				'EndDateOnSlot' => $langs->trans('EndDateOnSlot'),
				'DurationOnSlotInDays' => $langs->trans('DurationOnSlotInDays'),
				'DurationOnSlotInHours' => $langs->trans('DurationOnSlotInHours')
			)
		)
	);
	
	print $form->btsubmit('Télécharger','export');
	
	echo $form->end_form();
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

function _get_stat_recap_format(&$TRecap) {
global $db;
	
	$fk_user_last=-1;
	$lastType='';		
			
	$TTotal = array(
		'dureeJour'=>0
			,'dureeHeure'=>0
			,'dureeJourPlage'=>0
			,'dureeHeurePlage'=>0
	
	);		
	$TTotalType=array(
			'dureeJour'=>0
			,'dureeHeure'=>0
			,'dureeJourPlage'=>0
			,'dureeHeurePlage'=>0
	);
	
	
	$Tab=array();
	
	foreach($TRecap as $recap) {
		
			if($recap['fk_user']!=$fk_user_last) {
					
				if($TTotal['dureeJour']>0) {
					$Tab[]=array(
						'event'=>'<h3>TOTAL</h3>'
						,'fk_user'=>0
						,'date_debut'=>''
						,'date_fin'=>''
						,'date_debutPlage'=>''
						,'date_finPlage'=>''
						,'dureeJour'=>'<h3>'.$TTotal['dureeJour'].'</h3>'
						,'dureeHeure'=>'<h3>'.$TTotal['dureeHeure'].'</h3>'
						,'dureeJourPlage'=>'<h3>'.$TTotal['dureeJourPlage'].'</h3>'
						,'dureeHeurePlage'=>'<h3>'.$TTotal['dureeHeurePlage'].'</h3>'
					);
					
				}	
					
				$fk_user_last = $recap['fk_user'];
				$userAbs = new User($db);
				$userAbs->fetch($fk_user_last);
				
				$extrafields=new ExtraFields($db);
				$extralabels=$extrafields->fetch_name_optionals_label('user',true);
				$userAbs->fetch_optionals($userAbs->id, $extralabels);
				
				$compte_tier = $userAbs->array_options["options_COMPTE_TIERS"];
				
				$Tab[]=array(
					'event'=>'<br /><br /><strong>'.$userAbs->firstname.' '.$userAbs->lastname.'</strong> '.$compte_tier 
					,'fk_user'=>0
					,'date_debut'=>''
					,'date_fin'=>''
					,'date_debutPlage'=>''
					,'date_finPlage'=>''
					,'dureeJour'=>''
					,'dureeHeure'=>''
					,'dureeJourPlage'=>''
					,'dureeHeurePlage'=>''
				);
				
			
				$TTotal = array(
					'dureeJour'=>0
						,'dureeHeure'=>0
						,'dureeJourPlage'=>0
						,'dureeHeurePlage'=>0
				
				);		
				
			}
			
			$Tab[]= $recap;
		
			$TTotal['dureeJour']+=$recap['dureeJour'];
			$TTotal['dureeHeure']+=$recap['dureeHeure'];
			$TTotal['dureeJourPlage']+=$recap['dureeJourPlage'];
			$TTotal['dureeHeurePlage']+=$recap['dureeHeurePlage'];

			
		
	}
	
	
	if($TTotal['dureeJour']>0) {
	$Tab[]=array(
		'event'=>'<h3>TOTAL</h3>'
		,'fk_user'=>0
		,'date_debut'=>''
		,'date_fin'=>''
		,'date_debutPlage'=>''
		,'date_finPlage'=>''
		,'dureeJour'=>'<h3>'.$TTotal['dureeJour'].'</h3>'
		,'dureeHeure'=>'<h3>'.$TTotal['dureeHeure'].'</h3>'
		,'dureeJourPlage'=>'<h3>'.$TTotal['dureeJourPlage'].'</h3>'
		,'dureeHeurePlage'=>'<h3>'.$TTotal['dureeHeurePlage'].'</h3>'
	);
		
	}
			
	
	
	return $Tab;
	
	
}

function _get_stat_recap(&$ATMdb, $TType, $date_debut, $date_fin, $fk_usergroup, $fk_user,$export=false){
	global $conf, $db;
	
	$o=new TObjetStd;
	$t_debut_export = $o->set_date('date_debut',$date_debut );
	$t_fin_export = $o->set_date('date_fin',$date_fin );

	
	$sql="SELECT DISTINCT a.rowid
			FROM ".MAIN_DB_PREFIX."rh_absence as a INNER JOIN ".MAIN_DB_PREFIX."user  as u ON (u.rowid=a.fk_user)
					LEFT JOIN ".MAIN_DB_PREFIX."usergroup_user as g ON (u.rowid=g.fk_user)
							LEFT JOIN ".MAIN_DB_PREFIX."rh_absence_emploitemps as e ON (e.fk_user=u.rowid AND e.is_archive=1)
			WHERE a.date_debut<='". date("Y-m-d H:i:s", $t_fin_export)."' 
			AND a.date_fin>='".date("Y-m-d H:i:s", $t_debut_export)."'
			AND a.etat!='Refusee'
			";
	//on traite le cas où l'on recherche un groupe ou un utilisateur seulement
	if($fk_user!=0){
		$sql.=" AND a.fk_user=".$fk_user;
	}else if($fk_usergroup!=0){
		
		$sql.=" AND g.fk_usergroup=".$fk_usergroup." ";
	}else{
		$sql.=" AND a.entity=".$conf->entity;
	}
	
	$sql.=" ORDER BY u.lastname,u.firstname,a.type ";
	
	$ATMdb->Execute($sql);
	
	$TId  =$ATMdb->Get_All();

	$Tab=array();
	
	
	foreach ($TId as $abs) {
		$absence = new TRH_Absence;
		$absence->load($ATMdb, $abs->rowid);
		
		if($TType[$absence->type]) {
		
			$date_debut = $absence->get_date('date_debut');
			$date_fin = $absence->get_date('date_fin');
			
			$dureeJour = $absence->calculDureeAbsenceParAddition($ATMdb);
			$dureeHeure = $absence->dureeHeure;
			
			if($absence->date_debut<$t_debut_export) {
				 $absence->date_debut=$t_debut_export;
				 $absence->ddMoment='matin'; 	 
			}
				 
			if($absence->date_fin>$t_fin_export) {
				$absence->date_fin=$t_fin_export;
				$absence->dfMoment='apresmidi'; 	 
				
			}
			
			$date_debutPlage = $absence->get_date('date_debut');
			$date_finPlage = $absence->get_date('date_fin');
			
			$dureeJourPlage = $absence->calculDureeAbsenceParAddition($ATMdb);
			$dureeHeurePlage = $absence->dureeHeure;
			
			if(!$export){
				$Tab[]=array(
					'event'=>$absence->type
					,'fk_user'=>$absence->fk_user			
					,'date_debut'=>$date_debut
					,'date_fin'=>$date_fin
					,'date_debutPlage'=>$date_debutPlage
					,'date_finPlage'=>$date_finPlage
					,'dureeJour'=>$dureeJour
					,'dureeHeure'=>$dureeHeure
					,'dureeJourPlage'=>$dureeJourPlage
					,'dureeHeurePlage'=>$dureeHeurePlage
				);
			}
			else{
				
				$userAbsence = new User($db);
				$userAbsence->fetch($absence->fk_user);
				
				$Tab[]=array(
					'type_absence'=>$absence->type
					,'libelle_absence'=>$absence->libelle
					,'fk_user'=>$absence->fk_user
					,'trigramme'=>$userAbsence->login
					,'nom'=>($userAbsence->nom) ? $userAbsence->nom." ".$userAbsence->prenom : $userAbsence->lastname." ".$userAbsence->firstname
					,'date_debut'=>$date_debut
					,'date_fin'=>$date_fin
					,'dureeJour'=>$dureeJour
					,'dureeHeure'=>$dureeHeure
				);
			}

			

		}
		
		
	}
	
	
	
	return $Tab;
}
