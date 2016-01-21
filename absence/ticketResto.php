<?php
	require('config.php');
	dol_include_once('/absence/class/absence.class.php');
	dol_include_once('/absence/class/ticket.class.php');
	
	dol_include_once('/absence/lib/absence.lib.php');
	
	$langs->load('absence@absence');
	
	$ATMdb=new TPDOdb;
	$absence=new TRH_Absence;

	switch(__get('action')) {
		case 'GEN_TR' :
		
			if(isset($_POST['Archive'])) {
				_archive_ticket_resto($ATMdb, $_POST['TTicket']);
			}
			elseif(isset($_POST['GenererPrimoclic'])){
				_generate_ticket_resto($ATMdb, $_POST['TTicket'],'primoclic');
			}
			else{
				_generate_ticket_resto($ATMdb, $_POST['TTicket']);	
			}
			break;
	}

	_planningResult($ATMdb,$absence, 'edit');
	
	$ATMdb->close();
	
	llxFooter();
	
function _generate_ticket_resto(&$ATMdb, $Tab, $type = 'standard') {
	global $conf, $langs;
	
	
	if(isset($_REQUEST['bt_sage'])) {
		header('Content-type: application/octet-stream');
	    header('Content-Disposition: attachment; filename=TicketResto-'.date('Y-m-d-h-i-s').'.txt');
	    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

		foreach($Tab as $fk_user=>$row) {

                if($row['nbTicket'] > 0) {
					print "VM"
					.str_pad((int)substr($row['matricule'],3) ,10, ' ')
					."255"
					.str_pad("CL06",10,' ')
					.str_pad( number_format( $row['nbTicket'], 4, ',','' ),12,' ', STR_PAD_LEFT)."\r\n";
				}
		}

	}
	else {
		header('Content-type: application/octet-stream');
	    header('Content-Disposition: attachment; filename=TicketResto-'.date('Y-m-d-h-i-s').'.csv');
	    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		
		if($type != 'primoclic'){
			print $langs->trans('ProductCode') . ';' . $langs->trans('ClientCode') . ';' . $langs->trans('DeliveryPoint') . ';';
			print $langs->trans('Level') . ' 1;' . $langs->trans('Level') . ' 2;' . $langs->trans('EmployeeName') . ';';
			print $langs->trans('EditingNameOnCover') . ';' . $langs->trans('EditingNameOnTitle') . ';' . $langs->trans('FacialValueInCents') . ';';
			print $langs->trans('EmployersShareInCents') . ';' . $langs->trans('NbTitle') . ';' . $langs->trans('CompanyName') . ';';
			print $langs->trans('PostalCode') . ';' . $langs->trans('City') . ';' . $langs->trans('CompanyNameOnBook') . ';';
			print $langs->trans('PostalCodeAndCityOnBook') . ';' . $langs->trans('DeliveryDate') . ";\n";
		}
		else{
			print html_entity_decode($langs->trans('EmployeeName')).';'.$langs->trans('Matricule').';'.$langs->trans('NbTitle').';'.$langs->trans('FacialValueInCents').';';
			print $langs->trans('DeliveryPoint').';'.$langs->trans('Libelle').";\n";
		}
		
		foreach($Tab as $fk_user=>$row) {
			
			if($row['nbTicket'] > 0) {
				
				if($type != 'primoclic'){
					print implode(';',array(
						$conf->global->RH_CODEPRODUIT_TICKET_RESTO
						,(empty($row['code_client']) ? $conf->global->RH_CODECLIENT_TICKET_RESTO : $row['code_client'])
						,$row['pointlivraison']
						,$row['niveau1']
						,$row['niveau2']
						,$row['matricule']
						,$row['name']
						,$row['nomcouv']
						,$row['nomtitre']
						,$conf->global->RH_MONTANT_TICKET_RESTO
						,($conf->global->RH_MONTANT_TICKET_RESTO * ($conf->global->RH_PART_PATRON_TICKET_RESTO / 100) )
						,$row['nbTicket']
						,$row['raisonsociale']
						,$row['cp']
						,$row['ville']
						,$row['rscarnet']
						,$row['cpcarnet']
						,$row['date_distribution']
					))."\n";
				}
				else{
				print implode(';',array(
						$row['name']
						,''
						,$row['nbTicket']
						,$conf->global->RH_MONTANT_TICKET_RESTO
						,$row['pointlivraison']
						,$row['cp']." ".$row['ville']
					))."\n";
				}
				
			}
				
			
		}
	}
	//50;;;;;;;O/N;O/N;700;350;;??;;;O/N;O/N;*/
	exit;
}
	
