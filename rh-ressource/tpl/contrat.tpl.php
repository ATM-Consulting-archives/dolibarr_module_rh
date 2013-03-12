<h1>Contrat</h1>


<div>
	<table class="border" style="width:100%">
		<tr>
			<td>Libellé du contrat</td>
			<td>[contrat.libelle;strconv=no;protect=no]</td>
		</tr>
	 	<tr>
	 		<td>Type de ressource associée</td>
	 		<td>[contrat.typeRessource;strconv=no;protect=no]</td>
	 	</tr>
	 	<tr>
	 		<td>Type de contrat</td>
	 		<td>[contrat.typeContrat;strconv=no;protect=no]</td>
	 	</tr>
	 	<tr>
	 		<td>Tiers</td>
	 		<td>[contrat.tiersFournisseur;strconv=no;protect=no]</td>
	 	</tr>
	 	<tr>
	 		<td>Agence concernée</td>
	 		<td>[contrat.tiersAgence;strconv=no;protect=no]</td>
	 	</tr>
	 	<tr>
	 		<td>Date de début</td>
	 		<td>[contrat.date_debut;strconv=no;protect=no]</td>
	 	</tr>
	 	<tr>
	 		<td>Date de fin</td>
	 		<td>[contrat.date_fin;strconv=no;protect=no]</td>
	 	</tr><tr>
	 		<td>Loyer TTC</td>
	 		<td>[contrat.loyer_TTC;strconv=no;protect=no]</td>
	 	</tr><tr>
	 		<td>TVA </td>
	 		<td>[contrat.TVA;strconv=no;protect=no]</td>
	 	</tr><tr>
	 		<td>Loyer HT</td>
	 		<td>[contrat.loyer_HT;strconv=no;protect=no]</td>
	 	</tr>
	</table>
	
</div>



	
<div class="tabsAction" >
		[onshow;block=begin;when [view.mode]=='edit']
			<input type="submit" value="Enregistrer" name="save" class="button">
			&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[contrat.id]'">
		[onshow;block=end]
		[onshow;block=begin;when [view.mode]!='edit']
			<a class="butAction"  href="?id=[contrat.id]&action=edit">Modifier</a>
			&nbsp; &nbsp;<a class="butActionDelete"  href="?id=[contrat.id]&action=delete">Supprimer</a>
		[onshow;block=end]
</div>


