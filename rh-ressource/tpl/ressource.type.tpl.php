<h1>Type de ressource </h1>

<div>
	Code de la ressource : [ressourceType.code; strconv=no]</br></br>
	Libellé de la ressource : [ressourceType.libelle; strconv=no]</br></br>
</div>


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
			<a style="width:100px;display : inline-block;"><button type="button" value="[ressourceField.id;strconv=no;protect=no]" name="deleteField" onclick="submit();" class="button">Supprimer</button></a>
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

</div>


<div class="tabsAction" >
	[onshow;block=begin;when [view.mode]=='edit']
			<input type="button" value="Enregistrer" name="save" class="button" onclick="submit();">
			&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[ressourceType.id]'">
	[onshow;block=end]				
	[onshow;block=begin;when [view.mode]!='edit']
			<a class="butAction"  href="?id=[ressourceType.id]&action=edit">Modifier</a>
			&nbsp; &nbsp;<a class="butActionDelete"  href="?id=[ressourceType.id]&action=delete">Supprimer</a>
		
	[onshow;block=end]
</div>



