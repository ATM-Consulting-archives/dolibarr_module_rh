



<h2>Créer une ressource </h2>


<div>

	<!-- entête du tableau -->
<table class="border" style="width:100%">
	<tr>
		<td>Type</td>
		<td>[ressource.type;strconv=no;protect=no]
			[onshow;block=begin;when [view.mode]!='view']
				<input type="submit" value="Valider" name="validerType" class="button"
					[onshow;block=begin;when [ressource.type]=='Aucun type']
					disabled
					[onshow;block=end]
					>
			[onshow;block=end]
		</td>
	</tr>
	
</table>

</div>

