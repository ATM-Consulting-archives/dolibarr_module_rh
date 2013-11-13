
    [view.head;strconv=no]

	<table class="border" style="width:30%">
		<tr>
			<td>Utilisateur</td>
			<td>[pointage.name;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td>Matin</td>
			<td>[pointage.matin;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td>Après-midi</td>
			<td>[pointage.apresmidi;strconv=no;protect=no]</td>
		</tr>
	</table>


	
[onshow;block=begin;when [view.mode]=='view']
		<div class="tabsAction" style="text-align:center;">
			<a class="butAction"  href="?idPointage=[pointage.id]&id=[userCourant.id]&action=edit">Modifier</a>
			<a class="butActionDelete"  href="?idPointage=[pointage.id]&id=[userCourant.id]&action=delete">Supprimer</a>
		</div>
[onshow;block=end] 

[onshow;block=begin;when [view.mode]!='view']
	<div class="tabsAction" style="text-align:center;">
		<input type="submit" value="Enregistrer" name="save" class="button">
		&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[userCourant.id]'">
	</div>
[onshow;block=end] 

