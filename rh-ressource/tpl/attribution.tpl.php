[onshow;block=begin;when [view.mode]=='view']   
   [view.head;strconv=no]
   [ressource.entete;strconv=no;protect=no]
[onshow;block=end] 	

[onshow;block=begin;when [view.mode]!='view']
    [view.onglet;strconv=no]

	[ressource.entete;strconv=no;protect=no]
	
    [onshow;block=begin;when [view.mode]=='new']
    	[ressource.titreNouvelleAttribution;strconv=no;protect=no]
    [onshow;block=end]
    
    [onshow;block=begin;when [view.mode]=='edit']
    	[ressource.titreModificationAttribution;strconv=no;protect=no]
    [onshow;block=end]
    
[onshow;block=end] 



	<table class="border" style="width:100%">
		[NEmprunt.fk_rh_ressource;strconv=no;protect=no]
		[NEmprunt.type;strconv=no;protect=no]
		<tr>
			<td style="width:20%">Utilisateur</td>
			<td>[NEmprunt.fk_user;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td>Date début</td>
			<td>[NEmprunt.date_debut;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td>Date fin</td>
			<td>[NEmprunt.date_fin;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td>Commentaire</td>
			<td>[NEmprunt.commentaire;strconv=no;protect=no]</td>
		</tr>
			

	</table>


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
[onshow;block=begin;when [view.mode]=='view']
	<div class="tabsAction" style="text-align:center;">
		<a class="butAction"  href="?id=[ressource.id]&idEven=[NEmprunt.id]&action=edit">Modifier</a>
		<a class="butActionDelete"  
		onclick="if (window.confirm('Voulez vous supprimer l\'élément ?')){document.location.href='?id=[ressource.id]&idEven=[NEmprunt.id]&action=deleteAttribution'};">Supprimer</a>
		</div>
[onshow;block=end]
[onshow;block=end] 

[onshow;block=begin;when [view.userRight]==1]
[onshow;block=begin;when [view.mode]!='view']
	<div class="tabsAction" style="text-align:center;">
		<input type="submit" value="Enregistrer" name="save" class="button">
		&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[ressource.id]'">
	</div>
[onshow;block=end] 
[onshow;block=end] 


	

