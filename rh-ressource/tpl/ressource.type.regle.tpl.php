[onshow;block=begin;when [view.mode]=='view']

        
                <div class="fiche"> <!-- begin div class="fiche" -->
                [view.head;strconv=no]
                
                        <div class="tabBar">
                                
[onshow;block=end]  
		
			
<table width="100%" class="border">
	<tr><td width="20%">Code</td><td>[ressourceType.code; strconv=no]</td></tr>
	<tr><td width="20%">Libellé</td><td>[ressourceType.libelle; strconv=no]</td></tr>
</table>


<h2>Règle</h2>
<table width="100%" class="border">
	<tr>
		<td>Choix d'application</td>
		<td>Tous/Groupe/User</td>
	</tr>
	<tr>
		<td>Domaine d'application</td>
		<td>[newRule.objet; strconv=no]</td>
	</tr>
	<tr>
		<td>Groupe</td>
		<td>[newRule.fk_group; strconv=no]</td>
	</tr>
	<tr>
		<td>Utilisateur</td>
		<td>[newRule.fk_user; strconv=no]</td>
	</tr>
	<tr>
		<td>Durée</td>
		<td>[newRule.duree; strconv=no]</td>
	</tr>
	<tr>
		<td>Montant</td>
		<td>[newRule.montant; strconv=no]</td>
	</tr>
</table>


[onshow;block=begin;when [view.mode]!='edit']
	
		
		</div>
		
	<div class="tabsAction">
		<a href="?id=[ressourceType.id]&idRegle=[newRule.id]&action=edit" class="butAction">Modifier</a>
		<a class="butActionDelete"  href="?id=[ressourceType.id]&idRegle=[newRule.id]&action=delete">Supprimer</a>
	</div>
[onshow;block=end]	
[onshow;block=begin;when [view.mode]=='edit']


<div class="tabsAction" style="text-align:center;">
	<input type="submit" value="Enregistrer" name="save" class="button"> 
	&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[ressourceType.id]'">
</div>
[onshow;block=end]
