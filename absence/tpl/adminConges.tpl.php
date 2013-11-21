

    [view.head;strconv=no]

    [compteurGlobal.titreConges;strconv=no;protect=no]
	                             
                 
			<table class="border" style="width:100%;" >	

					<tr>
						<td style="width:30%;">Nombre de jours acquis par mois</td>
						<td> [compteurGlobal.congesAcquisMensuelInit;strconv=no;protect=no]</td>
					</tr>
					<tr>
						<td>Date clôture congés</td>
						<td>[compteurGlobal.date_congesClotureInit;strconv=no;protect=no]</td>
					</tr>
			</table>


	
	<br/><br/>
	
	 [compteurGlobal.titreRtt;strconv=no;protect=no]     
	 <br/>                                         
		<table class="border" style="width:100%">
				<tr>
					<td style="width:30%;">Nombre de RTT acquis pour les cadres</td>
					<td>[compteurGlobal.rttCumuleInitCadreCpro;strconv=no;protect=no]</td>
				</tr>
				<tr>
						<td>Date clôture RTT</td>
						<td>[compteurGlobal.date_rttClotureInit;strconv=no;protect=no]</td>
					</tr>
		</table>     
	<br/><br/><br/>


	
		[onshow;block=begin;when [view.mode]=='edit']
		<div class="tabsAction" >
			<input type="submit" value="Enregistrer" name="save" class="button"  onclick="document.location.href='?id=[compteurGlobal.rowid]&action=view'">
			&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[compteurGlobal.rowid]&action=view'">
		
		[onshow;block=end]
		
		[onshow;block=begin;when [view.mode]!='edit']
			[onshow;block=begin;when [userCourant.modifierParamGlobalConges]=='1']
			<div class="tabsAction" >
				<a class="butAction"  href="?id=[compteurGlobal.rowid]&action=edit">Modifier</a>
			</div>
			[onshow;block=end]
		[onshow;block=end]
	

		



