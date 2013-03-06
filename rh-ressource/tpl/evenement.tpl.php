[onshow;block=begin;when [view.mode]=='view']

        
                <div class="fiche"> <!-- begin div class="fiche" -->
                [view.head;strconv=no]
                
                        <div class="tabBar">
                                
[onshow;block=end] 

<h2>
	Evenement sur la ressource
</h2>

<div>
				
	Utilisateur : [evenement.fk_user;strconv=no;protect=no]<br>
	Type de la ressource : [evenement.fk_rh_ressource_type;strconv=no;protect=no]
	[onshow;block=begin;when [view.mode]=='edit']
		<input type="submit" value="Valider" name="validerType" class="butAction">
	[onshow;block=end]<br>
	Ressource : [evenement.fk_rh_ressource;strconv=no;protect=no]<br>
	<br>
	Dates de la réservation : Du [evenement.date_debut;strconv=no;protect=no] au [evenement.date_fin;strconv=no;protect=no]<br> 
	
	
</div>



	
<div class="tabsAction" >
		[onshow;block=begin;when [view.mode]=='edit']
			<input type="submit" value="Enregistrer" name="save" class="button">
			&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[emprunt.id]'">
		[onshow;block=end]
		[onshow;block=begin;when [view.mode]!='edit']
			<a class="butAction"  href="?id=[emprunt.id]&action=edit">Modifier</a>
			&nbsp; &nbsp;<a class="butActionDelete"  href="?id=[emprunt.id]&action=delete">Supprimer</a>
		[onshow;block=end]
</div>

</div>
</div>
