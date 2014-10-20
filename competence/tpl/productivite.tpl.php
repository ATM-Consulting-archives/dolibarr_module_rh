[onshow;block=begin;when [view.mode]=='view']        
	[view.head;strconv=no]
[onshow;block=end]  

[onshow;block=begin;when [view.mode]!='view']        
	[view.onglet;strconv=no]
[onshow;block=end]
		
		<table width="100%" class="border">
			<tr>
				<td>Date objectif</td>
				<td>[productivite.date_objectif;block=tr;strconv=no;protect=no]</td>
			</tr>
			<tr>
				<td>Libellé</td>
				<td>[productivite.indice;block=tr;strconv=no;protect=no]</td>
			</tr>
			<tr>
				<td>Objectif</td>
				<td>[productivite.objectif;block=tr;strconv=no;protect=no]</td>
			</tr>
		</table>
		<br/><br/>
		
		<br/>
		
		<table class="border" style="width:100%;">
			[onshow;block=begin;when [view.mode]=='view']
				<a style="text-align:center;width:20%;" class="butAction" href="productivite_liste.php">Retour</a>
				<a style="text-align:center;width:20%;" class="butAction" href="?action=edit&id=[productivite.id;block=tr;strconv=no;protect=no]&fk_user=[user.id;block=tr;strconv=no;protect=no]">Modifier</a>
				<a  style="text-align:center;width:20%;" class="butActionDelete" id="action-delete" onclick="if (window.confirm('Voulez vous supprimer l\'élément ?')){document.location.href='?fk_user=[userCourant.id]&id=[productivite.id;block=tr;strconv=no;protect=no]&action=delete'}">Supprimer</a>
			[onshow;block=end]	
		</table>


[onshow;block=begin;when [view.mode]=='edit']
<div class="tabsAction"  style="text-align:center">
	<input type="submit" value="Enregistrer" name="save" class="button" onclick="document.location.href='?id=[user.id;block=tr;strconv=no;protect=no]&action=view'">
	&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[productivite.id]&action=view'">
</div>
[onshow;block=end]

