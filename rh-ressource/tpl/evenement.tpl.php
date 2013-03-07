[onshow;block=begin;when [view.mode]=='view']

        
                <div class="fiche"> <!-- begin div class="fiche" -->
                [view.head;strconv=no]
                
                        <div class="tabBar">
                                
[onshow;block=end] 

<h2>
	Historique des événements sur la ressource
</h2>

<div>
	<table>			
	<tr>
		<td>Date</td>
		<td>Type</td>
		<td>Commentaire</td>
		<td>Montant HT</td>
		
	</tr>
	<tr>
		<td>[evenement.date;block=tr;strconv=no;protect=no]</td>
		<td>[evenement.type;block=tr;strconv=no;protect=no]</td>
		<td>[evenement.motif;block=tr;strconv=no;protect=no]</td>
		<td>[evenement.montantHT;block=tr;strconv=no;protect=no]</td>
	</tr>
	

 
	
	</table>
	
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
