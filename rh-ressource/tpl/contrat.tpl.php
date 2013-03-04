<h1>Contrat</h1>

<div>

	[contrat.type;strconv=no;protect=no] 
	[onshow;block=begin;when [view.mode]=='edit']
		<input type="submit" value="Valider" name="validerType" class="butAction">
	[onshow;block=end]
	<br>




</div>


	
<div class="tabsAction" >

		[onshow;block=begin;when [view.mode]=='edit']
			<input type="submit" value="Enregistrer" name="save" class="button">
			&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[contrat.id]'">
		[onshow;block=end]
		[onshow;block=begin;when [view.mode]!='edit']
			<a class="butAction"  href="?id=[contrat.id]&action=edit">Modifier</a>
			&nbsp; &nbsp;<a class="butActionDelete"  href="?id=[contrat.id]&action=delete">Supprimer</a>
		[onshow;block=end]
</div>


