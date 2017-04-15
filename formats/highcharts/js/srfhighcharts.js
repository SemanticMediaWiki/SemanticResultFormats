(function(){
    $(document).ready(function(){
        if(window.srfhighcharts !== undefined){
            for (var key in window.srfhighcharts){
                $('#'+key).highcharts(window.srfhighcharts[key]);
            }
        }
    })
})();
