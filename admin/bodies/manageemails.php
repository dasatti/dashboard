<?php




?>
<script type="text/javascript">


    function setSortVal(val){
        var sortCriterean = '';
        var date_from,date_to;
        
        if(val=='Today'){
            sortCriterean = 'today';
        }else if(val=='Yesterday'){
            sortCriterean = 'yesterday';
        }else if(val=='Last 7 Days'){
            sortCriterean = 'last_7_days';
        }else if(val=='Last 30 Days'){
            sortCriterean = 'last_30_days';
        }else if(val=='This Month'){
            sortCriterean = 'this_month';
        }else if(val=='Last Month'){
            sortCriterean = 'last_month';
        }else if(val=='Lifetime'){
            sortCriterean = 'lifetime';
        }
        else{
            
            sortCriterean='custom';
        }
       
        date_from = document.getElementById("daterangepicker_start").value;
        date_to = document.getElementById("daterangepicker_end").value;
        
        showEmailsData(sortCriterean,date_from,date_to);
        loadEmailsData(sortCriterean,date_from,date_to);
        saveDateRange(sortCriterean,date_from,date_to);
        
        
        $('#share_report_period').val(sortCriterean);
        $('#share_report_period_from').val(date_from);
        $('#share_report_period_to').val(date_to);
        
    }
 
 
 
    var period="lifetime";
    var emails_datatable;
    var chart;
    var saved_date_period = '<?php echo $LM_PERIOD; ?>';
    var saved_date_from = '<?php echo $LM_PERIOD_FROM ?>';
    var saved_date_to = '<?php echo $LM_PERIOD_TO ?>';
    
    jQuery(document).ready(
    
        
        function(){
        
            $('#share_report_type').val('emails');
            
            //fill table
            $.ajax({
                url:"<?php echo SURL ?>ajaxresponse/ajax2.php",
                type:"GET",
                cache:false,
                dataType:'json',
                data:{act:"get_emails_table_data",period:saved_date_period,from:saved_date_from,to:saved_date_to},
                success: function(data){
                    var row="";var sno=0;
                    $.each(data, function(index,element){
                        
                        sno++;
                        row+="<tr>";
                        row+="<td>"+sno+"</td>";
                        row+="<td>"+element.campaign_name+"</td>";
                        row+="<td>"+element.name+"</td>";
                        row+="<td>"+element.email+"</td>";
                        row+="<td>"+element.country+"</td>";
                        row+="<td>"+element.phone+"</td>";
                        row+="<td><span class=\"big-text\">"+element.message+"</span></td>";
                        row+="<td>"+element.gender+"</td>";
                        row+="<td>"+element.email_date_ae+"</td>";
                        row+="</tr>";                         
                    });
                    
                    //console.log(row);
                    $('#table-emails').find('tbody:last').append(row);
                    //console.log(data);
                    emails_datatable = $('#table-emails').DataTable({
                        "sPaginationType": "bootstrap",
                        "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                        "oTableTools": {
                        },
                        "order": [[ 7, "desc" ]],
                        "fnDrawCallback": function( oSettings, json ) {
                            lessTextAll(200);
                                
                          },
                          "columns":[
                              ,,,,{ "visible": false },,,{"visible":false},{"width":"15%"},
                          ]
                    });
                    
                    $('a.toggle-vis').on( 'click', function (e) {
                        e.preventDefault();
                        var column = emails_datatable.column( $(this).attr('data-column') );
                        column.visible( ! column.visible() );
                    } );
                    //dtToggleColsInit2();
                    $(document).dtToggleCols('#table-emails');
                    
                    
                }
            });
            
            
            //configure and fill chart
            chart = Morris.Line({
                element: 'emails_chart',
                data: [0, 0],
                xkey: 'elapsed',
                ykeys: ['value'],
                labels: ['Emails'],
                parseTime: false,
                lineColors: ['#242d3c']
            });
            
            showEmailsData(saved_date_period,saved_date_from,saved_date_to);
            
        }
    );
    
 
 
 function loadEmailsData(period,from,to){
        $.ajax({
                url:"<?php echo SURL ?>ajaxresponse/ajax2.php",
                type:"GET",
                cache:false,
                dataType:'json',
                data:{act:"get_emails_table_data",period:period,from:from,to:to},
                success: function(data){
                    //console.log("data is "+data);
                    $('#table-emails').dataTable().fnClearTable();
                    var sno=0;
                    $.each(data, function(index,element){   
                        var msg = "<span class=\"big-text\">"+element.message+"</span>";
                        sno++;
                        $('#table-emails').dataTable().fnAddData([
                            sno,
                            element.campaign_name,
                            element.name,
                            element.email,
                            element.country,
                            element.phone,
                            msg,
                            element.gender,
                            element.email_date_ae
                        ]);
                    });
                    
                    //$('#table-sms').dataTable();
                }
            });
 }
 
 
 
 
function getTitleFromPeriod(period){
    var title = "";
    if(period=='lifetime') title = "Lifetime";
    else if(period=='month') title = "Monthly";
    else if(period=='week') title = "Weekly";
    else if(period=='daily') title = "Daily";
    else if(period=='today') title = "Today";
    else if(period=='yesterday') title = "Yesterday";
    else if(period=='last_7_days') title = "Last 7 Days";
    else if(period=='last_30_days') title = "Last 30 Days";
    else if(period=='this_month') title = "This Month";
    else if(period=='last_month') title = "Last Month";
    else if(period=='custom') title = "Custom Range";
    return title;
}

 function showEmailsData(_period,from,to){
    if(_period=='') _period='lifetime';
    
    $.ajax({
            url: "<?php echo SURL ?>ajaxresponse/ajax2.php",
            type: "GET",
            cache:"false",
            data:{act:'get_emails_data',period:_period,from:from,to:to},
            dataType: 'json',beforeSend : function(){
                $('#emails-chart').addClass('chart-loader');
            },
            success: function(data){
                
                chart.setData(data);
                var title = getTitleFromPeriod(_period);
                $("#ch_title").text(title+ " Emails Breakup");
                $('#emails-chart').removeClass('chart-loader');
            }
        });
}



