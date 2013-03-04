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

$orgChoisie=$_POST["choixAffichage"];
$idUserCourant=$_GET["id"];


//////////////////////////////////////récupération des informations de l'utilisateur courant
		$sqlReqUser="SELECT * FROM `llx_user` where rowid=".$idUserCourant;
		$ATMdb->Execute($sqlReqUser);
		$Tab=array();
		while($ATMdb->Get_line()) {
					$userCourant=new User($db);
					$userCourant->id=$ATMdb->Get_field('rowid');
					$userCourant->lastname=$ATMdb->Get_field('name');
					$userCourant->firstname=$ATMdb->Get_field('firstname');
					$userCourant->fk_user=$ATMdb->Get_field('fk_user');
					$Tab[]=$userCourant;
					
		}
		//print "salut".$userCourant->rowid.$userCourant->lastname.$userCourant->firstname.$userCourant->fk_user;




//Fonction qui permet d'afficher les utilisateurs qui sont en dessous hiérarchiquement du salarié passé en paramètre
function afficherSalarieDessous(&$ATMdb, $idBoss = -1, $niveau=1){
		
				global $user, $db, $idUserCourant, $userCourant;
				
				?>
				<ul id="ul-niveau-<?=$niveau ?>">
				<?
				
				$sqlReq="SELECT rowid FROM `llx_user` where fk_user=".$idBoss;
				
				$ATMdb->Execute($sqlReq);
				
				$Tab=array();
				while($ATMdb->Get_line()) {
					$user=new User($db);
					$user->fetch($ATMdb->Get_field('rowid'));
					
					$Tab[]=$user;
				}
				
				foreach($Tab as &$user) {
					?>
					<li class="utilisateur" rel="<?=$user->id ?>"><?=$user->firstname." ".$user->lastname ?>
					<?
					afficherSalarieDessous($ATMdb, $user->id,$niveau+1);
					?></li><?
				}
				
				?></ul><?
								
}

//Fonction qui permet d'afficher les groupes dans la liste déroulante 
function afficherGroupes(&$ATMdb){
				global $user, $db, $idUserCourant, $userCourant;
				//récupère les id des différents groupes de l'utilisateur
				$sqlReq="SELECT fk_usergroup FROM `llx_usergroup_user` where fk_user=".$userCourant->id;
				$ATMdb->Execute($sqlReq);
				$Tab=array();
				while($ATMdb->Get_line()) {
					//récupère les id des différents nom des  groupes de l'utilisateur
					$ATMdb1=new Tdb;
					$sqlReq1="SELECT nom FROM `llx_usergroup` where rowid=".$ATMdb->Get_field('fk_usergroup');
					$ATMdb1->Execute($sqlReq1);
					
					$Tab1=array();
					
					while($ATMdb1->Get_line()) {
						//affichage des groupes concernant l'utilisateur 
						print '<option value="'.$ATMdb1->Get_field('nom').'">'.$ATMdb1->Get_field('nom').'</option>';
					}			
				}
}


?>


<form id="form" action="afficher.php?id=<?= $userCourant->id; ?>" method="post">
	<select id="choixAffichage" name="choixAffichage">
		<option value="entreprise">Afficher la hiérarchie de l entreprise</option>
		<option value="equipe">Afficher son équipe</option>
		<?php
			afficherGroupes($ATMdb);
		?>
	</select> 
	<input id="validSelect" type="submit" value="Valider"/>
</form>
	



<?php


if($orgChoisie=="entreprise"){	//on affiche l'organigramme de l'entreprise 
///////////////////////////////////////////////ORGANIGRAMME ENTREPRISE
?>
	<div id="organigrammePrincipal">
		<h1>Hiérarchie de l'entreprise</h1>
		<div id="chart" class="orgChart"></div>
		
		<ul id="JQorganigramme" style="display:none;">
			<li>Société
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
		<h1>Hiérarchie de votre équipe</h1>
		<div id="chart" class="orgChart"></div>
		
		<ul id="JQorganigramme" style="display:none;">
			<li>Votre Equipe
		<?php 		
			$ATMdb=new Tdb;
			if($userCourant->fk_user!="-1"){		// si on a un supérieur hiérarchique, on affiche son nom, puis l'équipe 
			
				$sqlReq="SELECT name,firstname FROM `llx_user` where rowid=".$userCourant->fk_user;
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
			//////////////// On considère pour l'instant que l'utilisateur courant est le supérieur hiérarchique du groupe 
			echo $orgChoisie;	
			$ATMdb=new Tdb;
			print '<ul><li>'.$userCourant->firstname." ".$userCourant->lastname."<br/>(Vous-même)";
			afficherSalarieDessous($ATMdb, $userCourant->id, 1);
			print "</li></ul>";
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

