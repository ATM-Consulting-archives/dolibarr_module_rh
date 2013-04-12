<?php

require('config.php');

require_once DOL_DOCUMENT_ROOT.'/core/lib/usergroups.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';


$langs->load("companies");
$langs->load("products");
$langs->load("admin");
$langs->load("users");
$langs->load("languages");


// Defini si peux lire/modifier permisssions
$canreaduser=($user->admin || $user->rights->user->user->lire);

$id = GETPOST('id','int');
$action = GETPOST('action','alpha');

if ($id)
{
    // $user est le user qui edite, $id est l'id de l'utilisateur edite
    $caneditfield=((($user->id == $id) && $user->rights->user->self->creer)
    || (($user->id != $id) && $user->rights->user->user->creer));
}

// Security check
$socid=0;
if ($user->societe_id > 0) $socid = $user->societe_id;
$feature2 = (($socid && $user->rights->user->self->creer)?'':'user');
if ($user->id == $id)	// A user can always read its own card
{
    $feature2='';
    $canreaduser=1;
}
$result = restrictedArea($user, 'user', $id, '&user', $feature2);
if ($user->id <> $id && ! $canreaduser) accessforbidden();

$dirtop = "../core/menus/standard";
$dirleft = "../core/menus/standard";

// Charge utilisateur edite
$fuser = new User($db);
$fuser->fetch($id);
$fuser->getrights();

$form = new Form($db);

$arret=0;

$ATMdb=new Tdb;

llxHeader('', '', '', '', 0, 0, array('/hierarchie/js/jquery.jOrgChart.js'));


?>
<link rel="stylesheet" type="text/css" href="./css/jquery.jOrgChart.css" />
<?


$head = user_prepare_head($fuser);
$current_head = 'hierarchie';
dol_fiche_head($head, $current_head, $langs->trans('Utilisateur'),0, 'user');

?>
<script>
    jQuery(document).ready(function() {
    	
    	$("#JQorganigramme").jOrgChart({
            chartElement : '#chart',
            dragAndDrop : false
        });
    });
    </script>

<?
global $user;

$orgChoisie=isset($_POST["choixAffichage"]) ? $_POST["choixAffichage"] : 'equipe';
$idUserCourant=$_GET["id"];

//////////////////////////////////////récupération des informations de l'utilisateur courant
	$sqlReqUser="SELECT * FROM `".MAIN_DB_PREFIX."user` where rowid=".$idUserCourant;
	$ATMdb->Execute($sqlReqUser);
	$Tab=array();
	$ATMdb->Get_line();
	$userCourant=new User($db);
	$userCourant->id=$ATMdb->Get_field('rowid');
	$userCourant->lastname=$ATMdb->Get_field('name');
	$userCourant->firstname=$ATMdb->Get_field('firstname');
	$userCourant->fk_user=$ATMdb->Get_field('fk_user');
	$Tab[]=$userCourant;
				

//print "salut".$userCourant->rowid.$userCourant->lastname.$userCourant->firstname.$userCourant->fk_user;




//Fonction qui permet d'afficher les utilisateurs qui sont en dessous hiérarchiquement du salarié passé en paramètre
function afficherSalarieDessous(&$ATMdb, $idBoss = 0, $niveau=1){
		
				global $user, $db, $idUserCourant, $userCourant, $conf;
				
				?>
				<ul id="ul-niveau-<?=$niveau ?>">
				<?
				
				$sqlReq="SELECT rowid FROM `".MAIN_DB_PREFIX."user` where fk_user=".$idBoss." AND entity=IN (0,".(! empty($conf->multicompany->enabled) && ! empty($conf->multicompany->transverse_mode)?"1,":"").$conf->entity;
				
				$ATMdb->Execute($sqlReq);
				
				$Tab=array();
				while($ATMdb->Get_line()) {
					$user=new User($db);
					$user->fetch($ATMdb->Get_field('rowid'));
					
					$Tab[]=$user;
				}
				
				foreach($Tab as &$user) {
					?>
					<li class="utilisateur" rel="<?=$user->id ?>">
						<a href="<?=DOL_URL_ROOT ?>/user/fiche.php?id=<?=$user->id ?>"><?=$user->firstname." ".$user->lastname ?></a>
						<? if(!empty($user->office_phone) || !empty($user->user_mobile)) { ?><div class="tel">Tél. : <?=$user->office_phone.' '.$user->user_mobile ?></div><? }
						if(!empty($user->email) ) { ?><div class="mail">Email : <a href="mailto:<?=$user->email ?>"><?=$user->email ?></div><? }
					
					afficherSalarieDessous($ATMdb, $user->id,$niveau+1);
					?></li><?
				}
				?></ul><?		
}

