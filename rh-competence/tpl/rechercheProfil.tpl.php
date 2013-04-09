
	<div>			
		<h2 style="color: #2AA8B9;">Recherche d'un profil</h2>	
		<br/>
		<table class="border" style="width:100%">	
			<tr>
				<td><b>Veuillez saisir les compétences du collaborateur recherché</b></td>
					
			</tr>
			<tr>
				<td>[recherche.libelle;block=tr;strconv=no;protect=no]
				[onshow;block=begin;when [view.mode]=='edit']
					<input type="submit" value="Rechercher" name="save" class="button">
				[onshow;block=end]
				
				<br/><br/><br/><br/>
				Exemples de recherches prises en compte : 
				<div style="margin-left:40px">
			
					<li>	Excel</li>
					<li>Excel niveau</li> avec (niveau = {Faible, Bon, Moyen, Excellent})
					<li>Excel niveau OU Word</li>
					<li>Excel niveau OU Word niveau OU...</li>
					<li>Excel niveau ET Word niveau OU...</li>
				</div>

				</td>
			</tr>
			
		</table>
		
					
	</div>





	
	
