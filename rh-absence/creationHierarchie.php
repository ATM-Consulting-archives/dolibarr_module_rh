<?php
	require('config.php');
	require('./class/absence.class.php');

	$langs->load('absence@absence');
	
	
	llxHeader();
?>


<div id="afficherUser"><h2>
		Bonjour 
		<?php echo $user->firstname." ".$user->lastname; 
		?>
</h2></div>
<br/><br/><br/>


<script>
	function enregistrerSuperieur(userSup){
			alert(userSup);
			
		}
	
</script>	

<div id="choixSuperieur">
	<h2>Veuillez choisir votre supérieur hiérarchique</h2>
	<br/>
	<select id="selectSuperieur">
	
	<?php		
		
		
		
		
		//récupération des informations sur les utilisateurs.
		$utilisateur=array();
		$sql="SELECT * FROM `llx_user`";
		$resql=$db->query($sql);
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
						if($obj->firstname!=$user->firstname&&$obj->lastname!=$user->lastname){	//on n'affiche pas l'utilisateur courant dans la liste des supérieurs
						// You can use here results
						echo '<option value="'.$obj->rowid.'">'.$obj->firstname." ".$obj->name.'</option>';
						}
						
					}
					$i++;
				}
			}
		}
	?>
	</select>	
	<input class="button" type="submit" name="valider" value="valider" onclick="javascript:enregistrerSuperieur(($('#selectSuperieur').val()));"></td>
		
	<?php	
	llxFooter();
		
	?>
	
	
