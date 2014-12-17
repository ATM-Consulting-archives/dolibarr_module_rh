<?php

	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	dol_include_once('/core/class/html.formother.class.php');
	dol_include_once('/absence/class/pointeuse.class.php');
	
	$langs->load('absence@absence');
	
	$ATMdb=new TPDOdb;
	$absence=new TRH_Absence;

	$fk_user = $user->id;

	if(__get('action')=='SAVE') {
		
		$d=new TRH_declarationTemps;
		$date_ref = $_POST['year'].'-'.$_POST['month'].'-01';
		if(!$d->load_by_date($ATMdb, $date_ref)) {
			// première signature
			
			if($_POST['nb_hour_diff']!=0) {
				
				$nb_hour_per_day = !empty($conf->global->TIMESHEET_WORKING_HOUR_PER_DAY) ? $conf->global->TIMESHEET_WORKING_HOUR_PER_DAY : 7;
				
				$recupSum = round($_POST['nb_hour_diff'] / $nb_hour_per_day,2);
				
				$compteur=new TRH_Compteur;
				$compteur->load_by_fkuser($ATMdb, $fk_user);
				$compteur->add($ATMdb, 'recup', -$recupSum, 'Récupération suite à la validation feuille de temps');
				
				setEventMessage("Votre compteur de récupération a été incrémenté de ".$recupSum." jour(s)");
				
			}
			
		}
		
		$d->set_date('date_ref', $date_ref);
		$d->fk_user = $fk_user;
		$d->nb_hour = $_POST['nb_hour']; 
		$d->nb_hour_diff = $_POST['nb_hour_diff']; 
	
		$d->save($ATMdb);
		
	}

	_fiche($ATMdb, $fk_user);
	
	$ATMdb->close();
	
	
