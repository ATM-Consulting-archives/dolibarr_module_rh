

    [view.head;strconv=no]

	<table class="border" style="width:100%;" >	
			<thead>
			<tr>
				<th>[translate.Code;strconv=no]</th>
				<th>[translate.Wording;strconv=no]</th>
				<th>[translate.Unit;strconv=no]</th>
				<th>[translate.StartHour;strconv=no]</th>
				<th>[translate.EndHour;strconv=no]</th>
				
				<th>[translate.AccountingOfficerCode]</th>
				<th>[translate.ColorCode]</th>
				<th>[translate.AskReservedAdmin]</th>
				<th>[translate.AskDelete]</th>
				
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
			<input type="submit" value="[translate.Register]" name="save" class="button"  onclick="document.location.href='?id=[compteurGlobal.rowid]&action=view'">
		</div>	
