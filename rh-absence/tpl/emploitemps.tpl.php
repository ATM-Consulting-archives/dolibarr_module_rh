
        [view.head;strconv=no]

		<div style=" display:inline-block;">                  
		<table class="border" style="width:100%;" >	
				<br/><br/>
				<tr>
					<td>       </td>
					<td>Travaillé [emploiTemps.lundiam;strconv=no;protect=no]</td>
					<td>Travaillé</td>
					<td>Horaires</td>
				</tr>
				<tr>
					<td>Lundi</td>
					<td> Matin  <input type="checkbox" name="lundiam" value="1"></td>
					<td> Après-midi  <input type="checkbox" name="lundipm" value="1"> </td>
					<td>       </td>
					<td>       </td>
					<td>       </td>
					<td>       </td>

				</tr>
				<tr>
					<td>Mardi</td>
					<td> Matin  <input type="checkbox" name="mardiam" value="1"></td>
					<td> Après-midi  <input type="checkbox" name="mardiam" value="1"></td>
					<td>       </td>
					<td>       </td>
					<td>       </td>
					<td>       </td>
				</tr>
				<tr>
					<td>Mercredi</td>
					<td> Matin </td>
					<td> Après-midi </td>
					<td>       </td>
					<td>       </td>
					<td>       </td>
					<td>       </td>
				</tr>
				<tr>
					<td>Jeudi</td>
					<td> Matin </td>
					<td> Après-midi </td>
					<td>       </td>
					<td>       </td>
					<td>       </td>
					<td>       </td>
				</tr>
				<tr>
					<td>Vendredi</td>
					<td> Matin </td>
					<td> Après-midi </td>
					<td>       </td>
					<td>       </td>
					<td>       </td>
					<td>       </td>
				</tr>
				<tr>
					<td>Samedi</td>
					<td> Matin </td>
					<td> Après-midi </td>
					<td>       </td>
					<td>       </td>
					<td>       </td>
					<td>       </td>
				</tr>
				<tr>
					<td>Dimanche</td>
					<td> Matin </td>
					<td> Après-midi </td>
					<td>       </td>
					<td>       </td>
					<td>       </td>
					<td>       </td>
				</tr>
		</table>
		</div>

	

	<div class="tabsAction" >
		[onshow;block=begin;when [view.mode]=='edit']
			<input type="submit" value="Enregistrer" name="save" class="button"  onclick="document.location.href='?id=[compteurGlobal.rowid]&action=view'">
			&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[compteurGlobal.rowid]&action=view'">
		[onshow;block=end]
		
		[onshow;block=begin;when [view.mode]!='edit']
			<a class="butAction"  href="?id=[compteurGlobal.rowid]&action=edit">Modifier</a>
		[onshow;block=end]
	</div>

		



