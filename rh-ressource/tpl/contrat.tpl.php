<?php 	llxHeader('', 'Liste des contrats');
?>
[onshow;block=begin;when [view.mode]=='view']

        
                <div class="fiche"> <!-- begin div class="fiche" -->
                [view.head;strconv=no]
                
                        
                                
[onshow;block=end]

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
	 	</tr><tr>
	 		<td>Loyer TTC</td>
	 		<td>[contrat.loyer_TTC;strconv=no;protect=no] €</td>
	 	</tr><tr>
	 		<td>TVA </td>
	 		<td>[contrat.TVA;strconv=no;protect=no] %</td>
	 	</tr>
	 	<tr>
	 		<td>Loyer HT</td>
	 		<td>[contrat.loyer_HT;strconv=no;protect=no] €</td>
	 	</tr>
	 	<tr>
	 		<td>[extraFields.nom;block=tr;strconv=no;protect=no]</td>
	 		<td>[extraFields.valeur;strconv=no;protect=no] [extraFields.unite;strconv=no;protect=no]</td>
	 	</tr>
	 	
	</table>
	
</div>


<script>
	$(document).ready( function(){
		//on empêche que la date de début dépasse pas celle de fin
		function comparerDates(){
			jd = parseInt($("#date_debut").val().substr(0,2));
			md = parseInt($("#date_debut").val().substr(3,2));
			ad = parseInt($("#date_debut").val().substr(6,4));
			jf = parseInt($("#date_fin").val().substr(0,2));
			mf = parseInt($("#date_fin").val().substr(3,2));
			af = parseInt($("#date_fin").val().substr(6,4));
			if(af<ad){
				$("#date_fin").val($("#date_debut").val());
				return;
			}
			else if(af==ad){
				
				if(mf<md){
					$("#date_fin").val($("#date_debut").val());
					return;}
					
				else if(mf==md){
					
					if(jf<jd){
						$("#date_fin").val($("#date_debut").val());
						return;}
					else if(jf=jd){return;}
					else{return;}
					
				}
				else{return;}
			}
			else{return;}
			
			
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
				&nbsp; &nbsp;<a class="butActionDelete"  href="?id=[contrat.id]&action=delete">Supprimer</a>
			[onshow;block=end]
		[onshow;block=end]
</div>
[onshow;block=end]
