[onshow;block=begin;when [view.mode]=='view']        
	[view.head;strconv=no]
[onshow;block=end]  

[onshow;block=begin;when [view.mode]!='view']        
	[view.onglet;strconv=no]
[onshow;block=end]

<div>

<table class="border" style="width:100%">
	<tr>
		<td>Date début</td>
		<td>[planFormation.date_debut;strconv=no;protect=no]</td>
	</tr>
	<tr>
		<td>Date fin</td>
		<td>[planFormation.date_fin;strconv=no;protect=no]</td>
	</tr>	
		
	<tr>
		<td>Intitulé du Plan</td>
		<td>[planFormation.libelle;strconv=no;protect=no]</td>
	</tr>
	<tr>
		<td>Description</td>
		<td>[planFormation.description;strconv=no;protect=no]</td>
	</tr>
	<tr>
		<td>Budget</td>
		<td colspan=3>[planFormation.budget;strconv=no;protect=no]&nbsp;&euro;</td>
	</tr>		
	
</table>

</div>
<br><br>

[onshow;block=begin;when [view.userRight]==1]
<div class="tabsAction" style="text-align:center;" >
		[onshow;block=begin;when [view.mode]!='edit']
			<input type="submit" value="Enregistrer" name="save" class="button">
		[onshow;block=end]
		[onshow;block=begin;when [view.mode]=='edit']
			<a class="butAction"  href="?id=[planFormation.numId]&action=edit">Modifier</a>
			&nbsp; &nbsp;<a class="butActionDelete"  onclick="if (window.confirm('Voulez vous supprimer l\'élément ?')){document.location.href='?id=[planFormation.numId]&action=delete'};">Supprimer</a>
		[onshow;block=end]
		&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href=''">
</div>
[onshow;block=end]


[onshow;block=begin;when [view.mode]=='view']
	[listeFormation.liste;strconv=no;protect=no]
[onshow;block=end]<br>

<div style="clear:both"></div>

