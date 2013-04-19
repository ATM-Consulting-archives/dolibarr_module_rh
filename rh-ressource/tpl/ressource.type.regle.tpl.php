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
		<td>
			<input class="" type="radio" id="limGen" name="choixApplication" value="gen"  CHECKED >
			<label for="choixApplication_1">Générale</label>
			<input class="" type="radio" id="limIntExt" name="choixApplication" value="intext"    >
			<label for="choixApplication_2">Interne\Externe</label>	
			<script>
			$(document).ready(function(){
				
				$('#limGen').click(function(){
					$('#general').show();
					$('#interne').hide();
					$('#externe').hide();
				});
				$('#limIntExt').click(function(){
					$('#general').hide();
					$('#interne').show();
					$('#externe').show();
				})
			})
		</script>	
		</td>
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
		<td> Nature à déduire</td>
		<td>[newRule.natureDeduire;strconv=no]</td>
	</tr>
	<tr>
		<td>Montant à déduire</td>
		<td>[newRule.montantDeduire;strconv=no]</td>
	</tr>
	<tr>
		<td>Données illimités ?</td>
		<td>[newRule.dataIllimite;strconv=no]</td>
	</tr>
	<tr>
		<td>Données Iphone ?</td>
		<td>[newRule.dataIphone;strconv=no]</td>
	</tr>
	<tr>
		<td>SMS Illimités ?</td>
		<td>[newRule.smsIllimite;strconv=no]</td>
	</tr>
	<tr>
		<td>Forfait Mail ?</td>
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
