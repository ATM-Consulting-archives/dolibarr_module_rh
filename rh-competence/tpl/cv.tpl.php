[onshow;block=begin;when [view.mode]=='view'] 
<div class="fiche">

		<div>				
			<h2 style="color: #2AA8B9;">Description de l'expérience Professionnelle</h2>

			<table class="border" style="width:100%">			
				<tr>
					<td>Date début</td>
					<td>Date fin</td>
					<td>Libellé Expérience</td>
					<td>Description </td>
					<td>Lieu Expérience</td>
					<td>Supprimer</td>
				</tr>
				<tr>
					<td>[cv.date_debut;block=tr;strconv=no;protect=no]</td>
					<td>[cv.date_fin;strconv=no;protect=no]</td>
					<td>[cv.libelleExperience;strconv=no;protect=no]</td>
					<td>[cv.descriptionExperience;strconv=no;protect=no]</td>
					<td>[cv.lieuExperience;strconv=no;protect=no]</td>
					<td><img src="./img/delete.png"  style="cursor:pointer;" onclick="document.location.href='?fk_user=[userCourant.id]&deleteId=[cv.id]&action=deleteCV'"></td>
				</tr>
			</table>
			<table class="border" style="width:100%;">
				[onshow;block=begin;when [view.mode]=='view']
				<a style="float:right;" class="butAction" href="?fk_user=[userCourant.id]">Annuler</a>
				<a  style="float:right;" class="butActionDelete" id="action-delete" onclick="document.location.href='?fk_user=[userCourant.id]&id=[cv.id]&action=deleteCV'">Supprimer</a>
				[onshow;block=end]	
			</table>
		</div>

</div>
[onshow;block=end] 


[onshow;block=begin;when [view.mode]=='edit']
<h2 style="color: #2AA8B9;">Expérience professionnelle</h2>
<table
	<tr>
		<td>Date début</td>
		<td>Date fin</td>
		<td>Libellé Expérience</td>
		<td>Lieu Expérience</td>
	</tr>
	<tr>
		<td>[cv.date_debut;block=tr;strconv=no;protect=no]</td>
		<td>[cv.date_fin;strconv=no;protect=no]</td>
		<td>[cv.libelleExperience;strconv=no;protect=no]</td>
		<td>[cv.lieuExperience;strconv=no;protect=no]</td>
	</tr>
</table>
<br/><br/>
<table
	<tr
		<td>Description </td>
	</tr>
	<tr>
		<td>[cv.descriptionExperience;strconv=no;protect=no]</td>
	</tr>
</table>

<div class="tabsAction"  style="text-align:center">
	<input type="submit" value="Enregistrer" name="save" class="button"  onclick="document.location.href='?fk_user=[userCourant.id]'">
	&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?fk_user=[userCourant.id]'">
</div>
[onshow;block=end]

