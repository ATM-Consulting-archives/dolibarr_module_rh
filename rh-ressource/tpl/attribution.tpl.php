<h2>
	Attribution d'une ressource à un utilisateur
</h2>

<div>
				
	Utilisateur : [emprunt.fk_user;strconv=no;protect=no]<br>
	Type de la ressource : [emprunt.fk_rh_ressource_type;strconv=no;protect=no]
	[onshow;block=begin;when [view.mode]=='edit']
		<input type="submit" value="Valider" name="validerType" class="butAction">
	[onshow;block=end]<br>
	Ressource : [emprunt.fk_rh_ressource;strconv=no;protect=no]<br>
	<br>
	Dates de la réservation : du [emprunt.date_debut;strconv=no;protect=no] au [emprunt.date_fin;strconv=no;protect=no]<br> 
	
	
</div>



	
<div class="tabsAction" >
		[onshow;block=begin;when [view.mode]=='edit']
			<input type="submit" value="Enregistrer" name="save" class="button">
			&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[contrat.id]'">
		[onshow;block=end]
		[onshow;block=begin;when [view.mode]!='edit']
			<a class="butAction"  href="?id=[emprunt.id]&action=edit">Modifier</a>
			&nbsp; &nbsp;<a class="butActionDelete"  href="?id=[contrat.id]&action=delete">Supprimer</a>
		[onshow;block=end]
</div>

