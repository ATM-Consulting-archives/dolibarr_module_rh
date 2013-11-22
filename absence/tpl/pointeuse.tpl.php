
	[view.head;strconv=no]
     
     			<table class="border" style="width:40%">
				<tr>
					<td>Matin heure d'arrivée</td>
					<td>[pointeuse.date_deb_am;strconv=no;protect=no]</td>
				</tr>	
				<tr>
					<td>Matin heure de départ</td>
					<td>[pointeuse.date_fin_am;strconv=no;protect=no]</td>
				</tr>	
				<tr>
					<td>Après-midi heure d'arrivée</td>
					<td>[pointeuse.date_deb_pm;strconv=no;protect=no]</td>
				</tr>	
				<tr>
					<td>Après-midi heure de départ</td>
					<td>[pointeuse.date_fin_pm;strconv=no;protect=no]</td>
				</tr>	
				<tr>
					<td>Jour</td>
					<td>[pointeuse.date_jour;strconv=no;protect=no]</td>
				</tr>	
				<tr>
					<td>Temps de présence constatée</td>
					<td>[pointeuse.time_presence;strconv=no;protect=no]</td>
				</tr>
			</table>

   		 <br/>

		[onshow;block=begin;when [view.mode]=='edit']
			<br>
			<input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[pointeuse.id]&action=view'">
			<input type="submit" value="Enregistrer" name="save" class="button">
		[onshow;block=end]
		[onshow;block=begin;when [view.mode]!='edit']
			<a href="?id=[pointeuse.id]&action=edit" class="butAction">Modifier</a>
			<span class="butActionDelete" id="action-delete"  onclick="if (window.confirm('Voulez-vous vraiment supprimer ce pointage ?')){document.location.href='?action=delete&id=[pointeuse.id]'};">Supprimer</span>			
		[onshow;block=end]
		<div style="clear:both;"></div>
	</div>


