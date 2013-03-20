

        	[view.head;strconv=no]

       
	<h1 style="color: #2AA8B9;">Congés payés</h1>                             
	<div style="display:inline-block; margin-top:-20px;">
		<div style="display:inline-block;">
		
        <h2 style="color: #2AA8B9;">Année N-1</h2>                         
		<table class="border" style="width:100%;"  >
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
		</table>
		</div>
		
		<div style="float:right; display:inline-block; margin-left: 100px;">
		<h2 style="color: #2AA8B9;">Année N</h2>                         
		<table class="border" style="width:100%;" >	
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
		</div>
	</div>
	
	<br/><br/><br/><br/>
	
	<h1 style="color: #2AA8B9;">RTT</h1>                             
	<div style="display:inline-block; margin-top:-20px;">
	    <div style="display:inline-block;">   
	    <h2 style="color: #2AA8B9;">Compteur de RTT</h2>                         
		<table class="border" style="width:100%">
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
		</table>
		</div>
		
		<div style="float:right; display:inline-block; margin-left: 90px;">
		<h2 style="color: #2AA8B9;">Méthode acquisition des jours</h2>                         
		<table class="border" style="width:100%">
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
		<br/><br/>


	
		<div class="tabsAction" >
		[onshow;block=begin;when [view.mode]=='edit']
			<input type="submit" value="Enregistrer" name="save" class="button"  onclick="document.location.href='?id=[rttCourant.id]&action=view'">
			&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[rttCourant.id]&action=view'">
		[onshow;block=end]
		
		[onshow;block=begin;when [view.mode]!='edit']
			<a class="butAction"  href="?id=[rttCourant.id]&action=edit">Modifier</a>
		[onshow;block=end]
		</div>

		



