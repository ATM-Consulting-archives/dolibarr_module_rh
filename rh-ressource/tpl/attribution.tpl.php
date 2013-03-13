[onshow;block=begin;when [view.mode]=='view']

        
    <div class="fiche"> <!-- begin div class="fiche" -->
    [view.head;strconv=no]
    
            <div class="tabBar">
	                                
	
	
	<h2>
		Historique des attributions
	</h2>
	
	<table class="border" style="width:100%">
		<tr>
			<td style="width:10%">Utilisateur</td>
			<td style="width:10%">Date début</td>
			<td style="width:10%">Date fin</td>
			<td style="width:60%">Commentaire</td>
			<td style="width:10%">Action</td>
	
		</tr>	
		<tr>
			<td style="width:10%">[historique.user;block=tr;strconv=no;protect=no]</td>
			<td style="width:10%">[historique.date_debut;strconv=no;protect=no]</td>
			<td style="width:10%">[historique.date_fin;strconv=no;protect=no]</td>
			<td style="width:60%">[historique.commentaire;strconv=no;protect=no]</td>
			<td style="width:10%"><img src="./img/delete.png"  style="cursor:pointer;" onclick="document.location.href='?id=[ressource.id]&idAttribution=[historique.id]&action=deleteAttribution'"></td>
	 
		</tr>
	</table>
	
		
	<div class="tabsAction" >	
		<a class="butAction"  href="?id=[ressource.id]&action=edit">Ajouter</a>
	</div>
	
	
	</div>
	</div>
[onshow;block=end] 


[onshow;block=begin;when [view.mode]=='edit']
	<h2>
		Nouvelle attribution
	</h2>
	
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
	
		
	<div class="tabsAction" >
		<input type="submit" value="Enregistrer" name="newEmprunt" class="button">
		&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[ressource.id]'">
	</div>	

[onshow;block=end]


	

