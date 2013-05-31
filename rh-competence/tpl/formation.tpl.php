<table width="100%" class="border"><tbody>
	<tr><td width="25%" valign="top">Réf.</td><td>[user.id]</td></tr>
	<tr><td width="25%" valign="top">Nom</td><td>[user.lastname]</td></tr>
	<tr><td width="25%" valign="top">Prénom</td><td>[user.firstname]</td></tr>
</tbody></table>
<br>
<div class="fiche">
		<div>
			<h2 style="color: #2AA8B9;">Description de la formation</h2>	
			<table class="border" style="width:100%">			
				<tr>
					<td><b>Nom</b></td>
					<td><b>Prénom</b></td>
					<td><b>Date début</b></td>
					<td><b>Date fin</b></td>
					<td><b>Date d'expiration de la formation</b></td>
				</tr>
				<tr>
					<td>[userCourant.nom;block=tr;strconv=no;protect=no]</td>
					<td>[userCourant.prenom;block=tr;strconv=no;protect=no]</td>
					<td>[formation.date_debut;block=tr;strconv=no;protect=no]</td>
					<td>[formation.date_fin;strconv=no;protect=no]</td>
					<td>[formation.date_formationEcheance;strconv=no;protect=no]</td>
				</tr>
			</table>
			
			<br/><br/>
			<table class="border" style="width:100%">			
				<tr>
					<td><b>Coût de la formation</b></td>
					<td><b>Montant pris en charge par l'organisme</b></td>
					<td><b>Montant pris en charge par l'entreprise</b></td>
				</tr>
				<tr>
					<td>[formation.coutFormation;strconv=no;protect=no]€</td>
					<td>[formation.montantOrganisme;strconv=no;protect=no]€</td>
					<td>[formation.montantEntreprise;strconv=no;protect=no]€</td>
				</tr>
			</table>
			
			<br/><br/>
			<table class="border" style="width:100%">			
				<tr>
					<td><b>Libellé de la formation</b></td>
					<td><b>Lieu de la formation</b></td>
				</tr>
				<tr>
					<td>[formation.libelleFormation;strconv=no;protect=no]</td>
					<td>[formation.lieuFormation;strconv=no;protect=no]</td>
				</tr>
			</table>
			
			<br/><br/>
			<table class="border" style="width:100%">
				<tr>
					<td><b>Commentaires</b></td>
				</tr>
				<tr>
					<td>[formation.commentaireFormation;strconv=no;protect=no]</td>
				</tr>
			</table>
			<br/><br/>
			
			<table class="border" style="width:40%">
				[newCompetence.hidden;strconv=no;protect=no]
				[newCompetence.fk_user_formation;strconv=no;protect=no]
				<tr>
					<td><b>Compétences acquises</b></td>
					<td style="text-align:center;"><b>Niveau acquis</b></td>
					[onshow;block=begin;when [view.mode]=='edit']
						<td style="text-align:center;"><b>Action</b></td>
					[onshow;block=end]
				<tr>
					<td>[TCompetence.libelleCompetence;block=tr;strconv=no;protect=no]</td>
					<td>[TCompetence.niveauCompetence;block=tr;strconv=no;protect=no]</td>
					[onshow;block=begin;when [view.mode]=='edit']
						<td style="text-align:center;">
							<a href="?id=[formation.id;strconv=no;protect=no]&idForm=[TCompetence.id;block=tr;strconv=no;protect=no]&action=deleteCompetence"><img title="Supprimer ce tag" style="width:25px;" src="./img/delete_tag.png"></a>
						</td>
					[onshow;block=end]
					
				</tr>
				[onshow;block=begin;when [view.mode]=='edit']
				<tr>
					<td>[newCompetence.libelleCompetence;strconv=no;protect=no]</td>
					<td>[newCompetence.niveauCompetence;strconv=no;protect=no]</td>
					<td><input type="submit" value="Ajouter" name="newCompetence" class="button"></td>
				</tr>
				[onshow;block=end]
			</table>
			<br/><br/>
			
			<table class="border" style="width:100%;">
				[onshow;block=begin;when [view.mode]=='view']
				<span  style="float:right;" class="butActionDelete" id="action-delete" onclick="document.location.href='?fk_user=[userCourant.id]&id=[formation.id]&action=deleteFormation'">Supprimer</span>
				<a style="float:right;" class="butAction" href="?fk_user=[userCourant.id]">Annuler</a>
				<a style="float:right;" href="?id=[formation.id]&action=editFormation&fk_user=[userCourant.id]" class="butAction">Modifier</a>
				
				[onshow;block=end]	
			</table>

	</div>
</div>
	
[onshow;block=begin;when [view.mode]=='edit']
	<div class="tabsAction" style="text-align:center">
		<input type="submit" value="Enregistrer" name="save" class="button">
		&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?fk_user=[userCourant.id]'">
	</div>
	
</div>
[onshow;block=end]
