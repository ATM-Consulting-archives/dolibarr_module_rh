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
		<td>Montant HT</td>
		<td>[NEvent.montantHT;strconv=no;protect=no]</td>
	</tr>
	<tr>
		<td>TVA</td>
		<td>[NEvent.TVA;strconv=no;protect=no]</td>
	</tr>
</table>
	
	
[onshow;block=begin;when [view.mode]=='view']
	<div class="tabsAction" >		
		<a class="butAction"  href="?id=[ressource.id]&idEven=[NEvent.id]&action=edit">Modifier</a>
		<a class="butActionDelete"  href="?id=[ressource.id]&idEven=[NEvent.id]&action=deleteEvent">Supprimer</a>
	</div>
[onshow;block=end]
 
[onshow;block=begin;when [view.mode]!='view']
	<div class="tabsAction" >
		<input type="submit" value="Enregistrer" name="save" class="button">
		&nbsp; &nbsp; 
		<input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[ressource.id]'">
	</div>
[onshow;block=end] 

</div>
</div>
