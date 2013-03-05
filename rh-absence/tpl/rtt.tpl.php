<?php llxHeader();?>


			<h1>Visualisation de vos RTT [userCourant.firstname] [userCourant.lastname]</h1>

        
        <div class="fiche"> <!-- begin div class="fiche" -->
          <div class="tabBar">
                      ID Utilisateur Courant : [rttCourant.id;strconv=no;protect=no]<br/>   
                      ID RTT Courant : [rttCourant.rowid;strconv=no;protect=no]<br/> <br/>   
 
			<table  border ="1">
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
		
			<table style="margin-top: 30px"  border ="1" >
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

			<div class="tabsAction" >
			[onshow;block=begin;when [view.mode]=='edit']
				<input type="submit" value="Enregistrer" name="save" class="button">
				&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[rttCourant.idNum]&action=view'">
			[onshow;block=end]
			
			[onshow;block=begin;when [view.mode]!='edit']
				<a class="butAction"  href="?id=[rttCourant.idNum]&action=edit">Modifier</a>
			[onshow;block=end]
			</div>
			
				
		  </div>
		</div>
			