//Fonction qui permet d'afficher un salarié
function afficherSalarie(&$ATMdb, $idUser, $niveau=1){
		
				global $user, $db, $idUserCourant, $userCourant;

				$sqlReq="SELECT rowid FROM `".MAIN_DB_PREFIX."user` where rowid=".$idUser;
				
				$ATMdb->Execute($sqlReq);
				
				$Tab=array();
				while($ATMdb->Get_line()) {
					$user=new User($db);
					$user->fetch($ATMdb->Get_field('rowid'));
					
					$Tab[]=$user;
				}
				
				foreach($Tab as &$user) {
					?>
					<li class="utilisateur" rel="<?=$user->id ?>">
						<a href="<?=DOL_URL_ROOT ?>/user/fiche.php?id=<?=$user->id ?>"><?=$user->firstname." ".$user->lastname ?></a>
						<? if(!empty($user->office_phone) || !empty($user->user_mobile)) { ?><div class="tel">Tél. : <?=$user->office_phone.' '.$user->user_mobile ?></div><? }
						if(!empty($user->email) ) { ?><div class="mail">Email : <a href="mailto:<?=$user->email ?>"><?=$user->email ?></div><? }
					
					?><?
				}
				?><?
}

//Fonction qui permet d'afficher un salarié
function afficherGroupeSousValideur(&$ATMdb, $idUser, $fkusergroup, $niveau=1){
		
				global $user, $db, $idUserCourant, $userCourant;

				$sqlReq=" SELECT  DISTINCT u.fk_user FROM ".MAIN_DB_PREFIX."usergroup_user as u WHERE u.fk_usergroup=".$fkusergroup." AND  u.fk_user NOT IN(SELECT v.fk_user FROM ".MAIN_DB_PREFIX."usergroup_user as v WHERE v.fk_user=".$idUser.")";
				
				$ATMdb->Execute($sqlReq);
				
				$Tab=array();
				while($ATMdb->Get_line()) {
					$user=new User($db);
					$user->fetch($ATMdb->Get_field('fk_user'));
					
					$Tab[]=$user;
				}
				print "<ul>";
				foreach($Tab as &$user) {
					
					?>
					<li class="utilisateur" rel="<?=$user->id ?>">
						<a href="<?=DOL_URL_ROOT ?>/user/fiche.php?id=<?=$user->id ?>"><?=$user->firstname." ".$user->lastname ?></a>
						<? if(!empty($user->office_phone) || !empty($user->user_mobile)) { ?><div class="tel">Tél. : <?=$user->office_phone.' '.$user->user_mobile ?></div><? }
						if(!empty($user->email) ) { ?><div class="mail">Email : <a href="mailto:<?=$user->email ?>"><?=$user->email ?></div><? }
					
					?><?
				}
				print "</ul>";
				
				?><?
}


//Fonction qui permet d'afficher les groupes dans la liste déroulante 
function afficherGroupes(&$ATMdb){
				global $user, $db, $idUserCourant, $userCourant;
				//récupère les id des différents groupes de l'utilisateur
				$sqlReq="SELECT fk_usergroup FROM `".MAIN_DB_PREFIX."usergroup_user` where fk_user=".$userCourant->id;
				$ATMdb->Execute($sqlReq);
				$Tab=array();
				while($ATMdb->Get_line()) {
					//récupère les id des différents nom des  groupes de l'utilisateur
					$ATMdb1=new Tdb;
					$sqlReq1="SELECT nom FROM `".MAIN_DB_PREFIX."usergroup` where rowid=".$ATMdb->Get_field('fk_usergroup');
					$ATMdb1->Execute($sqlReq1);
					
					$Tab1=array();
					
					while($ATMdb1->Get_line()) {
						//affichage des groupes concernant l'utilisateur 
						print '<option value="'.$ATMdb1->Get_field('nom').'">'.$ATMdb1->Get_field('nom').'</option>';
					}			
				}
}

function findFkUserGroup(&$ATMdb, $nomGroupe){
	$sqlFkGroupe='SELECT fk_usergroup FROM ".MAIN_DB_PREFIX."rh_valideur_groupe as v, ".MAIN_DB_PREFIX."usergroup as u WHERE u.nom="'.$nomGroupe.'" AND v.fk_usergroup=u.rowid';
	$ATMdb->Execute($sqlFkGroupe);
	while($ATMdb->Get_line()) {
			return $ATMdb->Get_field('fk_usergroup');
	}
}

function findIdValideur(&$ATMdb, $fkusergroup){
	$sqlidValideur='SELECT fk_user FROM ".MAIN_DB_PREFIX."rh_valideur_groupe WHERE fk_usergroup='.$fkusergroup;
	$ATMdb->Execute($sqlidValideur);
	$Tab=array();
	while($ATMdb->Get_line()) {
			//return $ATMdb->Get_field('fk_user');
			//$idValideurGroupe=findIdValideur($ATMdb,$fkusergroup);
			$Tab[]=$ATMdb->Get_field('fk_user');
	}
	?>
				<ul id="ul-niveau-1">
	<?
	foreach($Tab as $fkuser){
		afficherSalarie($ATMdb,$fkuser);
		//afficherSalarieDessous($ATMdb,$fkuser,1);
		afficherGroupeSousValideur($ATMdb,$fkuser,$fkusergroup,1);
		// SELECT  u.rowid FROM ".MAIN_DB_PREFIX."user as u WHERE u.rowid NOT IN (SELECT g.fk_user FROM ".MAIN_DB_PREFIX."rh_valideur_groupe as g WHERE g.fk_usergroup=2)
		print '</li>';
	}
	print '</ul>';
}

