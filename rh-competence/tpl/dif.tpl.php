<table width="100%" class="border"><tbody>
	<tr><td width="25%" valign="top">Réf.</td><td>[user.id]</td></tr>
	<tr><td width="25%" valign="top">Nom</td><td>[user.lastname]</td></tr>
	<tr><td width="25%" valign="top">Prénom</td><td>[user.firstname]</td></tr>
</tbody></table>
<br>
[dif.titre;strconv=no;protect=no]
<br>
<table class="border" style="width:100%">			
	<tr>
		<td><b>Année</b></td>
		<td><b>Nombre d'heures acquises</b></td>
		<td><b>Nombre d'heures prises</b></td>
		<td><b>Nombre d'heures restantes</b></td>
	</tr>
	<tr>
		<td>[dif.annee;block=tr;strconv=no;protect=no]</td>
		<td>[dif.nb_heures_acquises;block=tr;strconv=no;protect=no] heures</td>
		<td>[dif.nb_heures_prises;strconv=no;protect=no] heures</td>
		<td>[dif.nb_heures_restantes;strconv=no;protect=no] heures</td>
	</tr>
</table>

<table class="border" style="width:100%;">
	[onshow;block=begin;when [view.mode]=='view']
	<span  style="float:right;" class="butActionDelete" id="action-delete" onclick="document.location.href='?fk_user=[userCourant.id]&id=[dif.id]&action=deleteDIF'">Supprimer</span>
	<a style="float:right;" class="butAction" href="?fk_user=[userCourant.id]">Annuler</a>
	<a style="float:right;" href="?id=[dif.id]&action=editDIF&fk_user=[userCourant.id]" class="butAction">Modifier</a>
	
	[onshow;block=end]	
</table>
	
[onshow;block=begin;when [view.mode]=='edit']
<div class="tabsAction" style="text-align:center">
	<input type="submit" value="Enregistrer" name="save" class="button">
	&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?fk_user=[userCourant.id]'">
</div>
[onshow;block=end]
