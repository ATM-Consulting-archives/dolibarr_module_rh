

    [view.head;strconv=no]

    [compteurGlobal.titreConges;strconv=no;protect=no]
	                             
                 
			<table class="border" style="width:100%;" >	

					<tr>
						<td style="width:30%;">[translate.NbDaysAcquiredByMonth;strconv=no;protect=no]</td>
						<td> [compteurGlobal.congesAcquisMensuelInit;strconv=no;protect=no]</td>
					</tr>
					<tr>
						<td style="width:30%;">[translate.NbDaysAcquiredByYear;strconv=no;protect=no]</td>
						<td> [compteurGlobal.congesAcquisAnnuelInit;strconv=no;protect=no]</td>
					</tr>
					
					<tr>
						<td>[translate.ClosingHolidayDate;strconv=no;protect=no]</td>
						<td>[compteurGlobal.date_congesClotureInit;strconv=no;protect=no]</td>
					</tr>
			</table>


	
	<br/><br/>
	
	 [compteurGlobal.titreRtt;strconv=no;protect=no]     
	 <br/>                                         
		<table class="border" style="width:100%">
				<tr>
					<td style="width:30%;">[translate.NbDayOffAcquired;strconv=no;protect=no]</td>
					<td>[compteurGlobal.rttCumuleInit;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td style="width:30%;">[translate.NbDayOffNCAcquired;strconv=no;protect=no]</td>
					<td>[compteurGlobal.rttNonCumuleInit;strconv=no;protect=no]</td>
				</tr>
				<tr>
						<td>[translate.ClosingDateDayOff;strconv=no;protect=no]</td>
						<td>[compteurGlobal.date_rttClotureInit;strconv=no;protect=no]</td>
					</tr>
		</table>     
	<br/><br/><br/>


	
		[onshow;block=begin;when [view.mode]=='edit']
		<div class="tabsAction" >
			<input type="submit" value="[translate.Register;strconv=no;protect=no]" name="save" class="button"  onclick="document.location.href='?id=[compteurGlobal.rowid]&action=view'">
			&nbsp; &nbsp; <input type="button" value="[translate.Cancel;strconv=no;protect=no]" name="cancel" class="button" onclick="document.location.href='?id=[compteurGlobal.rowid]&action=view'">
		
		[onshow;block=end]
		
		[onshow;block=begin;when [view.mode]!='edit']
			[onshow;block=begin;when [userCourant.modifierParamGlobalConges]=='1']
			<div class="tabsAction" >
				<a class="butAction"  href="?id=[compteurGlobal.rowid]&action=edit">[translate.Modify;strconv=no;protect=no]</a>
			</div>
			[onshow;block=end]
		[onshow;block=end]
	

		



