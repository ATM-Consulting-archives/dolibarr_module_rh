
	<div>			
		<h2 style="color: #2AA8B9;">Statistiques sur les compétences des collaborateurs de l'entreprise</h2>	
		<br/>
		<table class="border" style="width:100%">	
			<tr>
				<td><b>Veuillez renseigner les paramètres pour la recherche de statistiques</b></td>	
			</tr>
			<tr>
				<td> Libellé compétence [competence.Tlibelle;block=tr;strconv=no;protect=no]
				Groupe [competence.TGroupe;block=tr;strconv=no;protect=no]
				[competence.btValider;block=tr;strconv=no;protect=no]</td>
			</tr>
			
		</table>	
	</div>


[onshow;block=begin;when [userCourant.droitRecherche]!='1']
		Vous ne possédez pas les droits pour connaître les statistiques des compétences dans l'entreprise.		
[onshow;block=end]
	
