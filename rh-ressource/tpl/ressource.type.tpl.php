<h1>Type de ressource </h1>

<div>
	Code de la ressource : [ressourceType.code; strconv=no]</br></br>
	Libellé de la ressource : [ressourceType.libelle; strconv=no]</br></br>
</div>

<div>
<h2>Champs de la ressource</h2>
<table class="border">
	<!-- entête du tableau -->
	<tr>
		<td>Id</td>
		<td>Code</td>
		<td>Libellé</td>
		<td>Type</td>
		<td>Obligatoire</td>
		<td>Action</td>
	</tr>

	<!-- fields déjà existants -->
	<tr>
		<td>[ressourceField.id;block=tr;strconv=no;protect=no]</td>
		<td>[ressourceField.code;strconv=no;protect=no]</td>
		<td>[ressourceField.libelle;strconv=no;protect=no]</td>
		<td>[ressourceField.type;strconv=no;protect=no]</td>
		<td>[ressourceField.obligatoire;strconv=no;protect=no]</td>
		<td><button type="submit" value="[ressourceField.id;strconv=no;protect=no]" name="deleteField" >Supprimer</button></td>
	</tr>
	
	<!-- Nouveau field-->
	<tr>
		<td>Nouveau</td>
		<td>[newField.code;strconv=no;protect=no]</td>
		<td>[newField.libelle;strconv=no;protect=no]</td>
		<td>[newField.type;strconv=no;protect=no]</td>
		<td>[newField.obligatoire;strconv=no;protect=no]</td>
		<td><input type="submit" value="Ajouter" name="newField"></td>
	</tr>
	

</table>
</div>

<p align="center">
		<input type="submit" value="Enregistrer" name="save" class="button">
		&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[affaire.id]'">
</p>