

    [view.head;strconv=no]

       
	<h1 style="color: #2AA8B9;">Congés payés</h1>                             
	<div style="display:inline-block; margin-top:-20px;">
		<div style="display:inline-block;">
		
       
		
		<div style="float:right; display:inline-block; margin-left: 100px;">                  
		<table class="border" style="width:150%;" >	
				<br/><br/>
				<tr>
					<td>Nombre de jours acquis Par Mois</td>
					<td> [compteurGlobal.congesAcquisMensuelInit;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>Date clôture Congés</td>
					<td>[compteurGlobal.date_congesClotureInit;strconv=no;protect=no]</td>
				</tr>
		</table>
		</div>
	</div>
	
	<br/><br/>
	
	<h1 style="color: #2AA8B9;">RTT</h1>                             
	<div style="display:inline-block; margin-top:-50px;">
	    
		
		<div style="float:right; display:inline-block; margin-left: 100px;">                        
		<table class="border" style="width:225%">
				<tr>
					<td>Date clôture RTT</td>
					<td>[compteurGlobal.date_rttClotureInit;strconv=no;protect=no]</td>
				</tr>
		</table>
	    </div>  
	</div>            
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
	

		



