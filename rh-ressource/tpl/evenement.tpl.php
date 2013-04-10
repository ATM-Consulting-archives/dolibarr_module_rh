[onshow;block=begin;when [view.mode]=='view']
    <div class="fiche"> <!-- begin div class="fiche" -->
    [view.head;strconv=no]
[onshow;block=end]                                

<div>
	<table class="border" style="width:100%">
		<tr>
			<td>Date début</td>
			<td>[NEvent.date_debut;block=tr;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td>Date fin</td>
			<td>[NEvent.date_fin;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td>Type</td>
			<td>[NEvent.type;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td>Motif</td>
			<td>[NEvent.motif;strconv=no;protect=no]</td>[NEvent.fk_rh_ressource;strconv=no;protect=no]
		</tr>
		<tr>
			<td>Utilisateur</td>
			<td>[NEvent.user;strconv=no;protect=no]</td>
		</tr>
		
		<tr>
			<td>Commentaire</td>
			<td>[NEvent.commentaire;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td>Coût TTC</td>
			<td>[NEvent.coutTTC;strconv=no;protect=no] €</td>
		</tr>
		<tr>
			<td>Coût pour l'entreprise TTC</td>
			<td>[NEvent.coutEntrepriseTTC;strconv=no;protect=no] €</td>
		</tr>
		<tr>
			<td>TVA</td>
			<td>[NEvent.TVA;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td>Coût pour l'entreprise HT</td>
			<td>[NEvent.coutEntrepriseHT;strconv=no;protect=no] €</td>
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
				alert('année');
				$("#date_fin").val($("#date_debut").val());
				return;
			}
			else if(af==ad){
				
				if(mf<md){
					alert('mois');
					$("#date_fin").val($("#date_debut").val());
					return;}
					
				else if(mf==md){
					
					if(jf<jd){
						alert('jour');
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
	[onshow;block=begin;when [view.mode]=='view']
		<a class="butAction"  href="ressource.php?id=[ressource.id]&action=view">Ressource associée</a>
		<a class="butAction"  href="?id=[ressource.id]&idEven=[NEvent.id]&action=edit">Modifier</a>
		<a class="butActionDelete"  href="?id=[ressource.id]&idEven=[NEvent.id]&action=deleteEvent">Supprimer</a>
	[onshow;block=end]
 
	[onshow;block=begin;when [view.mode]!='view']
		<input type="submit" value="Enregistrer" name="save" class="button">
		&nbsp; &nbsp; 
		<input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[ressource.id]'">
	[onshow;block=end]
	 
</div>
[onshow;block=end]