function _fiche(&$ATMdb, $fk_user) {
global $db,$langs,$conf;	
	
	ob_start();
	
	llxHeader();
	
	$form=new TFormCore('auto', 'formDeclaration');
	echo $form->hidden('action','SHOW');
	
	$formother = new FormOther($db);
	
	$selectmonth = __get('month', date('m', strtotime('-1month')  ) ,'integer');
	$selectyear = __get('year', date('Y', strtotime('-1month')  ),'integer');
	
	
	echo $formother->select_month($selectmonth,'month',1);
	$formother->select_year( $selectyear,'year',1, 20, 1);
	echo $form->btsubmit('Ok', 'bt_ok');
	
	$TLigne=array();
	
	if(__get('year')>0) {
		
		$t_debut = strtotime(__get('year').'-'.__get('month').'-01');
		
		$TStatPlanning = TRH_Absence::getPlanning($ATMdb, 0, $fk_user,  date('Y-m-d', $t_debut) , date('Y-m-t', $t_debut));
		list($dummy,$TStat) = each($TStatPlanning);

		?>
		<script type="text/javascript">
			function signFeuille() {
				
				if(window.confirm("<?=$langs->transnoentities('SigningDeclarationTime') ?>")) {
					
					document.forms['formDeclaration'].elements['action'].value="SAVE";
					document.forms['formDeclaration'].submit();
					
				}
				
			}
			
		</script>
		
		<table class="border" width="100%">
			<tr>
				<th><?php echo $langs->trans('Date'); ?></th>
				<th><?php echo $langs->trans('WorkedDays'); ?></th>
				<th><?php echo $langs->trans('DifferencielHeure'); ?></th>
				<th><?php echo $langs->trans('RestDays'); ?></th>
				
			</tr>
		<?php
		
		$total=$totaldiff=0;
		
		foreach($TStat as $date=>$stat) {
			//var_dump(TRH_Pointeuse::tempsPresenceDuJour($ATMdb, $fk_user,$date));
			
			if($conf->global->RH_ABSENCE_USE_THEORICTIME_FOR_DECLARATION) $nb_heure_travaille_ce_jour = $stat['nb_heure_presence'];
			else $nb_heure_travaille_ce_jour = $stat['nb_heure_presence_reelle'];
			
			$total += $nb_heure_travaille_ce_jour;
			$totaldiff += $stat['nb_heure_suplementaire'];
		
			$date_ligne = $langs->transnoentities(date('l', strtotime($date))) ;
			$heure_ligne = convertSecondToTime( $nb_heure_travaille_ce_jour * 3600 );
			
			if($stat['nb_jour_ferie']>0) {
				$raison = $langs->transnoentities('PublicHoliday');
			}
			else if($stat['estUnJourTravaille']=='NON' && $stat['nb_heure_presence'] == 0) {
				$raison = $langs->transnoentities('NoWorkedDays');
			}
			else if($stat['estUnJourTravaille']!='OUI' && $stat['estUnJourTravaille']!='NON' && $stat['absence']==0) {
				$raison = $langs->transnoentities('WorkedHalfDay');
			}
			else if($stat['typeAbsence']=='non') {
				$raison = '-';
			}
			else {
				$raison = html_entity_decode( $stat['typeAbsence'] );			
			}
	
			$TLigne[]=array(
				'date'=>$date_ligne
				,'nb_heure_presence'=>$heure_ligne
				,'raison'=>utf8_decode($raison)
			)
	
			?><tr>
				<td><?php	echo date('d', strtotime($date)).' '.$date_ligne; ?></td>
				<td align="right"><?php echo $heure_ligne ?></td>
				<td align="right"><?php echo  ( $stat['nb_heure_suplementaire']<0?'-':''  ).convertSecondToTime( abs($stat['nb_heure_suplementaire']) * 3600, 'allhourmin' ) ?></td>
				
				<td><?php echo $raison; ?></td>
			</tr><?php
		
		}
		
		?>
		<tr>
			<th>Total</th>
			<th align="right"><?=convertSecondToTime( $total * 3600, 'allhourmin' ) ?></th>
			<th align="right"><?= ( $totaldiff<0?'-':''  ). convertSecondToTime( abs($totaldiff) * 3600, 'allhourmin' ) ?></th>
			<th>  </th>
		</tr>
		</table>
		<?php
		
		echo $form->hidden('nb_hour', $total);
		echo $form->hidden('nb_hour_diff', $totaldiff);
		
		echo '<p align="right">';
		
		$d=new TRH_declarationTemps;
		$date_ref = $_POST['year'].'-'.$_POST['month'].'-01';
		if(!$d->load_by_date($ATMdb, $date_ref)) {
			echo $form->bt($langs->trans('NoteTheseTimes'), 'bt_sign', 'onclick="signFeuille()"');	
		}
		
		echo $form->btsubmit('Télécharger', 'bt_gen');
		echo '</p><br />';
		
	
		$l=new TListviewTBS('listDeclaration');
		
		print $l->render($ATMdb, "SELECT rowid as Id, nb_hour,nb_hour_diff,date_ref, '' as Action  
								FROM ".MAIN_DB_PREFIX."rh_declaration_temps
								WHERE fk_user=".$fk_user." ORDER BY date_ref DESC",array(
					'title'=>array(
						'nb_hour'=> $langs->trans('NbDeclaredHours')
						,'nb_hour_diff'=> $langs->trans('DifferencielHeure')
						,'date_ref'=> $langs->trans('DeclaredMonth') 
					)
					,'eval'=>array(
						'date_ref'=>' date("m/Y", strtotime("@val@"))'
						,'nb_hour'=>' convertSecondToTime( @val@ * 3600, "allhourmin" ) '
					)
					,'link'=>array(
						'Action'=>($user->rights->absence->myactions->deletedeclarationCadre ? '<a href="?action=deleteDeclaration&id=@Id@">'.img_delete().'</a>' : '')
					)
					,'liste'=>array(
						'titre'=> $langs->trans('DeclaredHoursList')
						
					)			
				)) ;
	
	}
	
	if(__get('action')=='SHOW' || __get('action')=='SAVE') {
		
		if(isset($_REQUEST['bt_gen'])) {
			ob_clean(); // TODO AA à quoi ça sert ce truc :/ don't remember ?
			
			$u=new User($db);
			$u->fetch($fk_user);
			
			$TBS=new TTemplateTBS();
			
			$TBS->render('./tpl/feuille-temps-cadre.odt'
				,array(
					'ligne'=>$TLigne
				)
				,array(
					'tpl'=>array(
						'username'=>$u->getFullName($langs)
						,'date'=>date('d/m/Y')
						,'dateMY'=>date('m/Y', $t_debut)
						,'town'=>$conf->mycompany->town
					)
					,'translate' => array(
						'MonthlyStatementAboutCountingWorkedDaysAndTakingDaysOff' => utf8_decode($langs->transnoentities('MonthlyStatementAboutCountingWorkedDaysAndTakingDaysOff')),
						'ManagersInDayPlan' => utf8_decode($langs->transnoentities('ManagersInDayPlan')),
						'EmployeeName' => utf8_decode( $langs->transnoentities('EmployeeName') ),
						'ConcernedMonthMY' => utf8_decode($langs->transnoentities('ConcernedMonthMY')),
						'Date' =>utf8_decode( $langs->transnoentities('Date')),
						'WorkedDays' => utf8_decode($langs->transnoentities('WorkedDays')),
						'RestDays' =>utf8_decode( $langs->transnoentities('RestDays')),
						'Eventually' => utf8_decode($langs->transnoentities('Eventually')),
						'Whether' => utf8_decode($langs->transnoentities('Whether')),
						'PossibleObservations' => utf8_decode($langs->transnoentities('PossibleObservations')),
						'RespectGivenDatasInDeclaration' => utf8_decode($langs->transnoentities('RespectGivenDatasInDeclaration')),
						'To' => utf8_decode($langs->transnoentities('To')),
						'MadeTheDate' => utf8_decode($langs->transnoentities('MadeTheDate', date('d/m/Y'))),
						'Signature' => utf8_decode($langs->transnoentities('Signature'))
					)
				)
			);
			
			exit;
		}
		else{
			print ob_get_clean();	
		}
			
		
	}
	
	$form->end();
	
	llxFooter();
	
}
