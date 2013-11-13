[onshow;block=begin;when [view.mode]=='view']
  
<div class="fiche">
	
    <div class="tabBar">
		<h2>
			Liste des codes analytiques de l'utilisateur
		</h2>
	
		<table width="100%" class="border"><tbody><tr><td width="25%" valign="top">Réf.</td><td>
			[user.id]</td></tr>
		<tr><td width="25%" valign="top">Nom</td><td>[user.lastname]</td></tr>
		<tr><td width="25%" valign="top">Prénom</td><td>[user.firstname]</td></tr>
		</tbody></table>
	
		<div>
							
			<table class="border" style="width:100%">			
				<tr>
					<td>Code analytique</td>
				</tr>
				<tr>
					<td>[analytique.code;block=tr;strconv=no;protect=no]</td>
					<td><img src="./img/delete.png"  style="cursor:pointer;" onclick="document.location.href='?fk_user=[userCourant.id]&deleteId=[analytique.id]&action=delete_code'"></td>
				</tr>
			</table>
			
			<div class="tabsAction" >
				<a class="butAction"  href="?fk_user=[userCourant.id]&action=add_code">Ajouter</a>
			</div>
		</div>
	</div>
</div>


[onshow;block=end] 


[onshow;block=begin;when [view.mode]=='edit']
<table width="100%" class="border"><tbody><tr><td width="25%" valign="top">Réf.</td><td>
			[user.id]</td></tr>
		<tr><td width="25%" valign="top">Nom</td><td>[user.lastname]</td></tr>
		<tr><td width="25%" valign="top">Prénom</td><td>[user.firstname]</td></tr>
</tbody></table><br/>
<table width="100%" class="border">
	<tr>
		<td width="20%">Code analytique</td>
		<td>Pourcentage</td>
	</tr>
	<tr>
		<td>[analytique.code;strconv=no;protect=no]</td>
		<td>[analytique.pourcentage;strconv=no;protect=no]%</td>
	</tr>
</table>

<div class="tabsAction" >
	<input type="submit" value="Enregistrer" name="save_code" class="button">
	&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?fk_user=[user.id]'">
</div>
[onshow;block=end]