

    [view.head;strconv=no]

	<table class="border" style="width:100%;" >	
			<thead>
			<tr>
				<th>Code</th>
				<th>Libellé</th>
				<th>unité</th>
				<th>heure de début</th>
				<th>heure de fin</th>
				
				<th>Code Comptable</th>
				<th>Code couleur</th>
				<th>Réservé Admin ?</th>
				<th>Supprimer ?</th>
				
			</tr>
			</thead>
			<tbody>
			<tr>
				<td>[typeAbsence.typeAbsence; strconv=no; block=tr]</td>
				<td>[typeAbsence.libelleAbsence; strconv=no]</td>
				<td>[typeAbsence.unite; strconv=no]</td>
				<td>[typeAbsence.hourStart; strconv=no]</td>
				<td>[typeAbsence.hourEnd; strconv=no]</td>
				<td>[typeAbsence.codeAbsence; strconv=no]</td>
				<td>[typeAbsence.colorId; strconv=no]</td>
			
			
				<td>[typeAbsence.admin; strconv=no][typeAbsence.decompteNormal; strconv=no][typeAbsence.isPresence; strconv=no]</td>
				<td>[typeAbsence.delete; strconv=no]</td>
			</tr>
			</tbody>
			<tfoot>
				<tr style="background-color: #3CC3D2; ">
				<td>[typeAbsenceNew.typeAbsence; strconv=no; block=tr]</td>
				<td>[typeAbsenceNew.libelleAbsence; strconv=no]</td>
				<td>[typeAbsenceNew.unite; strconv=no]</td>
				<td>[typeAbsenceNew.hourStart; strconv=no]</td>
				<td>[typeAbsenceNew.hourEnd; strconv=no]</td>
				<td>[typeAbsenceNew.codeAbsence; strconv=no]</td>
				<td>[typeAbsenceNew.colorId; strconv=no]</td>
				<td>[typeAbsenceNew.admin; strconv=no][typeAbsenceNew.decompteNormal; strconv=no][typeAbsenceNew.isPresence; strconv=no]</td>
				
				<td>Nouveau</td>
			</tr>
			</tfoot>
	</table>
	
		<div class="tabsAction" >
			<input type="submit" value="Enregistrer" name="save" class="button"  onclick="document.location.href='?id=[compteurGlobal.rowid]&action=view'">
		</div>	
