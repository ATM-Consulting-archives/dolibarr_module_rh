

        	[view.head;strconv=no]

       
	<h1 style="color: #2AA8B9;">Congés payés</h1>                             
	<div style="display:inline-block; margin-top:-20px;">
		<div style="display:inline-block;">
		
       
		
		<div style="float:right; display:inline-block; margin-left: 100px;">                  
		<table class="border" style="width:100%;" >	
				<br/><br/>
				<tr>
					<td>Acquis Par Mois</td>
					<td> [compteurGlobal.congesAcquisMensuelInit;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>Date clôture Congés</td>
					<td>[compteurGlobal.date_congesClotureInit;strconv=no;protect=no]</td>
				</tr>
		</table>
		</div>
	</div>
	
	<br/><br/><br/><br/>
	
	<h1 style="color: #2AA8B9;">RTT</h1>                             
	<div style="display:inline-block; margin-top:-20px;">
	    
		
		<div style="float:right; display:inline-block; margin-left: 90px;">                        
		<table class="border" style="width:100%">
				<tr>
					<td>Date clôture RTT</td>
					<td>[compteurGlobal.date_rttClotureInit;strconv=no;protect=no]</td>
				</tr>
		</table>
	    </div>  
	</div>            
	<br/><br/>


	<div class="tabsAction" >
		[onshow;block=begin;when [view.mode]=='edit']
			<input type="submit" value="Enregistrer" name="save" class="button"  onclick="document.location.href='?id=[compteurGlobal.rowid]&action=view'">
			&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[compteurGlobal.rowid]&action=view'">
		[onshow;block=end]
		
		[onshow;block=begin;when [view.mode]!='edit']
			<a class="butAction"  href="?id=[compteurGlobal.rowid]&action=edit">Modifier</a>
		[onshow;block=end]
	</div>

		



