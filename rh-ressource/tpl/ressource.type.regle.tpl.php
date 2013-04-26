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
		[onshow;block=end]
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
		<td>Limite</td>
		[onshow;block=begin;when [view.mode]!='view']
			<td>[newRule.choixLimite; strconv=no]</td>
		[onshow;block=end]
		[onshow;block=begin;when [view.mode]=='view']
			<td>[newRule.choixLimiteViewMode; strconv=no]</td>
		[onshow;block=end]
		
			<script>
			$(document).ready(function(){
				$('#choixLimite_1').click(function(){
					$('#general').show();
					$('#interne').hide();
					$('#externe').hide();
				});
				$('#choixLimite_2').click(function(){
					$('#general').hide();
					$('#interne').show();
					$('#externe').show();
				})
				
				[onshow;block=begin;when [newRule.choixLimiteViewMode]=='Général']
					$('#interne,#externe').hide();
				[onshow;block=end]
				
				[onshow;block=begin;when [newRule.choixLimiteViewMode]!='Général']
					$('#general').hide();
				[onshow;block=end]
				
				
			})
			</script>	
		
	</tr>
	<tr id="general">
		<td>Limite Générale</td>
		<td>[newRule.dureeH;strconv=no]:[newRule.dureeM;strconv=no]    (HH:MM)</td>
	</tr>
	<tr id="interne">
		<td>Limite Interne</td>
		<td>[newRule.dureeHInt;strconv=no]:[newRule.dureeMInt;strconv=no]    (HH:MM)</td>
	</tr>
	<tr id="externe">
		<td>Limite Externe</td>
		<td>[newRule.dureeHExt;strconv=no]:[newRule.dureeMExt;strconv=no]    (HH:MM)</td>
	</tr>
	<tr>
		<td>Nature prise en charge par l'utilisateur</td>
		<td>[newRule.natureDeduire;strconv=no]</td>
	</tr>
	<tr>
		<td>Montant pris en charge par l'utilisateur</td>
		<td>[newRule.montantDeduire;strconv=no] €</td>
	</tr>
	<tr>
		<td>Données illimitées</td>
		<td>[newRule.dataIllimite;strconv=no]</td>
	</tr>
	<tr>
		<td>Données Iphone</td>
		<td>[newRule.dataIphone;strconv=no]</td>
	</tr>
	<tr>
		<td>SMS Illimités</td>
		<td>[newRule.smsIllimite;strconv=no]</td>
	</tr>
	<tr>
		<td>Forfait Mail</td>
		<td>[newRule.mailforfait;strconv=no]</td>
	</tr>
	
	<tr>
		<td>Numéros Exclus (les séparer par des ";")</td>
		<td>[newRule.numeroExclus;strconv=no]</td>
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
