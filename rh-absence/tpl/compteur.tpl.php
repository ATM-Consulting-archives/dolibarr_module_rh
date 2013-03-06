

			<h1 style="text-align:center;">Visualisation de vos jours acquis [userCourant.firstname] [userCourant.lastname]</h1>

        
        <div class="fiche"> <!-- begin div class="fiche" -->
          <div class="tabBar">
            
            <h1 style="color: #2AA8B9;"> Jours de Congés</h1>                         

			<table  border ="1" style="display: inline-block; margin-left:20px;">
				<tr>
					<td>
						<div>
							<h2>Jours de congés payés N-1 ([congesPrec.anneePrec;strconv=no;protect=no])</h2>
							<b>Acquis</b><br/>
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
							<h2>Jours de congés payés N ([congesCourant.anneeCourante;strconv=no;protect=no])</h2>
							<b>Acquis</b><br/>
							Acquis Exercice : 	[congesCourant.acquisEx;strconv=no;protect=no]<br/>
							Acquis Ancienneté : [congesCourant.acquisAnc;strconv=no;protect=no]	<br/>
							Acquis Hors-Période : [congesCourant.acquisHorsPer;strconv=no;protect=no]	<br/>
				
							<br/>
							<b>Total : [congesCourant.total;strconv=no;protect=no]</b><br/>

							<b> Dernière clôture :</b><br/>
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
							Mensuel : 	[rttCourant.mensuel;strconv=no;protect=no]<br/>
							Jour ouvrés annuel :	[rttCourant.annuelCumule;strconv=no;protect=no]<br/>
							Jour non ouvrés annuel :  	[rttCourant.annuelNonCumule;strconv=no;protect=no]<br/>
						</div>
					</td>
				<tr>
			</table>
				
		  </div>
		</div>
			
			
		<div class="tabsAction" >
		[onshow;block=begin;when [view.mode]=='edit']
			<input type="submit" value="Enregistrer" name="save" class="button">
			&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[congeCourant.idNum]&action=view'">
		[onshow;block=end]
		
		[onshow;block=begin;when [view.mode]!='edit']
			<a class="butAction"  href="?id=[rttCourant.idNum]&action=edit">Modifier</a>
		[onshow;block=end]
		</div>




