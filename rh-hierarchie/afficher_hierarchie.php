
<?php
//<link rel="stylesheet" type="text/css" href="./css/slickmap.css" />
require('config.php');
require('./class/hierarchie.class.php');


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

llxHeader();

$head = user_prepare_head($fuser);
$current_head = 'hierarchie';
dol_fiche_head($head, $current_head, $langs->trans('Utilisateur'),0, 'user');


//Fonction qui permet d'afficher les utilisateurs qui sont en dessous hiérarchiquement du salarié passé en paramètre
function afficherSalarieDessous($salarie, $db1){
				global $user;
				$sqlReq="SELECT * FROM `llx_user` where fk_user=".$salarie->rowid;
				$resql=$db1->query($sqlReq);
				if ($resql)
				{
					$num = $db1->num_rows($resql);
					$i = 0;
					if ($num)
					{
						while ($i < $num)
						{
							$obj = $db1->fetch_object($resql);
							if ($obj)
							{
									// affichage des utilisateurs en dessous du salarié passé en paramètre
									if($user->id == $obj->rowid){
										print '<ul><li><a>'.$obj->firstname." ".$obj->name." (Vous-même) ".'</a>';
									}else{
										print '<ul><li><a>'.$obj->firstname." ".$obj->name.'</a>';
									}
									
									afficherSalarieDessous($obj,$db1);
									print '</li></ul>';
							}
							$i++;
						}
					}
				}
				return;
}

//Fonction qui permet d'afficher le nom du groupe d'un utilisateur
function afficherNomGroupe($groupe, $db1){
				$sqlReq="SELECT * FROM `llx_usergroup` where rowid=".$groupe->fk_usergroup;
				$resql=$db1->query($sqlReq);
				if ($resql)
				{
					$num = $db1->num_rows($resql);
					$i = 0;
					if ($num)
					{
						while ($i < $num)
						{
							$obj = $db1->fetch_object($resql);
							if ($obj)
							{
									// affichage nom groupe de l'utilisateur en haut de hierarchie
									print '<ul id="primaryNav" class="col13">';
									print '<li id="home"><a>'.$obj->nom.'</a></li>';
									
							}
							$i++;
						}
					}
				}
				return;
}


?>

<select id="choixAffichage" name="choixAffichage">
	<option selected value="entreprise">Afficher la hiérarchie de l'entreprise</option>
	<option value="groupe">Afficher la hiérarchie du groupe</option>
	<option value="equipe">Afficher son équipe</option>
</select> 


<?php
///////////////////////////////////////////////ORGANIGRAMME ENTREPRISE
?>
<div id="organigrammePrincipal" class="sitemap">
		<br/><br/><br/>
		<h1>Hiérarchie de l'entreprise C'PRO</h1>
		<br/><br/><br/>
		<ul id="primaryNav" class="col13">
			<li id="home"><a>C'PRO</a></li>
		<?php 
			
			$sqlSup="SELECT * FROM `llx_user` where fk_user=-1";
			$resql=$db->query($sqlSup);
			if ($resql)
			{
				$num = $db->num_rows($resql);
				$i = 0;
				if ($num)
				{
					
					while ($i < $num)
					{
						$obj = $db->fetch_object($resql);
						if ($obj)
						{
								// affichage des utilisateurs n'ayant pas de supérieurs
								print '<ul><li><a>'.$obj->firstname." ".$obj->name.'</a>';
								
								afficherSalarieDessous($obj, $db);
								print '</li></ul>';
						}
						$i++;
					}
				}
			}	
		?>
</div>


