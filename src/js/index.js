$(function(){
/* 
    $.ajax({
        type:'POST',
        url:'php/handle/.php',
        data:{
            act:"",
        },
        success:function(data){

        }
    });
 */

    var usi = {};
    usi.user_info = $('.user_info');
    usi.profile_wrap = usi.user_info.find('.profile_wrap');
    usi.name = usi.user_info.find('#name');
    usi.get_user_info = function(){
        $.ajax({
            type:'POST',
            url:'php/handle/user.php',
            data:{
                act:"userInfo"
            },
            success:function(data){
                // if(!data){}
                var _obj = JSON.parse(data);
                if(_obj){
                    TweenMax.from(usi.name,.5,{ opacity:0 });
                    TweenMax.to(usi.name,.5,{ opacity:1 });
                    usi.name.html(_obj.username);
                }
            }
        });
    };
    usi.init = function(){
        usi.get_user_info();
    };

    usi.init();


    var cat = {};
    cat.files_wrap = $('.files_wrap ');
    cat.file = cat.files_wrap.find('.file');
    cat.event = function(){
        cat.file.bind('dblclick',function(e){
            
            console.log(1);
            
        });
    };
    cat.init = function(){
        cat.event();
    };

    cat.init();
});