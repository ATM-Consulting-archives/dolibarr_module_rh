[onshow;block=begin;when [view.mode]=='view']

        
                <div class="fiche"> <!-- begin div class="fiche" -->
                [view.head;strconv=no]
                
                        <div class="tabBar">
                                
[onshow;block=end] 

<h2>
	Historique des événements sur la ressource
</h2>

<div>
					
	<table class="border" style="width:100%">			
	<tr>
		<td>Date</td>
		<td>Type</td>
		<td>Motif</td>
		<td>Utilisateur</td>
		<td>Commentaire</td>
		<td>Montant HT</td>
		<td>TVA</td>
		[onshow;block=begin;when [view.mode]=='edit']
			<td>Action</td>
		[onshow;block=end]
		
	</tr>
	<tr>
		<td>[historique.date;block=tr;strconv=no;protect=no]</td>
		<td>[historique.type;strconv=no;protect=no]</td>
		<td>[historique.motif;strconv=no;protect=no]</td>
		<td>[historique.user;strconv=no;protect=no]</td>
		<td>[historique.commentaire;strconv=no;protect=no]</td>
		<td>[historique.montantHT;strconv=no;protect=no]</td>
		<td>[historique.TVA;strconv=no;protect=no]</td>
		[onshow;block=begin;when [view.mode]=='edit']
			<td><img src="./img/delete.png"  style="cursor:pointer;" onclick="document.location.href='?id=[ressource.id]&idEvent=[historique.id]&action=deleteEvent'"></td>
		[onshow;block=end]
	</tr>
	
	[onshow;block=begin;when [view.mode]=='edit']
	<tr>
		<td>[NEvent.date;block=tr;strconv=no;protect=no]</td>
		<td>[NEvent.type;strconv=no;protect=no]</td>
		<td>[NEvent.motif;strconv=no;protect=no]</td>[NEvent.fk_rh_ressource;strconv=no;protect=no]
		<td>[NEvent.user;strconv=no;protect=no]</td>
		<td>[NEvent.commentaire;strconv=no;protect=no]</td>
		<td>[NEvent.montantHT;strconv=no;protect=no]</td>
		<td>[NEvent.TVA;strconv=no;protect=no]</td>
		<td><input type="submit" value="Ajouter" name="newEvent" class="button"></td>
	</tr>
	[onshow;block=end]
	

 
	
	</table>
	
</div>



	
<div class="tabsAction" >
		[onshow;block=begin;when [view.mode]=='edit']
			<input type="submit" value="Enregistrer" name="save" class="button">
			&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[ressource.id]'">
		[onshow;block=end]
		[onshow;block=begin;when [view.mode]!='edit']
			<a class="butAction"  href="?id=[ressource.id]&action=edit">Modifier</a>
		[onshow;block=end]
</div>

</div>
</div>
