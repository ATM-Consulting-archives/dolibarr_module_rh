<h1>Type de ressource </h1>

<div>
	Code de la ressource :[ressourceType.code; strconv=no]</br></br>
	Nom de la ressource :[ressourceType.libelle; strconv=no]</br></br>
</div>

<div>

<table class="border">
	<tr>
		<td>Code</td>
		<td>Libellé</td>
		<td>Type</td>
		<td>Obligatoire</td>
	</tr>

	<tr>
	<div>
		<td>[ressourceField.code;block=tr]</td>
		<td>[ressourceField.libelle]</td>
		<!--<td>[ressourceField.type]</td>
		<td>[ressourceField.obligatoire]</td>-->
	</div>
	</tr>

</table>
</div>

<p align="center">
		<input type="submit" value="Enregistrer" name="save" class="button"> 
		&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[affaire.id]'">
</p>