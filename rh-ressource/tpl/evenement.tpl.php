[onshow;block=begin;when [view.mode]=='view']
    <div class="fiche"> <!-- begin div class="fiche" -->
    [view.head;strconv=no]
        <div class="tabBar">
[onshow;block=end]                                



<h2>Evénement sur la ressource</h2>


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
		<td>Coût HT</td>
		<td>[NEvent.coutHT;strconv=no;protect=no]</td>
	</tr>
	<tr>
		<td>Coût pour l'entreprise HT</td>
		<td>[NEvent.coutEntrepriseHT;strconv=no;protect=no]</td>
	</tr>
	<tr>
		<td>TVA</td>
		<td>[NEvent.TVA;strconv=no;protect=no]</td>
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
<div class="tabsAction" style="text-align:center;">
	[onshow;block=begin;when [view.mode]=='view']
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
</div>
</div>