function lessTextAll(max_len){

    $('.big-text').each(function(){
        
        var text = $(this).text();
        if(!text.length>max_len) return;
        text = text.replace("More >>", "");
        var p1 = text.substr(0, max_len);
        var more_link = " <a href=\"#\" class=\"show-more\">More >></a>";
        var p2 = text.substr(max_len,text.length-max_len);
        var more_text = "";
        if(p2.length>0) 
            more_text = "<span class=\"more-text\">"+p2+"</span>"+more_link;
        $(this).html(p1+more_text);
    });
    
    $('.more-text').hide();
    $('.show-more').click(function(){
        var mt = $(this).prev('.more-text');
        $(mt).toggle();
        if($(mt).is(':visible')){
            $(this).text('<< Less');  
        } else if($(mt).is(':hidden')) {
            $(this).text('More >>');
        }
        return false;
    });
}



function toggleCol(ind){
    var table = $('#table-emails').DataTable();
    var column = table.column( ind );
    column.visible(! column.visible());
}

function dtToggleColsInit(){
    
    var dd_html = "<div class=\"btn-group pull-right dt-col-toggle\">";
    dd_html+="<button type=\"button\" class=\"btn btn-default dropdown-toggle\" data-toggle=\"dropdown\" aria-expanded=\"false\"> Show <span class=\"caret\"></span>";
    dd_html+="</button><ul class=\"dropdown-menu\" role=\"menu\"></ul></div>";
    
    var cols = [];
    var cols_visible = [];
    var dt = $('#table-emails').DataTable();
    dt.columns().columns().header().each(function(obj,ind){
        cols.push($(obj).text());
        if(!dt.column(ind).visible()){
            cols_visible.push("");
        }else {
            cols_visible.push("checked");
        }
    });
    var dd = $(dd_html);
    $.each(cols,function(ind, val){
        $(dd).find('ul').append('<li><input type=\"checkbox\" '+cols_visible[ind]+' onClick=\"toggleCol('+ind+');\">'+val+"</li><li class=\"divider\"></li>");
    })
    
    $('.dataTables_filter label').after(dd);
}


function dtToggleColsInit2(){
    
    var dd_html = "<div class=\"dt-coltoggle-dropdown\">Show<ul class=\"dt-coltoggle-dropdown-list\"></ul></div>";
    //var dd_html = "Show<ul class=\"dt-coltoggle-dropdown-list\"></ul>";
    var cols = [];
    var cols_visible = [];
    var dt = $('#table-emails').DataTable();
    
    //$(dt).find('.dt-coltoggle-dropdown').html(dd_html);
    
    dt.columns().columns().header().each(function(obj,ind){
        cols.push($(obj).text());
        if(!dt.column(ind).visible()){
            cols_visible.push("");
        }else {
            cols_visible.push("checked");
        }
    });
    var dd = $(dd_html);
    //var dd = $(dt).find('.dt-coltoggle-dropdown');
    $.each(cols,function(ind, val){
        $(dd).find('ul').append('<li><label><input type="checkbox" '+cols_visible[ind]+'  onClick=\"toggleCol('+ind+');\"/>'+val+'</label></li><li>');
    })
    
    $('.dataTables_filter label').css({"position":"absolute","right":"120px","float":"none","white-space":"normal"});
    $('.dataTables_filter label').after(dd);
    
    
    $(".dt-coltoggle-dropdown").click(function () {
        $(this).toggleClass("is-active");
    });

    $(".dt-coltoggle-dropdown ul").click(function(e) {
        e.stopPropagation();
    });
}

	
</script>






<div class="row">
    <div class="col-sm-4">
        <ol class="breadcrumb bc-3">
            <li> <a href="admin.php?act=dashboard"><i class="entypo-home"></i>Dashboard</a> </li>
            <li class="active"> <strong>Emails</strong> </li>
        </ol>
    </div>
    <?php    include 'bodies/request_call.php'; ?>
    <form>
        <div class="col-sm-4 fr">
            <div class="daterange daterange-inline add-ranges" data-format="MMMM D, YYYY" data-start-date="<?php echo $LM_PERIOD_FROM;?>" data-end-date="<?php echo $LM_PERIOD_To;?>"> <i class="entypo-calendar"></i> <span><?php echo $LM_PERIOD_FROM;?> - <?php echo $LM_PERIOD_TO;?></span> </div>
        </div>
    </form>
    <div class="clearfix"></div>
</div>




<h2>Recent Emails</h2>
<br />


<div class="row">
    <div class="col-md-12">
        <div id="sortResult">
            <table class="table table-bordered datatable tbl-mng-emails" id="table-emails">
                <thead>
                    <th>#</th>
                    <th>Campaign</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Country</th>
                    <th>Phone</th>
                    <th>Message</th>
                    <th>Gender</th>
                    <th>Date</th>
                </thead>
                <tbody>

                </tbody>

            </table>
        </div>
    </div>
</div>

<br>
<h2 id="ch_title">Breakup</h2>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <div class="panel-title">Chart</div>
            </div>
            <div id="emails_chart" class="chart-loader" style="height: 300px;"></div>
        </div>
    </div>
</div>


<?php include("bodies/recommend.php"); ?>