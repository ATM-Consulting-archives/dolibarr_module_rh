
[view.head3;strconv=no]
		[view.titreCalendar;strconv=no;protect=no] 	
		
		
		<table class="liste border">
			<tr>
				<td valign="top">
					
					[onshow;block=begin;when [absence.droits]=='1']
					<table class="nobordernopadding" style="width:100%">	
						<tr>
							<th colspan="2">[translate.Absences;strconv=no;protect=no]</th>
						</tr>		
						<tr>
							<td>[translate.Group;strconv=no;protect=no]</td>
							<td>[absence.TGroupe;strconv=no;protect=no]</td>
						<tr/>
						<tr>
							<td>[translate.User;strconv=no;protect=no]</td>
							<td>[absence.TUser;strconv=no;protect=no]</td>
						<tr/>
						
					</table>
					[onshow;block=end]
		 		
		 		</td>
		 		<td valign="top">	
		 					 			
				 	<table class="nobordernopadding">
				 		<tr>
							<th colspan="2">[translate.Diary;strconv=no;protect=no]</th>
						</tr>		
				 			<tr>
				 				<td>[translate.EventsRegisteredBy;strconv=no;protect=no] &nbsp;</td>
				 				<td>[agenda.userasked;strconv=no]</td>
				 			</tr>
				 			<tr><td class="nowrap">[translate.Or;strconv=no;protect=no] [translate.EventsAffectedTo;strconv=no;protect=no] &nbsp;</td>
				 				<td class="nowrap">[agenda.usertodo;strconv=no]
							</td></tr>
							<tr><td class="nowrap">[translate.Or;strconv=no;protect=no] [translate.EventsMadeBy;strconv=no;protect=no] &nbsp;</td>
							<td class="nowrap">[agenda.userdone;strconv=no]</td></tr>
							<tr><td class="nowrap">[translate.Type;strconv=no;protect=no] &nbsp;</td><td class="nowrap">
								[agenda.actioncode;strconv=no]
							</td></tr>
							<tr>[onshow;block=tr;when [agenda.projectEnabled]==1 ]<td class="nowrap">[translate.Project;strconv=no;protect=no] &nbsp; </td>
							<td class="nowrap">[agenda.projectid;strconv=no]</td>
						</tr>
					</table>
			
				</td>
				
				<td>[absence.btValider;strconv=no;protect=no]</td>
				</tr>
			</table>
		 
		 	<br> 
		 	
		 	
		
	
		<script>
		$('#groupe').change(function(){
				//alert('top');
				$.ajax({
					url: 'script/loadUtilisateurs.php?groupe='+$('#groupe option:selected').val()
					,dataType:'json'
				}).done(function(liste) {
					$("#idUtilisateur").empty(); // remove old options
					$.each(liste, function(key, value) {
					  $("#idUtilisateur").append($("<option></option>")
					     .attr("value", key).text(value));
					});	
				});
		});
		</script>

		
			<div id="agenda">
				 <script type="text/javascript">
        $(document).ready(function() {     
            var view="week";          
           
            var DATA_FEED_URL = "./script/absenceCalendarDataFeed.php?idUser=[absence.idUser;strconv=no;protect=no]&idGroupe=[absence.idGroupe;strconv=no;protect=no]&typeAbsence=[absence.typeAbsence;strconv=no;protect=no]&withAgenda=[view.agendaEnabled]&projectid=[view.projectid]&actioncode=[view.actioncode]&userdone=[view.userdone]&usertodo=[view.usertodo]&userasked=[view.userasked]&filter=[view.filter]&status=[view.status]"
            var op = {
                view: view,
                theme:0,
                showday: new Date(),
                EditCmdhandler:Edit,
                DeleteCmdhandler:Delete,
                ViewCmdhandler:View,    
                onWeekOrMonthToDay:wtd,
                onBeforeRequestData: cal_beforerequest,
                onAfterRequestData: cal_afterrequest,
                onRequestDataError: cal_onerror, 
                autoload:true,
                url: DATA_FEED_URL + "&method=list",  
                quickAddUrl: DATA_FEED_URL + "&method=add",   
                quickUpdateUrl:false,  
                quickDeleteUrl: false   
                ,method:"GET"
                ,enableDrag :true 
                
            };
           
           /* var $dv = $("#calhead");
            var _MH = document.documentElement.clientHeight;
            var dvH = $dv.height() + 2;
            op.height = _MH - dvH;*/
           
            op.height = document.documentElement.clientHeight - 450;
           
            if(op.height<500)op.height=500;
           
            op.eventItems =[];

            var p = $("#gridcontainer").bcalendar(op).BcalGetOp();
            if (p && p.datestrshow) {
                $("#txtdatetimeshow").text(p.datestrshow);
            }
            $("#caltoolbar").noSelect();
            
            $("#hdtxtshow").datepicker({ picker: "#txtdatetimeshow", showtarget: $("#txtdatetimeshow"),
            onReturn:function(r){                          
                            var p = $("#gridcontainer").gotoDate(r).BcalGetOp();
                            if (p && p.datestrshow) {
                                $("#txtdatetimeshow").text(p.datestrshow);
                            }
                     } 
            });
            function cal_beforerequest(type)
            {
                $("#errorpannel").hide();
                $("#loadingpannel").show();    
            }
            function cal_afterrequest(type)
            {
                switch(type)
                {
                    case 1:
                        $("#loadingpannel").hide();
                        break;
                    case 2:
                    case 3:
                    case 4:
                        $("#loadingpannel").html("Success!");
                        window.setTimeout(function(){ $("#loadingpannel").hide();},2000);
                    break;
                }              
               
            }
            function cal_onerror(type,data)
            {
                $("#errorpannel").show();
            }
            function Edit(data)
            {
               
            }    
            function View(data)
            {
               /* var str = "";
                for(x in data) {
                    str += "[" + x + "]: " + data[x] + "\n";
                }
                alert(str);
               */
              
               if (data[0].indexOf('url:')!=-1) {
               		document.location.href=data[0].substring(4);
               }
               else {
               		document.location.href=data[9];	
               }
               
                              
            }    
            function Delete(data,callback)
            {           
                
                $.alerts.okButton="Ok";  
                $.alerts.cancelButton="Cancel";  
                hiConfirm("Voulez vous supprimer cet événement ? ", 'Confirmez',function(r){ r && callback(0);});           
            }
            function wtd(p)
            {
               if (p && p.datestrshow) {
                    $("#txtdatetimeshow").text(p.datestrshow);
                }
                $("#caltoolbar div.fcurrent").each(function() {
                    $(this).removeClass("fcurrent");
                })
                $("#showdaybtn").addClass("fcurrent");
            }
            //to show day view
            $("#showdaybtn").click(function(e) {
                //document.location.href="#day";
                $("#caltoolbar div.fcurrent").each(function() {
                    $(this).removeClass("fcurrent");
                })
                $(this).addClass("fcurrent");
                var p = $("#gridcontainer").swtichView("day").BcalGetOp();
                if (p && p.datestrshow) {
                    $("#txtdatetimeshow").text(p.datestrshow);
                }
            });
            //to show week view
            $("#showweekbtn").click(function(e) {
                //document.location.href="#week";
                $("#caltoolbar div.fcurrent").each(function() {
                    $(this).removeClass("fcurrent");
                })
                $(this).addClass("fcurrent");
                var p = $("#gridcontainer").swtichView("week").BcalGetOp();
                if (p && p.datestrshow) {
                    $("#txtdatetimeshow").text(p.datestrshow);
                }

            });
            //to show month view
            $("#showmonthbtn").click(function(e) {
                //document.location.href="#month";
                $("#caltoolbar div.fcurrent").each(function() {
                    $(this).removeClass("fcurrent");
                })
                $(this).addClass("fcurrent");
                var p = $("#gridcontainer").swtichView("month").BcalGetOp();
                if (p && p.datestrshow) {
                    $("#txtdatetimeshow").text(p.datestrshow);
                }
            });
            
            $("#showreflashbtn").click(function(e){
                $("#gridcontainer").reload();
            });
            
            //Add a new event
            $("#faddbtn").click(function(e) {
                
                url = "[agenda.newEvent;strconv=no]";
                document.location.href=url;
                
            });
            //go to today
            $("#showtodaybtn").click(function(e) {
                var p = $("#gridcontainer").gotoDate().BcalGetOp();
                if (p && p.datestrshow) {
                    $("#txtdatetimeshow").text(p.datestrshow);
                }


            });
            //previous date range
            $("#sfprevbtn").click(function(e) {
                var p = $("#gridcontainer").previousRange().BcalGetOp();
                if (p && p.datestrshow) {
                    $("#txtdatetimeshow").text(p.datestrshow);
                }

            });
            //next date range
            $("#sfnextbtn").click(function(e) {
                var p = $("#gridcontainer").nextRange().BcalGetOp();
                if (p && p.datestrshow) {
                    $("#txtdatetimeshow").text(p.datestrshow);
                }
            });
            
        });
    </script>    

    <div>

      <div id="calhead" style="padding-left:1px;padding-right:1px;">          
            <div id="loadingpannel" class="ptogtitle loadicon" style="display: none;">[translate.Loading;strconv=no;protect=no].</div>
             <div id="errorpannel" class="ptogtitle loaderror" style="display: none;">[translate.ErrImpossibleLoadData;strconv=no;protect=no]</div>
            </div>          
            
            <div id="caltoolbar" class="ctoolbar">
            <div>
            	[onshow; block=div; when [view.agendaEnabled]==1]
	            <div id="faddbtn" class="fbutton">
	                <div><span title="[translate.ClickToCreateNewEvent;strconv=no;protect=no]" class="addcal">
	
	                [translate.NewEvent;strconv=no;protect=no]                
	                </span></div>
	            </div>
	            <div class="btnseparator"></div>
            </div>
             <div id="showtodaybtn" class="fbutton">
                <div><span title='[translate.ClickToBackToToday;strconv=no;protect=no]' class="showtoday">
                [translate.Today;strconv=no;protect=no]</span></div>
            </div>
              <div class="btnseparator"></div>

           <div id="showdaybtn" class="fbutton">
           	[onshow; block=div; when [view.agendaEnabled]==1]
                <div><span title='[translate.Day;strconv=no;protect=no]' class="showdayview">[translate.Day;strconv=no;protect=no]</span></div>
            </div>
              <div  id="showweekbtn" class="fbutton fcurrent">
                <div><span title='[translate.Week;strconv=no;protect=no]' class="showweekview">[translate.Week;strconv=no;protect=no]</span></div>
            </div>
              <div  id="showmonthbtn" class="fbutton">
                <div><span title='[translate.Month;strconv=no;protect=no]' class="showmonthview">[translate.Month;strconv=no;protect=no]</span></div>

            </div>
            <div class="btnseparator"></div>
              <div  id="showreflashbtn" class="fbutton">
                <div><span title='[translate.RefreshView;strconv=no;protect=no]' class="showdayflash">[translate.Refresh;strconv=no;protect=no]</span></div>
                </div>
             <div class="btnseparator"></div>
            <div id="sfprevbtn" title="[translate.Previous;strconv=no;protect=no]"  class="fbutton">
              <span class="fprev"></span>

            </div>
            <div id="sfnextbtn" title="[translate.Next;strconv=no;protect=no]" class="fbutton">
                <span class="fnext"></span>
            </div>
            <div class="fshowdatep fbutton">
                    <div>
                        <input type="hidden" name="txtshow" id="hdtxtshow" />
                        <span id="txtdatetimeshow">[translate.Loading;strconv=no;protect=no]</span>

                    </div>
            </div>
            
            <div class="clear"></div>
            </div>
      </div>
      <div style="padding:1px;">

        
        <div id="dvCalMain" class="calmain printborder">
            <div id="gridcontainer" style="overflow-y: visible;">
            </div>
        </div>
        
        </div>
     
  </div>
				
	</div>
			