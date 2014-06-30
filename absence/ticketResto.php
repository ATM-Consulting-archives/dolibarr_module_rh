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
			else{
				_generate_ticket_resto($ATMdb, $_POST['TTicket']);	
			}
			
			
			break;
	
		
	}


	_planningResult($ATMdb,$absence, 'edit');	
	
	
	
	$ATMdb->close();
	
	llxFooter();
	
function _generate_ticket_resto(&$ATMdb, $Tab) {
global $conf;
	
	header('Content-type: application/octet-stream');
    header('Content-Disposition: attachment; filename=TicketResto-'.date('Y-m-d-h-i-s').'.csv');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

	print "Code produit;Code Client;Point de livraison;Niveau 1;Niveau 2;Matricule;Nom Salarié;Edition nom sur couverture;Edition nom sur titre;Valeur faciale en centimes;Part patronale en centimes;Nombre de titre;Raison Sociale;Code Postal;Ville;RS sur carnet;CP et Ville sur carnet;Date de livraison;\n";
	
	foreach($Tab as $fk_user=>$row) {
		
		if($row['nbTicket'] > 0) {

			print implode(';',array(
				$conf->global->RH_CODEPRODUIT_TICKET_RESTO
				,$conf->global->RH_CODECLIENT_TICKET_RESTO
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
			
		
	}

	exit;
}
	
function _archive_ticket_resto(&$ATMdb, $Tab) {
global $conf;
	
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

	setEventMessage("Envoi ticket archivé");

}
		
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
	print $TBS->render('./tpl/ticketresto.tpl.php'
		,array(
		)
		,array(
			'recherche'=>array(
				'TGroupe'=>$form->combo('','groupe',$TGroupe,$idGroupeRecherche)
				,'btValider'=>$form->btsubmit('Valider', 'valider')
				,'TUser'=>$form->combo('','fk_user',$TUser,$idUserRecherche)
				
				,'date_debut'=> $form->calendrier('', 'date_debut', $date_debut, 12)
				,'date_fin'=> $form->calendrier('', 'date_fin', $date_fin, 12)
				,'titreRecherche'=>load_fiche_titre("Export des tickets restaurant",'', 'title.png', 0, '')
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
	
	echo $form->end_form();
	
	switch(__get('action')) {
		
		case 'HISTORY':
			_show_history($ATMdb, __get('fk_user'));
			
			break;
		default:
			_ticket($ATMdb);			
	}
	
	
	
	?></div>
	
	
	<?
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
	
}	

function _ticket(&$ATMdb) {
global $db,$conf, $langs;	
	
	$form=new TFormCore('auto', 'formTR', 'POST');
	echo $form->hidden('action', 'GEN_TR');

	$t_debut = Tools::get_time( __get('date_debut',0 ));
	$t_fin = Tools::get_time( __get('date_fin',0 ));
	
	$date_debut = date('Y-m-d', $t_debut);
	$date_fin = date('Y-m-d', $t_fin);
	
	if($t_debut<0) return false;
	
	print "Du ".date('d/m/Y', $t_debut)." au ".date('d/m/Y', $t_fin);

	
	print '<table class="planning" border="0">';
	print '<tr class="entete">';
	
	$idGroup = __get('groupe', 0, 'int');

	$TTicketResto = TRH_TicketResto::getTicketFor($ATMdb, $date_debut, $date_fin, __get('groupe', 0, 'int'), __get('fk_user', 0, 'int'));

	$first=true;

	$TON = array('O'=>'Oui', 'N'=>'Non');

	dol_include_once('/user/class/usergroup.class.php');

	$group = new UserGroup($db);
	$group->fetch($idGroup);
	
	if(!empty($group->note)) {
		
		$var = explode("\n", $group->note);
		
		$rs =  $var[0];
		$cp = $var[1];
		$ville = $var[2];
	
		
	}
	else{
		
		$rs =  $conf->global->MAIN_INFO_SOCIETE_NOM;
		$cp = $conf->global->MAIN_INFO_SOCIETE_ZIP;
		$ville = $conf->global->MAIN_INFO_SOCIETE_TOWN;
	
		
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
				<td>Nom Salarié</td>
				<td>Présence (jour complet)</td>
				<td>Repas passé en NdF (sur jour complet de présence)</td>
				<td>Nombre de titre</td>
				<td>Point de livraison</td>
				<td>Niveau 1</td>
				<td>Niveau 2</td>
				<td>Matricule</td>
				<td>Edition nom sur couverture</td>
				<td>Edition nom sur titre</td>
				<td>Raison Sociale</td>
				<td>Code Postal</td>
				<td>Ville</td>
				<td>RS sur carnet</td>
				<td>CP et Ville sur carnet</td>
				<td>Date de livraison</td>
			</tr><?php 
			
			$first = false;
		}
		
		?><tr>
		<td nowrap="nowrap"><?php echo $form->texte('', 'TTicket['.$idUser.'][name]', $u->getFullName($langs), 20,255)
			.'<a href="?action=HISTORY&fk_user='.$idUser.'">'.img_picto("Voir les envoi précédent de cet utilisateur", 'history.png').'</a>';  
		?></td><?php
		
		if($u->array_options['options_ticketresto_ok']==1) {
			
			?><td align="right"><?php echo $stat['presence'] ?></td>
			<td align="right"><?php echo $stat['ndf'] ?></td>
			<td align="right"><?php echo $form->texte('', 'TTicket['.$idUser.'][nbTicket]', $stat['presence']-$stat['ndf'], 3)  ?> de <?php echo (int)$conf->global->RH_MONTANT_TICKET_RESTO ?> centimes</td>
		
			<td align="right"><?php echo $form->texte('', 'TTicket['.$idUser.'][pointlivraison]', '', 10,255)  ?></td>
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

		}
		else{
			
			?>
			<td colspan="15">Cet utilisateur n'a pas choisi les tickets restaurant</td>
			<?php
				
		}
		
		?></tr><?

	}
	

	?></table><br /><?php
	
	echo $form->btsubmit('Générer le fichier', 'Generer');
	echo ' puis ';
	echo $form->btsubmit('Archiver cet envoi', 'Archive');
	
	$form->end();

}
function _show_history(&$ATMdb, $fk_user) {
global $db,$conf;

	$THistory = TRH_TicketResto::getHistory($ATMdb, $fk_user);
	
	$u=new User($db);
	$u->fetch($fk_user);
	print $u->getNomUrl(1);
	
	
	print '<table class="planning" border="0">';
	print '<tr class="entete">';
	
	$first=true;

	$TON = array('O'=>'Oui', 'N'=>'Non');

	foreach($THistory as $t) {
		
		if($first) {
			
			?><tr>
				<td>Nombre de titre</td>
				<td>Point de livraison</td>
				<td>Niveau 1</td>
				<td>Niveau 2</td>
				<td>Matricule</td>
				<td>Edition nom sur couverture</td>
				<td>Edition nom sur titre</td>
				<td>Raison Sociale</td>
				<td>Code Postal</td>
				<td>Ville</td>
				<td>RS sur carnet</td>
				<td>CP et Ville sur carnet</td>
				<td>Date de livraison</td>
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

