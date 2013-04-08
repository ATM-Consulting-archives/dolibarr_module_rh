[onshow;block=begin;when [view.mode]=='view']

        
    <div class="fiche"> <!-- begin div class="fiche" -->
    [view.head;strconv=no]
    
            <div class="tabBar">
	                                
[onshow;block=end] 	
	
	<table class="border" style="width:100%">
		[NEmprunt.fk_rh_ressource;strconv=no;protect=no]
		[NEmprunt.type;strconv=no;protect=no]
		<tr>
			<td>Utilisateur</td>
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
		//on empêche que la date de début dépasse celle de fin
		 $('body').click( 	function(){
			if($("#date_debut").val()>$("#date_fin").val()){
				$("#date_fin").val($("#date_debut").val());
			}
		});	
		
	});
</script>


[onshow;block=begin;when [view.userRight]==1]
[onshow;block=begin;when [view.mode]=='view']
	<div class="tabsAction" style="text-align:center;">
		<a class="butAction"  href="?id=[ressource.id]&idEven=[NEmprunt.id]&action=edit">Modifier</a>
		<a class="butActionDelete"  href="?id=[ressource.id]&idEven=[NEmprunt.id]&action=deleteAttribution">Supprimer</a>
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

	</div>
	</div>

	

