<h1>Créer une ressource </h1>

<div>
<table class="border">
	<!-- entête du tableau -->
	<tr>
		<td>Id</td>
		<td>Libellé</td>
		<td>Type</td>
		<td>Bail</td>
		<td>Statut</td>
		<td>Action</td>
	</tr>

	<!-- liste de ressources -->
	<tr>
		<td>[ressourceField.id;block=tr;strconv=no;protect=no]</td>
		<td>[ressourceField.libelle;strconv=no;protect=no]</td>
		<td>[ressourceField.type;strconv=no;protect=no]</td>
		<td>[ressourceField.bail;strconv=no;protect=no]</td>
		<td>[ressourceField.statut;strconv=no;protect=no]</td>
		<td><button type="submit" value="[ressourceField.id;strconv=no;protect=no]" name="deleteRessource" >Supprimer</button></td>
	</tr>
	
	
	

</table>
</div>

<p align="center">
		<input type="submit" value="Enregistrer" name="save" class="button">
		&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[affaire.id]'">
</p>