[onshow;block=begin;when [view.mode]=='view']
	[view.head;strconv=no]                     
[onshow;block=end]  

[onshow;block=begin;when [view.mode]!='view']
	[view.onglet;strconv=no]                     
[onshow;block=end]  
	
			
<table width="100%" class="border">
	<tr><td width="20%">Code</td><td>[ressourceType.code; strconv=no]</td></tr>
	<tr><td width="20%">Libellé</td><td>[ressourceType.libelle; strconv=no]</td></tr>
</table>
<br>

[ressourceType.titreEvenement; strconv=no]

<table width="100%" class="border">
	<tr>
		<td width="20%">Libellé</td>
		<td>[newEvent.libelle;strconv=no]</td>
	</tr>
	<tr>
		<td>Code</td>
		<td>[newEvent.code;strconv=no]</td>
	</tr>
	<tr>
		<td>Code Analytique</td>
		<td>[newEvent.codeanalytique;strconv=no]</td>
	</tr>
	
</table>


[onshow;block=begin;when [view.mode]!='edit']		
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
