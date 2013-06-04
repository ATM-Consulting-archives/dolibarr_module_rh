

	<div>
			
		[cv.titre;strconv=no;protect=no]

		<table class="border" style="width:100%">			
			<tr>
				<td>Date début</td>
				<td>[cv.date_debut;block=tr;strconv=no;protect=no]</td>
			<tr/>
			<tr>
				<td>Date fin</td>
				<td>[cv.date_fin;strconv=no;protect=no]</td>
			</tr>
			<tr>
				<td>Libellé Expérience</td>
				<td>[cv.libelleExperience;strconv=no;protect=no]</td>
			</tr>
			<tr>
				<td>Description </td>
				<td>[cv.descriptionExperience;strconv=no;protect=no]</td>
			</tr>
			<tr>
				<td>Lieu Expérience</td>
				<td>[cv.lieuExperience;strconv=no;protect=no]</td>
			</tr>
		</table>
		
	</div>


	<br><br>

	<table class="border" style="width:60%">
	[newCompetence.hidden;strconv=no;protect=no]
	[newCompetence.fk_user_lignecv;strconv=no;protect=no]
	<tr>
		<td>Compétences acquises</td>
		<td style="text-align:center;">Niveau acquis</td>
		[onshow;block=begin;when [view.mode]=='edit']
			<td style="text-align:center;">Action</td>
		[onshow;block=end]
	<tr>
		<td style="text-align:center;">[TCompetence.libelleCompetence;block=tr;strconv=no;protect=no]</td>
		<td style="text-align:center;">[TCompetence.niveauCompetence;block=tr;strconv=no;protect=no]</td>
		[onshow;block=begin;when [view.mode]=='edit']
			<td style="text-align:center;">
				<a href="?id=[cv.id;strconv=no;protect=no]&idForm=[TCompetence.id;block=tr;strconv=no;protect=no]&action=deleteCompetenceCV"><img title="Supprimer ce tag" src="./img/delete.png"></a>
			</td>
		[onshow;block=end]
		
	</tr>
	[onshow;block=begin;when [view.mode]=='edit']
	<tr>
		<td style="text-align:center;">[newCompetence.libelleCompetence;strconv=no;protect=no]</td>
		<td style="text-align:center;">[newCompetence.niveauCompetence;strconv=no;protect=no]</td>
		<td style="text-align:center;"><input type="submit" value="Ajouter" name="newCompetenceCV" class="button"></td>
	</tr>
	[onshow;block=end]
	</table>
	<br/><br/>


[onshow;block=begin;when [view.mode]=='view']
<table class="border" style="width:100%;">
				[onshow;block=begin;when [view.mode]=='view']
				<a  style="float:right;" class="butActionDelete" id="action-delete" onclick="document.location.href='?fk_user=[userCourant.id]&id=[cv.id]&action=deleteCV'">Supprimer</a>
				<a style="float:right;" class="butAction" href="?fk_user=[userCourant.id]">Annuler</a>
				<a style="float:right;" href="?id=[cv.id]&action=editCv&fk_user=[userCourant.id]" class="butAction">Modifier</a>
				[onshow;block=end]	
</table>
[onshow;block=end]


[onshow;block=begin;when [view.mode]=='edit']


<div class="tabsAction"  style="text-align:center">
	<input type="submit" value="Enregistrer" name="save" class="button"  onclick="document.location.href='?fk_user=[userCourant.id]'">
	&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?fk_user=[userCourant.id]'">
</div>
[onshow;block=end]

