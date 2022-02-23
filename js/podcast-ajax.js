jQuery(function($){
    var maincontent = $('#container-grid-podcast');
    var loadmore = $('#load_more_button');

    loadmore.click(function (e) {
        e.preventDefault();    
        //console.log('click in the button'); 
        var paged =  Number($('#paged').attr('value'))+Number(1);
        $.post(
            ajax_object.ajaxurl, {
            action: 'load_more_podcast',
            paged: paged
        }, function(data) {
            maincontent.animate({opacity:0.5});
            maincontent.append(data); 
            maincontent.animate({opacity:1});
            $('#paged').attr('value',paged);
        });        
    });
});