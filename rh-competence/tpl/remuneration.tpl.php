<div class="fiche">
		<div>				
			<h2 style="color: #2AA8B9;">Description de vos rémunérations</h2>
			<table class="border" style="width:20%">	
				<tr>
					<td><b>Date d'entrée dans l'entreprise</b></td>	
				</tr>
				<tr>
					<td>[remuneration.date_entreeEntreprise;block=tr;strconv=no;protect=no]</td>
				</tr>
			</table>
			<br/><br/>
			<table class="border" style="width:50%">		
				<tr>
					<td><b>Année de rémunération</b></td>
					<td><b>Rémunération brute annuelle</b></td>
					<td><b>Salaire Mensuel</b></td>
				</tr>
				<tr>
					<td>[remuneration.anneeRemuneration;block=tr;strconv=no;protect=no]</td>
					<td>[remuneration.bruteAnnuelle;strconv=no;protect=no]€</td>
					<td>[remuneration.salaireMensuel;strconv=no;protect=no]€</td>
				</tr>
			</table>
			<br/><br/>
			<table class="border" style="width:50%">		
				<tr>
					<td><b>Prime d'ancienneté</b></td>
					<td><b>Prime semestrielle</b></td>
					<td><b>Prime exceptionnelle</b></td>
				</tr>
				<tr>
					<td>[remuneration.primeAnciennete;strconv=no;protect=no]€</td>
					<td>[remuneration.primeSemestrielle;strconv=no;protect=no]€</td>
					<td>[remuneration.primeExceptionnelle;strconv=no;protect=no]€</td>
				</tr>
			</table>
			<br/><br/>
			<h2 style="color: #2AA8B9;">Vos cotisations</h2>
			<table class="border" style="width:30%">		
				<tr>
					<td></td>
					<td><b>Part Salariale</b></td>
					<td><b>Part Patronale</b></td>
				</tr>
				<tr>
					<td><b>PREVOYANCE</b></td>
					<td>[remuneration.prevoyancePartSalariale;block=tr;strconv=no;protect=no]€</td>
					<td>[remuneration.prevoyancePartPatronale;strconv=no;protect=no]€</td>
				</tr>
			</table>
			<br/><br/>
			<table class="border" style="width:30%">		
				<tr>
					<td></td>
					<td><b>Part Salariale</b></td>
					<td><b>Part Patronale</b></td>
				</tr>
				<tr>
					<td><b>URSSAF</b></td>
					<td>[remuneration.urssafPartSalariale;strconv=no;protect=no]€</td>
					<td>[remuneration.urssafPartPatronale;strconv=no;protect=no]€</td>
				</tr>
			</table>
			<br/><br/>
			<table class="border" style="width:30%">		
				<tr>
					<td></td>
					<td><b>Part Salariale</b></td>
					<td><b>Part Patronale</b></td>
				</tr>
				<tr>
					<td><b>RETRAITE</b></td>
					<td>[remuneration.retraitePartSalariale;strconv=no;protect=no]€</td>
					<td>[remuneration.retraitePartPatronale;strconv=no;protect=no]€</td>
				</tr>
			</table>
			<br/><br/>
			<table class="border" style="width:30%">		
				
				<tr>
					<td><b>TOTAL</b></td>
					<td>[remuneration.totalRemSalariale;strconv=no;protect=no]€</td>
					<td>[remuneration.totalRemPatronale;strconv=no;protect=no]€</td>
				</tr>
			</table>
			<br/>
			
			<table class="border" style="width:100%;">
				[onshow;block=begin;when [view.mode]=='view']
				<a style="text-align:center;width:20%;" class="butAction" href="?fk_user=[userCourant.id]">Retour</a>
				<a style="text-align:center;width:20%;" class="butAction" href="?fk_user=[userCourant.id]&action=edit&id=[remuneration.id;block=tr;strconv=no;protect=no]">Modifier</a>
				<a  style="text-align:center;width:20%;" class="butActionDelete" id="action-delete" onclick="document.location.href='?fk_user=[userCourant.id]&id=[remuneration.id;block=tr;strconv=no;protect=no]&action=delete'">Supprimer</a>
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

