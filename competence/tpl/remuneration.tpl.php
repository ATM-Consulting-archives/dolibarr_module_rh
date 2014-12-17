<table width="100%" class="border"><tbody>
	<tr><td width="25%" valign="top">Réf.</td><td>[user.id]</td></tr>
	<tr><td width="25%" valign="top">Nom</td><td>[user.lastname]</td></tr>
	<tr><td width="25%" valign="top">Prénom</td><td>[user.firstname]</td></tr>
</tbody></table>
<br>

<div class="fiche">
		<div>
			
			<h2 style="color: #2AA8B9;">Description de vos rémunérations</h2>
			<table class="border" style="width:20%">	
				<tr>
					<td><b>Commentaire</b></td>	
				</tr>
				<tr>
					<td>[remuneration.commentaire;block=tr;strconv=no;protect=no]</td>
				</tr>
			</table>
			<br/><br/>
			<table class="border" style="width:20%">	
				<tr>
					<td><b>Date d'entrée dans l'entreprise</b></td>	
				</tr>
				<tr>
					<td>[remuneration.date_entreeEntreprise;block=tr;strconv=no;protect=no]</td>
				</tr>
			</table>
			<br/><br/>
			<table class="border" style="width:100%">		
				<tr>
					<td><b>Date début rémunération</b></td>
					<td><b>Date fin rémunération</b></td>
					<td><b>Rémunération brute annuelle</b></td>
					<td><b>Salaire Mensuel</b></td>
					<td><b>Net à payer</b></td>
				</tr>
				<tr>
					<td>[remuneration.date_debutRemuneration;block=tr;strconv=no;protect=no]</td>
					<td>[remuneration.date_finRemuneration;block=tr;strconv=no;protect=no]</td>
					<td>[remuneration.bruteAnnuelle;strconv=no;protect=no]€</td>
					<td>[remuneration.salaireMensuel;strconv=no;protect=no]€</td>
					<td>[remuneration.net_a_payer;strconv=no;protect=no]€</td>
					
				</tr>
			</table>
			<br/><br/>
			<table class="border" style="width:100%">		
				<tr>
					<td><b>Prime d'ancienneté</b></td>
					<td><b>Prime de Noël</b></td>
					<td><b>Commission</b></td>
					<td><b>Participation</b></td>
					<td><b>Autre</b></td>
				</tr>
				<tr>
					<td>[remuneration.primeAnciennete;strconv=no;protect=no]€</td>
					<td>[remuneration.primeNoel;strconv=no;protect=no]€</td>
					<td>[remuneration.commission;strconv=no;protect=no]€</td>
					<td>[remuneration.participation;strconv=no;protect=no]€</td>
					<td>[remuneration.autre;strconv=no;protect=no]€</td>
				</tr>
			</table>
			<br/><br/>
			<table class="border" style="width:100%">		
				<tr>
					<td><b>Nombre d'heures/mois</b></td>
					<td><b>AN</b></td>
					<td><b>Cout global</b></td>
					<td><b>Cumul heures travaillées</b></td>
					<td><b>Cumul heures sup</b></td>
					<td><b>HS</b></td>
				</tr>
				<tr>
					<td>[remuneration.nbHeuresMois;strconv=no;protect=no]</td>
					<td>[remuneration.an;strconv=no;protect=no]</td>
					<td>[remuneration.coutGlobal;strconv=no;protect=no]€</td>
					<td>[remuneration.cumHeureTrav;strconv=no;protect=no]</td>
					<td>[remuneration.cumHSup;strconv=no;protect=no]</td>
					<td>[remuneration.HS;strconv=no;protect=no]</td>
				</tr>
			</table>
			<br/><br/>
			<h2 style="color: #2AA8B9;">Charges</h2>
			<table class="border" style="width:30%">		
				<tr>
					<td><b>Montant</b></td>	
				</tr>
				<tr>
					<td>[remuneration.charges;block=tr;strconv=no;protect=no]€</td>
				</tr>
			</table>
			<br/>
			
			<table class="border" style="width:100%;">
				[onshow;block=begin;when [view.mode]=='view']
				<a style="text-align:center;width:20%;" class="butAction" href="?fk_user=[userCourant.id]">Retour</a>
				[onshow;block=begin;when [userCourant.ajoutRem]=='1']
					<a style="text-align:center;width:20%;" class="butAction" href="?fk_user=[userCourant.id]&action=edit&id=[remuneration.id;block=tr;strconv=no;protect=no]">Modifier</a>
					<a  style="text-align:center;width:20%;" class="butActionDelete" id="action-delete" onclick="if (window.confirm('Voulez vous supprimer l\'élément ?'))document.location.href='?fk_user=[userCourant.id]&id=[remuneration.id;block=tr;strconv=no;protect=no]&action=delete'">Supprimer</a>
				[onshow;block=end]
				[onshow;block=end]	
			</table>
		</div>

</div>



[onshow;block=begin;when [view.mode]=='edit']
<div class="tabsAction"  style="text-align:center">
	<input type="submit" value="Enregistrer" name="save" class="button" onclick="document.location.href='?id=[remuneration.id;block=tr;strconv=no;protect=no]&action=view'">
	&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?fk_user=[userCourant.id]'">
</div>
[onshow;block=end]

