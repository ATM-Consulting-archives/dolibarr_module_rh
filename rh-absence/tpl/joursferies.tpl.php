
    [view.head;strconv=no]
	[onshow;block=begin;when [joursFeries.titreAction]=='new']
		[joursFeries.titreCreate;strconv=no;protect=no]
	[onshow;block=end] 
	[onshow;block=begin;when [joursFeries.titreAction]=='view']
		[joursFeries.titreVisu;strconv=no;protect=no]
	[onshow;block=end] 
	
	<table class="border" style="width:30%">
		<tr>
			<td>Jour non travaillé</td>
			<td>[joursFeries.date_jourOff;strconv=no;protect=no]</td>

		</tr>
		<tr>
			<td>Période</td>
			<td>[joursFeries.moment;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td>Commentaire</td>
			<td>[joursFeries.commentaire;strconv=no;protect=no]</td>
		</tr>
	</table>

[onshow;block=begin;when [view.mode]=='view']
		[onshow;block=begin;when [userCourant.droitAjoutJour]=='1']
		<div class="tabsAction" >
		<div  style="text-align:center;">
			<a class="butAction"  href="?&fk_user=[userCourant.id]">Retour</a>
			<a class="butAction"  href="?idJour=[joursFeries.id]&fk_user=[userCourant.id]&action=edit">Modifier</a>
			<a class="butActionDelete" onclick="if (window.confirm('Voulez-vous vraiment supprimer ce jour férié ?')){href='?idJour=[joursFeries.id]&fk_user=[userCourant.id]&action=delete'};">Supprimer</a>
		</div>
		</div>
		[onshow;block=end] 
[onshow;block=end] 

[onshow;block=begin;when [view.mode]!='view']
	<div class="tabsAction" style="text-align:center;">
		<input type="submit" value="Enregistrer" name="save" class="button">
		&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[userCourant.id]'">
	</div>
[onshow;block=end] 

