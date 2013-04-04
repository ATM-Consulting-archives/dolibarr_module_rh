[onshow;block=begin;when [ressource.id]!=0]        
                <div class="fiche"> <!-- begin div class="fiche" -->
                [view.head;strconv=no]
                
                        <div class="tabBar">
[onshow;block=end] 	

[onshow;block=begin;when [ressource.id]=0]
		<br> 
		Spécifier le type de ressource à afficher :  
                [ressource.type;strconv=no;protect=no]
                [ressource.btValider;strconv=no;protect=no]
[onshow;block=end] 	


<h1>Agenda des ressources</h1>
		
			<div id="agenda">
				 <script type="text/javascript">
        $(document).ready(function() {     
           var view="month";          
           
           [onshow;block=begin;when [ressource.id]!=0]        
                var DATA_FEED_URL = "ressourceCalendarDataFeed.php?id=[ressource.id;strconv=no]";
			[onshow;block=end]
			
			[onshow;block=begin;when [ressource.id]=0]
					 var DATA_FEED_URL = "ressourceCalendarDataFeed.php?type=[ressource.typeAAfficher;strconv=no]";
			[onshow;block=end]

            //var DATA_FEED_URL = "ressourceCalendarDataFeed.php?id=[ressource.id;strconv=no]&type=[ressource.typeAAfficher;strconv=no]"
            //alert(DATA_FEED_URL);
            var op = {
                view: view,
                theme:3,
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
                quickUpdateUrl: DATA_FEED_URL + "&method=update",
                quickDeleteUrl: DATA_FEED_URL + "&method=remove"        
            };
            var $dv = $("#calhead");
            var _MH = document.documentElement.clientHeight;
            var dvH = $dv.height() + 2;
            op.height = _MH - dvH;
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
                var t="Loading data...";
                switch(type)
                {
                    case 1:
                        t="Loading data...";
                        break;
                    case 2:                      
                    case 3:  
                    case 4:    
                        t="The request is being processed ...";                                   
                        break;
                }
                $("#errorpannel").hide();
                $("#loadingpannel").html(t).show();    
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
               var eurl="../wdCalendar/edit.php?id={0}&start={2}&end={3}&isallday={4}&title={1}";   
                if(data)
                {
                    var url = StrFormat(eurl,data);
                    OpenModelWindow(url,{ width: 600, height: 400, caption:"Gérer le calendrier",onclose:function(){
                       $("#gridcontainer").reload();
                    }});
                }
            }    
            function View(data)
            {
                var str = "";
                $.each(data, function(i, item){
                    str += "[" + i + "]: " + item + "\n";
                });
                alert(str);               
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
/*            $("#faddbtn").click(function(e) {
                var url ="../wdCalendar/edit.php";
                OpenModelWindow(url,{ width: 500, height: 400, caption: "Créer un nouveau calendrier"});
            });
*/            //go to today
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
            <div class="cHead"><div class="ftitle">Mon calendrier</div>
            <div id="loadingpannel" class="ptogtitle loadicon" style="display: none;">Chargement...</div>
             <div id="errorpannel" class="ptogtitle loaderror" style="display: none;">Impossible de charger les données.</div>
            </div>          
            
            <div id="caltoolbar" class="ctoolbar">
              <!--<div id="faddbtn" class="fbutton">
                <div><span title='Click to Create New Event' class="addcal">

                Nouvel Evénement                
                </span></div>
            </div>-->
            <div class="btnseparator"></div>
             <div id="showtodaybtn" class="fbutton">
                <div><span title='Click to back to today ' class="showtoday">
                Aujourd'hui</span></div>
            </div>
              <div class="btnseparator"></div>

            <!--<div id="showdaybtn" class="fbutton">
                <div><span title='Day' class="showdayview">Jour</span></div>
            </div>-->
              <div  id="showweekbtn" class="fbutton">
                <div><span title='Week' class="showweekview">Semaine</span></div>
            </div>
              <div  id="showmonthbtn" class="fbutton fcurrent">
                <div><span title='Month' class="showmonthview">Mois</span></div>

            </div>
            <div class="btnseparator"></div>
              <div  id="showreflashbtn" class="fbutton">
                <div><span title='Refresh view' class="showdayflash">Rafraîchir</span></div>
                </div>
             <div class="btnseparator"></div>
            <div id="sfprevbtn" title="Prev"  class="fbutton">
              <span class="fprev"></span>

            </div>
            <div id="sfnextbtn" title="Next" class="fbutton">
                <span class="fnext"></span>
            </div>
            <div class="fshowdatep fbutton">
                    <div>
                        <input type="hidden" name="txtshow" id="hdtxtshow" />
                        <span id="txtdatetimeshow">Chargement</span>

                    </div>
            </div>
            
            <div class="clear"></div>
            </div>
      </div>
      <div style="padding:1px;">

        <div class="t1 chromeColor">
            &nbsp;</div>
        <div class="t2 chromeColor">
            &nbsp;</div>
        <div id="dvCalMain" class="calmain printborder">
            <div id="gridcontainer" style="overflow-y: visible;">
            </div>
        </div>
        <div class="t2 chromeColor">

            &nbsp;</div>
        <div class="t1 chromeColor">
            &nbsp;
        </div>   
        </div>
     
  </div>
				
	</div>
			