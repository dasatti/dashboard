/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */



function saveDateRange(period,date_from,date_to){
    $.ajax({
        url:"ajaxresponse/ajax2.php",
        type: "GET",
        data:{act:'save_date_range',period:period,from:date_from,to:date_to},
        success: function(data){
            console.log("Date range saved");
        }
    })
}

function getSavedDateRange(){
    var date_range;
    date_range = $.ajax({
        url:'ajaxresponse/ajax2.php',
        type:"GET",
        async:false,
        data:{act:'get_saved_date_range'},
        dataType:'json'
//        success : function(data){
//            console.log("retreiving saved date range");
//            console.log(data);
//            date_range = data;
//        }
    });
    return date_range.responseJSON;
}


function setLifetimeFilterStartDate(){
    
    var drp = $(document).find('.daterange').data('daterangepicker');
    if(!drp) return;
    $.ajax({
        url: "ajaxresponse/ajax2.php",
        type: "GET",
        cache:"false",
        data:{act:'get_campaign_start_date'},
        dataType: 'json',
        success: function(data){
            if(data!=null)
                drp.ranges.Lifetime[0] = moment(data);
        }
    });
    
}

    
jQuery(window).load(function() 
{
   setLifetimeFilterStartDate();
});