function _archive_ticket_resto(&$ATMdb, $Tab) {
	global $conf, $langs;
	
	foreach($Tab as $fk_user=>$row) {
		$t=new TRH_TicketResto;
		
		$t->loadByUserDate($ATMdb, $fk_user, date('Y-m-d', Tools::get_time( $row['date_distribution'] )) );
		
		$t->set_values($row);
		$t->fk_user=$fk_user;
		
		$t->montant=$conf->global->RH_MONTANT_TICKET_RESTO;
		$t->partpatron=($conf->global->RH_MONTANT_TICKET_RESTO * ($conf->global->RH_PART_PATRON_TICKET_RESTO / 100) );
		$t->entity = $conf->entity;
		
		$t->code_produit = $conf->global->RH_CODEPRODUIT_TICKET_RESTO;
		$t->code_client = $conf->global->RH_CODECLIENT_TICKET_RESTO;
		
		$t->save($ATMdb);
	}

	setEventMessage($langs->trans('SendingArchivedTicket'));
}
		
function _planningResult(&$ATMdb, &$absence, $mode) {
	global $langs, $conf, $db, $user;	
	llxHeader('', $langs->trans('Summary'));
	print dol_get_fiche_head(adminRecherchePrepareHead($absence, '')  , '', $langs->trans('Schedule'));

	
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
		$TUser[$ATMdb->Get_field('rowid')]=ucwords(strtolower(htmlentities($ATMdb->Get_field('lastname'), ENT_COMPAT , 'UTF-8')))." ".htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'UTF-8');
	}
	
	$TStatPlanning=array();
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/ticketresto.tpl.php'
		,array(
		)
		,array(
			'recherche'=>array(
				'TGroupe'=>$form->combo('','groupe',$TGroupe,$idGroupeRecherche)
				,'btValider'=>$form->btsubmit($langs->trans('Submit'), 'valider')
				,'TUser'=>$form->combo('','fk_user',$TUser,$idUserRecherche)
				
				,'date_debut'=> $form->calendrier('', 'date_debut', $date_debut, 12)
				,'date_fin'=> $form->calendrier('', 'date_fin', $date_fin, 12)
				,'titreRecherche'=>load_fiche_titre($langs->trans('RestaurantTicketsExport'),'', 'title.png', 0, '')
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
				'InformSearchNbTicketParameters' => $langs->trans('InformSearchNbTicketParameters'),
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
	
	<?php
	
	echo $form->end_form();
	
	switch(__get('action')) {
		
		case 'HISTORY':
			_show_history($ATMdb, __get('fk_user'));
			
			break;
		default:
			_ticket($ATMdb);			
	}
	
	
	
	?></div>
	
	
	<?php
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
	
}	

function _ticket(&$ATMdb) {
	global $db,$conf, $langs;	
	
	$form=new TFormCore('auto', 'formTR', 'POST');
	echo $form->hidden('action', 'GEN_TR');
	
	if(__get('date_debut')=='') return false;
	
	$t_debut = Tools::get_time( __get('date_debut',0 ));
	$t_fin = Tools::get_time( __get('date_fin',0 ));
	
	$date_debut = date('Y-m-d', $t_debut);
	$date_fin = date('Y-m-d', $t_fin);
	
	if($t_debut<0) return false;
	
	print "Du ".date('d/m/Y', $t_debut)." au ".date('d/m/Y', $t_fin);

	
	print '<table class="planning" border="0">';
	print '<tr class="entete">';
	
	$idGroup = __get('groupe', 0, 'int');

	$TTicketResto = TRH_TicketResto::getTicketFor($ATMdb, $date_debut, $date_fin, $idGroup, __get('fk_user', 0, 'int'));

	$first=true;

	$TON = array('O'=> $langs->trans('Yes'), 'N'=> $langs->trans('No'));

	dol_include_once('/user/class/usergroup.class.php');

	$group = new UserGroup($db);
	$group->fetch($idGroup);
	
	if(!empty($group->note)) {
		
		$var = explode("\n", $group->note);
		
		$rs =  $var[0];
		$address = $var[1];
		$cp = $var[2];
		$ville = $var[3];
	
		
		$pointlivraison = $var[4];
		$code_client = $var[5];
	}
	else{
		
		$rs =  $conf->global->MAIN_INFO_SOCIETE_NOM;
		$address =  $conf->global->MAIN_INFO_SOCIETE_ADDRESS;
		$cp = $conf->global->MAIN_INFO_SOCIETE_ZIP;
		$ville = $conf->global->MAIN_INFO_SOCIETE_TOWN;
	
		$code_client='';
	}
	
	
	$autoPL = false;
	if(empty($pointlivraison)) {
		$pointlivraison= $rs.' '.$address.' '.$cp.' '.$ville;
		$autoPL = true;
	}

	dol_include_once('/core/class/extrafields.class.php');
    $extrafields = new ExtraFields($db);
    $optionsArray = $extrafields->fetch_name_optionals_label('user');

	foreach($TTicketResto as $idUser=>$stat) {
		$u=new User($db);
		$u->fetch($idUser);
		$u->fetch_optionals($u->id, $optionsArray);
		
		if($first) {
			
			?><tr>
				<td><?php echo $langs->trans('EmployeeName'); ?></td>
				<td><?php echo $langs->trans('PresenceCompleteDay'); ?></td>
				<td><?php echo $langs->trans('MealInExpenseOnCompletePresenceDay'); ?></td>
				<td title="<?php echo $langs->trans('NoCountedByDefault'); ?>"><?php echo $langs->trans('MealinExpenseSuspiciousDeclarationEarlierDate'); ?></td>
				<td><?php echo $langs->trans('NbTitle'); ?></td>
				<td><?php echo $langs->trans('DeliveryPoint'); ?></td>
				<td><?php echo $langs->trans('Level'); ?> 1</td>
				<td><?php echo $langs->trans('Level'); ?> 2</td>
				<td><?php echo $langs->trans('ReferenceNumber'); ?></td>
				<td><?php echo $langs->trans('EditingNameOnCover'); ?></td>
				<td><?php echo $langs->trans('EditingNameOnTitle'); ?></td>
				<td><?php echo $langs->trans('CompanyName'); ?></td>
				<td><?php echo $langs->trans('PostalCode'); ?></td>
				<td><?php echo $langs->trans('City'); ?></td>
				<td><?php echo $langs->trans('CompanyNameOnBook'); ?></td>
				<td><?php echo $langs->trans('PostalCodeAndCityOnBook'); ?></td>
				<td><?php echo $langs->trans('DeliveryDate'); ?></td>
			</tr><?php 
			
			$first = false;
		}
		
		?><tr>
		<td nowrap="nowrap"><?php echo $form->texte('', 'TTicket['.$idUser.'][name]', $u->getFullName($langs), 20,255)
			.'<a href="?action=HISTORY&fk_user='.$idUser.'">'.img_picto($langs->trans('SeeUserPreviousSendings'), 'history.png').'</a>';  
		?></td><?php
		
		if($u->array_options['options_ticketresto_ok']==1) {
			
			if(!empty($u->array_options['options_ticketresto_pointlivraison'])) $pointlivraison = $u->array_options['options_ticketresto_pointlivraison'];
			
			?><td align="right"><?php echo $stat['presence'] ?></td>
			<td align="right"><?php echo $stat['ndf'] ?></td>
			<td align="right"><?php echo !empty($stat['ndf_suspicious']) ? '<strong style="color:red;" class="classfortooltip" title="'.implode(', ', $stat['TRefSuspisious']).'">'.$stat['ndf_suspicious'].'</strong>' : '' ?></td>
			<td align="right"><?php echo $form->texte('', 'TTicket['.$idUser.'][nbTicket]', $stat['presence']-$stat['ndf'], 3)  ?> de <?php echo (int)$conf->global->RH_MONTANT_TICKET_RESTO ?> centimes</td>
			<td align="right"><?php echo $form->texte('', 'TTicket['.$idUser.'][pointlivraison]',$pointlivraison, 10,255).$form->hidden('TTicket['.$idUser.'][code_client]', $code_client)  ?></td>
			<td align="right"><?php echo $form->texte('', 'TTicket['.$idUser.'][niveau1]', '', 10,255)  ?></td>
			<td align="right"><?php echo $form->texte('', 'TTicket['.$idUser.'][niveau2]', '', 10,255)  ?></td>
			<td align="right"><?php echo $form->texte('', 'TTicket['.$idUser.'][matricule]', $u->array_options['options_COMPTE_TIERS'], 10,255)  ?></td>
			<td align="right"><?php echo $form->combo('', 'TTicket['.$idUser.'][nomcouv]', $TON , false)  ?></td>
			<td align="right"><?php echo $form->combo('', 'TTicket['.$idUser.'][nomtitre]', $TON , false)  ?></td>
			<td align="right"><?php echo $form->texte('', 'TTicket['.$idUser.'][raisonsociale]', $rs , 10,255)  ?></td>
			<td align="right"><?php echo $form->texte('', 'TTicket['.$idUser.'][cp]', $cp, 5,255)  ?></td>
			<td align="right"><?php echo $form->texte('', 'TTicket['.$idUser.'][ville]',$ville, 10,255)  ?></td>
			<td align="right"><?php echo $form->combo('', 'TTicket['.$idUser.'][rscarnet]', $TON , false)  ?></td>
			<td align="right"><?php echo $form->combo('', 'TTicket['.$idUser.'][cpcarnet]', $TON , false)  ?></td>
			<td align="right"><?php echo $form->calendrier('', 'TTicket['.$idUser.'][date_distribution]', strtotime('+15day', $t_fin) )  ?></td>
			<?php

		} else {
			
			?>
			<td colspan="16"><?php echo $langs->trans('UserNotSelectedTickets'); ?></td>
			<?php
				
		}
		
		?>
		</tr>
		<?php
	
	}

	?></table><br /><?php
	
	echo $form->btsubmit($langs->trans('GenerateFile'), 'Generer');
	//echo $form->btsubmit('Générer le fichier Sage', 'bt_sage');
	echo $form->btsubmit($langs->trans('GenerateFilePrimoclic'), 'GenererPrimoclic');
	echo $langs->trans('Then');
	echo $form->btsubmit($langs->trans('ArchiveThisSending'), 'Archive');
	
	$form->end();

}

function _show_history(&$ATMdb, $fk_user) {
	global $db, $conf, $langs;

	$THistory = TRH_TicketResto::getHistory($ATMdb, $fk_user);
	
	$u=new User($db);
	$u->fetch($fk_user);
	print $u->getNomUrl(1);
	
	
	print '<table class="planning" border="0">';
	print '<tr class="entete">';
	
	$first=true;

	$TON = array('O'=> $langs->trans('Yes'), 'N'=> $langs->trans('No'));

	foreach($THistory as $t) {
		
		if($first) {
			
			?><tr>
				<td><?php echo $langs->trans('NbTitle'); ?></td>
				<td><?php echo $langs->trans('DeliveryPoint'); ?></td>
				<td><?php echo $langs->trans('Level'); ?> 1</td>
				<td><?php echo $langs->trans('Level'); ?> 2</td>
				<td><?php echo $langs->trans('ReferenceNumber'); ?></td>
				<td><?php echo $langs->trans('EditingNameOnCover'); ?></td>
				<td><?php echo $langs->trans('EditingNameOnTitle'); ?></td>
				<td><?php echo $langs->trans('CompanyName'); ?></td>
				<td><?php echo $langs->trans('PostalCode'); ?></td>
				<td><?php echo $langs->trans('City'); ?></td>
				<td><?php echo $langs->trans('CompanyNameOnBook'); ?></td>
				<td><?php echo $langs->trans('PostalCodeAndCityOnBook'); ?></td>
				<td><?php echo $langs->trans('DeliveryDate'); ?></td>
			</tr><?php 
			
			$first = false;
		}
		
		?><td align="right"><?php echo $t->nbTicket ?></td>
		
		<td align="right"><?php echo  $t->pointlivraison ?></td>
		<td align="right"><?php echo  $t->niveau1 ?></td>
		<td align="right"><?php echo  $t->niveau2 ?></td>
		<td align="right"><?php echo  $t->matricule ?></td>
		<td align="right"><?php echo $t->nomcouv ?></td>
		<td align="right"><?php echo $t->nomtitre ?></td>
		<td align="right"><?php echo $t->raisonsociale ?></td>
		<td align="right"><?php echo $t->cp ?></td>
		<td align="right"><?php echo $t->ville ?></td>
		<td align="right"><?php echo $t->rscarnet  ?></td>
		<td align="right"><?php echo $t->cpcarnet  ?></td>
		<td align="right"><?php echo $t->get_date('date_distribution');  ?></td>
		</tr>
		<?php

	}
	

	?></table><br /><?php
	
	
}

