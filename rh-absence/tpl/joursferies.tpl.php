

    [view.head;strconv=no]

	<table class="border" style="width:30%">

		<tr>
			<td>Jour non travaillé</td>
			<td>[joursFeries.date_jourOff;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td>Matin</td>
			<td>[joursFeries.matin;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td>Après-midi</td>
			<td>[joursFeries.apresmidi;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td>Commentaire</td>
			<td>[joursFeries.commentaire;strconv=no;protect=no]</td>
		</tr>
			

	</table>


	
[onshow;block=begin;when [view.mode]=='view']
	<div class="tabsAction" style="text-align:center;">
		<a class="butAction"  href="?id=[joursFeries.id]&action=edit">Modifier</a>
		<a class="butActionDelete"  href="?id=[joursFeries.id]&action=delete">Supprimer</a>
		</div>
[onshow;block=end] 

[onshow;block=begin;when [view.mode]!='view']
	<div class="tabsAction" style="text-align:center;">
		<input type="submit" value="Enregistrer" name="save" class="button">
		&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[joursFeries.id]'">
	</div>
[onshow;block=end] 