function afficherUtilisateurGroupe(&$ATMdb, $nomGroupe){
			echo $nomGroupe;
			$fkusergroup=findFkUserGroup($ATMdb, $nomGroupe);	
			$idValideurGroupe=findIdValideur($ATMdb,$fkusergroup);

			//afficherSalarieDessous($ATMdb,$idValideurGroupe, 1);
}

?>


<form id="form" action="afficher.php?id=<?= $userCourant->id; ?>" method="post">
	<select id="choixAffichage" name="choixAffichage">
		<option value="entreprise">Afficher la hiérarchie de l'entreprise</option>
		<option value="equipe">Afficher son équipe</option>
		<?php
			afficherGroupes($ATMdb);
		?>
	</select> 
	<input id="validSelect" type="submit" value="Valider" class="button" />
</form>

<?php


if($orgChoisie=="entreprise"){	//on affiche l'organigramme de l'entreprise 
///////////////////////////////ORGANIGRAMME ENTREPRISE


	$socName = empty($conf->global->MAIN_INFO_SOCIETE_LOGO_MINI) ? $conf->global->MAIN_INFO_SOCIETE_NOM : '<img src="'.DOL_URL_ROOT.'/viewimage.php?cache=1&amp;modulepart=companylogo&amp;file='.urlencode('thumbs/'.$conf->global->MAIN_INFO_SOCIETE_LOGO_MINI).'" />';
	//print_r($conf->global);

	$socName = empty($conf->global->MAIN_INFO_SOCIETE_LOGO) ? $conf->global->MAIN_INFO_SOCIETE_NOM : '<img src="'.DOL_URL_ROOT.'/viewimage.php?cache=1&modulepart=companylogo&file='.urlencode($conf->global->MAIN_INFO_SOCIETE_LOGO).'" />';
//	print_r($conf->global);


?>
	<div id="organigrammePrincipal">
		<h2>Hiérarchie de l'entreprise</h2>
		<div id="chart" class="orgChart" align="center"></div>
		
		<ul id="JQorganigramme" style="display:none;">
			<li><?=$socName ?>
		<?php 		
			$ATMdb=new Tdb;
			afficherSalarieDessous($ATMdb);
			$ATMdb->close();
		?>
			</li>
		</ul>
	</div>
	
	
	
<?php
}else if($orgChoisie=="equipe"){	//on affiche l'organigramme de l'équipe
?>
	<div id="organigrammeEquipe">
		<h2>Hiérarchie de votre équipe</h2>
		<div id="chart" class="orgChart" align="center"></div>
		
		<ul id="JQorganigramme" style="display:none;">
			<li>Votre Equipe
		<?php 		
			$ATMdb=new Tdb;
			if($userCourant->fk_user!="0"){		// si on a un supérieur hiérarchique, on affiche son nom, puis l'équipe 
			
				$sqlReq="SELECT name,firstname FROM `".MAIN_DB_PREFIX."user` where rowid=".$userCourant->fk_user;
				$ATMdb->Execute($sqlReq);
				$Tab=array();
				while($ATMdb->Get_line()) {
					//récupère les id des différents nom des  groupes de l'utilisateur
					
					print '<ul><li>'.$ATMdb->Get_field('firstname')." ".$ATMdb->Get_field('name')."<br/>(Votre supérieur)";
					
				}
				afficherSalarieDessous($ATMdb,$userCourant->fk_user);
				
			}else {		// si on n'a pas de supérieur, on écrit son nom, puis ceux de ses collaborateurs inférieurs
						
					print '<ul><li>'.$userCourant->firstname." ".$userCourant->lastname."<br/>(Vous-même)";
					afficherSalarieDessous($ATMdb,$userCourant->id, 1);
					print "</li></ul>";
				
			}
			
			$ATMdb->close();
		?>
			</li>
		</ul>
	</div>
	
	
	
	
	
<?php 
}else{	//on affiche l'organigramme du groupe  
?>	
	<div id="organigrammeGroupe">
		<h1>Hiérarchie du groupe</h1>
		<div id="chart" class="orgChart"></div>
		
		<ul id="JQorganigramme" style="display:none;">
			<li> 
		<?php 	
			$ATMdb=new Tdb;
			//on affiche les utilisateurs du groupe en cours
			afficherUtilisateurGroupe($ATMdb,$orgChoisie);
			$ATMdb->close();
		?>
			</li>
		</ul>
	</div>
<?php	
}



?>
<script>	
	$(document).ready( function(){
		$("#choixAffichage option[value='<?= $orgChoisie?>']").attr('selected', 'selected');
		 <?php 
		 	if($orgChoisie==""){?>
		 		$('#organigrammeGroupe').hide();
		 	<?php }
		 ?>
	});
</script>


<?php

dol_fiche_end();

llxFooter();
$db->close();

