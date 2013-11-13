[onshow;block=begin;when [view.mode]=='view']        
	[view.head;strconv=no]
[onshow;block=end]  

[onshow;block=begin;when [view.mode]!='view']        
	[view.onglet;strconv=no]
[onshow;block=end]

<div>

<table class="border" style="width:100%">
	<tr>
		<td>Intitulé de la formation</td>
		<td>[formation.libelle;strconv=no;protect=no]</td>
	</tr>
	<tr>
		<td>Description</td>
		<td>[formation.description;strconv=no;protect=no]</td>
	</tr>
	<tr>
		<td>Budget</td>
		<td>[formation.budget;strconv=no;protect=no]&nbsp;&euro;</td>
	</tr>		
	<tr>
		<td>Budget Consommé</td>
		<td>[formation.budgetConsomme;strconv=no;protect=no]&nbsp;&euro;</td>
	</tr>
	
</table>

</div>

<div class="tabsAction" style="text-align:center;" >
		[onshow;block=begin;when [view.mode]!='view']
			<input type="submit" value="Enregistrer" name="save" class="button">
			&nbsp;&nbsp;<input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href=''">
		[onshow;block=end]
		[onshow;block=begin;when [view.mode]=='view']
			<a class="butAction"  href="?id=[formation.ID]&action=edit">Modifier</a>
			&nbsp;&nbsp;<a class="butAction"  href="sessionFormation.php?idFormation=[formation.ID;strconv=no]&action=new">Ajouter une Session de Formation</a>
			&nbsp;&nbsp;<a class="butActionDelete"  onclick="if (window.confirm('Voulez vous supprimer l\'élément ?')){document.location.href='?id=[formation.ID;strconv=no]&action=delete'};">Supprimer</a>
		[onshow;block=end]
</div>


[onshow;block=begin;when [view.mode]=='view']
[onshow;block=end]

<div style="clear:both"></div>

