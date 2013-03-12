[onshow;block=begin;when [view.mode]=='view']

        
                <div class="fiche"> <!-- begin div class="fiche" -->
                [view.head;strconv=no]
                
                        <div class="tabBar">
                                
[onshow;block=end] 

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
	
	[onshow;block=begin;when [view.mode]=='edit']
	<tr>
		<td>[NEmprunt.fk_user;strconv=no;protect=no]</td>
		<td>[NEmprunt.date_debut;strconv=no;protect=no]</td>[NEmprunt.type;strconv=no;protect=no]
		<td>[NEmprunt.date_fin;strconv=no;protect=no]</td>[NEmprunt.fk_rh_ressource;strconv=no;protect=no]
		<td>[NEmprunt.commentaire;strconv=no;protect=no]</td>
		<td><input type="submit" value="Ajouter" name="newEmprunt" class="button"></td>
	</tr>
	[onshow;block=end]
	
	
</table>




	
<div class="tabsAction" >
		[onshow;block=begin;when [view.mode]=='edit']
			<input type="submit" value="Enregistrer" name="save" class="button">
			&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[ressource.id]'">
		[onshow;block=end]
		[onshow;block=begin;when [view.mode]!='edit']
			<a class="butAction"  href="?id=[ressource.id]&action=edit">Ajouter</a>
		[onshow;block=end]
</div>

</div>
</div>
