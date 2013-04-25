

    [view.head;strconv=no]


       
	<h1 style="color: #2AA8B9;">Congés payés</h1>  
	<br>                          
	<div style="display:inline-block; margin-top:-20px;">
		<div style="display:inline-block;">
		
                       
		<table class="border" style="width:150%;"  >
				<tr>
					<td colspan="2"><h2 style="color: #2AA8B9;">Année N-1</h2>  </td> 
				</tr>
				<tr>
					<td>Utilisateur Courant</td>
					<td>[userCourant.firstname;strconv=no;protect=no] [userCourant.lastname;strconv=no;protect=no]</td>
				</tr>	
				<tr>
					<td>Acquis Exercice</td>
					<td>[congesPrec.acquisEx;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>Acquis Ancienneté</td>
					<td> [congesPrec.acquisAnc;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>Acquis Hors-Période</td>
					<td>[congesPrec.acquisHorsPer;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>Report congés non soldés</td>
					<td>[congesPrec.reportConges;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td><b>Total Congés</b></td>
					<td><b>[congesPrec.total;strconv=no;protect=no] </b></td>
				</tr>
				<tr>
					<td>Congés Pris</td>
					<td>[congesPrec.congesPris;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td><b>Reste à prendre</b></td>
					<td><b>[congesPrec.reste;strconv=no;protect=no]</b></td>
				</tr>

				<tr>
					<td colspan="2"><h2 style="color: #2AA8B9;">Année N</h2>  </td> 
				</tr>
				<tr>
					<td>Acquis Exercice</td>
					<td>[congesCourant.acquisEx;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>Acquis Ancienneté</td>
					<td>[congesCourant.acquisAnc;strconv=no;protect=no]	</td>
				</tr>
				<tr>
					<td>Acquis Hors-Période</td>
					<td>[congesCourant.acquisHorsPer;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>Acquis Par Mois</td>
					<td> [congesCourant.nombreCongesAcquisMensuel;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td><b>Total</b></td>
					<td><b>[congesCourant.total;strconv=no;protect=no]</b></td>
				</tr>
				<tr>
					<td><b>Dernière clôture Congés</b></td>
					<td>[congesCourant.date_congesCloture;strconv=no;protect=no]</td>
				</tr>
		</table>


	
	<br/><br/><br/><br/>
	
	<h1 style="color: #2AA8B9;">RTT</h1>  
	                           
 
	                       
		<table class="border" style="width:150%">
				<tr>
					 <td colspan="2"><h2 style="color: #2AA8B9;">Compteur de RTT</h2>     </td>
				</tr>

				<tr>
					<td>Jours RTT Acquis</td>
					<td>[rttCourant.acquis;strconv=no;protect=no]</td>
				</tr>

				<tr>
					<td>Jours RTT Pris</td>
					<td>[rttCourant.pris;strconv=no;protect=no]	</td>
				</tr>
				<tr>
					<td>Jours RTT Restant à prendre</td>
					<td>[rttCourant.reste;strconv=no;protect=no]</td>
				</tr>

		
		             
				<tr>
					 <td colspan="2"><h2 style="color: #2AA8B9;">Méthode d'acquisition des jours</h2></td>
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
					<td>Mensuel</td>
					<td>[rttCourant.mensuelInit;strconv=no;protect=no]	</td>
				</tr>
				<tr>
					<td>Jours cumulés annuel</td>
					<td>[rttCourant.annuelCumuleInit;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>Jours non cumulés annuel</td>
					<td>[rttCourant.annuelNonCumuleInit;strconv=no;protect=no]</td>
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
					$("#rttTypeAcquisition").val("Annuel").attr('selected');
					$("#rttAcquisMensuelInit").val(0);
					$("#rttAcquisAnnuelCumuleInit").val(12);
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
				}
			});

		});
	</script>

