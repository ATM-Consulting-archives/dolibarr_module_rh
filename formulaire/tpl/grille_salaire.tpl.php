<div class="fiche">
			
		[onshow;block=begin;when [view.mode]=='edit']
			<h2 style="color: #2AA8B9;">Création d'une grille de salaire</h2>
		[onshow;block=end]
		[onshow;block=begin;when [view.mode]=='view']
			<h2 style="color: #2AA8B9;">Fiche grille salaire</h2>
		[onshow;block=end]
			<div class="tabBar">
				<table width="100%" class="border">
					<tr>
						<td>Nombre d'annees d'anciennete</td>
						<td>[grille_salaire.nb_annees_anciennete;block=tr;strconv=no;protect=no]</td>
					</tr>
					<tr>
						<td>Montant</td>
						<td>[grille_salaire.montant;block=tr;strconv=no;protect=no]</td>
					</tr>
				</table>
				<br/><br/>
				
				<br/>
				
				<table class="border" style="width:100%;">
					[onshow;block=begin;when [view.mode]=='view']
					<a style="text-align:center;width:20%;" class="butAction" href="fiche_type_poste.php?action=view&id=[grille_salaire.id_fiche_poste;block=tr;strconv=no;protect=no]">Retour</a>
						<a style="text-align:center;width:20%;" class="butAction" href="?action=edit&id=[grille_salaire.id;block=tr;strconv=no;protect=no]">Modifier</a>
						<a  style="text-align:center;width:20%;" class="butActionDelete" id="action-delete" onclick="if (window.confirm('Voulez vous supprimer l\'élément ?')){document.location.href='?id=[grille_salaire.id;block=tr;strconv=no;protect=no]&action=delete'}">Supprimer</a>
					[onshow;block=end]	
				</table>
			</div>

</div>



[onshow;block=begin;when [view.mode]=='edit']
<div class="tabsAction"  style="text-align:center">
	<input type="submit" value="Enregistrer" name="save" class="button" onclick="document.location.href='?id=[grille_salaire.id;block=tr;strconv=no;protect=no]&action=view'">
	&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[grille_salaire.id]&action=view'">
</div>
[onshow;block=end]

