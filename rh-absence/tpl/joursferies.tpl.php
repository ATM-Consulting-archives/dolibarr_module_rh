
        [view.head;strconv=no]

	<h2>Jours fériés </h2>
	

<div>
	<!-- entête du tableau -->
		[onshow;block=begin;when [view.mode]=='edit']
			<c style="margin-left : 40px; width:80px;display : inline-block;" ></c>
		[onshow;block=end]
		[onshow;block=begin;when [view.mode]!='edit']
			<c style="width:35px;display : inline-block;" ></c>
		[onshow;block=end]
		<c style="width:220px;display : inline-block;" >Jours non travaillés</c>
		<c style="width:220px;display : inline-block;">Matin</c>
		<c style="width:140px;display : inline-block;">Après-midi</c>

		[onshow;block=begin;when [view.mode]=='edit']
			<c style="width:100px;display : inline-block;">Action</c>
		[onshow;block=end]
		
	<ul id="sortable" style="list-style-type:none;">

	<!-- fields déjà existants -->
	<li id="[ressourceField.indice;block=li;strconv=no;protect=no]" >

		[onshow;block=begin;when [view.mode]=='edit']
			<c style="width:80px;display : inline-block;">Déplacer</c>
		[onshow;block=end]
		<c style="width:220px;display : inline-block;">[jour.date_jourOff;strconv=no;protect=no]</c>
		<c style="width:220px;display : inline-block;">[jour.matin;strconv=no;protect=no]</c>
		<c style="width:140px;display : inline-block;">[jour.apresmidi;strconv=no;protect=no]</c>

		[onshow;block=begin;when [view.mode]=='edit']
			<c style="width:100px;display : inline-block;">
				<img src="./img/delete.png"  onclick="document.location.href='?id=[ressourceType.id]&idField=[ressourceField.id]&action=deleteField'">
			</c>
		[onshow;block=end]
	</li>
	
[onshow;block=begin;when [view.mode]=='edit']
	<!-- Nouveau field-->
	<li id="[newField.indice;strconv=no;protect=no]">
		[newField.ordre;strconv=no;protect=no]
		<c style="width:80px;display : inline-block;">Nouveau</c>
		<c style="width:220px;display : inline-block;">[newJour.date_jourOff;strconv=no;protect=no]</c>
		<c style="width:220px;display : inline-block;">[newJour.matin;strconv=no;protect=no]</c>
		<c style="width:140px;display : inline-block;">[newJour.apresmidi;strconv=no;protect=no]</c>

		<c style="width:110px;display : inline-block;"><input type="submit" value="Ajouter" name="newField" class="button"></c>
	</li>
[onshow;block=end]

	</ul>

[onshow;block=begin;when [view.mode]!='edit']
	
		
		</div>
		
	<div class="tabsAction">
		<a href="?id=[ressourceType.id]&action=edit" class="butAction">Modifier</a>
		<span class="butActionDelete" id="action-delete"  onclick="document.location.href='?action=delete&id=[ressourceType.id]'">Supprimer</span>
	</div>
[onshow;block=end]	
[onshow;block=begin;when [view.mode]=='edit']


<div class="tabsAction" style="text-align:center;">
	<input type="submit" value="Enregistrer" name="save" class="button"> 
	&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[ressourceType.id]'">
</div>
[onshow;block=end]

		



