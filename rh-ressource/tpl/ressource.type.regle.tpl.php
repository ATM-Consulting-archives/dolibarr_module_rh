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
				[onshow;block=begin;when [newRule.choixApplicationViewMode]=='Utilisateur']
					$('#group').hide();
				[onshow;block=end]
				[onshow;block=begin;when [newRule.choixApplicationViewMode]=='Groupe']
					$('#user').hide();
				[onshow;block=end]
				[onshow;block=begin;when [newRule.choixApplicationViewMode]=='Tous']
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
	
	<tr>
		<td>Domaine d'application</td>
		<td>[newRule.objet; strconv=no]</td>
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
		<td>Période d'application</td>
		<td>[newRule.periode; strconv=no]</td>
	</tr>
	<tr>
		<td>Durée</td>
		<td>[newRule.dureeH;strconv=no]:[newRule.dureeM;strconv=no]    (HH:MM)</td>
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
