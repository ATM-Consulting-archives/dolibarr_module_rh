

    [view.head;strconv=no]

	<table class="border" style="width:100%;" >	
			<thead>
			<tr>
				<th>[translate.Code;strconv=no]</th>
				<th>[translate.Wording;strconv=no]</th>
				<th>[translate.Unit;strconv=no]</th>
				<th>[translate.AccountingOfficerCode;strconv=no]</th>
				<th>[translate.ColorCode]</th>
				<th>[translate.AskReservedAdmin;strconv=no]</th>
				<th>[translate.OnlyCountBusinessDay;strconv=no]</th>
				<th>[translate.AbsenceSecable;strconv=no]</th>
			    <th>[translate.AskDelete;strconv=no]</th>
               
            	
			</tr>
			</thead>
			<tbody>
			<tr>
				<td>[typeAbsence.typeAbsence; strconv=no; block=tr]</td>
				<td>[typeAbsence.libelleAbsence; strconv=no]</td>
				<td>[typeAbsence.unite; strconv=no]</td>
				<td>[typeAbsence.codeAbsence; strconv=no]</td>
				<td>[typeAbsence.colorId; strconv=no]</td>
				<td>[typeAbsence.admin; strconv=no]</td>
			    <td>[typeAbsence.decompteNormal; strconv=no][typeAbsence.isPresence; strconv=no]</td>
                <td>[typeAbsence.secable; strconv=no]</td>
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
				<td colspan="3">[typeAbsenceNew.decompteNormal; strconv=no][typeAbsenceNew.isPresence; strconv=no]</td>
				
				<td>[translate.New]</td>
			</tr>
			</tfoot>
	</table>
	
		<div class="tabsAction" >
			<input type="submit" value="[translate.Register]" name="save" class="button"  onclick="document.location.href='?id=[compteurGlobal.rowid]&action=view'">
		</div>	
