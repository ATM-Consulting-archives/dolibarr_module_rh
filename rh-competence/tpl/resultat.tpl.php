
<div>			
	[formation.titreResultat;strconv=no;protect=no]
	<br>
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
			<td style="width:30%"><b>Libellé de la formation</b></td>
			<td>[formation.libelleFormation;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td><b>Lieu de la formation</b></td>
			<td>[formation.lieuFormation;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td><b>Commentaires</b></td>
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
			[TCompetence.niveauCompetence;block=tr;strconv=no;protect=no]
			</td>
			
		</tr>	
	</table>
	
	<br/><br/>
	<table class="border" style="width:100%;">
		[onshow;block=begin;when [view.mode]=='view']
			<a style="float:right;" class="butAction" href="?fk_user=[userCourant.id]">Retour</a>
		[onshow;block=end]	
	</table>
</div>






