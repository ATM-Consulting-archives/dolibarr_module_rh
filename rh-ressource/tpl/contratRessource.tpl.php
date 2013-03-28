[onshow;block=begin;when [view.mode]=='view']

        
                <div class="fiche"> <!-- begin div class="fiche" -->
                [view.head;strconv=no]
                
                        <div class="tabBar">
[onshow;block=end] 




<h2>Contrat associé à la ressource</h2>


<table class="border" style="width:50%">
	<tr>
		<td>Contrat</td>
		<td>[NAssociation.fk_rh_contrat;strconv=no;protect=no]</td>[NAssociation.fk_rh_ressource;strconv=no;protect=no]
	</tr>
	<tr>
		<td>Commentaire</td>
		<td>[NAssociation.commentaire;strconv=no;protect=no]</td>
	</tr>
</table>

[onshow;block=begin;when [view.mode]=='view']
	<div class="tabsAction" style="text-align:center;">
		<a class="butAction"  href="?id=[ressource.id]&idAssoc=[NAssociation.id]&action=edit">Modifier</a>
		<a class="butActionDelete"  href="?id=[ressource.id]&idAssoc=[NAssociation.id]&action=deleteAttribution">Supprimer</a>
		</div>
[onshow;block=end] 

[onshow;block=begin;when [view.mode]!='view']
	<div class="tabsAction" style="text-align:center;">
		<input type="submit" value="Enregistrer" name="save" class="button">
		&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[ressource.id]'">
	</div>
[onshow;block=end] 

</div>
</div>
