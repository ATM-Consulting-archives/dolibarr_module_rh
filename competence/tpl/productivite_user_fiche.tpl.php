
<h2 style="color: #2AA8B9;">Productivité utilisateur</h2>

<div class="tabBar">
		
		<table width="100%" class="border">
			<tr>
				<td>Indice</td>
				<td>[productivite_user.indice;block=tr;strconv=no;protect=no]</td>
			</tr>
			<tr>
				<td>Date objectif</td>
				<td>[productivite_user.date_objectif;block=tr;strconv=no;protect=no]</td>
			</tr>
			<tr>
				<td>Objectif</td>
				<td>[productivite_user.objectif;block=tr;strconv=no;protect=no]</td>
			</tr>
		</table>
		<br/><br/>
		
		<br/>
		
		<table class="border" style="width:100%;">
			[onshow;block=begin;when [view.mode]=='view']
				<a style="text-align:center;width:20%;" class="butAction" href="productivite_user.php?action=view&fk_user=[user.id;block=tr;strconv=no;protect=no]">Retour</a>
				<a style="text-align:center;width:20%;" class="butAction" href="?action=edit&id=[productivite_user.id;block=tr;strconv=no;protect=no]&fk_user=[user.id;block=tr;strconv=no;protect=no]">Modifier</a>
			[onshow;block=end]	
		</table>

</div>



[onshow;block=begin;when [view.mode]=='edit']
<div class="tabsAction"  style="text-align:center">
	<input type="submit" value="Enregistrer" name="save" class="button" onclick="document.location.href='?id=[user.id;block=tr;strconv=no;protect=no]&action=view'">
	&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[productivite_user.id]&action=view&fk_user=[user.id]'">
</div>
[onshow;block=end]

