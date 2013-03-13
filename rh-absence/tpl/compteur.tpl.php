


	[onshow;block=begin;when [view.mode]=='view']
        	[view.head;strconv=no]
     [onshow;block=end] 
        
       
            
            <h1 style="color: #2AA8B9;"> Jours de Congés</h1>                         

			<table  border ="1" style="display: inline-block; margin-left:20px;">
				<tr>
					<td>
						<div>
							<h2>Jours de congés payés année N-1 </h2>
							<b>Acquis</b><br/>
							Utilisateur courant : [congesPrec.user;strconv=no;protect=no]<br/>
							
							Acquis Exercice : [congesPrec.acquisEx;strconv=no;protect=no]	<br/>
							Acquis Ancienneté : [congesPrec.acquisAnc;strconv=no;protect=no]	<br/>
							Acquis Hors-Période : [congesPrec.acquisHorsPer;strconv=no;protect=no]	<br/>
							
							<br/>
							Report congés non soldés : [congesPrec.reportConges;strconv=no;protect=no]<br/>
							
							<br/>
							<b>Total : [congesPrec.total;strconv=no;protect=no] </b><br/>
							Pris : [congesPrec.congesPris;strconv=no;protect=no]<br/>
							<b>Reste à prendre : [congesPrec.reste;strconv=no;protect=no]</b><br/>
						</div>
					</td>
				<tr>
			</table>
		
			<table style="margin-left: 50px; display: inline-block; "  border ="1">
				<tr>
					<td>
						<div>
							<h2>Jours de congés payés année N </h2>
							<b>Acquis</b><br/>
							Acquis Exercice : 	[congesCourant.acquisEx;strconv=no;protect=no]<br/>
							Acquis Ancienneté : [congesCourant.acquisAnc;strconv=no;protect=no]	<br/>
							Acquis Hors-Période : [congesCourant.acquisHorsPer;strconv=no;protect=no]	<br/>
							Acquis Par Mois : [congesCourant.nombreCongesAcquisMensuel;strconv=no;protect=no]	<br/>
							<br/>
							<b>Total : [congesCourant.total;strconv=no;protect=no]</b><br/>

							<b> Dernière clôture Congés : [congesCourant.date_congesCloture;strconv=no;protect=no]</b><br/>
						</div>
					</td>
				<tr>
			</table>

			
			<br/><br/><br/>
			  <h1 style="color: #2AA8B9;"> Jours de RTT</h1> 	
			
			<table  border ="1" style="display: inline-block; margin-left:20px;">
				<tr>
					<td>
						<div>
							<h2>Crédit RTT</h2>
							<b>Acquis</b><br/>
							Jours RTT Acquis : [rttCourant.acquis;strconv=no;protect=no]	<br/>
							Jours RTT Pris : [rttCourant.pris;strconv=no;protect=no]<br/>
							Jours RTT Restant à prendre :	[rttCourant.reste;strconv=no;protect=no]<br/>
						</div>
					</td>
				<tr>
			</table>
		
			<table style="margin-left: 50px; display: inline-block;"  border ="1" >
				<tr>
					<td>
						<div>
							<h2>Acquisition des jours</h2>
							Type acquisition : [rttCourant.typeAcquisition;strconv=no;protect=no]<br/>
							Mensuel : 	[rttCourant.mensuelInit;strconv=no;protect=no]<br/>
							Jours cumulés annuel :	[rttCourant.annuelCumuleInit;strconv=no;protect=no]<br/>
							Jours non cumulés annuel :  	[rttCourant.annuelNonCumuleInit;strconv=no;protect=no]<br/>
							<b> Dernière clôture RTT : 		[rttCourant.date_rttCloture;strconv=no;protect=no]</b><br/>

						</div>
					</td>
				<tr>
			</table>
				
		<div id="test"></div>
			
			
		<div class="tabsAction" >
		[onshow;block=begin;when [view.mode]=='edit']
			<input type="submit" value="Enregistrer" name="save" class="button">
			&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[rttCourant.id]&action=view'">
		[onshow;block=end]
		
		[onshow;block=begin;when [view.mode]!='edit']
			<a class="butAction"  href="?id=[rttCourant.id]&action=edit">Modifier</a>
		[onshow;block=end]
		</div>

		<script>
			$(document).ready( function(){
				//on empêche que la date de début dépasse celle de fin
				 $('body').click( 	function(){
					$("#test").html($("#rttCloture").val());
					//$("#rttCloture").val()
					//$("#rttCloture").val($("#rttCloture").val());
						
					
	    		});	
				
			});
		</script>



