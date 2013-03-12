[onshow;block=begin;when [view.mode]=='view']

        
                <div class="fiche"> <!-- begin div class="fiche" -->
                [view.head;strconv=no]
                
                        <div class="tabBar">
                                


<h2>
	Liste des contrats associés à la ressource
</h2>

<div>

<table class="border" style="width:100%">			
	<tr>
		<td>Libelle</td>
		<td>Date début</td>
		<td>Date fin</td>
		<td>Bail</td>
		<td>Loyer TTC</td>
		<td>TVA</td>
		<!--que si on a les droits-->
			<td>Action</td>
		
		
	</tr>
	<tr>
		
		<td><a href='contrat.php?id=[contrats.id;block=tr;strconv=no;protect=no]&action=view' >[contrats.libelle;strconv=no;protect=no]</a></td>
		<td>[contrats.date_debut;strconv=no;protect=no]</td>
		<td>[contrats.date_fin;strconv=no;protect=no]</td>
		<td>[contrats.bail;strconv=no;protect=no]</td>
		<td>[contrats.loyer_TTC;strconv=no;protect=no]</td>
		<td>[contrats.TVA;strconv=no;protect=no]</td>
		<!--que si on a les droits-->
		<td><img src="./img/delete.png"  style="cursor:pointer;" onclick="document.location.href='?id=[ressource.id;strconv=no;protect=no]&idAssoc=[contrats.id;strconv=no;protect=no]&action=deleteAssoc'"></td>
		
	</tr>
	
</table>	
</div>	

<div class="tabsAction" >		
	<a class="butAction"  href="?id=[ressource.id]&action=edit">Ajouter</a>
</div>

[onshow;block=end] 


[onshow;block=begin;when [view.mode]!='view']

<h2>Nouveau contrat associé à la ressource</h2>


<table class="border" style="width:100%">
	<tr>
		<td>Contrat</td>
		<td>[NAssociation.fk_rh_contrat;strconv=no;protect=no]</td>[NAssociation.fk_rh_ressource;strconv=no;protect=no]
	</tr>
	<tr>
		<td>Commentaire</td>
		<td>[NAssociation.commentaire;strconv=no;protect=no]</td>
	</tr>
</table>
	
	<div class="tabsAction" >
		<input type="submit" value="Ajouter" name="newAssociation" class="button">
		&nbsp; &nbsp; 
		<input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[ressource.id]'">
	</div>
[onshow;block=end] 

</div>
</div>
