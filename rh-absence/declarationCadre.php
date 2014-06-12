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
		$d->load_by_date($ATMdb, $date_ref);
		
		$d->set_date('date_ref', $date_ref);
		$d->fk_user = $fk_user;
		$d->nb_hour = $_POST['nb_hour']; 
	
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
	echo $formother->select_month(__get('month',-1,'integer'),'month',1);
	$formother->select_year(__get('year',-1,'integer') ,'year',1, 20, 1);
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
				<th>Date</th>
				<th>Jour travaillé</th>
				<th>Jour de repos</th>
			</tr>
		<?
		
		$total=0;
		//var_dump($TStat);
		foreach($TStat as $date=>$stat) {
			
			$total+=$stat['nb_heure_presence'];
			$date_ligne = $langs->trans(date('l', strtotime($date))) ;
			$heure_ligne = convertSecondToTime( $stat['nb_heure_presence'] * 3600 );
			
			if($stat['nb_jour_ferie']>0) {
				$raison = 'Férié';
			}
			else if($stat['estUnJourTravaille']=='NON') {
				$raison = 'Jour non travaillé';
			}
			else if($stat['estUnJourTravaille']!='OUI' && $stat['estUnJourTravaille']!='NON' && $stat['absence']==0) {
				$raison = 'Demi-journée travaillée';
			}
			else if($stat['typeAbsence']=='non') {
				$raison = '-';
			}
			else {
				$raison = $stat['typeAbsence'];			
			}
	
			$TLigne[]=array(
				'date'=>$date_ligne
				,'nb_heure_presence'=>$heure_ligne
				,'raison'=>utf8_decode($raison)
			)
	
			?><tr>
				<td><?	echo date('d', strtotime($date)).' '.$date_ligne; ?></td>
				<td align="right"><? echo $heure_ligne ?></td>
				<td><? echo $raison; ?></td>
			</tr><?
		
		}
		
		?>
		<tr>
			<th>Total</th>
			<th align="right"><?=convertSecondToTime( $total * 3600, 'allhourmin' ) ?></th>
			<th>  </th>
		</tr>
		</table>
		<?
		
		echo $form->hidden('nb_hour', $total);
		
		echo '<p align="right">';
		echo $form->bt('Signer ces temps', 'bt_sign', 'onclick="signFeuille()"');
		echo $form->btsubmit('Télécharger', 'bt_gen');
		echo '</p><br />';
		
	
		$l=new TListviewTBS('listDeclaration');
		
		print $l->render($ATMdb, "SELECT nb_hour,date_ref 
								FROM ".MAIN_DB_PREFIX."rh_declaration_temps
								WHERE fk_user=".$fk_user." ORDER BY date_ref DESC",array(
					'title'=>array(
						'nb_hour'=>"Nombre d'heures déclarées"
						,'date_ref'=>'Mois déclaré' 
					)
					,'eval'=>array(
						'date_ref'=>' date("m/Y", strtotime("@val@"))'
						,'nb_hour'=>' convertSecondToTime( @val@ * 3600, "allhourmin" ) '
					)
					,'liste'=>array(
						'titre'=>"Liste des heures déclarées"
						
					)			
				)) ;
	
	}
	
	if(__get('action')=='SHOW' || __get('action')=='SAVE') {
		
		if(isset($_REQUEST['bt_gen'])) {
			ob_clean();
			
			$u=new User($db);
			$u->fetch($fk_user);
			
			$TBS=new TTemplateTBS();
			
			$TBS->render('./tpl/feuille-temps-cadre.docx'
				,array(
					'ligne'=>$TLigne
				)
				,array(
					'tpl'=>array(
						'username'=>$u->getFullName($langs)
						,'date'=>date('d/m/Y')
						,'dateMY'=>date('m/Y', $t_debut)
						,'ville'=>$conf->mycompany->ville
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
