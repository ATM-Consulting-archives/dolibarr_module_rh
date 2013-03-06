[onshow;block=begin;when [view.mode]=='view']

        
                <div class="fiche"> <!-- begin div class="fiche" -->
                [view.head;strconv=no]
                
                        <div class="tabBar">
                                
[onshow;block=end] 

<h2>
	Attribution d'une ressource à un utilisateur
</h2>

<table class="border">
	<tr>
		<td>Utilisateur</td>
		<td>Date début</td>
		<td>Date fin</td>
		<td>Action</td>
	</tr>	
	<tr>
		<td>[historique.user;block=tr;strconv=no;protect=no]</td>
		<td>[historique.date_debut;strconv=no;protect=no]</td>
		<td>[historique.date_fin;strconv=no;protect=no]</td>
		<td></td>
	</tr>
	
	[onshow;block=begin;when [view.mode]=='edit']
	<tr>
		<td>[NEmprunt.fk_user;strconv=no;protect=no]</td>
		<td>[NEmprunt.date_debut;strconv=no;protect=no]</td>
		<td>[NEmprunt.date_fin;strconv=no;protect=no]</td>[NEmprunt.fk_rh_ressource;strconv=no;protect=no]
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
			<a class="butAction"  href="?id=[ressource.id]&action=edit">Modifier</a>
			&nbsp; &nbsp;<a class="butActionDelete"  href="?id=[ressource.id]&action=delete">Supprimer</a>
		[onshow;block=end]
</div>

</div>
</div>
