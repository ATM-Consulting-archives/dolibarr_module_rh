[onshow;block=begin;when [view.mode]=='view']

	
		<div class="fiche"> <!-- begin div class="fiche" -->
		[view.head;strconv=no]
		
			<div class="tabBar">
				
[onshow;block=end]				
			
			<table width="100%" class="border">
			<tr><td width="20%">Code</td><td>[ressourceType.code; strconv=no]</td></tr>
			<tr><td width="20%">Libellé</td><td>[ressourceType.libelle; strconv=no]</td></tr>
			</table>
		

	<h2>Champs de la ressource</h2>

<div>
	<!-- entête du tableau -->
		[onshow;block=begin;when [view.mode]=='edit']
			<a style="margin-left : 40px; width:80px;display : inline-block;" ></a>
		[onshow;block=end]
		[onshow;block=begin;when [view.mode]!='edit']
			<a style="width:35px;display : inline-block;" ></a>
		[onshow;block=end]
		<a style="width:220px;display : inline-block;" >Code</a>
		<a style="width:220px;display : inline-block;">Libellé</a>
		<a style="width:140px;display : inline-block;">Type</a>
		<a style="width:70px;display : inline-block;">Obligatoire</a>
		[onshow;block=begin;when [view.mode]=='edit']
			<a style="width:100px;display : inline-block;">Action</a>
		[onshow;block=end]
		
	<ul id="sortable" style="list-style-type:none;">

	<!-- fields déjà existants -->
	<li id="[ressourceField.indice;block=li;strconv=no;protect=no]" >
		[ressourceField.ordre;strconv=no;protect=no]
		[onshow;block=begin;when [view.mode]=='edit']
			<a style="width:80px;display : inline-block;">Déplacer</a>
		[onshow;block=end]
		<a style="width:220px;display : inline-block;">[ressourceField.code;strconv=no;protect=no]</a>
		<a style="width:220px;display : inline-block;">[ressourceField.libelle;strconv=no;protect=no]</a>
		<a style="width:140px;display : inline-block;">[ressourceField.type;strconv=no;protect=no]</a>
		<a style="width:70px;display : inline-block;">[ressourceField.obligatoire;strconv=no;protect=no]</a>
		[onshow;block=begin;when [view.mode]=='edit']
			<a style="width:100px;display : inline-block;"><button type="submit" value="[ressourceField.id;strconv=no;protect=no]" name="deleteField" class="button">Supprimer</button></a>
		[onshow;block=end]
	</li>
	
[onshow;block=begin;when [view.mode]=='edit']
	<!-- Nouveau field-->
	<li id="[newField.indice;strconv=no;protect=no]">
		[newField.ordre;strconv=no;protect=no]
		<a style="width:80px;display : inline-block;">Nouveau</a>
		<a style="width:220px;display : inline-block;">[newField.code;strconv=no;protect=no]</a>
		<a style="width:220px;display : inline-block;">[newField.libelle;strconv=no;protect=no]</a>
		<a style="width:140px;display : inline-block;">[newField.type;strconv=no;protect=no]</a>
		<a style="width:70px;display : inline-block;">[newField.obligatoire;strconv=no;protect=no]</a>
		<a style="width:110px;display : inline-block;"><input type="submit" value="Ajouter" name="newField" class="button"></a>
	</li>
[onshow;block=end]

	</ul>

[onshow;block=begin;when [view.mode]=='edit']
	<script>
	 $(document).ready(function(){
	 	$( "#sortable" ).css('cursor','pointer');
		$(function() {
			$( "#sortable" ).sortable({
			   stop: function(event, ui) {
					var result = $('#sortable').sortable('toArray'); 
					for (var i = 0; i< result.length; i++){
						$(".ordre"+result[i]).attr("value", i)
						}
				}
			});
		});
	});
	</script>
[onshow;block=end]
[onshow;block=begin;when [view.mode]=='view']
	
		</div>
		</div>
		
		<div class="tabsAction">
		<a href="?id=[ressourceType.id]&action=edit" class="butAction">Modifier</a>
		<span class="butActionDelete" id="action-delete"  onclick="document.location.href='?action=delete&id=[ressourceType.id]'">Supprimer</span>
		</div>
		
		

	
[onshow;block=end]	
[onshow;block=begin;when [view.mode]!='view']

		<p align="center">
			<input type="submit" value="Enregistrer" name="save" class="button"> 
			&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[ressourceType.id]'">
		</p>
[onshow;block=end]	


