 [view.head;strconv=no]

	[newRule.titreRegle; strconv=no]

<table width="100%" class="border">
	<tr>
		<td>Choix d'application</td>
		[onshow;block=begin;when [view.mode]=='view']
		<td>
			[newRule.choixApplicationViewMode; strconv=no]
		</td>
		[onshow;block=end]
		[onshow;block=begin;when [view.mode]!='view']
		<td>
			[newRule.choixApplication; strconv=no]
		</td>
		<script>
			$(document).ready(function(){
				[onshow;block=begin;when [newRule.choixApplicationViewMode]== '[trad.user]']
					$('#group').hide();
				[onshow;block=end]
				[onshow;block=begin;when [newRule.choixApplicationViewMode]== '[trad.group]']
					$('#user').hide();
				[onshow;block=end]
				[onshow;block=begin;when [newRule.choixApplicationViewMode]== '[trad.all]']
					$('#user, #group').hide();
				[onshow;block=end]
				
				$('#choixApplication_3').click(function(){
					$('#user').show();
					$('#group').hide();
				});
				$('#choixApplication_2').click(function(){
					$('#group').show();
					$('#user').hide();
				})
				$('#choixApplication_1').click(function(){
					$('#group').hide();
					$('#user').hide();
				})
			})
		</script>
		[onshow;block=end]
	</tr>
	
	<tr id="group">
		<td>Groupe</td>
		<td>[newRule.fk_group; strconv=no]</td>
	</tr>
	<tr id="user">
		<td>Utilisateur</td>
		<td>[newRule.fk_user; strconv=no]</td>
	</tr>
	<tr>
		<td>Type d'absence concerné</td>
		<td>[newRule.typeAbsence;strconv=no]</td>
	</tr>
	<tr>
		<td>Nombre de jours cumulables</td>
		<td>[newRule.nbJourCumulable;strconv=no]</td>
	</tr>
	<tr>
		<td>Période d'application</td>
		<td>[newRule.periode;strconv=no]</td>
	</tr>
	<tr>
		<td>Prendre en compte la durée contigue (jour fériés, absences ou jour non travaillé les jours précédents ou suivants)</td>
		<td>[newRule.contigue;strconv=no]</td>
	</tr>
	<tr>
		<td>Ne pas considéré les jours non travaillé comme contigue</td>
		<td>[newRule.contigueNoJNT;strconv=no]</td>
	</tr>
	 
	<tr>
		<td>Mode restrictif</td>
		<td>[newRule.restrictif;strconv=no]</td>
	</tr>
</table>


[onshow;block=begin;when [view.mode]!='edit']
	<div class="tabsAction">
		<a href="?fk_user=[userCourant.id]" class="butAction">Retour</a>
		<a href="?id=[newRule.id]&action=edit&fk_user=[userCourant.id]" class="butAction">Modifier</a>
		<a class="butActionDelete"  onclick="if (window.confirm('Voulez vous supprimer cette règle ?')){document.location.href='?id=[newRule.id]&action=delete&fk_user=[userCourant.id]'};">Supprimer</a>
	</div>
		
		</div>
		
	
[onshow;block=end]	
[onshow;block=begin;when [view.mode]=='edit']


<div class="tabsAction" style="text-align:center;">
	<input type="submit" value="Enregistrer" name="save" class="button"> 
	&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[newRule.id]&fk_user=[userCourant.id]'">
</div>
[onshow;block=end]
