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


    // 全局环境
    var g = {
        method:null,
        id:-1,
    }



    /**
     * 用户信息对象
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



    /**
     * 目录信息对象
     */
    var cat = {};
    cat.files_con = $('.files_con');
    cat.files_loading = $('.files_loading');
    cat.file = cat.files_con.find('.file');
    cat.pre_files = cat.files_con.find('.pre_files');
    cat.cur_files = cat.files_con.find('.cur_files');
    cat.file_checkbox = cat.files_con.find('.file_checkbox');
    cat.breadcrumbs = $('#breadcrumbs');
    // loading图标
    cat.svg_loading = function(style){

        var style = style || {};
        var css_name = style.css_name ? style.css_name+' svg_loading' : 'svg_loading',
            size = style.size || 20,
            color = style.color;

        return $('\
        <svg \
            version="1.1" \
            class="'+css_name+'" \
            x="0px" y="0px" \
            width="'+size+'px" height="'+size+'px" \
            viewBox="0 0 50 50" \
            style="enable-background:new 0 0 50 50;" \
            xml:space="preserve"\
        >\
            <path \
                fill="'+color+'" \
                d="M25.251,6.461c-10.318,0-18.683,8.365-18.683,18.683h4.068c0-8.071,6.543-14.615,14.615-14.615V6.461z"\
            >\
                <animateTransform \
                    attributeType="xml" \
                    attributeName="transform" \
                    type="rotate" \
                    from="0 25 25" to="360 25 25" \
                    dur="0.6s" \
                    repeatCount="indefinite" \
                />\
            </path>\
        </svg>');
    }

    // 暂存区，存放刚刚点击过的目录的html
    cat.ts = {};

    // 生成节点
    cat.build_nodes = function(target){

        if(typeof target == 'object'){
            var act = target.attr('act');
            var fid = target.attr('files_id');
            var sign = act+'_'+fid;
            g.method = act;
            g.id = fid;
        }

        cat.files_loading.show();
        cat.files_con.html('');

        if(!cat.ts[sign]){
            // 限于后台代码没预备，暂时手动载入
            if(target == 'all_-1'){
                cat.ts['all_-1'] = 
                '<div class="file" act="user" files_id="0" title="私有目录">\
                    <div class="icon_img"><img src="lib/coloursIcon/wenjian.png" alt=""></div>\
                    <div class="filename"><a href="javascript:;">私有目录</a></div>\
                    <div class="file_checkbox"><i class="iconfont">&#xeb26;</i></div>\
                </div>\
                <div class="file" act="lesson" files_id="0" title="班级目录">\
                    <div class="icon_img"><img src="lib/coloursIcon/wenjian.png" alt=""></div>\
                    <div class="filename"><a href="javascript:;">班级目录</a></div>\
                    <div class="file_checkbox"><i class="iconfont">&#xeb26;</i></div>\
                </div>';
                cat.files_con.append(cat.ts['all_-1']);
                cat.files_con.html(cat.ts['all_-1']);
                cat.files_loading.hide();
            }else{
                $.ajax({type:'POST', url:'php/handle/data.php', data:{ act:act, fid:fid }, success:function(data){
                    var obj = JSON.parse(data),
                        files = [obj.folder,obj.file],
                        ii_path = {
                            folder:'lib/coloursIcon/wenjian.png',
                            txt:'lib/coloursIcon/txt.png',
                            unknown:'lib/coloursIcon/unknown.png',
                        };
                    var nodes = '';
                    if(obj.folder || obj.file){

                        $.each(files,function(i,list){
                            $.each(list,function(j,item){
                                var id = item.id,
                                    name = item.name,
                                    ext = name.substr(name.lastIndexOf('.')+1),
                                    icon = ii_path.folder;
                                if(i == 0){
                                    icon = ii_path.folder;
                                }else{
                                    icon = ii_path[ext] ? ii_path[ext] : ii_path.unknown;
                                }
                                nodes += 
                                '<div class="file" act="user" files_id="'+id+'" title="'+name+'">\
                                    <div class="icon_img"><img src="'+icon+'" alt=""></div>\
                                    <div class="filename"><a href="javascript:;">'+name+'</a></div>\
                                    <div class="file_checkbox"><i class="iconfont">&#xeb26;</i></div>\
                                </div>';
                            });
                        });
                        
                    }else{
                        nodes = 
                        '\
                            <p class="files_empty">\
                                <img src="img/empty.png">\
                                您还没上传过文件哦，点击<a href="javascript:;">上传</a>按钮~\
                            </p>\
                        '; 
                    }
   
                    cat.ts[sign] = nodes;
                    cat.files_con.html(nodes);
                    cat.files_loading.hide();
                }});
            }
        }else{
            cat.files_con.html(cat.ts[sign]);
            cat.files_loading.hide();
        }
    }

    // 生成面包屑
    cat.build_breadcrumbs = function(target,back){
        var name = target.attr('title');
        var id = target.attr('files_id');
        var act = target.attr('act');

        if(back == 'back'){
            if(cat.breadcrumbs.find('a:last').get()[0] != target.get()[0]){
                target.nextAll().remove();
                target.removeAttr('href');
                target.toggleClass('path_active');
            }
        }else{
            // 暂时手动做
            if( (act == 'user' && id == 0) || (act == 'lesson' && id == 0) ){
                cat.breadcrumbs.html('\
                    <a act="all" files_id="-1" class="path_active" href="javascript:;">全部文件</a>\
                    <span>&gt;</span>\
                    <a act="'+act+'" files_id="'+id+'">'+name+'</a>\
                ');
            }else if( (act == 'all' && id == -1) ){
                cat.breadcrumbs.html('<a act="all" files_id="-1">全部文件</a>');
            }else{
                var last_a = cat.breadcrumbs.find('a:last');
                last_a.attr('href','javascript:;');
                last_a.toggleClass('path_active');
                cat.breadcrumbs.append('\
                    <span>&gt;</span>\
                    <a act="'+act+'" files_id="'+id+'" >'+name+'</a>\
                ');
            }
        }
    };
    

    // 存放被选中的文件的id及相关信息，以对象为单位
    cat.checked_files = {
        length:0
    };


    // 各种事件
    cat.event = function(){
        // 文件双击事件
        cat.files_con.on('dblclick',function(e){
            var target = $(e.target).parents('.file');
            if(target){
                cat.build_nodes(target);
                cat.build_breadcrumbs(target);
                cat.checked_files = {};
            }
        });


        // 文件点击事件
        cat.files_con.on('click',function(e){
            var file = $(e.target).parents('.file');
            var checkbox = $(e.target).parent('.file_checkbox');
            file.toggleClass('file_active');
            if(checkbox[0]){
                checkbox.toggleClass('file_checkbox_ed');
            }else{
                file.find('.file_checkbox').toggleClass('file_checkbox_ed');
            }

            var key = file.attr('act')+'_'+file.attr('files_id');

            if (cat.checked_files[key]){
                cat.checked_files[key] = '';
                if (cat.checked_files.length > 0) cat.checked_files.length--;
            }else{
                cat.checked_files[key] = {
                    target:file,
                    title:file.attr('title'),
                    id: file.attr('files_id')
                };
                cat.checked_files.length++;
            }

        });


        // 面包屑
        cat.breadcrumbs.on('click',function(e){
            cat.build_nodes($(e.target));
            cat.build_breadcrumbs($(e.target),'back');
        });

        // cat.file_checkbox.on('click',function(e){
        //     console.log('cat.file_checkbox');
        //     e.stopPropagation();
        // });
    };

    // 初始化
    cat.init = function(){

        if(!cat.files_loading.html()){
            cat.files_loading.append(cat.svg_loading({size:40,color:'#8bc34a'}));
        }
        cat.build_nodes('all_-1');
        cat.event();
    };

    cat.init();


    var panel = {};
    panel.panel_btns = $('.panel_btns');
    panel.panel_btns_small = $('.panel_btns_small');
    panel.panel_btns_other = $('.panel_btns_other');
    panel.rechristen = function(){

        if (cat.checked_files.length != 1) return false; // 暂时只允许同时更改一个文件的名字

        var arr = [];
        for (var key in cat.checked_files){
            if (key == 'length') continue;
            arr.push(cat.checked_files[key]);
        }
        
        var target = arr[0];
        var e_filename = target.target.find('.filename');
        var e_filename_a = target.target.find('a');
        e_filename_a.hide();
        if (e_filename.find('.filename_input')){
            
        }
        e_filename.append('\
            <div class="filename_input">\
                <input type="text">\
                <i class="iconfont confirm" title="确认">&#xeb29;</i>\
                <i class="iconfont cancel" title="取消">&#xeb2c;</i>\
            </div>\
        ');

        var pre_name = e_filename_a.html();
        var o_filename_input = e_filename.find('.filename_input')
        var o_input = o_filename_input.find('input');
        var o_confirm_btn = o_filename_input.find('.confirm');
        var o_cancel_btn = o_filename_input.find('.cancel');
        var now_name = '';
        o_input.val(pre_name);
        o_input.focus();
        
        o_confirm_btn.on('click',function(){
            now_name = o_input.val();
            // console.log(o_input.val());
            // console.log(e_filename_a.html());
            e_filename_a.html(now_name);
            e_filename_a.show();
            o_filename_input.hide();
        });


        
        // target

        // $.ajax({
        //     type:'POST',
        //     url:'php/handle/file.php',
        //     data:{
        //         act:"rechristen",
        //         fid:g.id,
        //         method:g.method,
        //         type:'folder',
        //         name:''
        //     },
        //     success:function(data){
                
        //     }
        // });
    };
    //  云闲月赏花云闲月赏花云闲月赏花
    panel.event = function(){
        panel.panel_btns.on('click',function(e){
            var _target = $(e.target).parents('a')[0] || $(e.target)[0];
            var target = $(_target);
            var act = target.attr('act');
            switch (act){
                case 'rechristen':
                    panel.rechristen();
                    break;
            }
        });

        panel.panel_btns_small.hover(function(){
            TweenMax.to($(this),.2,{width:116});
            TweenMax.to($(this),.2,{height:170}).delay(.1);
        },function(){
            TweenMax.to($(this),.2,{height:33});
            TweenMax.to($(this),.2,{width:66}).delay(.1);
        });
    };

    panel.init = function(){
        this.event();
    };
    panel.init();



    var side = {};
    side.side_large = $('.side_large');
    side.side_small = $('.side_small');
    side.main_menu = side.side_large.find('#main_menu');
    side.event = function(){
        side.main_menu.on('click',function(e){
            var target = $(e.target);
            if(target[0].tagName != 'A' && target[0].tagName != 'I') return false;
            cat.build_nodes(target);
            cat.build_breadcrumbs(target);
        });
        side.side_small.on('click',function(e){
            var target = $(e.target)[0].tagName != 'A' ? $(e.target).parent() : $(e.target);
            cat.build_nodes(target);
            cat.build_breadcrumbs(target);
        });
    };
    side.init = function(){
        this.event();
    };
    side.init();



    var footer = {};
    footer.footer_menu = $('.footer_menu');
    footer.event = function(){
        footer.footer_menu.on('click',function(e){
            var target = $(e.target)[0].tagName != 'A' ? $(e.target).parent() : $(e.target);
            cat.build_nodes(target);
            cat.build_breadcrumbs(target);
        });
    };
    footer.init = function(){
        this.event();
    };
    footer.init();

});