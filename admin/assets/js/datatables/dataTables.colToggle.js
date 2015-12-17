/* 
    Document   : dataTables.colToggle.js
    Created on : May 5, 2015, 11:33:04 AM
    Author     : danish <dasatti@gmail.com>
    Description:
        A jQuery extension for datatables to show/hide columns.
        If you have large number of columns to display in datatable with lower space
        you can hide un-important column and user can un-hide them any time if required.

 */


(function($){



    function toggleCol2(table_div,ind){
        //alert('fdsf');
        
        var table = $(table_div).DataTable(); 
        var column = table.column( ind );
        column.visible(! column.visible());
    }



    $.fn.dtToggleCols = function(table_div){

        var dd_html = "<div class=\"dt-coltoggle-dropdown\">Show<ul class=\"dt-coltoggle-dropdown-list\"></ul></div>";
        //var dd_html = "Show<ul class=\"dt-coltoggle-dropdown-list\"></ul>";
        var cols = [];
        var cols_visible = [];
        var cols_visible_bool = [];
        
        
        var dt = $(table_div).DataTable(); 
        //$(dt).find('.dt-coltoggle-dropdown').html(dd_html);

        dt.columns().columns().header().each(function(obj,ind){
            cols.push($(obj).text());
            if(!dt.column(ind).visible()){
                cols_visible.push("");cols_visible_bool.push(false);
            }else {
                cols_visible.push("checked");cols_visible_bool.push(true);
            }
        });
        
        var dd = $(dd_html);
        //var dd = $(dt).find('.dt-coltoggle-dropdown');
        $.each(cols,function(ind, val){
            //$(dd).find('ul').append('<li><label><input type="checkbox" '+cols_visible[ind]+'  onClick=\"toggleCol2("'+table_div+'",'+ind+');\"/>'+val+'</label></li><li>');
            var cb = $(document.createElement('input')).attr({"type":"checkbox",checked:cols_visible_bool[ind]});
            $(cb).bind('click',function(){
                var column = dt.column( ind );
                column.visible(! column.visible());
            });
            $(dd).find('ul').append(
                $(document.createElement('li')).append(
                    $(document.createElement('label')).append(
                    cb,val
                    )
                )
            );
        })

        $('.dataTables_filter label').css({"position":"absolute","right":"120px","float":"none","white-space":"normal"});
        $('.dataTables_filter label').after(dd);
        $('.export-data').css({"right":"325px"});  


        $(".dt-coltoggle-dropdown").click(function () {
            $(this).toggleClass("is-active");
        });

        $(".dt-coltoggle-dropdown ul").click(function(e) {
            e.stopPropagation();
        });
    }
    
    
}(jQuery));