
<div class="fiche">
    <div class="tabBar">
		<div>			
			<h2 style="color: #2AA8B9;">Description de la formation correspondant à la compétence recherchée</h2>	
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
			
			<table class="border" style="width:30%">
				<tr>
					<td><b>Compétences acquises</b></td>
				</tr>
				<tr>
					<td>
					[TCompetence.libelleCompetence;block=tr;strconv=no;protect=no]
					</td>
					
				</tr>	
			</table>
			
			<br/><br/>
			<table class="border" style="width:100%;">
				[onshow;block=begin;when [view.mode]=='view']
				<a style="float:right;" class="butAction" href="?fk_user=[userCourant.id]">Annuler</a>
				[onshow;block=end]	
			</table>
		</div>
	</div>
</div>




