[onshow;block=begin;when [view.mode]=='view']

        
                [view.head;strconv=no]
                
                        <div class="tabBar">
[onshow;block=end] 

<table class="border" style="width:100%">
		<tr>
			<td>Numéro Id</td>
			<td>[ressource.numId;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td>Libellé</td>
			<td>[ressource.libelle;strconv=no;protect=no]</td>
		</tr>
</table><br>


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
	[onshow;block=begin;when [view.userRight]==1]
	<div class="tabsAction" style="text-align:center;">
		<a class="butAction"  href="?id=[ressource.id]&idAssoc=[NAssociation.id]&action=edit">Modifier</a>
		<a class="butActionDelete"  href="?id=[ressource.id]&idAssoc=[NAssociation.id]&action=deleteAssoc">Supprimer</a>
	</div>
	[onshow;block=end]
[onshow;block=end] 

[onshow;block=begin;when [view.mode]!='view']
	<div class="tabsAction" style="text-align:center;">
		<input type="submit" value="Enregistrer" name="save" class="button">
		&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[ressource.id]'">
	</div>
[onshow;block=end] 

</div>
