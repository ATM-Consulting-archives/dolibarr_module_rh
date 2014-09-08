
    [view.head;strconv=no]

	<table class="border" style="width:30%">
		<tr>
			<td>[translate.User;strconv=no]</td>
			<td>[pointage.name;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td>[translate.Morning;strconv=no]</td>
			<td>[pointage.matin;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td>[translate.Afternoon;strconv=no]</td>
			<td>[pointage.apresmidi;strconv=no;protect=no]</td>
		</tr>
	</table>


	
[onshow;block=begin;when [view.mode]=='view']
		<div class="tabsAction" style="text-align:center;">
			<a class="butAction"  href="?idPointage=[pointage.id]&id=[userCourant.id]&action=edit">[translate.Modify;strconv=no]</a>
			<a class="butActionDelete"  href="?idPointage=[pointage.id]&id=[userCourant.id]&action=delete">[translate.Delete;strconv=no]</a>
		</div>
[onshow;block=end] 

[onshow;block=begin;when [view.mode]!='view']
	<div class="tabsAction" style="text-align:center;">
		<input type="submit" value="[translate.Register;strconv=no]" name="save" class="button">
		&nbsp; &nbsp; <input type="button" value="[translate.Cancel;strconv=no]" name="cancel" class="button" onclick="document.location.href='?id=[userCourant.id]'">
	</div>
[onshow;block=end] 

