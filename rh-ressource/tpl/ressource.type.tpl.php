<h1>Type de ressource </h1>

<div>
	Code de la ressource : [ressourceType.code; strconv=no]</br></br>
	Libellé de la ressource : [ressourceType.libelle; strconv=no]</br></br>
</div>

<div>
<h2>Champs de la ressource</h2>


	<!-- entête du tableau -->
		<a>Code</a>
		<a>Libellé</a>
		<a>Type</a>
		<a>Libellé</a>
		<a>Obligatoire</a>
		<a>Action</a>
		
<ul id="sortable" >

	<li id="[ressourceField.indice;block=li;strconv=no;protect=no]" >
		[ressourceField.ordre;strconv=no;protect=no]
		<a>[onshow;block=begin;when [view.mode]=='edit']Déplacer[onshow;block=end]</a>
		<a>[ressourceField.code;strconv=no;protect=no]</a>
		<a>[ressourceField.libelle;strconv=no;protect=no]</a>
		<a>[ressourceField.type;strconv=no;protect=no]</a>
		<a>[ressourceField.obligatoire;strconv=no;protect=no]</a>
		<a>
		[onshow;block=begin;when [view.mode]=='edit']
			<button type="submit" value="[ressourceField.id;strconv=no;protect=no]" name="deleteField" class="button">Supprimer</button>
		[onshow;block=end]
		</a>
	</li>
	
	
	
	
	
	
	<!-- Nouveau field-->

	[onshow;block=begin;when [view.mode]=='edit']
	<li id="[newField.indice;strconv=no;protect=no]">
		[newField.ordre;strconv=no;protect=no]
		<a>Nouveau </a>
		<a>[newField.code;strconv=no;protect=no]</a>
		<a>[newField.libelle;strconv=no;protect=no]</a>
		<a>[newField.type;strconv=no;protect=no]</a>
		<a>[newField.obligatoire;strconv=no;protect=no]</a>
		<a><input type="submit" value="Ajouter" name="newField" class="button"></a>
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
[onshow;block=end]

</script>
</div>

<div class="tabsAction" >
		[onshow;block=begin;when [view.mode]=='edit']
			<input type="submit" value="Enregistrer" name="save" class="button">
			&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[ressourceType.id]'">
		[onshow;block=end]
		[onshow;block=begin;when [view.mode]!='edit']
			<a class="butAction"  href="?id=[ressourceType.id]&action=edit">Modifier</a>
			&nbsp; &nbsp;<a class="butActionDelete"  href="?id=[ressourceType.id]&action=delete">Supprimer</a>
			<!--
			<input type="button" value="Modifier" class="button"  onclick="document.location.href='?id=[ressourceType.id]&action=edit'">
			&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[ressourceType.id]'">-->
		[onshow;block=end]
</div>




