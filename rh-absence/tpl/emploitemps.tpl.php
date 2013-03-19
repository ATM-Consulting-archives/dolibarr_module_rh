
        [view.head;strconv=no]

		Utilisateur : [userCourant.firstname;strconv=no;protect=no] [userCourant.lastname;strconv=no;protect=no]
		<br/>Compteur : [planning.id;strconv=no;protect=no]
		<br/>
		<div style=" display:inline-block;">                  
		<table class="border" style="width:100%;" >	
				<br/><br/>
				<tr>
					<td>       </td>
					<td>Matin</td>
					<td>Après-midi</td>
					<td>Matin</td>
					<td>Midi</td>
					<td>Après-midi</td>
					<td>Soir</td>
				</tr>
				<tr>
					<td>Lundi</td>
					<td style="text-align:center;">[planning.lundiam;strconv=no;protect=no]</td>
					<td style="text-align:center;">[planning.lundipm;strconv=no;protect=no]</td>
					<td style="text-align:center;"> [horaires.lundi_heuredam;strconv=no;protect=no]      </td>
					<td style="text-align:center;"> [horaires.lundi_heurefam;strconv=no;protect=no]     </td>
					<td style="text-align:center;"> [horaires.lundi_heuredpm;strconv=no;protect=no]    </td>
					<td style="text-align:center;"> [horaires.lundi_heurefpm;strconv=no;protect=no]    </td>

				</tr>
				<tr>
					<td>Mardi</td>
					<td style="text-align:center;">[planning.mardiam;strconv=no;protect=no]</td>
					<td style="text-align:center;">[planning.mardipm;strconv=no;protect=no]</td>
					<td style="text-align:center;"> [horaires.mardi_heuredam;strconv=no;protect=no]      </td>
					<td style="text-align:center;"> [horaires.mardi_heurefam;strconv=no;protect=no]     </td>
					<td style="text-align:center;"> [horaires.mardi_heuredpm;strconv=no;protect=no]    </td>
					<td style="text-align:center;"> [horaires.mardi_heurefpm;strconv=no;protect=no]    </td>
				</tr>
				<tr>
					<td>Mercredi</td>
					<td style="text-align:center;">[planning.mercrediam;strconv=no;protect=no]</td>
					<td style="text-align:center;">[planning.mercredipm;strconv=no;protect=no]</td>
					<td style="text-align:center;"> [horaires.mercredi_heuredam;strconv=no;protect=no]      </td>
					<td style="text-align:center;"> [horaires.mercredi_heurefam;strconv=no;protect=no]     </td>
					<td style="text-align:center;"> [horaires.mercredi_heuredpm;strconv=no;protect=no]    </td>
					<td style="text-align:center;"> [horaires.mercredi_heurefpm;strconv=no;protect=no]    </td>
				</tr>
				<tr>
					<td>Jeudi</td>
					<td style="text-align:center;">[planning.jeudiam;strconv=no;protect=no]</td>
					<td style="text-align:center;">[planning.jeudipm;strconv=no;protect=no]</td>
					<td style="text-align:center;"> [horaires.jeudi_heuredam;strconv=no;protect=no]      </td>
					<td style="text-align:center;"> [horaires.jeudi_heurefam;strconv=no;protect=no]     </td>
					<td style="text-align:center;"> [horaires.jeudi_heuredpm;strconv=no;protect=no]    </td>
					<td style="text-align:center;"> [horaires.jeudi_heurefpm;strconv=no;protect=no]    </td>
				</tr>
				<tr>
					<td>Vendredi</td>
					<td style="text-align:center;">[planning.vendrediam;strconv=no;protect=no]</td>
					<td style="text-align:center;">[planning.vendredipm;strconv=no;protect=no]</td>
					<td style="text-align:center;"> [horaires.vendredi_heuredam;strconv=no;protect=no]      </td>
					<td style="text-align:center;"> [horaires.vendredi_heurefam;strconv=no;protect=no]     </td>
					<td style="text-align:center;"> [horaires.vendredi_heuredpm;strconv=no;protect=no]    </td>
					<td style="text-align:center;"> [horaires.vendredi_heurefpm;strconv=no;protect=no]    </td>
				</tr>
				<tr>
					<td>Samedi</td>
					<td style="text-align:center;">[planning.samediam;strconv=no;protect=no]</td>
					<td style="text-align:center;">[planning.samedipm;strconv=no;protect=no]</td>
					<td style="text-align:center;"> [horaires.samedi_heuredam;strconv=no;protect=no]      </td>
					<td style="text-align:center;"> [horaires.samedi_heurefam;strconv=no;protect=no]     </td>
					<td style="text-align:center;"> [horaires.samedi_heuredpm;strconv=no;protect=no]    </td>
					<td style="text-align:center;"> [horaires.samedi_heurefpm;strconv=no;protect=no]    </td>
				</tr>
				<tr>
					<td>Dimanche</td>
					<td style="text-align:center;">[planning.dimancheam;strconv=no;protect=no]</td>
					<td style="text-align:center;">[planning.dimanchepm;strconv=no;protect=no]</td>
					<td style="text-align:center;"> [horaires.dimanche_heuredam;strconv=no;protect=no]      </td>
					<td style="text-align:center;"> [horaires.dimanche_heurefam;strconv=no;protect=no]     </td>
					<td style="text-align:center;"> [horaires.dimanche_heuredpm;strconv=no;protect=no]    </td>
					<td style="text-align:center;"> [horaires.dimanche_heurefpm;strconv=no;protect=no]    </td>
				</tr>
		</table>
		</div>

	

	<div class="tabsAction" >
		[onshow;block=begin;when [view.mode]=='edit']
			<input type="submit" value="Enregistrer" name="save" class="button"  onclick="document.location.href='?id=[planning.id]&action=view'">
			&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[planning.id]&action=view'">
		[onshow;block=end]
		
		[onshow;block=begin;when [view.mode]!='edit']
			<a class="butAction"  href="?id=[planning.id]&action=edit">Modifier</a>
		[onshow;block=end]
	</div>

		



