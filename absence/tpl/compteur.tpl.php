
    [view.head;strconv=no]

	[congesCourant.titreConges;strconv=no;protect=no] 
	<br><br/>                     
	<div style="display:inline-block; margin-top:-20px;">
		<div style="display:inline-block;">
		
                       
		<table class="border " style="width:200%;"  >
				<tr>
					<td colspan="2" style="color: #2AA8B9; font-size: 15px;font-family: arial,tahoma,verdana,helvetica; font-weight: bold; text-decoration: none; text-shadow: 1px 1px 2px #CFCFCF;" > 
				    [translate.Year;strconv=no;protect=no] N-1   </td> </div>
				</tr>
				<tr>
					<td>Utilisateur courant</td>
					<td>[userCourant.firstname;strconv=no;protect=no] [userCourant.lastname;strconv=no;protect=no]</td>
				</tr>	
				<tr>
					<td>Acquis sur exercice ([congesPrec.dates])</td>
					<td>[congesPrec.acquisEx;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>Acquis ancienneté</td>
					<td> [congesPrec.acquisAnc;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>Acquis hors-période</td>
					<td>[congesPrec.acquisHorsPer;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>Report congés non soldés</td>
					<td>[congesPrec.reportConges;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td><b>Total congés</b></td>
					<td><b>[congesPrec.total;strconv=no;protect=no] </b></td>
				</tr>
				<tr>
					<td>Congés pris et/ou posés</td>
					<td>[congesPrec.congesPris;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td><b>Reste à prendre avant le [congesPrec.dateFin]</b></td>
					<td><b>[congesPrec.reste;strconv=no;protect=no]</b></td>
				</tr>

				<tr>
					<td colspan="2" style="color: #2AA8B9; font-size: 15px;font-family: arial,tahoma,verdana,helvetica; font-weight: bold; text-decoration: none; text-shadow: 1px 1px 2px #CFCFCF;" > 
						Année N    </td> 
				</tr>
				<tr>
					<td>Acquis exercice</td>
					<td>[congesCourant.acquisEx;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>Acquis ancienneté</td>
					<td>[congesCourant.acquisAnc;strconv=no;protect=no]	</td>
				</tr>
				<tr>
					<td>Acquis hors-période</td>
					<td>[congesCourant.acquisHorsPer;strconv=no;protect=no]</td>
				</tr>
				
				<tr>
					<td>Congés pris</td>
					<td>[congesCourant.congesPris;strconv=no;protect=no]</td>
				</tr>
				
				<tr>
					<td>Nombre de jours acquis par mois</td>
					<td> [congesCourant.nombreCongesAcquisMensuel;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td><b>Total</b></td>
					<td><b>[congesCourant.total;strconv=no;protect=no]</b></td>
				</tr>
				<tr>
					<td><b>Dernière clôture congés</b></td>
					<td>[congesCourant.date_congesCloture;strconv=no;protect=no]</td>
				</tr>
		</table>


	
	<br/><br/>
	
	[rttCourant.titreRtt;strconv=no;protect=no]   
	 <br/>                              
		<table class="border" style="width:200%">
				<tr>
					 <td  colspan="2" style="color: #2AA8B9; font-size: 15px;font-family: arial,tahoma,verdana,helvetica; font-weight: bold; text-decoration: none; text-shadow: 1px 1px 2px #CFCFCF;" >
					 	Compteur de RTT cumulés </td>
				</tr>

				<tr>
					<td style="width:65%;">Jours RTT cumulés acquis</td>
					<td>[rttCourant.cumuleAcquis;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>Jours RTT cumulés pris</td>
					<td>[rttCourant.cumulePris;strconv=no;protect=no]	</td>
				</tr>
				<tr>
					<td>Jours RTT cumulés reportés N-1</td>
					<td>[rttCourant.cumuleReport;strconv=no;protect=no]	</td>
				</tr>
				<tr>
					<td><b>Jours RTT cumulés à prendre</b></td>
					<td>[rttCourant.cumuleTotal;strconv=no;protect=no]</td>
				</tr>
				<tr>
					 <td colspan="2" style="color: #2AA8B9; font-size: 15px;font-family: arial,tahoma,verdana,helvetica; font-weight: bold; text-decoration: none; text-shadow: 1px 1px 2px #CFCFCF;" >
					 	Compteur de RTT non cumulés</td>
				</tr>
				<tr>
					<td>Jours RTT non cumulés acquis</td>
					<td>[rttCourant.nonCumuleAcquis;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>Jours RTT non cumulés pris</td>
					<td>[rttCourant.nonCumulePris;strconv=no;protect=no]	</td>
				</tr>
				<tr>
					<td>Jours RTT non cumulés reportés N-1</td>
					<td>[rttCourant.nonCumuleReport;strconv=no;protect=no]	</td>
				</tr>
				<tr>
					<td><b>Jours RTT non cumulés à prendre</b></td>
					<td>[rttCourant.nonCumuleTotal;strconv=no;protect=no]</td>
				</tr>

		             
				<tr>
					  <td colspan="2" style="color: #2AA8B9; font-size: 15px;font-family: arial,tahoma,verdana,helvetica; font-weight: bold; text-decoration: none; text-shadow: 1px 1px 2px #CFCFCF;" >
					 	Méthode d'acquisition des jours</h2></td>
				</tr>
				<tr>
					<td>Métier collaborateur</td>
					<td>[rttCourant.rttMetier;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>Type acquisition</td>
					
					<td>[onshow;block=begin;when [view.mode]=='edit'] 
							[rttCourant.typeAcquisition;strconv=no;protect=no]
						[onshow;block=end]
						[onshow;block=begin;when [view.mode]!='edit']
						 	[rttCourant.rttTypeAcquis;strconv=no;protect=no]
						[onshow;block=end]
					 </td>
				</tr>
				<tr>
					<td>Jours RTT acquis par mois</td>
					<td>[rttCourant.mensuelInit;strconv=no;protect=no]	</td>
				</tr>
				<tr>
					<td>Jours RTT cumulés annuels</td>
					<td>[rttCourant.cumuleAcquisInit;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>Jours RTT non cumulés annuels</td>
					<td>[rttCourant.nonCumuleAcquisInit;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>Report des RTT</td>
					<td>[rttCourant.reportRtt;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td><b> Dernière clôture RTT</b></td>
					<td>[rttCourant.date_rttCloture;strconv=no;protect=no]</td>
				</tr>	
		</table>
	    </div>  
	</div>            


	
		
		[onshow;block=begin;when [view.mode]=='edit']
			<div class="tabsAction" >
			<input type="submit" value="Enregistrer" name="save" class="button"  onclick="document.location.href='?id=[rttCourant.id]&action=view'">
			&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[rttCourant.id]&action=view'">
			</div>
		[onshow;block=end]
		
		[onshow;block=begin;when [view.mode]!='edit']
			[onshow;block=begin;when [userCourant.modifierCompteur]=='1']
			<div class="tabsAction" >
				<a class="butAction"  href="?id=[rttCourant.id]&action=edit">Modifier</a>
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

