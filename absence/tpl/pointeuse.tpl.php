
	[view.head;strconv=no]
     
     			<table class="border" style="width:40%">
				<tr>
					<td>[translate.MorningHourOfArrival;strconv=no]</td>
					<td>[pointeuse.date_deb_am;strconv=no;protect=no]</td>
				</tr>	
				<tr>
					<td>[translate.MorningDepartureTime;strconv=no]</td>
					<td>[pointeuse.date_fin_am;strconv=no;protect=no]</td>
				</tr>	
				<tr>
					<td>[translate.AfternoonHourOfArrival;strconv=no]</td>
					<td>[pointeuse.date_deb_pm;strconv=no;protect=no]</td>
				</tr>	
				<tr>
					<td>[translate.AfternoonDepartureTime;strconv=no]</td>
					<td>[pointeuse.date_fin_pm;strconv=no;protect=no]</td>
				</tr>	
				<tr>
					<td>[translate.Day;strconv=no]</td>
					<td>[pointeuse.date_jour;strconv=no;protect=no]</td>
				</tr>	
				<tr>
					<td>[translate.PresenceTimeNoted;strconv=no]</td>
					<td>[pointeuse.time_presence;strconv=no;protect=no]</td>
				</tr>
				<tr>
					<td>[translate.Note;strconv=no]</td>
					<td>[pointeuse.motif;strconv=no;protect=no]</td>
				</tr>
			</table>

   		 <br/>

		[onshow;block=begin;when [view.mode]=='edit']
			<br>
			<input type="button" value="[translate.Cancel;strconv=no]" name="cancel" class="button" onclick="document.location.href='?id=[pointeuse.id]&action=view'">
			<input type="submit" value="[translate.Register;strconv=no]" name="save" class="button">
		[onshow;block=end]
		[onshow;block=begin;when [view.mode]!='edit']
			<a href="?id=[pointeuse.id]&action=edit" class="butAction">[translate.Modify;strconv=no]</a>
			<span class="butActionDelete" id="action-delete"  onclick="if (window.confirm('[translate.ConfirmDeleteScore;strconv=no]')){document.location.href='?action=delete&id=[pointeuse.id]'};">[translate.Delete;strconv=no]</span>			
		[onshow;block=end]
		<div style="clear:both;"></div>
	</div>


