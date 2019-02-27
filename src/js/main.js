$(function(){
    
    var log_form_wrap = $('.log_form_wrap');
    
    set_log_loc();
    function set_log_loc(){
        var w = ($(window).width()-log_form_wrap.width())/2,
            h = ($(window).height()-log_form_wrap.height())/2;
        log_form_wrap.css('position','absolute').css('left',w).css('top',h);
    }
    
    var h = ($(window).height()-log_form_wrap.height())/2;
    TweenLite.fromTo(
        log_form_wrap, 
        .5, 
        {top:h-20,opacity:0}, 
        {top:h,opacity:1}
    );

    $(window).resize(function(){
        set_log_loc();
    });
    
});