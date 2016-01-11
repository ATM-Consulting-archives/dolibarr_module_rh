
    [view.head;strconv=no]

	[congesCourant.titreConges;strconv=no;protect=no] 
	<br><br/>                     
	
		
                       
		<table class="border " style="width:100%;"  >
				<tr>
					<td colspan="2" style="color: #2AA8B9; font-size: 15px;font-family: arial,tahoma,verdana,helvetica; font-weight: bold; text-decoration: none; text-shadow: 1px 1px 2px #CFCFCF;" > 
				    [translate.Year;strconv=no;protect=no] N-1 (année en cours)</td> </div>
				</tr>
				<tr>
					<td width="30%">[translate.CurrentUser;strconv=no;protect=no]</td>
					<td>[userCourant.link;strconv=no;protect=no]</td>
				</tr>	
				<tr>
					<td>[translate.AcquiredOnExercise;strconv=no;protect=no] ([congesPrec.dates])</td>
					<td>[congesPrec.acquisEx;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>[translate.AcquiredSeniority;strconv=no;protect=no]</td>
					<td> [congesPrec.acquisAnc;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>[translate.AcquiredOutOfPeriod;strconv=no;protect=no]</td>
					<td>[congesPrec.acquisHorsPer;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>[translate.OpenPostponement;strconv=no;protect=no]</td>
					<td>[congesPrec.reportConges;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td><b>[translate.TotalHolidays;strconv=no;protect=no]</b></td>
					<td><b>[congesPrec.total;strconv=no;protect=no] </b></td>
				</tr>
				<tr>
					<td>[translate.HolidaysTaken;strconv=no;protect=no]</td>
					<td>[congesPrec.congesPris;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td><b>[translate.RemainingBefore;strconv=no;protect=no] [congesPrec.dateFin]</b></td>
					<td><b>[congesPrec.reste;strconv=no;protect=no]</b></td>
				</tr>

				<tr>
					<td colspan="2" style="color: #2AA8B9; font-size: 15px;font-family: arial,tahoma,verdana,helvetica; font-weight: bold; text-decoration: none; text-shadow: 1px 1px 2px #CFCFCF;" > 
						[translate.Year;strconv=no;protect=no] N (année suivante)   </td> 
				</tr>
				<tr>
					<td>[translate.AcquiredExercise;strconv=no;protect=no]</td>
					<td>[congesCourant.acquisEx;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>[translate.AcquiredSeniority;strconv=no;protect=no]</td>
					<td>[congesCourant.acquisAnc;strconv=no;protect=no]	</td>
				</tr>
				<tr>
					<td>[translate.AcquiredOutOfPeriod;strconv=no;protect=no]</td>
					<td>[congesCourant.acquisHorsPer;strconv=no;protect=no]</td>
				</tr>
				
				<tr>
					<td>[translate.HolidaysTaken;strconv=no;protect=no]</td>
					<td>[congesCourant.congesPris;strconv=no;protect=no]</td>
				</tr>
				
				<tr>
					<td>[translate.NbDaysAcquiredByMonth;strconv=no;protect=no]</td>
					<td> [congesCourant.nombreCongesAcquisMensuel;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>[translate.NbDaysAcquiredByYear;strconv=no;protect=no]</td>
					<td> [congesCourant.nombreCongesAcquisAnnuel;strconv=no;protect=no]</td>
				</tr>
				
				<tr>
					<td><b>[translate.Total;strconv=no;protect=no]</b></td>
					<td><b>[congesCourant.total;strconv=no;protect=no]</b></td>
				</tr>
				<tr>
					<td><b>[translate.LastClosingHoliday;strconv=no;protect=no]</b></td>
					<td>[congesCourant.date_congesCloture;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>[translate.acquisRecuperation;strconv=no;protect=no]</td>
					<td>[congesCourant.acquisRecuperation;strconv=no;protect=no]</td>
				</tr>
				
				
		</table>


	
	<br/><br/>
	
	[rttCourant.titreRtt;strconv=no;protect=no]   
	 <br/>                              
		<table class="border" style="width:100%">
				<tr>
					 <td  colspan="2" style="color: #2AA8B9; font-size: 15px;font-family: arial,tahoma,verdana,helvetica; font-weight: bold; text-decoration: none; text-shadow: 1px 1px 2px #CFCFCF;" >
					 	[translate.CounterCumulatedDayOff] </td>
				</tr>

				<tr>
					<td width="30%">[translate.CumulatedDayOffAcquired;strconv=no;protect=no]</td>
					<td>[rttCourant.cumuleAcquis;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>[translate.CumulatedDayOffTaken;strconv=no;protect=no]</td>
					<td>[rttCourant.cumulePris;strconv=no;protect=no]	</td>
				</tr>
				<tr>
					<td>[translate.PostponedCumulatedDayOff;strconv=no;protect=no] N-1</td>
					<td>[rttCourant.cumuleReport;strconv=no;protect=no]	</td>
				</tr>
				<tr>
					<td><b>[translate.CumulatedDayOffToTake;strconv=no;protect=no]</b></td>
					<td>[rttCourant.cumuleTotal;strconv=no;protect=no]</td>
				</tr>
				<tr>
					 <td colspan="2" style="color: #2AA8B9; font-size: 15px;font-family: arial,tahoma,verdana,helvetica; font-weight: bold; text-decoration: none; text-shadow: 1px 1px 2px #CFCFCF;" >
					 	[translate.CounterNonCumulatedDayOff;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>[translate.NonCumulatedDayOffAcquired;strconv=no;protect=no]</td>
					<td>[rttCourant.nonCumuleAcquis;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>[translate.NonCumulatedDayOffTaken;strconv=no;protect=no]</td>
					<td>[rttCourant.nonCumulePris;strconv=no;protect=no]	</td>
				</tr>
				<tr>
					<td>[translate.PostponedNonCumulatedDayOff;strconv=no;protect=no] N-1</td>
					<td>[rttCourant.nonCumuleReport;strconv=no;protect=no]	</td>
				</tr>
				<tr>
					<td><b>[translate.NonCumulatedDaysOffToTake;strconv=no;protect=no]</b></td>
					<td>[rttCourant.nonCumuleTotal;strconv=no;protect=no]</td>
				</tr>

		             
				<tr>
					  <td colspan="2" style="color: #2AA8B9; font-size: 15px;font-family: arial,tahoma,verdana,helvetica; font-weight: bold; text-decoration: none; text-shadow: 1px 1px 2px #CFCFCF;" >
					 	[translate.AcquisitionMethodOfDays;strconv=no;protect=no]</h2></td>
				</tr>
				<tr>
					<td>[translate.CollabJob;strconv=no;protect=no]</td>
					<td>[rttCourant.rttMetier;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>[translate.AcquisitionType;strconv=no;protect=no]</td>
					
					<td>[onshow;block=begin;when [view.mode]=='edit'] 
							[rttCourant.typeAcquisition;strconv=no;protect=no]
						[onshow;block=end]
						[onshow;block=begin;when [view.mode]!='edit']
						 	[rttCourant.rttTypeAcquis;strconv=no;protect=no]
						[onshow;block=end]
					 </td>
				</tr>
				<tr>
					<td>[translate.AcquiredDaysOffPerMonth;strconv=no;protect=no]</td>
					<td>[rttCourant.mensuelInit;strconv=no;protect=no]	</td>
				</tr>
				<tr>
					<td>[translate.YearlyCumulatedDaysOff;strconv=no;protect=no]</td>
					<td>[rttCourant.cumuleAcquisInit;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>[translate.YearlyNonCumulatedDaysOff;strconv=no;protect=no]</td>
					<td>[rttCourant.nonCumuleAcquisInit;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>[translate.DaysOffPostponement;strconv=no;protect=no]</td>
					<td>[rttCourant.reportRtt;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td><b>[translate.LastClosingDayOff;strconv=no;protect=no]</b></td>
					<td>[rttCourant.date_rttCloture;strconv=no;protect=no]</td>
				</tr>	
		</table>
	       


	
		
		[onshow;block=begin;when [view.mode]=='edit']
			<div class="tabsAction" >
			<input type="submit" value="[translate.Register;strconv=no;protect=no]" name="save" class="button"  onclick="document.location.href='?id=[rttCourant.id]&action=view'">
			&nbsp; &nbsp; <input type="button" value="[translate.Cancel;strconv=no;protect=no]" name="cancel" class="button" onclick="document.location.href='?id=[rttCourant.id]&action=view'">
			</div>
		[onshow;block=end]
		
		[onshow;block=begin;when [view.mode]!='edit']
			[onshow;block=begin;when [userCourant.modifierCompteur]=='1']
			<div class="tabsAction" >
				<a class="butAction"  href="?id=[rttCourant.id]&action=edit">[translate.Modify;strconv=no;protect=no]</a>
			</div>
			[onshow;block=end]
		[onshow;block=end]
		
		[onshow;block=begin;when [view.mode]!='edit']
			[onshow;block=begin;when [userCourant.modifierCompteur]!='1']
			 <br/> <br/>
			[onshow;block=end]
		[onshow;block=end]
		

		
		
		<script>
		$(document).ready( function(){
			//on empêche que la date de début dépasse celle de fin
			$('#rttMetier').change( 	function(){
				
				
				if($('#rttMetier').val()=="cadre"){
					$("#rttTypeAcquisition").val("Annuel").attr('selected');
					$("#rttAcquisMensuelInit").val(0);
					$("#rttAcquisAnnuelCumuleInit").val(12);
					$("#rttAcquisAnnuelNonCumuleInit").val(0);
				}
				else if($('#rttMetier').val()=="noncadre37cpro"){
					$("#rttTypeAcquisition").val("Annuel").attr('selected');
					$("#rttAcquisMensuelInit").val(0);
					$("#rttAcquisAnnuelCumuleInit").val(5);
					$("#rttAcquisAnnuelNonCumuleInit").val(7);
				}
				else if($('#rttMetier').val()=="noncadre37cproinfo"){
					$("#rttTypeAcquisition").val("Mensuel").attr('selected');
					$("#rttAcquisMensuelInit").val(1);
					$("#rttAcquisAnnuelCumuleInit").val(0);
					$("#rttAcquisAnnuelNonCumuleInit").val(0);
				}
				else if($('#rttMetier').val()=="noncadre38cpro"){
					$("#rttTypeAcquisition").val("Annuel").attr('selected');
					$("#rttAcquisMensuelInit").val(0);
					$("#rttAcquisAnnuelCumuleInit").val(3);
					$("#rttAcquisAnnuelNonCumuleInit").val(3);
				}
				else if($('#rttMetier').val()=="noncadre38cproinfo"){
					$("#rttTypeAcquisition").val("Mensuel").attr('selected');
					$("#rttAcquisMensuelInit").val(0.5);
					$("#rttAcquisAnnuelCumuleInit").val(0);
					$("#rttAcquisAnnuelNonCumuleInit").val(0);
				}
				else if($('#rttMetier').val()=="noncadre39"){
					$("#rttTypeAcquisition").val("Annuel").attr('selected');
					$("#rttAcquisMensuelInit").val(0);
					$("#rttAcquisAnnuelCumuleInit").val(0);
					$("#rttAcquisAnnuelNonCumuleInit").val(0);
				}else{
					$("#rttTypeAcquisition").val("Annuel").attr('selected');
					$("#rttAcquisMensuelInit").val(0);
					$("#rttAcquisAnnuelCumuleInit").val(0);
					$("#rttAcquisAnnuelNonCumuleInit").val(0);
				}
			});

		});
	</script>

