[onshow;block=begin;when [view.mode]=='view']
		
		[view.head;strconv=no]
                                
[onshow;block=end]
[onshow;block=begin;when [view.mode]!='view']
     
	[view.onglet;strconv=no]
	
	[onshow;block=begin;when [view.mode]=='new']
		[contrat.titreNouveau;strconv=no;protect=no]
	[onshow;block=end]
	[onshow;block=begin;when [view.mode]=='edit']
		[contrat.titreModification;strconv=no;protect=no]
	[onshow;block=end]
	
                            
[onshow;block=end]


<div>
	<table class="border" style="width:100%">
		<tr>
			<td style="width:20%">Libellé du contrat</td>
			<td>[contrat.libelle;strconv=no;protect=no]</td>
		</tr>
		<tr>
	 		<td>Numéro du contrat</td>
	 		<td>[contrat.numContrat;strconv=no;protect=no]</td>
	 	</tr>
	 	
	 	<tr>
	 		<td>Type de ressource associée</td>
	 		<td>[contrat.typeRessource;strconv=no;protect=no]</td>
	 	</tr>
	 	
	 	<tr>
	 		<td>Fournisseur concerné</td>
	 		<td>[contrat.tiersFournisseur;strconv=no;protect=no]</td>
	 	</tr>
	 	<tr>
	 		<td>Date de début</td>
	 		<td>[contrat.date_debut;strconv=no;protect=no]</td>
	 	</tr>
	 	<tr>
	 		<td>Date de fin</td>
	 		<td>[contrat.date_fin;strconv=no;protect=no]</td>
	 	</tr>
	 	<tr id="km" >
	 		<td>Kilomètrage</td>
	 		<td>[contrat.kilometre;strconv=no;protect=no] km</td>
	 	</tr>
	 	<tr id="dureeeenmois" >
	 		<td>Durée mois</td>
	 		<td>[contrat.dureemois;strconv=no;protect=no] mois</td>
	 	</tr>
	 	[onshow;block=begin;when [view.mode]!='view']
		 	<script>
		 		$('#fk_tier_fournisseur').change(function()
		 			{actuKm();})
		 		$(document).ready(function()
		 			{actuKm();});
		 		
		 		function actuKm(){
		 			if ($('#fk_tier_fournisseur option:selected').html()=='Parcours'){
		 				$('#km').show();
		 				$('#dureeeenmois').show();}
		 			else{
		 				$('#km').hide();
		 				$('#dureeeenmois').hide();
		 			}
		 		}
		 	</script>
	 	[onshow;block=end]
	 	[onshow;block=begin;when [view.mode]=='view']
	 	<script>
	 		if ('[contrat.tiersFournisseur;strconv=no;protect=no]'!='Parcours'){
	 			$('#km').hide();
	 			
	 		}
	 	</script>
	 	[onshow;block=end]
	 	
	 	[onshow;block=begin;when [view.userRightViewContrat]==1]
		 	<tr>
		 		<td>Montant Entretien</td>
		 		<td>[contrat.entretien;strconv=no;protect=no] €</td>
		 	</tr><tr>
		 		<td>Montant Assurance</td>
		 		<td>[contrat.assurance;strconv=no;protect=no] €</td>
		 	</tr><tr>
		 		<td>Loyer mensuel TTC</td>
		 		<td>[contrat.loyer_TTC;strconv=no;protect=no] €</td>
		 	</tr>
		 	<script>
				function actuHT(){
					ttc = parseFloat($('#loyer_TTC').val());
					tva = parseFloat($('#TVA option:selected').html());
					ht = ttc*(1-(tva/100));
					ht = ht.toFixed(2)
					$('#loyer_HT').val(ht);
				}
				
				$('#loyer_TTC').live('keyup', function(){
					actuHT();});
				$(function() {$('#TVA').change(function(){actuHT();	});	});
			</script>
		 	<tr>
		 		<td>TVA </td>
		 		<td>[contrat.TVA;strconv=no;protect=no] %</td>
		 	</tr>
		 	<tr>
		 		<td>Loyer mensuel HT</td>
		 		<td>[contrat.loyer_HT;strconv=no;protect=no] €</td>
		 	</tr>
	 	[onshow;block=end]
	</table>
	
</div>


<script>
	$(document).ready( function(){
		//on empêche que la date de début dépasse pas celle de fin
		function comparerDates(){
		
			dd = $("#date_debut").val().split("/");
			df = $("#date_fin").val().split("/");
			
			var dDebut = new Date(dd[2], dd[1]-1, dd[0], 0,0,0,0); 
			var dFin = new Date(df[2], df[1]-1, df[0], 0,0,0,0); 
			
			if(dFin.getTime() < dDebut.getTime()) {
				$("#date_fin").val($("#date_debut").val());
			}

		};
		
		$("#date_debut").change(comparerDates);
		$("#date_fin").change(comparerDates);
	});
</script>

[onshow;block=begin;when [view.userRight]==1]
<div class="tabsAction" style="text-align:center;">
		[onshow;block=begin;when [view.mode]=='edit']
			<input type="submit" value="Enregistrer" name="save" class="button">
			&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[contrat.id]'">
		[onshow;block=end]
		[onshow;block=begin;when [view.mode]!='edit']
			[onshow;block=begin;when [view.mode]=='new']
				<input type="submit" value="Enregistrer" name="save" class="button">
			[onshow;block=end]
			[onshow;block=begin;when [view.mode]!='new']
				<a class="butAction"  href="?id=[contrat.id]&action=edit">Modifier</a>
				&nbsp; &nbsp;<a class="butActionDelete"  onclick="if (window.confirm('Voulez vous supprimer l\'élément ?')){document.location.href='?id=[contrat.id]&action=delete'};">Supprimer</a>
			[onshow;block=end]
		[onshow;block=end]
</div>
[onshow;block=end]
