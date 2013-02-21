<h1>Créer une ressource </h1>

<div>

	<!-- entête du tableau -->
<table class="border">
	<tr>
		<td>Id</td>
		<td>Libellé</td>
		<td>Type</td>
		<td></td>
	</tr>
	
	<tr>
		<td>[ressource.id;strconv=no;protect=no] </td>
		<td>[ressource.libelle;strconv=no;protect=no] </td>
		<td>[ressource.type;strconv=no;protect=no] </td>
		<td><input type="submit" value="Valider" name="save" class="button"></td>
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


<p align="center">
		<input type="submit" value="Enregistrer" name="save" class="button">
		&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[ressource.id]'">
</p>