<?php
$idUser=$user->id;
////////////////////////////////////////////////ORGANIGRAMME GROUPE
?>
<div id="organigrammeGroupe" class="sitemap">
		<br/><br/><br/>
		<h1>Hiérarchie de votre groupe</h1>
		<br/><br/><br/>
		<?php 
		/*
			$resql = TRequeteCore::get_id_from_what_you_want($ATMdb,'llx_user', array('fk_user'=>'-1'));
			print_r($resql);
			$Tuser = array();
			foreach($resql as $k=>$id){
				$Tuser[$k] = new TRH_Hierarchie($ATMdb); 
				$Tuser[$k]->load($ATMdb, $id);
				print_r ($Tuser[$k]);
			}
			//
			*/
			////////////on récupère l'id du groupe de l'utilisateur 
			$sqlGroupe="SELECT fk_usergroup FROM `llx_usergroup_user` where fk_user=".$user->id;
			$resql=$db->query($sqlGroupe);
			if ($resql)
			{
				$num = $db->num_rows($resql);
				$i = 0;
				if ($num)
				{

					while ($i < $num)
					{
						$obj = $db->fetch_object($resql);
						if ($obj)
						{
									
									//////////////on récupère les id des utilisateurs du groupe
									
									afficherNomGroupe($obj,$db);
									$sqlGroupe="SELECT fk_user FROM `llx_usergroup_user` where fk_usergroup=".$obj->fk_usergroup;
									$resql1=$db->query($sqlGroupe);
									if ($resql1)
									{
										$num1 = $db->num_rows($resql1);
										$j = 0;
										if ($num1)
										{
											
											while ($j < $num1)
											{
												$obj1 = $db->fetch_object($resql1);
												if ($obj1)
												{
														/////////////////////////////on affiche les utilisateurs
														$sqlUser="SELECT * FROM `llx_user` where rowid=".$obj1->fk_user;
														$resql2=$db->query($sqlUser);
														if ($resql2)
														{
															$num2 = $db->num_rows($resql2);
															$k = 0;
															if ($num2)
															{
																
																while ($k < $num2)
																{
																	$obj2 = $db->fetch_object($resql2);
																	if ($obj2)
																	{
																			// affichage des utilisateurs n'ayant pas de supérieurs
																			print '<ul><li><a>'.$obj2->firstname." ".$obj2->name.'</a>';
																			afficherSalarieDessous($obj2, $db);
																			
																			print '</li></ul>';
																			break;
																			
																	}
																	$k++;
																}
															}
														}	
												}
												$j++;
												break;
											}
										}
									}	
						}
						$i++;
						break;
					}
				}
				
			}	
		?>
</div>



<div id="organigrammeEquipe" class="sitemap">
		
		<br/><br/><br/>
		<h1>Hiérarchie de votre équipe</h1>
		<br/><br/><br/>
		<ul id="primaryNav" class="col13">
		<?php 
			if($user->fk_user != "-1"){
				$sqlSup="SELECT * FROM `llx_user` where rowid=".$user->fk_user;
				$resql=$db->query($sqlSup);
				
				if ($resql)
				{
					$num = $db->num_rows($resql);
					$i = 0;
					if ($num)
					{
						
						while ($i < $num)
						{
							$obj = $db->fetch_object($resql);
							if ($obj)
							{
									// affichage des utilisateurs n'ayant pas de supérieurs
									print '<ul><li><a>'.$obj->firstname." ".$obj->name." (Votre supérieur) ".'</a>';
									afficherSalarieDessous($obj, $db);
									print '</li></ul>';
									break;
							}
							
							$i++;
						}
						
					}
				}
			}
			else {
						print '<ul><li><a>'.$user->firstname." ".$user->lastname." (Vous-même)".'</a>';
						$sqlSup="SELECT * FROM `llx_user` where fk_user=".$user->id;
						$resql=$db->query($sqlSup);
						if ($resql)
						{
							$num = $db->num_rows($resql);
							$i = 0;
							if ($num)
							{
								
								while ($i < $num)
								{
									$obj = $db->fetch_object($resql);
									if ($obj)
									{
											// affichage des utilisateurs n'ayant pas de supérieurs
											print '<ul><li><a>'.$obj->firstname." ".$obj->name.'</a>';
											afficherSalarieDessous($obj, $db);
											print '</li></ul>';
									}
									$i++;
								}
							}
						}	
			}
				
		?>
</div>



<script>
	
	$(document).ready( function(){
		$('#organigrammePrincipal').show();
		$('#organigrammeGroupe').hide();
		$('#organigrammeEquipe').hide();
		
		 $('#choixAffichage').change( 	function(){  
				var indexSelect = $("select[name='choixAffichage'] option:selected").val();
				if(indexSelect=="entreprise"){
					$('#organigrammePrincipal').show();
					$('#organigrammeGroupe').hide();
					$('#organigrammeEquipe').hide();
				}
				else if(indexSelect=="groupe"){
					$('#organigrammeGroupe').show();
					$('#organigrammePrincipal').hide();
					$('#organigrammeEquipe').hide();
				}
				else if(indexSelect=="equipe"){
					$('#organigrammeEquipe').show();
					$('#organigrammeGroupe').hide();
					$('#organigrammePrincipal').hide();
				}
		});
	});
</script>


<?php

dol_fiche_end();

llxFooter();
$db->close();

