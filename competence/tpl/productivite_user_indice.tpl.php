
<h2 style="color: #2AA8B9;">Nouvel indice de productivité</h2>

<div class="tabBar">		
		<table width="100%" class="border">
			<tr>
				<td>Indice</td>
				<td>[productivite_indice.indice;block=tr;strconv=no;protect=no]</td>
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

</div>



[onshow;block=begin;when [view.mode]=='edit']
<div class="tabsAction"  style="text-align:center">
	<input type="submit" value="Enregistrer" name="save" class="button" onclick="document.location.href='?id=[user.id;block=tr;strconv=no;protect=no]&action=view'">
	&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='productivite_user_fiche.php?id=[productivite_indice.id]&action=view&fk_user=[user.id]'">
</div>
[onshow;block=end]

