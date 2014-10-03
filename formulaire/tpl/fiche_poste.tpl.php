<div class="fiche">
			
		[onshow;block=begin;when [view.mode]=='edit']
			<h2 style="color: #2AA8B9;">Création d'un type de poste</h2>
		[onshow;block=end]
		[onshow;block=begin;when [view.mode]=='view']
			<h2 style="color: #2AA8B9;">Fiche type de poste</h2>
		[onshow;block=end]
			<div class="tabBar">
				<table width="100%" class="border">
					<tr>
						<td>Libellé type poste</td>
						<td>[fiche_poste.type_poste;block=tr;strconv=no;protect=no]</td>
					</tr>
					<tr>
						<td>Numéro convention</td>
						<td>[fiche_poste.numero_convention;block=tr;strconv=no;protect=no]</td>
					</tr>
					<tr>
						<td>Descriptif</td>
						<td>[fiche_poste.descriptif;block=tr;strconv=no;protect=no]</td>
					</tr>
				</table>
				<br/><br/>
				
				<br/>
				
				<table class="border" style="width:100%;">
					[onshow;block=begin;when [view.mode]=='view']
					<a style="text-align:center;width:20%;" class="butAction" href="liste_types_postes.php">Retour</a>
						<a style="text-align:center;width:20%;" class="butAction" href="?action=edit&id=[fiche_poste.id;block=tr;strconv=no;protect=no]">Modifier</a>
						<a  style="text-align:center;width:20%;" class="butActionDelete" id="action-delete" onclick="if (window.confirm('Voulez vous supprimer l\'élément ?')){document.location.href='?id=[fiche_poste.id;block=tr;strconv=no;protect=no]&action=delete'}">Supprimer</a>
					[onshow;block=end]	
				</table>
			</div>

</div>



[onshow;block=begin;when [view.mode]=='edit']
<div class="tabsAction"  style="text-align:center">
	<input type="submit" value="Enregistrer" name="save" class="button" onclick="document.location.href='?id=[fiche_poste.id;block=tr;strconv=no;protect=no]&action=view'">
	&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[fiche_poste.id]&action=view'">
</div>
[onshow;block=end]

