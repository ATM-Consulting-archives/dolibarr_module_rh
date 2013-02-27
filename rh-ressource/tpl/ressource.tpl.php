<h1>Créer une ressource </h1>

<div>

	<!-- entête du tableau -->
<table class="border">
	<tr>
		<td>Id</td>
		<td>Libellé</td>
		<td>Type</td>
		[onshow;block=begin;when [view.mode]=='edit']
			<td></td>
		[onshow;block=end]
		
	</tr>
	
	<tr>
		<td>[ressource.id;strconv=no;protect=no] </td>
		<td>[ressource.libelle;strconv=no;protect=no] </td>
		<td>[ressource.type;strconv=no;protect=no] </td>
		[onshow;block=begin;when [view.mode]=='edit']
			<td><input type="submit" value="Valider" name="save" class="butAction"></td>
		[onshow;block=end]
		
	</tr>
</table>

</div>

<h2>Champs</h2>

<table class="border">
	<tr>
	<td>Libellé</td> 
	<td>Valeur</td>
	</tr>
	
	<tr>
		<td> [ressourceField.libelle;block=tr;strconv=no;protect=no] </td>
		<td> [ressourceField.valeur;strconv=no;protect=no] </td>
		
	</tr>
</table>

<br>

<h2>Ressource associé</h2>
<div>
	
		[onshow;block=begin;when [view.mode]=='edit']
			 [fk_ressource.liste_fk_rh_ressource;strconv=no;protect=no]
		[onshow;block=end]
		
		[onshow;block=begin;when [view.mode]!='edit']
			Cette ressource est associé à [fk_ressource.fk_rh_ressource;strconv=no;protect=no].
		[onshow;block=end]
		
	
</div>

<div class="tabsAction" >

		[onshow;block=begin;when [view.mode]=='edit']
			<input type="submit" value="Enregistrer" name="save" class="button">
			&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[ressource.id]'">
		[onshow;block=end]
		[onshow;block=begin;when [view.mode]!='edit']
			<a class="butAction"  href="?id=[ressource.id]&action=edit">Modifier</a>
			&nbsp; &nbsp;<a class="butActionDelete"  href="?id=[ressource.id]&action=delete">Supprimer</a>
			<!--&nbsp; &nbsp; <input type="button" value="Supprimer" name="cancel" class="butActionDelete" onclick="document.location.href='?id=[ressource.id]&action=edit'">-->

		[onshow;block=end]
	

</div>