<table width="100%" class="border"><tbody>
	<tr><td width="25%" valign="top">Réf.</td><td>[user.id]</td></tr>
	<tr><td width="25%" valign="top">Nom</td><td>[user.lastname]</td></tr>
	<tr><td width="25%" valign="top">Prénom</td><td>[user.firstname]</td></tr>
</tbody></table>
<br>

<div class="fiche">
		<div>
			
			<h2 style="color: #2AA8B9;">Ajout d'une prime</h2>
			<table width="100%" class="border">
				<tr>
					<td>Date prime</td>
					[onshow;block=begin;when [view.mode]=='edit']
						<td>[remunerationPrime.date_prime;block=tr;strconv=no;protect=no]</td>
					[onshow;block=end]
					[onshow;block=begin;when [view.mode]=='view']
						<td>[remunerationPrime.date_prime;block=tr;strconv=no;protect=no]</td>
					[onshow;block=end]
				</tr>
					<td>Montant prime</td>
					<td>[remunerationPrime.montant_prime;block=tr;strconv=no;protect=no] €</td>
				</tr>
				<tr>
					<td>Motif</td>
					<td>[remunerationPrime.motif;block=tr;strconv=no;protect=no]</td>
				</tr>	
			</table>
			<br/><br/>
			
			<br/>
			
			<table class="border" style="width:100%;">
				[onshow;block=begin;when [view.mode]=='view']
				<a style="text-align:center;width:20%;" class="butAction" href="?fk_user=[userCourant.id]">Retour</a>
				[onshow;block=begin;when [userCourant.ajoutRem]=='1']
					<a style="text-align:center;width:20%;" class="butAction" href="?fk_user=[userCourant.id]&action=edit&type=prime&id=[remunerationPrime.id;block=tr;strconv=no;protect=no]">Modifier</a>
					<a  style="text-align:center;width:20%;" class="butActionDelete" id="action-delete" onclick="if (window.confirm('Voulez vous supprimer l\'élément ?')){document.location.href='?fk_user=[userCourant.id]&id=[remunerationPrime.id;block=tr;strconv=no;protect=no]&type=prime&action=delete'}">Supprimer</a>
				[onshow;block=end]
				[onshow;block=end]	
			</table>
		</div>

</div>



[onshow;block=begin;when [view.mode]=='edit']
<div class="tabsAction"  style="text-align:center">
	<input type="submit" value="Enregistrer" name="save" class="button" onclick="document.location.href='?id=[remunerationPrime.id;block=tr;strconv=no;protect=no]&fk_user=[userCourant.id]&type=prime&action=view'">
	&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?fk_user=[userCourant.id]'">
</div>
[onshow;block=end]

