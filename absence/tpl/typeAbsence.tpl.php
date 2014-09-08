

    [view.head;strconv=no]

	<table class="border" style="width:100%;" >	
			<thead>
			<tr>
				<th>[translate.Code]</th>
				<th>[translate.Wording]</th>
				<th>[translate.Unit]</th>
				<th>[translate.AccountingOfficerCode]</th>
				<th>[translate.AskReservedAdmin]</th>
				<th>[translate.OnlyCountBusinessDay]</th>
				<th>[translate.AskDelete]</th>
				
			</tr>
			</thead>
			<tbody>
			<tr>
				<td>[typeAbsence.typeAbsence; strconv=no; block=tr]</td>
				<td>[typeAbsence.libelleAbsence; strconv=no]</td>
				<td>[typeAbsence.unite; strconv=no]</td>
				<td>[typeAbsence.codeAbsence; strconv=no]</td>
				<td>[typeAbsence.admin; strconv=no]</td>
				<td>[typeAbsence.decompteNormal; strconv=no][typeAbsence.isPresence; strconv=no]</td>
				<td>[typeAbsence.delete; strconv=no]</td>
			</tr>
			</tbody>
			<tfoot>
				<tr style="background-color: #3CC3D2; ">
				<td>[typeAbsenceNew.typeAbsence; strconv=no; block=tr]</td>
				<td>[typeAbsenceNew.libelleAbsence; strconv=no]</td>
				<td>[typeAbsenceNew.unite; strconv=no]</td>
				<td>[typeAbsenceNew.codeAbsence; strconv=no]</td>
				<td>[typeAbsenceNew.admin; strconv=no]</td>
				<td>[typeAbsenceNew.decompteNormal; strconv=no][typeAbsenceNew.isPresence; strconv=no]</td>
				
				<td>[translate.New]</td>
			</tr>
			</tfoot>
	</table>
	
		<div class="tabsAction" >
			<input type="submit" value="[translate.Register]" name="save" class="button"  onclick="document.location.href='?id=[compteurGlobal.rowid]&action=view'">
		</div>	
