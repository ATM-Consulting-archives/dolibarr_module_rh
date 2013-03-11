[onshow;block=begin;when [view.mode]=='view']

        
                <div class="fiche"> <!-- begin div class="fiche" -->
                [view.head;strconv=no]
                
                        <div class="tabBar">
                                


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
		<!--que si on a les droits-->
			<td>Action</td>
		
		
	</tr>
	<tr>
		<td>[historique.date;block=tr;strconv=no;protect=no]</td>
		<td>[historique.type;strconv=no;protect=no]</td>
		<td>[historique.motif;strconv=no;protect=no]</td>
		<td>[historique.user;strconv=no;protect=no]</td>
		<td>[historique.commentaire;strconv=no;protect=no]</td>
		<td>[historique.montantHT;strconv=no;protect=no]</td>
		<td>[historique.TVA;strconv=no;protect=no]</td>
		<!--que si on a les droits-->
			<td><img src="./img/delete.png"  style="cursor:pointer;" onclick="document.location.href='?id=[ressource.id]&idEvent=[historique.id]&action=deleteEvent'"></td>
		
	</tr>
	
</table>	
</div>	

<div class="tabsAction" >		
	<a class="butAction"  href="?id=[ressource.id]&action=edit">Ajouter</a>
</div>

[onshow;block=end] 


[onshow;block=begin;when [view.mode]!='view']

<h2>
	Nouvel événement sur la ressource
</h2>


<table class="border" style="width:100%">
	<tr>
		<td>Date</td>
		<td>[NEvent.date;block=tr;strconv=no;protect=no]</td>
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
	
	<div class="tabsAction" >
		<input type="submit" value="Ajouter" name="newEvent" class="button">
		&nbsp; &nbsp; 
		<input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[ressource.id]'">
	</div>
[onshow;block=end] 

</div>
</div>
